<?php
// contact.php（フォーム一体型・おしゃれなHTMLベース）
$to = 'info@vto.co.jp';
$error = [];
$name = '';
$email = '';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '') {
        $error['name'] = 'お名前を入力してください。';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = '有効なメールアドレスを入力してください。';
    }
    if ($message === '') {
        $error['message'] = 'お問い合わせ内容を入力してください。';
    }
    if (empty($error)) {
        $subject = '【サイトお問い合わせ】【お問い合わせ】Virtuoso Inc. サイトより';
        $body = "お名前: $name\nメールアドレス: $email\n\nお問い合わせ内容:\n$message";
        $headers = "From: $email\r\nReply-To: $email\r\n";
        mb_language('ja');
        mb_internal_encoding('UTF-8');
        $mail_sent = mb_send_mail($to, $subject, $body, $headers);
        if ($mail_sent) {
            $success = true;
        } else {
            $error['send'] = '送信に失敗しました。時間をおいて再度お試しください。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ | Virtuoso Inc.</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #f8fafc; }
        .contact-section { padding: 4rem 0; }
        .contact-form { background: #fff; border-radius: 1.2rem; box-shadow: 0 8px 32px rgba(168,85,247,0.08); padding: 2.5rem 2rem; max-width: 480px; margin: 2rem auto; }
        .contact-form label { font-weight: 500; color: #a855f7; }
        .contact-form input, .contact-form textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.8rem; margin-top: 0.3rem; margin-bottom: 1.2rem; font-size: 1rem; background: #f9fafb; transition: border 0.2s; }
        .contact-form input:focus, .contact-form textarea:focus { border: 1.5px solid #a855f7; outline: none; background: #fff; }
        .contact-form button { background: linear-gradient(90deg,#f472b6,#a855f7,#3b82f6); color: #fff; border: none; border-radius: 2rem; padding: 0.9rem 2.5rem; font-size: 1.1rem; font-weight: 600; letter-spacing: 0.1em; cursor: pointer; box-shadow: 0 2px 12px rgba(168,85,247,0.12); transition: background 0.2s, transform 0.1s; }
        .contact-form button:hover { background: linear-gradient(90deg,#a855f7,#f472b6,#3b82f6); transform: translateY(-2px) scale(1.03); }
        .form-group { margin-bottom: 1.5rem; }
        .form-error { color: #ef4444; font-size: 0.95em; margin-top: -1rem; margin-bottom: 1rem; }
        .contact-thanks { text-align: center; background: #fff; border-radius: 1.2rem; box-shadow: 0 8px 32px rgba(168,85,247,0.08); padding: 3rem 2rem; max-width: 480px; margin: 2rem auto; color: #a855f7; font-size: 1.2rem; }
        .contact-title { text-align: center; font-size: 2rem; font-weight: 600; color: #a855f7; margin-bottom: 2rem; letter-spacing: 0.1em; }
    </style>
</head>
<body>
    <section class="contact-section" id="contact">
        <div class="container-small">
            <div class="contact-title">CONTACT</div>
            <?php if ($success): ?>
                <div class="contact-thanks">
                    <p>お問い合わせありがとうございました。<br>内容を確認の上、担当者よりご連絡いたします。</p>
                </div>
            <?php else: ?>
                <?php if (!empty($error['send'])): ?>
                    <div class="form-error">送信に失敗しました。時間をおいて再度お試しください。</div>
                <?php endif; ?>
                <form class="contact-form" method="post" action="">
                    <div class="form-group">
                        <label for="name">お名前<span style="color:#ef4444;">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($error['name'])): ?><div class="form-error"><?php echo $error['name']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">メールアドレス<span style="color:#ef4444;">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($error['email'])): ?><div class="form-error"><?php echo $error['email']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="message">お問い合わせ内容<span style="color:#ef4444;">*</span></label>
                        <textarea id="message" name="message" rows="6"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <?php if (!empty($error['message'])): ?><div class="form-error"><?php echo $error['message']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group" style="text-align:center;">
                        <button type="submit">送信する</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
