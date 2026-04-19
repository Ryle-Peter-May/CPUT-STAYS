<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cputstays@gmail.com';
        $mail->Password = 'xcab zmtb scqd csai';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('cputstays@gmail.com', 'CPUT STAYS');
        $mail->addAddress($to);
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
