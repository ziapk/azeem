<?php

require_once dirname(__FILE__) . "/../vendor/autoload.php"; //PHPMailer Object 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Newsletter extends Connection
{
    public function send($array = [])
    {
        try {
            $mail = new PHPMailer; //From email address and name 
            // $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'mail.reclinesolutions.com'; // Specify main and backup SMTP servers
            // $mail->Host = 'premium212.web-hosting.com'; // Specify main and backup SMTP servers
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'customer@reclinesolutions.com'; // SMTP username
            $mail->Password = 'nKDa#%BB),Q9'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, [ICODE]ssl[/ICODE] also accepted
            $mail->Port = 465; // TCP port to connect to

            //Recipients
            $mail->setFrom('customer@reclinesolutions.com', $array['client']);

            foreach ($array['sentTo'] as $value) {
                $mail->addAddress($value['email'], $value['name']); // Add a recipient
            }
            foreach ($array['ccEmails'] as $value) {
                $mail->addCC($value['email'], $value['name']); // Add a recipient
            }

            $mail->addReplyTo('support@reclinesolutions.com', 'Software Support');
            $mail->isHTML(true);
            $mail->Subject = $array['subject'];
            $mail->Body = $array['body'];
            $mail->AltBody = "EMPTY";
            if (!$mail->send()) {
                return ['status' => 400, 'message' => $mail->ErrorInfo];
            } else {
                return ['status' => 200, 'message' =>  "Message has been sent successfully"];
            }
        } catch (Exception $e) {
            return ['status' => 400, 'message' =>  "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
