import contextlib
import io
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import MagicMock, patch

import deploy_ftps as deploy


class DeployTests(unittest.TestCase):
    def test_public_bundle_and_order(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            files = deploy.build_bundle(root)
            self.assertEqual(files[-1], Path('index.html'))
            self.assertIn(Path('contact.php'), files)
            self.assertNotIn(Path('.htaccess'), files)
            self.assertTrue(any(p.parts[0] == 'assets' for p in files))
            self.assertFalse(any(p.parts[0] in ('.git', 'scripts', '.github') for p in files))
            self.assertNotIn(Path('contact2.php'), files)
            self.assertTrue(all((root / p).is_file() for p in files))
            self.assertIn(Path('.htaccess'), deploy.build_bundle(root, True))

    def test_preview_is_read_only_and_checks_destination(self):
        for names, valid in [(['index.html', 'contact.php'], True), (['other.html'], False)]:
            ftp = MagicMock()
            ftp.nlst.return_value = names
            factory = MagicMock()
            factory.return_value.__enter__.return_value = ftp
            with patch.object(deploy, 'SessionReuseFTP', factory), patch.dict(os.environ, {'FTP_PASSWORD': 'test-only'}), patch('sys.argv', ['deploy', '--mode', 'preview']), contextlib.redirect_stdout(io.StringIO()):
                if valid:
                    deploy.main()
                else:
                    with self.assertRaises(ValueError):
                        deploy.main()
            ftp.cwd.assert_called_once_with(deploy.REMOTE)
            ftp.prot_p.assert_called_once()
            for operation in ('mkd', 'storbinary', 'rename', 'delete', 'rmd'):
                getattr(ftp, operation).assert_not_called()

    def test_upload_renames_after_transfer_without_deletion(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / 'contact.php').write_text('<?php exit;')
            ftp = MagicMock()
            with contextlib.redirect_stdout(io.StringIO()):
                deploy.publish_files(ftp, root, [Path('contact.php')])
            temporary, target = ftp.rename.call_args.args
            self.assertEqual(target, 'contact.php')
            self.assertTrue(temporary.endswith('.php'))
            self.assertTrue(temporary.startswith('.deploy-'))
            self.assertEqual(ftp.storbinary.call_args.args[0], 'STOR ' + temporary)
            self.assertEqual([c[0] for c in ftp.method_calls], ['storbinary', 'rename'])


if __name__ == '__main__':
    unittest.main()
