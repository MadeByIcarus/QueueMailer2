<?php declare(strict_types=1);

namespace Icarus\QueueMailer2\Model;


use Doctrine\ORM\Mapping as ORM;
use Icarus\DoctrineHelpers\Entities\Attributes\BigIdentifier;
use Nette\InvalidArgumentException;
use Nette\Mail\Message;


/**
 * @ORM\Entity
 */
class Email
{

    use BigIdentifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $snoozeUntil;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $error;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $errorTime;

    /**
     * @ORM\Column(type="string")
     */
    private $sender;

    /**
     * @ORM\Column(type="string")
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cc;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $bcc;

    /**
     * @ORM\Column(type="string")
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $attachments;



    function __construct()
    {
        $this->createdAt = new \DateTime();
    }



    /**
     * @return $this
     */
    public function setSentToNow(): self
    {
        $this->sentAt = new \DateTime();
        $this->clearError();
        return $this;
    }



    public function isSent(): bool
    {
        return (bool)$this->sentAt;
    }



    public function setDelay(int $seconds): self
    {
        if ($seconds <= 0 || !is_int($seconds)) {
            throw new InvalidArgumentException(
                "Expected unsigned integer. Got '$seconds'" .
                !is_int($seconds) ? (" which is " . gettype($seconds)) : "."
            );
        }
        $this->snoozeUntil = clone $this->createdAt;
        $this->snoozeUntil->modify("+$seconds seconds");
        return $this;
    }



    public function getMessage(): Message
    {
        $message = new Message();

        $message->setFrom($this->sender);
        $message->addTo($this->recipient);

        if ($this->cc) {
            $message->addCc($this->cc);
        }

        if ($this->bcc) {
            $message->addBcc($this->bcc);
        }

        $message->setSubject($this->subject);
        $message->setHtmlBody($this->body);

        if ($this->attachments) {
            $attachments = explode(";", $this->attachments);
            foreach ($attachments as $a) {
                $message->addAttachment($a);
            }
        }

        return $message;
    }



    public function setSender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }



    public function setTo(string $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }



    public function setCc(?string $cc): self
    {
        $this->cc = $cc;
        return $this;
    }



    public function setBcc(?string $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }



    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }



    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }



    public function addAttachmentPath($path): self
    {
        if ($this->attachments) {
            $attachments = explode(";", $this->attachments);
        } else {
            $attachments = [];
        }
        $attachments[] = $path;
        $this->attachments = implode(";", $attachments);

        return $this;
    }



    public function setError(string $error): self
    {
        $this->error = $error;
        $this->errorTime = new \DateTime();
        return $this;
    }



    public function clearError(): void
    {
        $this->error = null;
        $this->errorTime = null;
    }

}