<?php
session_start();
require 'vendor/autoload.php';
include 'dbcon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ankitpoddertmsl@gmail.com';
        $mail->Password = 'pqjv eoms sdef hswl';  // Use Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ankitpoddertmsl@gmail.com', 'Unsubscribe Confirmation');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Unsubscribe OTP';
        $mail->Body = "Your OTP to unsubscribe is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to send OTP. {$mail->ErrorInfo}";
        return false;
    }
}

if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];

    $check = mysqli_query($conn, "SELECT * FROM email_verification WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['unsub_email'] = $email;
        $_SESSION['unsub_otp'] = rand(100000, 999999);
        if (sendOTP($email, $_SESSION['unsub_otp'])) {
            $_SESSION['step'] = "verify";
        }
    } else {
        $_SESSION['error'] = "Email not found!";
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    if ($_SESSION['unsub_otp'] == $entered_otp) {
        $email = $_SESSION['unsub_email'];
        mysqli_query($conn, "DELETE FROM email_verification WHERE email='$email'");
        $_SESSION['success'] = "You have been unsubscribed!";
        unset($_SESSION['step']);
        unset($_SESSION['unsub_email']);
        unset($_SESSION['unsub_otp']);
    } else {
        $_SESSION['error'] = "Incorrect OTP!";
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial; background: #f4f4f4; text-align: center; padding-top: 60px; }
        .card {
            background: white;
            width: 400px;
            padding: 30px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px #ccc;
        }
        input {
            padding: 10px;
            width: 100%;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #aaa;
        }
        input[type="submit"] {
            background: #dc3545;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #c82333;
        }
        .msg { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Unsubscribe from Email List</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="msg" style="color:red"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="msg" style="color:green"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['step'])): ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="submit" name="send_otp" value="Send OTP">
            </form>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="otp" placeholder="Enter 6-digit OTP" required>
                <input type="submit" name="verify_otp" value="Confirm Unsubscribe">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
