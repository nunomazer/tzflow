<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 11:45
 */

namespace Tzflow\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MR extends BaseCommand
{
    protected $name = 'mr';
    protected $description = 'Creates a new Merge Request';
    protected $help = 'Creates a new Merge Request / Push Request, depending of the driver (repository) being used: Gitlab';

    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'target',
            't',
            InputOption::VALUE_OPTIONAL,
            'The target branch for this MR, if not passed, the default.mr.target-branch config key will be used'
        );

        $this->addOption(
            'source',
            's',
            InputOption::VALUE_OPTIONAL,
            'The source branch for this MR, if not passed, the current branch in git folder will be used'
        );

        $this->addOption(
            'title',
            null,
            InputOption::VALUE_OPTIONAL,
            'a short text description (title) for the MR'
        );

        $this->addOption(
            'description',
            'D',
            InputOption::VALUE_OPTIONAL,
            'a long text description for the MR'
        );

        $this->addOption(
            'no-push',
            null,
            InputOption::VALUE_NONE,
            'If passed then don\'t push current branch to remote origin before open MR'
        );

        $this->addOption(
            'no-assignee',
            null,
            InputOption::VALUE_NONE,
            'set this if no assignee will be made, otherwise you\'ll be asked to choose the assignee user'
        );

        $this->addOption(
            'no-milestone',
            null,
            InputOption::VALUE_NONE,
            'set this if no milestone will be set, otherwise you\'ll be asked to choose the milestone'
        );

        $this->addOption(
            'wip',
            null,
            InputOption::VALUE_NONE,
            'set this if you want to create a WIP MR'
        );

        $this->addOption(
            'merge',
            null,
            InputOption::VALUE_NONE,
            'set this if you want to create the MR and then merge it'
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

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->handle('MERGE REQUEST', $input, $output);

    }

}