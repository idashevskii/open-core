<?php

namespace OpenCore\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    public static function sendTextMail($to, string $subject, string $body) {
        switch (getenv('EMAIL_METHOD')) {
            case 'STMP': return self::sendUsingSmtp($to, $subject, $body);
            case 'LOG': return self::sendUsingLog($to, $subject, $body);
        }
    }

    public static function getMailLog() {
        $ret = null;
        $filename = self::mailLogFileName();
        if (file_exists($filename)) {
            $handle = fopen($filename, 'r');
            flock($handle, LOCK_SH);

            $ret = json_decode(fread($handle, filesize($filename)), true);

            flock($handle, LOCK_UN);
            fclose($handle);
        }
        return $ret ? $ret : [];
    }

    private static function mailLogFileName() {
        return APP_ROOT . '/logs/mail.log';
    }

    private static function sendUsingLog($to, string $subject, string $body) {
        $MAX_LOG_SIZE = 16;

        $filename = self::mailLogFileName();
        $handle = fopen($filename, 'c+');
        flock($handle, LOCK_EX);

        $size = filesize($filename);
        $data = $size ? json_decode(fread($handle, $size), true) : [];

        $data[] = ['to' => $to, 'subject' => $subject, 'body' => $body];

        if (count($data) > $MAX_LOG_SIZE) {
            array_shift($data);
        }

        ftruncate($handle, 0);
        fseek($handle, 0);
        fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private static function sendUsingSmtp($to, string $subject, string $body) {

        $mail = new PHPMailer(true);
        try {

            $mail->SMTPDebug = 0;
//            $mail->SMTPDebug = 4;
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST');
            $mail->CharSet = 'UTF-8';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');
            $mail->SMTPSecure = 'tls';
            $mail->Port = (int) getenv('SMTP_PORT');
            //Recipients
            $mail->setFrom(getenv('EMAIL_FROM_ADDRESS'), getenv('EMAIL_FROM_NAME'));
            $mail->addReplyTo(getenv('EMAIL_REPLY_ADDRESS'), getenv('EMAIL_REPLY_NAME'));

            foreach ((array) $to as $toItem) {
                $mail->addAddress($toItem);
            }

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;
            //$mail->AltBody = '';

            $mail->send();
        } catch (Exception $e) {
            throw new MailerException('Mailer Error (' . $e->getMessage() . '): ' . $mail->ErrorInfo);
        }
    }

}