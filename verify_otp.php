<?php
session_start();
include 'dbcon.php';

if (isset($_POST['verify'])) {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];

    $insert = mysqli_query($conn, "INSERT INTO email_verification(email, password, is_verified) VALUES('$email', '$password', 1)");

    if ($insert) {
        // Redirect to thank-you page
        header("Location: thankyou.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to insert user.";
    }
}


    header("Location: register.php");
    exit();
}
?>
