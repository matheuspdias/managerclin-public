<?php

namespace App\Mail;

use SendGrid;
use SendGrid\Mail\Mail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class SendGridTransport extends AbstractTransport
{
    protected $sendGrid;
    protected $fromEmail;
    protected $fromName;

    public function __construct(string $apiKey, string $fromEmail, ?string $fromName = null)
    {
        parent::__construct();

        $this->sendGrid = new SendGrid($apiKey);
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $mail = new Mail();
        $mail->setFrom($this->fromEmail, $this->fromName);

        // Set recipients
        $to = $email->getTo();
        if (count($to) > 0) {
            foreach ($to as $address) {
                $mail->addTo($address->getAddress(), $address->getName());
            }
        }

        // Set CC
        $cc = $email->getCc();
        if (count($cc) > 0) {
            foreach ($cc as $address) {
                $mail->addCc($address->getAddress(), $address->getName());
            }
        }

        // Set BCC
        $bcc = $email->getBcc();
        if (count($bcc) > 0) {
            foreach ($bcc as $address) {
                $mail->addBcc($address->getAddress(), $address->getName());
            }
        }

        // Set subject
        $mail->setSubject($email->getSubject());

        // Set content
        if ($email->getHtmlBody()) {
            $mail->addContent("text/html", $email->getHtmlBody());
        }

        if ($email->getTextBody()) {
            $mail->addContent("text/plain", $email->getTextBody());
        }

        // Send the email
        try {
            $response = $this->sendGrid->send($mail);

            if ($response->statusCode() >= 400) {
                throw new \Exception('SendGrid API returned error: ' . $response->body());
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email via SendGrid: ' . $e->getMessage());
        }
    }

    public function __toString(): string
    {
        return 'sendgrid';
    }
}