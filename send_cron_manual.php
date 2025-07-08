<?php
require 'vendor/autoload.php';
include 'dbcon.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$interval_seconds = 60; // 60 = 1 min

$stop_file = '../stop_cron.txt';
$stop_status = file_exists($stop_file) ? trim(file_get_contents($stop_file)) : '0';

function fetchRandomComic() {
    $latest = json_decode(file_get_contents("https://xkcd.com/info.0.json"), true);
    if (!$latest || !isset($latest['num'])) return false;
    $rand = rand(1, $latest['num']);
    return json_decode(file_get_contents("https://xkcd.com/$rand/info.0.json"), true);
}

function sendComicToAll($conn, $comic) {
    $title = $comic['title'];
    $img = $comic['img'];
    $alt = $comic['alt'];
    $num = $comic['num'];
    $url = "https://xkcd.com/$num";

    $body = "
        <h2>$title</h2>
        <a href='$url'><img src='$img' alt='$alt' style='max-width:100%'></a>
        <p>$alt</p>
        <hr><p style='font-size:13px;'>To unsubscribe, click <a href='http://localhost/EMAIL/unsubscribe.php'>here</a>.</p>";

    $status = [];
    $res = mysqli_query($conn, "SELECT email FROM email_verification WHERE is_verified = 1");
    while ($row = mysqli_fetch_assoc($res)) {
        $email = $row['email'];
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ankitpoddertmsl@gmail.com';
            $mail->Password = 'pqjv eoms sdef hswl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ankitpoddertmsl@gmail.com', 'XKCD Comics');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "🗞️ XKCD Comic - $title";
            $mail->Body = $body;
            $mail->send();

            $status[] = "✅ Comic sent to $email";
        } catch (Exception $e) {
            $status[] = "❌ Failed to $email: {$mail->ErrorInfo}";
        }
    }

    return $status;
}

$statusList = [];
if ($stop_status === '0') {
    $comic = fetchRandomComic();
    $statusList = $comic ? sendComicToAll($conn, $comic) : ["❌ Failed to fetch XKCD comic."];
} else {
    $statusList[] = "⛔ Sending is paused.";
}

// Stop logic
if (isset($_POST['stop_all'])) {
    file_put_contents($stop_file, '1');
    $statusList[] = "🛑 Sending comics paused by user.";
}
if (isset($_POST['resume_all'])) {
    file_put_contents($stop_file, '0');
    $statusList[] = "✅ Comic sending resumed.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>XKCD Comic Auto Sender</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            padding: 50px;
            text-align: center;
        }
        .card {
            width: 600px;
            background: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        .status {
            margin-top: 20px;
            text-align: left;
            padding: 15px;
            font-size: 14px;
            background: #f1f1f1;
            border-radius: 6px;
        }
        .success { color: green; }
        .fail { color: red; }
        .btn {
            padding: 10px 25px;
            margin-top: 15px;
            margin-right: 10px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        .stop-btn { background: red; color: white; }
        .resume-btn { background: green; color: white; }
        #timer {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>📬 XKCD Comic Auto Sender</h2>
        <div id="timer">⏳ Sending next comic in <span id="countdown">--:--</span></div>

        <div class="status">
            <?php foreach ($statusList as $msg): ?>
                <div class="<?= strpos($msg, '✅') !== false ? 'success' : (strpos($msg, '❌') !== false ? 'fail' : '') ?>">
                    <?= $msg ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="post">
            <button type="submit" name="stop_all" class="btn stop-btn">🛑 Stop Sending</button>
            <button type="submit" name="resume_all" class="btn resume-btn">▶️ Resume Sending</button>
        </form>
        <form method="post">
            <button type="submit" name="stop_all" class="btn"><a href="index.html">GO BACK</button>
        </form>
    </div>

    <script>
        let secondsLeft = <?= $interval_seconds ?>;
        function updateCountdown() {
            let mins = Math.floor(secondsLeft / 60);
            let secs = secondsLeft % 60;
            document.getElementById("countdown").textContent = 
                (mins < 10 ? "0" : "") + mins + ":" + (secs < 10 ? "0" : "") + secs;
            if (secondsLeft > 0) {
                secondsLeft--;
                setTimeout(updateCountdown, 1000);
            }
        }
        updateCountdown();
        setTimeout(() => {
            window.location.reload();
        }, <?= $interval_seconds * 1000 ?>);
    </script>
</body>
</html>

