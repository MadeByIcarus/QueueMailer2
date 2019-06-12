<?php
/**
 * Created by PhpStorm.
 * User: pavelgajdos
 * Date: 30.01.17
 * Time: 14:15
 */

namespace Icarus\QueueMailer2\DI;


use Icarus\QueueMailer2\Console\Commands\DumpCommand;
use Icarus\QueueMailer2\Console\Commands\SendCommand;
use Icarus\QueueMailer2\Model\EmailService;
use Icarus\QueueMailer2\QueueMailer2;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\ORM\DI\Traits\TEntityMapping;


class QueueMailerExtension extends CompilerExtension
{

    use TEntityMapping;

    private $defaults = [
        'defaultLanguage' => 'en',
        'defaultSender' => ''
    ];



    public function getConfigSchema(): Schema
    {
        return
            Expect::structure([
                'defaultLanguage' => Expect::string('en'),
                'defaultSender' => Expect::email()->required()
            ]);
    }



    public function loadConfiguration()
    {
        $config = $this->config;

        $this->getContainerBuilder()->addDefinition($this->prefix("QueueMailer2"))
            ->setFactory(QueueMailer2::class, [$config->defaultSender, $config->defaultLanguage]);

        $this->getContainerBuilder()->addDefinition($this->prefix("EmailService"))
            ->setFactory(EmailService::class);

        $this->getContainerBuilder()->addDefinition($this->prefix('sendCommand'))
            ->setFactory(SendCommand::class);

        $this->getContainerBuilder()->addDefinition($this->prefix('dumpCommand'))
            ->setFactory(DumpCommand::class);

        $this->setEntityMappings($this->getEntityMappings());
    }



    private function getEntityMappings(): array
    {
        return [
            'Icarus\QueueMailer2' => __DIR__ . '/../Model/'
        ];
    }
}