<?php
require 'vendor/autoload.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

// Définir la variable d'environnement MAILER_DSN directement dans le script
putenv('MAILER_DSN=smtp://100.moonnaie@gmail.com:q%20y%20r%20g%20v%20e%20s%20e%20p%20t%20g%20j%20z%20h%20u%20m@smtp.gmail.com:587?encryption=tls');

$dsn = getenv('MAILER_DSN');  // Récupère la valeur de MAILER_DSN depuis l'environnement
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('100.moonnaie@gmail.com')  // Remplacez par votre adresse email d'expéditeur
    ->to('ilyas.salek@gmail.com') // Remplacez par l'adresse email du destinataire
    ->subject('Test from Symfony Mailer!')
    ->text('This is a test email sent from a standalone PHP script.');

try {
    $mailer->send($email);
    echo 'Email sent!';
} catch (Exception $e) {
    echo 'Failed to send email: ' . $e->getMessage();
}
