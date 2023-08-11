<?php require_once dirname(__FILE__) . "/../vendor/autoload.php"; //PHPMailer Object 
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer; //From email address and name 
$mail->SMTPDebug = 2;
$mail->isSMTP();
$mail->Host = "mail.reclinesolutions.com";
$mail->From = "customer@reclinesolutions.com";
$mail->Username = 'customer@reclinesolutions.com'; // SMTP username
$mail->Password = 'nKDa#%BB),Q9'; // SMTP password
$mail->FromName = "Zia ur Rehman"; //To address and name 
$mail->Port = 465;


$mail->SMTPDebug = 2; // Enable verbose debug output
$mail->isSMTP(); // Set mailer to use SMTP
$mail->Host = 'mail.reclinesolutions.com'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'customer@reclinesolutions.com'; // SMTP username
$mail->Password = 'nKDa#%BB),Q9'; // SMTP password
$mail->SMTPSecure = 'tls'; // Enable TLS encryption, [ICODE]ssl[/ICODE] also accepted
$mail->Port = 465; // TCP port to connect to



//Recipients
$mail->setFrom('zia.pccr@gmail.com', 'Mailer');
$mail->addAddress('zia.pccr@yahoo.com', 'Zia ur Rehman'); // Add a recipient
$mail->addAddress('zia.pccr@hotmail.com', 'Zia ur Rehman'); // Name is optional
$mail->addReplyTo('support@reclinesolutions.com', 'Information');
$mail->addCC('cc@example.com');
$mail->addBCC('bcc@example.com');

$mail->isHTML(true);
$mail->Subject = "Pending Invoice 001";
$mail->Body = "<i>Mail body in HTML</i>";
$mail->AltBody = "This is the plain text version of the email content";
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message has been sent successfully";
}
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message has been sent successfully";
}
