<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:06
 */

namespace Tzflow\Gitlab;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;
use GuzzleHttp\Exception\ClientException;
use Tzflow\Git;

/**
 * Class MR - Merge Request for Gitlab repo
 * @package Tzflow\Gitlab
 */
class Info
{
    public $command;
    public $service;
    public $input;

    public function handle(Service $service, BaseCommand $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->service = $service;
        $this->input = $input;

        $this->command->climate->whisper('Getting tags ...');
        $this->service->listTags();

    }
}