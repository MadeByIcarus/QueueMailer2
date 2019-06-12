<?php declare(strict_types=1);

namespace Icarus\QueueMailer2\Console\Commands;


use Contributte\Console\Exception\Logical\InvalidArgumentException;
use Icarus\QueueMailer2\QueueMailer2;
use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class SendCommand extends Command
{
    protected static $defaultName = 'queue-mailer:send';

    /**
     * @var QueueMailer2
     */
    private $queueMailer2;



    public function __construct(QueueMailer2 $queueMailer2)
    {
        parent::__construct();

        $this->queueMailer2 = $queueMailer2;
    }



    protected function configure()
    {
        $this->addArgument(
            'batch-size',
            InputArgument::REQUIRED,
            'Specifies the size of the batch. Maximum is ' . QueueMailer2::MAX_BATCH_SIZE . "."
        );

        $this->setDescription('Sends a batch of emails.');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = $input->getArgument('batch-size');

        if (!Validators::isNumericInt($batchSize)) {
            throw new InvalidArgumentException("Argument <batch-size> must be a positive integer.");
        }

        $batchSize = (int)$batchSize;

        foreach ($this->queueMailer2->sendBatch($batchSize) as $msg) {
            $output->writeln($msg);
        }
    }
}
