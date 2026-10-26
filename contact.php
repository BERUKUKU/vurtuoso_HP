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

// 確認・エラー画面で共通のブランド表示を使用する。
$renderStart = function (string $title, string $step) use ($h): void {
    echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow"><title>' . $h($title) . ' | VIRTUOSO</title><link rel="stylesheet" href="css/contact-response.css"></head><body><header class="response-header"><a href="./" aria-label="VIRTUOSO トップページ"><img src="assets/images/VIRTUOSO-wordmark.svg" alt="VIRTUOSO" width="1600" height="237"></a><a class="header-contact" href="contact.html">CONTACT</a></header><main><section class="response-hero" aria-label="お問い合わせ"><p>CONTACT</p><span>' . $h($step) . '</span></section><div class="response-content"><section class="confirm-card" aria-labelledby="response-title"><p class="eyebrow">' . $h($step) . '</p><h1 id="response-title">' . $h($title) . '</h1>';
};
$renderEnd = function (): void {
    echo '</section><footer class="response-footer">株式会社ヴァーチュオーゾ</footer></div></main></body></html>';
};

// エラーがある場合
if (!empty($errors)) {
    $renderStart('入力内容をご確認ください', 'CHECK YOUR DETAILS');
    echo '<p class="response-intro">以下の項目を修正して、もう一度お進みください。</p><ul class="error-list">';
    foreach ($errors as $e) {
        echo '<li>' . $h($e) . '</li>';
    }
    echo '</ul><div class="actions"><button class="back" type="button" onclick="history.back();">入力画面に戻る</button><a class="text-link" href="contact.html">お問い合わせページへ</a></div>';
    $renderEnd();
    exit;
}

// 確認画面（初回送信時）
if (!isset($_POST['confirm'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $renderStart('送信内容の確認', '02 / CONFIRM');
    echo '<p class="response-intro">内容をご確認のうえ、「この内容で送信する」を押してください。</p><ol class="response-steps" aria-label="お問い合わせの進行状況"><li>01 入力</li><li aria-current="step">02 確認</li><li>03 完了</li></ol><dl><dt>お問い合わせ項目</dt><dd>' . $h($categoryLabel) . '</dd><dt>お名前</dt><dd>' . $h($name) . '</dd><dt>法人様名</dt><dd>' . ($company !== '' ? $h($company) : '（未入力）') . '</dd><dt>メールアドレス</dt><dd>' . $h($email) . '</dd><dt>お問い合わせ内容</dt><dd>' . $h($message) . '</dd></dl><div class="actions"><form method="post" style="margin:0;"><input type="hidden" name="confirm" value="1"/><input type="hidden" name="csrf_token" value="' . $h($_SESSION['csrf_token']) . '"/><input type="hidden" name="website" value="' . $h($input('website')) . '"/><input type="hidden" name="category" value="' . $h($category) . '"/><input type="hidden" name="name" value="' . $h($name) . '"/><input type="hidden" name="company" value="' . $h($company) . '"/><input type="hidden" name="email" value="' . $h($email) . '"/><input type="hidden" name="emailConfirm" value="' . $h($emailConfirm) . '"/><input type="hidden" name="message" value="' . $h($message) . '"/><input type="hidden" name="privacy" value="1"/><button type="submit" class="send">この内容で送信する <span aria-hidden="true">↗</span></button></form><button class="back" type="button" onclick="history.back();">修正する</button></div>';
    $renderEnd();
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
