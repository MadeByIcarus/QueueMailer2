<?php declare(strict_types=1);

namespace Icarus\QueueMailer2\Console\Commands;


use Contributte\Console\Exception\Logical\InvalidArgumentException;
use Icarus\QueueMailer2\QueueMailer2;
use Nette\Utils\Validators;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DumpCommand extends Command
{

    protected static $defaultName = 'queue-mailer:dump';

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
            'id',
            InputArgument::REQUIRED,
            'Entity ID in the database.'
        );

        $this->addArgument(
            'output-dir',
            InputArgument::REQUIRED,
            'Output directory for a preview file.'
        );

        $this->setDescription('Dumps an email to .eml file for preview.');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $outputDir = $input->getArgument('output-dir');

        if (!Validators::isNumericInt($id)) {
            throw new InvalidArgumentException("Argument <id> must be a positive integer.");
        }

        $id = (int)$id;

        try {
            $filename = $this->queueMailer2->dump($id, $outputDir);
            $output->writeln("Mail preview saved in file: " . $filename);
        } catch (\Throwable $e) {
            $output->writeln("Error: " . $e->getMessage());
        }
    }
}
