<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:32
 */

namespace Tzflow\Gitlab;


use League\CLImate\CLImate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;

class Service
{

    public $gl = null;

    public $project_id = null;

    public function __construct()
    {
        $this->loadDriverSettings();
    }

    public function loadDriverSettings()
    {
        $this->gl = Gitlab::client(config('gitlab.api.token'), config('gitlab.api.url', 'https://gitlab.com/api/v4/'));
        $this->project_id = config('gitlab.project.id');
    }

    public function handle(BaseCommand $command, InputInterface $input, OutputInterface $output)
    {

        $prj = $this->gl->getProject($this->project_id);

        $command->climate->yellow()->flank('Running tzflow at project: <background_yellow><white>' . $prj->name_with_namespace . '</white></background_yellow>');
        $command->climate->yellow()->flank('URL: ' . config('gitlab.api.url', 'https://gitlab.com/api/v4/'));

        $command->climate->br(2);

        $cm = null;
        if ($command->getName() == 'info') {
            $cm = new Info();
        }

        if ($command->getName() == 'mr') {
            $cm = new MR();
        }

        if ($command->getName() == 'mr-merge') {
            $cm = new AcceptMR();
        }

        if ($command->getName() == 'run') {
            $cm = new Run();
        }

        if ($cm) {
            $cm->handle($this, $command, $input, $output);
        }
    }

    public function listTags()
    {
        $climate = new CLImate();

        $tags = $this->gl->getTags($this->project_id);

        $climate->info('Project TAGS:');
        $data = [];
        foreach ($tags as $tag) {
            $data[] = $tag->name;
        }

        $climate->columns($data);
    }
}