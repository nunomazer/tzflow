<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:06
 */

namespace Tzflow\Gitlab;

use Symfony\Component\Console\Input\InputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;

/**
 * Class MR - Merge Request for Gitlab repo
 * @package Tzflow\Gitlab
 */
class MR
{
    public $command;
    public $service;
    public $input;

    public function handle(Service $service, BaseCommand $command, InputInterface $input)
    {
        $this->command = $command;
        $this->service = $service;
        $this->input = $input;

        $target = $this->getTarget($input);

        $source = $this->command->getSource($input);

        if ($input->getOption('no-push') == false) {
            $push = $this->command->climate->confirm('PUSH changes before open the MR?');
            if ($push->confirmed()) {
                if (\Tzflow\Git::push($source, $this->command) === false) return;
            }
        }

        $title = $this->getTitle($this->service->gl, $source, $service->project_id);

        $description = $this->getMRDescription($source);

        $assignee_id = null;
        if ($this->input->getOption('no-assignee') == false) {
            $assignee_id = $this->askAssignee();
        }

        $milestone_id = null;
        if ($this->input->getOption('no-milestone') == false) {
            $milestone_id = $this->askMilestone();
        }

        try {
            $this->command->climate->info('Creating MR ... wait ... this can take a while ...');

            $mr = $this->service->gl->createMR(
                $this->service->project_id,
                $source,
                $target,
                $title,
                $description,
                $assignee_id,
                $milestone_id
            );

            $this->command->climate->br();
            $this->command->climate->info('  MR !' . $mr->iid . ' created.');
            $this->command->climate->br();

            if ($this->input->getOption('merge')) {
                $this->command->climate->br();
                $this->command->climate->info('--------------------------');
                $this->command->climate->info('*  Calling Accept Merge  *');
                $this->command->climate->info('--------------------------');
                $this->command->climate->br();
                $this->command->call('gitlab:mr-merge', [
                    'id' => $mr->iid,
                    '--remove-source' => $this->option('remove-source'),
                    '--update-local' => $this->option('update-local'),
                    '--tag-after' => $this->option('tag-after'),
                ]);
            }
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $this->command->climate->info('');
            $this->command->climate->error('  Http status error: ' . $ex->getCode() . ' - ' . $ex->getResponse()->getReasonPhrase());
            $this->command->climate->error('  ' . $ex->getResponseBodySummary($ex->getResponse()));
            $this->command->climate->info('');
        } catch (\Exception $ex) {
            $this->command->climate->error($ex->getMessage());
        }
    }

    protected function askAssignee()
    {
        $this->command->climate->info('Loading members ...');
        $members = $this->service->gl->getProjectMembers($this->service->project_id);
        $choice = ['No assignee'];
        foreach ($members as $member) {
            $choice[] = $member->name;
        }
        $choice = array_unique($choice);

        $ask = $this->command->climate->radio('Assignee to:', $choice);
        $assignee_choosen = $ask->prompt();

        $assignee_id = null;
        if ($assignee_choosen != 'No assignee') {
            foreach ($members as $member) {
                if ($member->name == $assignee_choosen) {
                    $assignee_id = $member->id;
                }
            }
        }

        return $assignee_id;
    }

    protected function askMilestone()
    {
        $this->command->climate->info('Loading milestones ...');
        $milestones = $this->service->gl->getProjectMilestones($this->service->project_id, true);
        $choice = ['No milestone'];
        foreach ($milestones as $ml) {
            $choice[] = $ml->title;
        }

        $ask = $this->command->climate->radio('Milestone:', $choice);
        $ml_choosen = $ask->prompt();

        $ml_id = null;
        if ($ml_choosen != 'No milestone') {
            foreach ($milestones as $ml) {
                if ($ml->title == $ml_choosen) {
                    $ml_id = $ml->id;
                }
            }
        }

        return $ml_id;
    }

    protected function getTarget()
    {
        $target = config($this->command->driver.'.default.mr.target-branch');
        if ($this->input->getOption('target')) {
            $target = $this->input->getOption('target');
        }

        return $target;
    }

    protected function getIssue($source)
    {
        return $issue = intval(explode('-', $source)[0]);
    }

    protected function getTitle(Gitlab $gl, $source, $project_id)
    {
        $issue = $this->getIssue($source);

        if ($this->input->getOption('title')) {
            $title = $this->input->getOption('title');
        } else {
            $title = 'Merge "' . $source . '" -> "' . $this->getTarget() . '"';

            if ($issue > 0) {
                $this->command->climate->info('Loading issue title ...');
                $title = 'Resolve "' . $gl->getIssue($this->service->project_id, $issue)->title . '"';
            }
        }

        if ($this->input->getOption('wip')) {
            $title = 'WIP: ' . $title;
        }

        $this->command->climate->yellow('Title: ' . $title);
        $this->command->climate->br();
        return $title;

    }

    protected function getMRDescription($source)
    {
        $issue = $this->getIssue($source);
        $description = null;
        if ($this->input->getOption('description')) {
            $description = $this->input->getOption('description');
        } else {
            if ($issue > 0) {
                $description = 'Closes%20%23' . $issue;
            }
        }

        return $description;
    }
}