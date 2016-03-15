<?php
$myemail = 'contact@m2talent.fi';
if (isset($_POST['email'])) {

$email = strip_tags($_POST['email']);
$country = strip_tags($_POST['country']);

$to = $myemail;
$email_subject = "MAZHR Registration";
$email_body = "Please inform me about the new service:\n".

"Email: $email\n".
"Country: $country\n";
$headers = "From: $myemail\n";
$headers .= "Reply-To: $email";
mail($to,$email_subject,$email_body,$headers);
}?>