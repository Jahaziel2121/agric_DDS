<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {

    // ================= SMTP SETTINGS =================
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // ✔ YOUR GMAIL ACCOUNT
    $mail->Username = 'yourgmail@gmail.com';

    // ✔ YOUR APP PASSWORD (NO SPACES REQUIRED)
    $mail->Password = 'imtgnpprdyqrmbly';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // ================= EMAIL INFO =================
    $mail->setFrom('yourgmail@gmail.com', 'AGRIC DSS');
    $mail->addAddress('receiver@gmail.com'); // change to your test receiver

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from AGRIC DSS';

    $mail->Body = "
        <h2>Success <i class='fas fa-check-circle'></i></h2>
        <p>Your PHPMailer is working correctly.</p>
    ";

    $mail->send();

    echo "Email sent successfully!";

} catch (Exception $e) {
    echo "Email failed: " . $mail->ErrorInfo;
}