<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:06
 */

namespace Tzflow\Gitlab;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;
use Tzflow\Git;

/**
 * Class to accept a determined Merge Request for Gitlab repo
 * @package Tzflow\Gitlab
 */
class AcceptMR
{
    public $command;
    public $service;
    public $input;
    public $mr_id;

    public function handle(Service $service, BaseCommand $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->service = $service;
        $this->input = $input;

        $this->mr_id = $this->input->getArgument('id');

        $this->command->climate->info('Checking MR state ...');
        if ($this->isMergedClosed()) {
            return $this->command->climate->error('Can\'t accept, MR <bold>!' . $this->mr_id . '</bold> is Merged or Closed already !!');
        }

        if ($this->input->getOption('no-push') == false) {
            $push = $this->command->climate->confirm('PUSH changes before accept the MR?');
            if ($push->confirmed()) {
                if (Git::push($this->command->getSource($this->input), $this->command) === false) return ;
            }
        }

        if ($this->input->getOption('yes') == false) {
            $this->listIssues();

            $this->listCommits();

            if ($this->command->climate->confirm('List changes?')->confirmed()) {
                $this->listChanges();
            }
        }

        $continue = $this->input->getOption('yes');
        if ($this->input->getOption('yes') == false) {
            $continue = $this->command->climate->confirm('Accept and merge this MR?')->confirmed();
        }

        if ( $continue ) {
            $this->command->climate->info('Wait ... this can take a while ...');

            $message = null;
            if ($this->input->getOption('description')) {
                $message = $this->input->getOption('description');
            }

            $removeSourceBranch = $this->input->getOption('remove-source');

            try {
                $mr = $this->service->gl->acceptMR($this->service->project_id, $this->mr_id, $message, $removeSourceBranch);

                $this->command->climate->info('');
                $this->command->climate->info('  MR !'.$mr->iid. ' MERGED.');
                $this->command->climate->info('');

                $this->afterMerge($mr);

                return;

            } catch (\GuzzleHttp\Exception\ClientException $ex) {
                $this->command->climate->info('');
                $this->command->climate->error('  Http status error: ' . $ex->getCode() . ' - ' . $ex->getResponse()->getReasonPhrase());
                $this->command->climate->error('  ' . $ex->getResponseBodySummary($ex->getResponse()));
                $this->command->climate->info('');
            } catch (\Exception $ex) {
                $this->command->climate->error($ex->getMessage());

                if (strpos(strtolower($ex->getMessage()), 'gitlab is not responding') !== FALSE) {
                    if ($this->isMergedClosed()) {
                        $this->command->climate->backgroundYellow('Even with the error result it seems the MR was merged.');
                        $this->command->climate->backgroundYellow('The error may have been generated due to an excessive server response time.');

                        $this->afterMerge($mr);

                        return;
                    }
                }
            }
        }

        return $this->command->climate->backgroundYellow('MR still opened');
    }

    protected function afterMerge($mr) {
        if ($this->input->getOption('update-local') || $this->input->getOption('tag-after')) {
            if (is_numeric($mr)) {
                $mr = $this->service->gl->getMR($this->service->project_id, $mr);
            }

            $this->updateLocal($mr);

            if ($this->input->getOption('tag-after')) {
                $this->command->climate->info('Tagging');
                $this->service->gl->createTag($this->service->project_id, $this->input->getOption('tag-after'), $mr->target_branch);
                $this->info('Branch ' . $mr->target_branch . ' tagged with name ' . $this->input->getOption('tag-after'));
            }
        }
    }

    protected function isMergedClosed() {
        $mr = $this->service->gl->getMR($this->service->project_id, $this->mr_id);

        return ($mr->state == 'merged' || $mr->state == 'closed');
    }

    public function listCommits() {
        $this->command->climate->info('Loading commits for this MR ...');

        $commits = $this->service->gl->getMRCommits($this->service->project_id, $this->mr_id);

        $tableCommits = [];

        $this->command->climate->yellow('Commits in this MR');
        foreach ($commits as $key => $commit) {
            $tableCommits[] = [
                'Hash' => $commit->short_id,
                'Title' => $commit->title,
                'Atuhor' => $commit->author_name,
                'Created' => Carbon::parse($commit->created_at)->toFormattedDateString(),
                'Message' => substr(str_replace('\n', '', $commit->message), 0, 8) . '..',
            ];
        }

        if (empty($tableCommits)) {
            return $this->command->climate->backgroundYellow(' - No commits in this MR');
        }

        return $this->command->climate->table($tableCommits);
    }

    public function listIssues() {
        $this->command->climate->info('Loading issues that will be closed in this MR ...');

        $issues = $this->service->gl->getMRIssues($this->service->project_id, $this->mr_id);

        $tableIssues = [];

        $this->command->climate->yellow('ISSUES to be closed');
        foreach ($issues as $key => $issue) {
            $tableIssues[] = [
                'id' => $issue->iid,
                'Title' => $issue->description,
                'Auhor' => $issue->author->name,
                'Assignee' => $issue->assignee->name,
                'Created' => Carbon::parse($issue->created_at)->toFormattedDateString(),
            ];
        }

        if (empty($tableIssues)) {
            return $this->command->climate->backgroundYellow(' - No issues will be closed in this MR');
        }

        return $this->command->climate->table($tableIssues);
    }

    public function listChanges() {
        $this->command->climate->info('Loading changes in this MR ...');

        $changes = $this->service->gl->getMRChanges($this->service->project_id, $this->mr_id)->changes;

        $noChanges = true;

        $this->command->climate->yellow('Changes in this MR');
        foreach ($changes as $key => $change) {
            $noChanges = false;

            $tableChanges[0] = [
                'Path' => $change->old_path . ($change->old_path != $change->new_path) ? ' => ' . $change->new_path : '',
                'Mode' => $change->a_mode . ($change->a_mode != $change->b_mode) ? ' => ' . $change->b_mode : '',
                'New file' => $change->new_file ? 'x' : '',
                'Renamed file' => $change->renamed_file ? 'x' : '',
                'Deleted file' => $change->deleted_file ? 'x' : '',
            ];

            $this->command->climate->table($tableChanges);

            $diff = explode(PHP_EOL, $change->diff);

            foreach ($diff as $key => $line) {
                if (substr($line, 0, 1) == '@') {
                    $this->command->climate->out($line);
                }
                if (substr($line, 0, 1) == '-') {
                    $this->command->climate->error($line);
                }
                if (substr($line, 0, 1) == '+') {
                    $this->command->climate->green($line);
                }
            }

            if ($this->command->climate->confirm('Show next change?')->confirmed() == false) {
                break;
            }

        }

        if ($noChanges) {
            return $this->command->climate->backgroundYellow(' - No changes in this MR');
        }

        $this->command->climate->blue()->flank('End of changes.');

    }

    protected function updateLocal($mr) {
        $this->command->climate->out('Checkout branch ' . $mr->target_branch);
        exec('git checkout '. $mr->target_branch, $out, $status);
        $this->showExecOutput($out);

        if ($status) {
            $this->command->climate->error('Checkout err ' . $status);
            return false;
        }

        $this->command->climate->out('Pull branch ' . $mr->target_branch);
        exec('git pull origin ' . $mr->target_branch, $out, $status);
        $this->showExecOutput($out);

        if ($status) {
            $this->command->climate->error('Pull err ' . $status);
            return false;
        }

        return true;
    }

    protected function showExecOutput($out) {
        foreach ($out as $line) {
            $this->line($line);
        }
    }
}