<?php


namespace Icarus\QueueMailer2\Templates;


class SimpleMessageTemplate implements IMessageTemplate
{

    /**
     * @var string
     */
    private $recipient;



    public function __construct(string $recipient)
    {
        $this->recipient = $recipient;
    }



    function getFrom(): ?string
    {
        return null;
    }



    function getRecipient(): string
    {
        return $this->recipient;
    }



    function getSubject(): string
    {
        return "Very Simple Template";
    }



    function getParameters()
    {
        return null;
    }



    function getFile(): string
    {
        return __DIR__ . '/layouts/simple.latte';
    }
}