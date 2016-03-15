<?php
$to = "bionik@gmail.com";
$email_subject = "MAZHR Registration";
$email_body = "Please inform me about the new service:";
$headers = "From: $to\n";
$headers .= "Reply-To: $to";
mail($to,$email_subject,$email_body,$headers);
?>
