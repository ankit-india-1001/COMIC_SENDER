<?php
require 'vendor/autoload.php';
include 'dbcon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function fetchRandomComic() {
    $latest = json_decode(file_get_contents("https://xkcd.com/info.0.json"), true);
    if (!$latest || !isset($latest['num'])) return false;

    $rand_num = rand(1, $latest['num']);
    return json_decode(file_get_contents("https://xkcd.com/$rand_num/info.0.json"), true);
}

function sendComicToAll($conn, $comic) {
    $title = $comic['title'];
    $img = $comic['img'];
    $alt = $comic['alt'];
    $link = "https://xkcd.com/" . $comic['num'];

    $body = "
    <div style='font-family: Arial, sans-serif; padding: 10px;'>
        <h2>$title</h2>
        <a href='$link' target='_blank'>
            <img src='$img' alt='$alt' style='max-width:100%; height:auto;'>
        </a>
        <p>$alt</p>
        <hr>
        <p style='font-size:13px;'>Sent via XKCD Comic Email System</p>
    </div>";

    $query = "SELECT email FROM email_verification";
    $result = mysqli_query($conn, $query);

    $status = "";

    while ($row = mysqli_fetch_assoc($result)) {
        $email = $row['email'];
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ankitpoddertmsl@gmail.com';
            $mail->Password = 'pqjv eoms sdef hswl'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ankitpoddertmsl@gmail.com', 'XKCD Comics');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "XKCD Comic - $title";
            $mail->Body = $body;

            $mail->send();
            $status .= "✅ Sent to $email<br>";
        } catch (Exception $e) {
            $status .= "❌ Failed to send to $email: {$mail->ErrorInfo}<br>";
        }
    }
    return $status;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send XKCD Comic</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial; background: #f9f9f9; text-align: center; padding: 50px; }
        .btn {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .result {
            margin-top: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <h1>Send XKCD Comic to All Registered Emails</h1>
    <form method="POST">
        <button type="submit" name="send" class="btn">Send Comic</button>
    </form>
        
    <?php
    if (isset($_POST['send'])) {
        $comic = fetchRandomComic();
        if ($comic) {
            $output = sendComicToAll($conn, $comic);
            echo "<div class='result'>$output</div>";
        } else {
            echo "<div class='result'>❌ Failed to fetch XKCD comic</div>";
        }
    }
    ?>
</body>
</html>
