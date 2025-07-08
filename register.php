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
        $mail->Username = 'ankitpoddertmsl@gmail.com'; // Replace with your Gmail
        $mail->Password = 'pqjv eoms sdef hswl';        // Replace with App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ankitpoddertmsl@gmail.com', 'Email Verification');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP is: <b>$otp</b><br><br>
                          If you wish to unsubscribe, <a href='http://localhost:8000/EMAIL/unsubscribe.php?'>click here</a>.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $_SESSION['error'] = "Email could not be sent. {$mail->ErrorInfo}";
        return false;
    }
}

// On form submission
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $otp = rand(100000, 999999);

    $check = mysqli_query($conn, "SELECT * FROM email_verification WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Email already registered!";
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        $_SESSION['otp'] = $otp;

        // if (sendOTP($email, $otp)) {
        //     header("Location: verify.php");
        //     exit();
        // }
        if (sendOTP($email, $otp)) {
        // Append to register.txt
        $logFile = __DIR__ . "/register.txt";
        file_put_contents($logFile, $email . PHP_EOL, FILE_APPEND | LOCK_EX);

        header("Location: verify.php");
        exit();
}

    }
}
?>

<!-- HTML UI -->
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .extra-button {
            background: #dc3545;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .extra-button:hover {
            background: #c82333;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
        }
        .msg.success {
            color: green;
        }
        .msg.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Email OTP Verification</h2>

        <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='msg error'>".$_SESSION['error']."</div>";
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['success'])) {
                echo "<div class='msg success'>".$_SESSION['success']."</div>";
                unset($_SESSION['success']);
            }

            if (isset($_GET['unsubscribed'])) {
                echo "<div class='msg success'>You have been unsubscribed successfully.</div>";
            }
        ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="submit" name="submit" value="Send OTP">
        </form>

        <!-- Redirects to Unsubscribe System -->
        <form action="unsubscribe.php" method="get">
            <input type="submit" value="Unsubscribe" class="extra-button">
        </form>

    </div>
</body>
</html>
