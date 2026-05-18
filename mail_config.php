<?php
/**
 * Mail Configuration - SMTP settings for sending emails
 * Update the credentials below with your Gmail App Password
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function sendVerificationEmail($to_email, $to_name, $code) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // ✔ YOUR GMAIL ACCOUNT
        $mail->Username = 'moreerror404@gmail.com';

        // ✔ YOUR APP PASSWORD (Spaces removed for safety)
        $mail->Password = 'csbmibnicntrzgtu';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // XAMPP SSL Bypass (Required for local environments)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Email Info
        $mail->setFrom('moreerror404@gmail.com', 'AGRIC DSS');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'AGRIC DSS - Email Verification Code';

        $mail->Body = "
        <div style='font-family: Inter, Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 0;'>
            <div style='background: linear-gradient(135deg, #1b5e20, #2e7d32); padding: 30px; text-align: center; border-radius: 12px 12px 0 0;'>
                <h2 style='color: white; margin: 0;'>🌾 AGRIC DSS</h2>
                <p style='color: rgba(255,255,255,0.8); margin: 5px 0 0;'>Agricultural Decision Support System</p>
            </div>
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0;'>
                <h3 style='color: #333; margin-top: 0;'>Verify Your Email Address</h3>
                <p style='color: #666; font-size: 14px;'>Hello <strong>$to_name</strong>,</p>
                <p style='color: #666; font-size: 14px;'>Thank you for registering. Please use the code below to complete your registration:</p>
                <div style='background: linear-gradient(135deg, #fff8e1, #fff3e0); border: 2px dashed #ff8f00; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0;'>
                    <div style='font-size: 12px; color: #e65100; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;'>Verification Code</div>
                    <div style='font-size: 36px; font-weight: 800; color: #e65100; letter-spacing: 6px; margin: 8px 0;'>$code</div>
                    <div style='font-size: 12px; color: #999;'>This code expires in 10 minutes</div>
                </div>
                <p style='color: #999; font-size: 12px;'>If you did not create an account, please ignore this email.</p>
            </div>
            <div style='background: #f5f5f5; padding: 15px; text-align: center; border-radius: 0 0 12px 12px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='color: #aaa; font-size: 11px; margin: 0;'>© AGRIC DSS - Agricultural Market System</p>
            </div>
        </div>
        ";

        $mail->AltBody = "Your AGRIC DSS verification code is: $code. This code expires in 10 minutes.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
