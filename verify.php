<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter OTP</title>
    <style>
        body {
            background: linear-gradient(135deg, #FFDEE9, #B5FFFC);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        .card h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type=number] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        input[type=submit] {
            background: #007BFF;
            color: #fff;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type=submit]:hover {
            background: #0056b3;
        }
        .msg {
            text-align: center;
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Verify OTP</h2>
        <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='msg'>" . $_SESSION['error'] . "</div>";
                unset($_SESSION['error']);
            }
        ?>
        <form action="verify_otp.php" method="POST">
            <input type="number" name="otp" placeholder="Enter 6-digit OTP" required>
            <input type="submit" name="verify" value="Verify OTP">
        </form>
    </div>
</body>
</html>
