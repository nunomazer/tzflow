<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:32
 */

namespace Tzflow\Gitlab;


use Symfony\Component\Console\Input\InputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;

class Service
{

    public $gl = null;

    public $project_id = null;

    public function __construct()
    {
        $this->gl = Gitlab::client(config('gitlab.api.token'), config('gitlab.api.url', 'https://gitlab.com/api/v4/'));
        $this->project_id = config('gitlab.project.id');
    }

    public function handle(BaseCommand $command, InputInterface $input)
    {
        if ($command->getName() == 'mr') {
            $cm = new MR();
        }

        if ($command->getName() == 'mr-merge') {
            $cm = new AcceptMR();
        }

        $cm->handle($this, $command, $input);
    }
}