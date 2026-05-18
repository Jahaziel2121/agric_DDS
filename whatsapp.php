<?php

function sendWhatsAppOTP($phone, $otp) {

    $token = "YOUR_WHATSAPP_TOKEN";

    $url = "https://waba.360dialog.io/v1/messages";

    $data = [
        "to" => $phone,
        "type" => "text",
        "text" => [
            "body" => "Your AGRIC DSS OTP is: $otp"
        ]
    ];

    $headers = [
        "Content-Type: application/json",
        "D360-API-KEY: $token"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_exec($ch);
    curl_close($ch);
}
?>