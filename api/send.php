<?php require_once "vendor/autoload.php"; //PHPMailer Object 
$mail = new PHPMailer; //From email address and name 
$mail->From = "support@reclinesolutions.com"; 
$mail->FromName = "Support Team"; //To address and name 
$mail->addAddress("zia.pccr@yahoo.com", "Zia ur Rehman");//Recipient name is optional
// $mail->addAddress("recepient1@example.com"); //Address to which recipient will reply 
$mail->addReplyTo("support@reclinesolutions.com", "Reply"); //CC and BCC 
// $mail->addCC("cc@example.com"); 
// $mail->addBCC("bcc@example.com"); //Send HTML or Plain Text email 
$mail->isHTML(true); 
$mail->Subject = "Pending Invoice 001"; 
$mail->Body = "<i>Mail body in HTML</i>";
$mail->AltBody = "This is the plain text version of the email content"; 
if(!$mail->send()) 
{
echo "Mailer Error: " . $mail->ErrorInfo; 
} 
else { echo "Message has been sent successfully"; 
}
if(!$mail->send()) 
{ 
echo "Mailer Error: " . $mail->ErrorInfo; 
} 
else 
{ 
echo "Message has been sent successfully"; 
}