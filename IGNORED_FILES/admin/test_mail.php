<?
require_once('../config/configuration.php');
require_once("../config/class.phpmailer.php");

$mail                = new PHPMailer();
$body                = 'le test';
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host          = "smtp.orange.fr";
$mail->SMTPAuth      = false;                  // enable SMTP authentication
$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
$mail->Subject       = "PHPMailer Test Subject via smtp, basic with authentication";
$mail->MsgHTML($body);
$mail->SetFrom('list@mydomain.com', 'List manager');
$mail->AddAddress('ccaron.smartlink@gmail.com', 'Christophe Caron');
$mail->Send();
?>