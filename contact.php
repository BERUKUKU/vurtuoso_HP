<?php
// 本番運用：エラーを画面に出さない（ログには残す）
ini_set('display_errors', '0');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
]);

// HTML出力時のみ使用するエスケープ関数（受信時にはエスケープしない）
$h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

// 受信値：文字列以外は空扱い。trim のみ（$singleLine=true なら CR/LF も除去）
$input = function (string $key, bool $singleLine = true): string {
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = trim($v);
    if ($singleLine) {
        // ヘッダーインジェクション対策：改行を除去
        $v = trim(str_replace(["\r", "\n"], '', $v));
    } else {
        $v = str_replace(["\r\n", "\r"], "\n", $v);
    }
    return $v;
};

// ハニーポット：人間には見えない欄に値があればボットとみなし、成功を装って終了
if ($input('website') !== '') {
    header('Location: thank-you.html');
    exit;
}

// フォームデータの取得
$category = $input('category');
$name = $input('name');
$company = $input('company');
$email = $input('email');
$emailConfirm = $input('emailConfirm');
$message = $input('message', false);
$privacy = isset($_POST['privacy']);

// カテゴリーの日本語変換（早めに用意しておく）
$categoryLabels = [
    'website' => 'WEBサイト制作について',
    'renewal' => 'サイトリニューアルについて',
    'ecsite' => 'EC SITE制作について',
    'system' => 'システム開発について',
    'sns' => 'SNS運用について',
    'ad' => '広告のご相談',
    'marketing' => 'マーケティング戦略のご相談',
    'other' => 'その他'
];
$categoryLabel = $categoryLabels[$category] ?? '';

// バリデーション
$errors = [];

if (!array_key_exists($category, $categoryLabels)) {
    $errors[] = 'お問い合わせ項目を選択してください。';
}

if ($name === '') {
    $errors[] = 'お名前を入力してください。';
}

if ($email === '') {
    $errors[] = 'メールアドレスを入力してください。';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'メールアドレスの形式が正しくありません。';
}

if ($emailConfirm === '') {
    $errors[] = '確認用メールアドレスを入力してください。';
} elseif ($email !== $emailConfirm) {
    $errors[] = 'メールアドレスと確認用メールアドレスが一致しません。';
}

if ($message === '') {
    $errors[] = 'お問い合わせ内容を入力してください。';
}

if (!$privacy) {
    $errors[] = 'プライバシーポリシーに同意してください。';
}

// エラーがある場合
if (!empty($errors)) {
    // シンプルなエラー画面（赤字表示＋戻るボタン）
    echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>入力内容に不備があります</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;min-height:100vh;margin:0;background:linear-gradient(135deg, rgba(252,231,243,0.5) 0%, rgba(243,232,255,0.55) 50%, rgba(239,246,255,0.5) 100%);display:flex;align-items:center;justify-content:center;padding:2rem;overflow:hidden;position:relative;color:#1f2937;} .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:0.35;} .orb.o1{width:360px;height:360px;background:linear-gradient(135deg, rgba(249,168,212,0.45), rgba(216,180,254,0.45));top:-80px;left:-60px;} .orb.o2{width:400px;height:400px;background:linear-gradient(135deg, rgba(147,197,253,0.4), rgba(249,168,212,0.4));bottom:-120px;right:-80px;} .card{position:relative;z-index:1;max-width:780px;width:100%;background:rgba(255,255,255,0.78);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,0.7);border-radius:20px;box-shadow:0 18px 48px rgba(0,0,0,0.12);padding:2.25rem;} h1{font-size:1.35rem;margin:0 0 1.25rem;color:#b91c1c;letter-spacing:0.01em;} ul{margin:0 0 1.5rem;padding-left:1.25rem;color:#b91c1c;font-weight:600;line-height:1.6;} .actions{display:flex;gap:0.75rem;flex-wrap:wrap;} .btn{border:none;border-radius:12px;padding:0.9rem 1.4rem;font-weight:600;cursor:pointer;transition:transform 0.15s ease, box-shadow 0.2s ease;} .btn-primary{color:#fff;background:linear-gradient(90deg,#f472b6,#a855f7,#3b82f6);box-shadow:0 12px 24px rgba(168,85,247,0.35);} .btn-primary:hover{transform:translateY(-1px);box-shadow:0 16px 32px rgba(168,85,247,0.4);} .btn-ghost{background:rgba(255,255,255,0.9);color:#374151;border:1px solid rgba(168,85,247,0.25);} .btn-ghost:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(0,0,0,0.08);} @media(max-width:640px){body{padding:1.25rem;} .card{padding:1.75rem;} .actions{flex-direction:column;} .btn{width:100%;text-align:center;}}</style></head><body><div class="orb o1"></div><div class="orb o2"></div><div class="card"><h1>入力内容に不備があります</h1><ul>';
    foreach ($errors as $e) {
        echo '<li>' . $h($e) . '</li>';
    }
    echo '</ul><div class="actions"><button class="btn btn-primary" type="button" onclick="history.back();">入力画面に戻る</button><a class="btn btn-ghost" href="/contact.html">お問い合わせトップへ</a></div></div></body></html>';
    exit;
}

