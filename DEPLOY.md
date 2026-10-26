# 本番反映

GitHub Actions の **本番反映（手動FTPS）** を手動実行する。pushだけではheteml本番へ転送しない。GitHub Pagesの公開は別設定。

1. サーバーのバックアップをCyberduckで保存する。
2. リポジトリの Actions → 本番反映（手動FTPS）→ Run workflow。
3. ブランチ main、mode `preview`、`.htaccess` はOFFで実行する。
4. 成功ログで接続先・公開フォルダ・転送対象を確認する。previewは読み取りのみで、差分比較は行わない。
5. 反映するときは同じ画面でmode `publish` を選ぶ。完了後、本番の表示とお問い合わせフォームを確認する。

接続先は `ftp-shinya-vto.heteml.net:21`、明示的FTPS、ユーザー `shinya-vto`、公開先 `/web/vto_2026ver/` に固定。パスワードはリポジトリの Settings → Secrets and variables → Actions → Repository secrets の `FTP_PASSWORD` に登録する。

転送対象は公開HTML・contact.php・CSS・JS・そこから参照されるassetsのみ。下書き・Git情報・転送スクリプトは含めない。対象ファイルは毎回すべて転送し、サーバーにだけあるファイルは削除しない。.htaccessは通常上書きせず、変更が必要なときだけチェックを入れる。

ファイル単位で一時ファイルからリネームするため、サイト全体の同時切替ではない。失敗時はログを確認し、必要ならバックアップをCyberduckで戻す。接続失敗時に証明書検証やTLSを無効化しない。
