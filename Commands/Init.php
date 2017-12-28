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
use Torzer\GitlabClient\Gitlab;

class Init extends BaseCommand
{
    protected $name = 'init';
    protected $description = 'Creates a new tzflow.json file in current folder';
    protected $help = 'Creates the configuration file called tzflow.json in current folder, required for tzflow works correctly';

    protected function configure()
    {
        parent::configure();

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->handle('INIT tzflow.json', $input, $output);

        do  {
            $driver = $this->climate->radio('Driver (hub repository):  ', ['gitlab'])->prompt();
        } while ($driver == '');

        do {
            $id = $this->climate->input('Project id at ' . $driver)->prompt();
        } while ($id == '');

        do {
            $token = $this->climate->input('Your token at ' . $driver)->prompt();
        } while ($token == '');


        $this->climate->comment('Testing connection with project ... ');

        if ($driver == 'gitlab') {
            $hub = Gitlab::client($token);
        }

        $project = $hub->getProject($id);

        $this->climate->info('Project ' . $project->name . ' found !!');


        $json = [
            "driver" => $driver,
            "gitlab" => [
                "project" => [
                    "id" => $id,
                ],
                "api" => [
                    "token" => $token,
                ],
                "default" => [
                    "mr" => [
                        "target-branch" => "dev",
                    ]
                ]
            ]
        ];

        if (file_put_contents('tzflow.json', json_encode($json, JSON_PRETTY_PRINT))) {
            $json['gitlab']['api']['token'] = 'should not version this value';
            file_put_contents('tzflow.json.example', json_encode($json, JSON_PRETTY_PRINT));

            $this->climate->info('*** tzflow.json created ***');
            $this->climate->yellow('You should add the tzflow.json to .gitignore to protect your token');
            $this->climate->info('A tzflow.json.example, without token value, was created to be included in git tracking');
        } else {
            $this->climate->error('Error creating tzflow.json file !!');
        }

    }

}