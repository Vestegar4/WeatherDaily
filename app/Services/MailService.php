<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

if (!isset($_ENV['SMTP_HOST'])) {
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    }
}

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        $this->mail->SMTPDebug = 1; 
        $this->mail->Debugoutput = 'html'; 

        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
                
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'] ?? '';
        $this->mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
        
        $this->mail->setFrom($_ENV['SMTP_USER'] ?? 'no-reply@weatherdaily.com', 'WeatherDaily System');
    }

    public function send($toEmail, $toName, $subject, $bodyContent) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;'>
                    <h2 style='color: #084bb8;'>WeatherDaily Notification</h2>
                    <p>Halo <strong>$toName</strong>,</p>
                    <p style='font-size: 16px;'>$bodyContent</p>
                    <hr>
                    <small style='color: #888;'>Pesan ini dikirim otomatis oleh sistem WeatherDaily.</small>
                </div>
            ";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            echo "<br><strong>MAIL ERROR:</strong> " . $this->mail->ErrorInfo;
            return false;
        }
    }
}