<?php
namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


class SendMailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    public function send( String $from,
                          String $to ,
                          String $subject,
                          String $template,
                          array $context) : void
    {
        // On crée le mail
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("Mail/$template.html.twig")
            ->context($context);
        // On envoie le mail
            $this->mailer->send($email);
    }
}