// 確認画面（初回送信時）
if (!isset($_POST['confirm'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>送信内容の確認</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;background:#f8fafc;color:#111827;padding:2rem;} .confirm-card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.08);padding:2rem;} h1{font-size:1.4rem;margin-bottom:1.2rem;color:#111827;} dl{margin:0;} dt{font-weight:600;margin-top:1rem;color:#4b5563;} dd{margin:0.25rem 0 0.75rem 0;white-space:pre-wrap;word-break:break-word;} .actions{display:flex;gap:0.75rem;margin-top:1.5rem;} button{border:none;border-radius:10px;padding:0.85rem 1.4rem;font-weight:600;cursor:pointer;} .send{background:linear-gradient(90deg,#f472b6,#a855f7,#3b82f6);color:#fff;box-shadow:0 8px 20px rgba(168,85,247,0.25);} .back{background:#e5e7eb;color:#111827;}</style></head><body><div class="confirm-card"><h1>この内容で送信してよろしいですか？</h1><dl><dt>お問い合わせ項目</dt><dd>' . $h($categoryLabel) . '</dd><dt>お名前</dt><dd>' . $h($name) . '</dd><dt>法人様名</dt><dd>' . ($company !== '' ? $h($company) : '（未入力）') . '</dd><dt>メールアドレス</dt><dd>' . $h($email) . '</dd><dt>お問い合わせ内容</dt><dd>' . $h($message) . '</dd></dl><div class="actions"><form method="post" style="margin:0;"><input type="hidden" name="confirm" value="1"/><input type="hidden" name="csrf_token" value="' . $h($_SESSION['csrf_token']) . '"/><input type="hidden" name="website" value="' . $h($input('website')) . '"/><input type="hidden" name="category" value="' . $h($category) . '"/><input type="hidden" name="name" value="' . $h($name) . '"/><input type="hidden" name="company" value="' . $h($company) . '"/><input type="hidden" name="email" value="' . $h($email) . '"/><input type="hidden" name="emailConfirm" value="' . $h($emailConfirm) . '"/><input type="hidden" name="message" value="' . $h($message) . '"/><input type="hidden" name="privacy" value="1"/><button type="submit" class="send">送信する</button></form><button class="back" type="button" onclick="history.back();">修正する</button></div></div></body></html>';
    exit;
}

// CSRFトークン検証（確認画面→送信）
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postedToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
if ($sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(400);
    echo '不正なリクエストです。お手数ですが、最初から入力し直してください。';
    exit;
}
unset($_SESSION['csrf_token']); // ワンタイム化（二重送信防止）

// メール文字コード設定：mb_send_mail に統一する。
// mb_language('uni') は UTF-8 + Base64 で件名・本文をエンコードし、
// MIME-Version / Content-Type / Content-Transfer-Encoding も自動付与する。
// そのため手動で Content-Type 等を付けると二重定義・文字化けの原因になるので指定しない。
// 'Japanese'(ISO-2022-JP) にしないのは、絵文字・丸数字などが '?' に化けるのを避けるため。
mb_language('uni');
mb_internal_encoding('UTF-8');
$to = 'info@vto.co.jp'; // 受信先メールアドレス
$subject = '【サイトお問い合わせ】【お問い合わせ】' . $categoryLabel . ' - ' . $name . '様より';

// メール本文作成
$body = "お問い合わせがありました。\n\n";
$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$body .= "■ お問い合わせ項目\n";
$body .= $categoryLabel . "\n\n";
$body .= "■ お名前\n";
$body .= $name . "\n\n";
$body .= "■ 法人様名\n";
$body .= ($company !== '' ? $company : '（未入力）') . "\n\n";
$body .= "■ メールアドレス\n";
$body .= $email . "\n\n";
$body .= "■ お問い合わせ内容\n";
$body .= $message . "\n";
$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";


// メールヘッダー（From固定。Reply-Toはバリデーション済みのemailのみ）
$headers = [];
$headers[] = 'From: ' . mb_encode_mimeheader('Virtuoso お問い合わせフォーム', 'UTF-8', 'B', "\r\n") . ' <info@vto.co.jp>';
$headers[] = 'Reply-To: ' . $email;

// メール送信
$mailSent = mb_send_mail($to, $subject, $body, implode("\r\n", $headers));

if ($mailSent) {
    // 自動返信メール（お客様宛）
    $autoReplySubject = '【自動返信】お問い合わせを受け付けました - 株式会社ヴァーチュオーゾ';
    $autoReplyBody = $name . " 様\n\n";
    $autoReplyBody .= "この度は、株式会社ヴァーチュオーゾへお問い合わせいただき、誠にありがとうございます。\n\n";
    $autoReplyBody .= "以下のお問い合わせを受け付けました。\n";
    $autoReplyBody .= "担当者より折り返しご連絡させていただきますので、今しばらくお待ちください。\n\n";
    $autoReplyBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $autoReplyBody .= "■ お名前\n";
    $autoReplyBody .= $name . "\n\n";
    $autoReplyBody .= "■ お問い合わせ項目\n";
    $autoReplyBody .= $categoryLabel . "\n";
    $autoReplyBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $autoReplyBody .= "※本メールは自動送信です。お心当たりのない場合は、お手数ですが破棄してください。\n\n";
    $autoReplyBody .= "───────────────────────────\n";
    $autoReplyBody .= "株式会社ヴァーチュオーゾ\n";
    $autoReplyBody .= "〒107-0062 東京都港区南青山2丁目2-15-1214\n";
    $autoReplyBody .= "Email: info@vto.co.jp\n";
    $autoReplyBody .= "Website: https://vto.co.jp\n";
    $autoReplyBody .= "───────────────────────────\n";

    $autoReplyHeaders = [];
    $autoReplyHeaders[] = 'From: ' . mb_encode_mimeheader('株式会社ヴァーチュオーゾ', 'UTF-8', 'B', "\r\n") . ' <info@vto.co.jp>';

    mb_send_mail($email, $autoReplySubject, $autoReplyBody, implode("\r\n", $autoReplyHeaders));

    header('Location: thank-you.html');
    exit;
} else {
    http_response_code(500);
    echo 'メール送信に失敗しました。';
}
?>
