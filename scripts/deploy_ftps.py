#!/usr/bin/env python3
"""Manually preview or publish the website over explicit FTPS (no remote deletes)."""
import argparse
import ftplib
import hashlib
import os
from pathlib import Path, PurePosixPath
import re
import shutil
import ssl
import tempfile

ROOT = Path(__file__).resolve().parents[1]
HOST = 'ftp-shinya-vto.heteml.net'
USER = 'shinya-vto'
REMOTE = '/web/vto_2026ver'
PAGES = ('index.html', 'about.html', 'company.html', 'contact.html', 'privacy.html', 'thank-you.html', 'contact.php')


def build_bundle(destination, include_htaccess=False):
    files = {Path(name) for name in PAGES}
    for directory, suffix in [('css', '.css'), ('js', '.js')]:
        files.update(path.relative_to(ROOT) for path in (ROOT / directory).rglob('*' + suffix))
    # Only public source and the media referenced by it; no Git, scripts, prompts or drafts.
    for relative in tuple(files):
        text = (ROOT / relative).read_text(encoding='utf-8')
        for asset in re.findall(r'assets/[A-Za-z0-9_./-]+', text):
            path = Path(asset)
            if '..' in path.parts or not (ROOT / path).is_file():
                raise ValueError(f'Missing or invalid public asset: {asset}')
            files.add(path)
    if include_htaccess:
        files.add(Path('.htaccess'))
    for relative in files:
        source = ROOT / relative
        if source.is_symlink() or not source.is_file():
            raise ValueError(f'Invalid deployment source: {relative}')
        target = destination / relative
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(source, target)
    # Media first, then styles/scripts, then PHP/HTML; index.html is published last.
    def order(path):
        name = path.as_posix()
        rank = 3 if name == 'index.html' else 2 if path.suffix in ('.html', '.php') else 1 if path.suffix in ('.css', '.js') else 0
        return rank, name
    return sorted(files, key=order)


class SessionReuseFTP(ftplib.FTP_TLS):
    """Reuse the control TLS session on protected passive data connections."""
    def ntransfercmd(self, cmd, rest=None):
        connection, size = ftplib.FTP.ntransfercmd(self, cmd, rest)
        if self._prot_p:
            try:
                connection = self.context.wrap_socket(
                    connection, server_hostname=self.host, session=self.sock.session)
            except Exception:
                connection.close()
                raise
        return connection, size


def publish_files(ftp, bundle, files):
    created = set()
    for relative in files:
        parent = PurePosixPath(relative.as_posix()).parent
        for directory in reversed((parent, *parent.parents)):
            name = str(directory)
            if name == '.' or name in created:
                continue
            try:
                ftp.mkd(name)
            except ftplib.error_perm:
                # A 550 may also mean no permission: verify the directory really exists.
                ftp.cwd(name)
                ftp.cwd(REMOTE)
            created.add(name)
        target = relative.as_posix()
        digest = hashlib.sha256((bundle / relative).read_bytes()).hexdigest()[:12]
        # Preserve .php extension so a partial upload cannot expose PHP source.
        temporary = str(PurePosixPath(target).with_name('.deploy-' + digest + '-' + relative.name))
        print(f'UPLOAD {target}', flush=True)
        with (bundle / relative).open('rb') as source:
            ftp.storbinary('STOR ' + temporary, source)
        ftp.rename(temporary, target)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--mode', choices=('bundle', 'preview', 'publish'), default='preview')
    parser.add_argument('--include-htaccess', action='store_true')
    args = parser.parse_args()
    with tempfile.TemporaryDirectory(prefix='virtuoso-site-') as temporary:
        bundle = Path(temporary)
        files = build_bundle(bundle, args.include_htaccess)
        total = sum((bundle / relative).stat().st_size for relative in files)
        print(f'Mode: {args.mode}; destination: {HOST}:{REMOTE}/')
        print(f'{len(files)} public files; {total / 1024 / 1024:.1f} MiB')
        print('.htaccess: ' + ('included' if args.include_htaccess else 'preserve server copy'))
        for relative in files:
            print(f'PLAN {relative.as_posix()}')
        if args.mode == 'bundle':
            return
        password = os.environ.get('FTP_PASSWORD')
        if not password:
            raise ValueError('FTP_PASSWORD secret is not configured')
        with SessionReuseFTP(context=ssl.create_default_context(), timeout=60) as ftp:
            ftp.connect(HOST, 21)
            ftp.login(USER, password)
            ftp.prot_p()
            ftp.set_pasv(True)
            # Never auto-create the destination root or fall back to a different folder.
            ftp.cwd(REMOTE)
            names = {PurePosixPath(name).name for name in ftp.nlst()}
            if not {'index.html', 'contact.php'}.issubset(names):
                raise ValueError('Destination does not contain the expected site files')
            print('FTPS login and existing public directory verified.', flush=True)
            if args.mode == 'preview':
                print('PREVIEW ONLY: no files uploaded, renamed or deleted.')
            else:
                publish_files(ftp, bundle, files)
                print('Published successfully. Remote files not present in the bundle were preserved.')
        summary = os.environ.get('GITHUB_STEP_SUMMARY')
        if summary:
            with open(summary, 'a', encoding='utf-8') as output:
                output.write(f'## VIRTUOSO: {args.mode}\n\n')
                output.write(f'- Destination: `{REMOTE}/`\n- Files: {len(files)}\n')
                output.write(f'- Commit: `{os.environ.get("GITHUB_SHA", "local")}`\n')
                output.write('- Remote deletion: disabled\n')
                output.write('- .htaccess: ' + ('included' if args.include_htaccess else 'preserved') + '\n')


if __name__ == '__main__':
    main()
