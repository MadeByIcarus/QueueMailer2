<?php

namespace Icarus\QueueMailer2;


use Icarus\QueueMailer2\Model\Email;
use Icarus\QueueMailer2\Model\EmailService;
use Icarus\QueueMailer2\Templates\IMessageTemplate;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
use Nette\Utils\Validators;
use Nettrine\ORM\EntityManagerDecorator;
use Tracy\Debugger;


class QueueMailer2
{

    const MAX_BATCH_SIZE = 50;

    /**
     * @var EntityManagerDecorator
     */
    private $entityManager;

    /**
     * @var string
     * Default sender
     */
    private $sender;

    /**
     * @var string
     * Default language
     */
    private $defaultLanguage;

    /**
     * @var ITemplateFactory
     */
    private $templateFactory;

    /**
     * @var LinkGenerator
     */
    private $linkGenerator;

    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @var EmailService
     */
    private $emailService;



    function __construct(
        $defaultSender,
        $defaultLanguage,
        EmailService $emailService,
        EntityManagerDecorator $entityManager,
        IMailer $mailer,
        ITemplateFactory $templateFactory,
        LinkGenerator $linkGenerator
    )
    {
        $this->sender = $defaultSender;
        $this->defaultLanguage = $defaultLanguage;

        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
        $this->linkGenerator = $linkGenerator;
        $this->mailer = $mailer;
        $this->templateFactory = $templateFactory;
    }



    public function queue(Email $email, bool $doFlush = true)
    {
        $this->entityManager->persist($email);
        if ($doFlush) {
            $this->entityManager->flush();
        }
    }



    public function sendBatch(int $batchSize = 1)
    {
        if ($batchSize > self::MAX_BATCH_SIZE) {
            throw new \InvalidArgumentException("Maximum batch size is " . self::MAX_BATCH_SIZE . ". Given $batchSize.");
        }

        $emails = $this->emailService->findPendingEmails($batchSize);

        if (!$emails) {
            yield "No e-mail to send.";
            return;
        }

        foreach ($emails as $email) {
            $error = null;
            $this->send($email, $error);

            if ($error) {
                yield $error;
            }
        }
    }



    public function send(Email $email, &$error = null)
    {
        if ($email->isSent()) {
            $error = 'Email ' . $email->getId() . ' cannot resend.';
            return; // silently prevent sending duplicates
        }

        $message = $email->getMessage();

        try {
            $this->mailer->send($message);
            $email->setSentToNow();
        } catch (\Exception $e) {
            $email->setError($e->getMessage());
            Debugger::log($e, Debugger::ERROR);
            $error = 'An error occurred during sending email ID ' . $email->getId() . ': ' . $e->getMessage();
        }

        $this->entityManager->persist($email);
        $this->entityManager->flush();
    }



    public function dump(int $id, string $outputDir)
    {
        /** @var Email $email */
        $email = $this->entityManager->find(Email::class, $id);

        if (!$email) {
            throw new \InvalidArgumentException(Email::class . " with id $id not found.");
        }

        $emlContent = $email->getMessage()->generateMessage();
        $filename = rtrim($outputDir, "/") . "/email_" . $id . "_" . time() . ".eml";
        @file_put_contents($filename, $emlContent);

        if (!file_exists($filename)) {
            throw new InvalidStateException("Could not save .eml file at $filename.");
        }

        return $filename;
    }



    public function createMailFromTemplate(IMessageTemplate $messageTemplate)
    {
        /** @var Template $template */
        $template = $this->templateFactory->createTemplate();
        $template->setFile($messageTemplate->getFile());
        if ($messageTemplate->getParameters()) {
            $template->setParameters($messageTemplate->getParameters());
        }
        $subject = $messageTemplate->getSubject();
        $to = $messageTemplate->getRecipient();

        return $this->createMail($to, $subject, $template, $messageTemplate->getFrom());
    }



    public function createMail(string $to, string $subject, Template $body, $from = null)
    {
        if (!Validators::isEmail($to)) {
            throw new InvalidArgumentException("Invalid Email address '$to'");
        }

        if ($from && !Validators::isEmail($from)) {
            throw new InvalidArgumentException("Invalid Email address '$from'");
        }

        $email = new Email();
        $email->setSender($from ?: $this->sender);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setBody($body);

        return $email;
    }
}