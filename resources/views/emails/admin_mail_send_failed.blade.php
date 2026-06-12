<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メール送信失敗通知</title>
</head>
<body>
    <p>以下のメール送信に失敗しました。メールアドレスをご確認ください。</p>
    <ul>
        <li>会員ID：{{ $details['member_id'] }}</li>
        <li>送信先：{{ $details['failed_to'] }}</li>
        <li>エラー：{{ $details['error'] }}</li>
    </ul>
</body>
</html>   