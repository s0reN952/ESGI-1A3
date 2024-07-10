<?php
require 'vendor/autoload.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

/
putenv('MAILER_DSN=smtp://100.moonnaie@gmail.com:q%20y%20r%20g%20v%20e%20s%20e%20p%20t%20g%20j%20z%20h%20u%20m@smtp.gmail.com:587?encryption=tls');

$dsn = getenv('MAILER_DSN');  
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('100.moonnaie@gmail.com')  
    ->to('ilyas.salek@gmail.com') 
    ->subject('Test from Symfony Mailer!')
    ->text('This is a test email sent from a standalone PHP script.');

try {
    $mailer->send($email);
    echo 'Email sent!';
} catch (Exception $e) {
    echo 'Failed to send email: ' . $e->getMessage();
}
