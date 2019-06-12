<?php


namespace Icarus\QueueMailer2\Templates;


interface IMessageTemplate
{

    function getFrom(): ?string;



    function getRecipient(): string;



    function getSubject(): string;



    function getParameters();



    function getFile(): string;

}