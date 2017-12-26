<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 11:45
 */

namespace Tzflow\Commands;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class Run extends BaseCommand
{
    protected $name = 'run';
    protected $description = 'Run one of the custom flows described in "tzflow.json" file';
    protected $help = 'Uses the "flow" key in "tzflow.json" to automate even more the git workflow';

    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            'flow',
            InputArgument::OPTIONAL,
            'the name of the flow to be executed, it is a section in "flow" key, "default"'
        );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = $input->getArgument('flow') ? $input->getArgument('flow') : "default";
        $this->handle('RUNNING AUTOMATED FLOW <yellow>'. $name . '</yellow>', $input, $output);

    }

}