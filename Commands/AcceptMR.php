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

class AcceptMR extends BaseCommand
{
    protected $name = 'mr-merge';
    protected $description = 'Accept a Merge Request from current project on remote repository';
    protected $help = 'Accept a Merge Request from current project on remote repository, depending of the driver 
                    (repository) being used: Gitlab';

    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            'id',
            InputArgument::REQUIRED,
            'the id (number) of the MR at the project'
        );

        $this->addOption(
            'description',
            'D',
            InputOption::VALUE_OPTIONAL,
            'a message (description) to be inserted in MR acceptance'
        );

        $this->addOption(
            'source',
            's',
            InputOption::VALUE_OPTIONAL,
            'The current source branch in git folder, used to push changes to remote branch'
        );

        $this->addOption(
            'no-push',
            null,
            InputOption::VALUE_NONE,
            'If passed then don\'t push current branch to remote origin before accept MR'
        );

        $this->addOption(
            'remove-source',
            null,
            InputOption::VALUE_NONE,
            'used when merging after MR, set the acceptance to remove source'
        );

        $this->addOption(
            'update-local',
            null,
            InputOption::VALUE_NONE,
            'used when merging after MR, checkout target source and pull it after merge'
        );

        $this->addOption(
            'tag-after',
            null,
            InputOption::VALUE_OPTIONAL,
            'used when merging after MR, checkout target source, pull it and tag it after merge'
        );

        $this->addOption(
            'yes',
            'y',
            InputOption::VALUE_OPTIONAL,
            'don\'t interact listing commits and issues or asking for confirmation'
        );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->handle('ACCEPT MERGE REQUEST', $input, $output);

    }

}