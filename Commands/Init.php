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

        $json = null;
        if (file_exists('./tzflow.json')) {
            $json = json_decode(file_get_contents('./tzflow.json'), true);
        }

        do {
            $driver = $this->climate->radio('Driver (hub repository):  ', ['gitlab'])->prompt();
        } while ($driver == '');

        do {
            $txt = 'Project id at ' . $driver ;
            if ($json) {
                $txt .= ' [' . $json[$driver]['project']['id'] . ']';
            }
            $id = $this->climate->input($txt)->prompt();
            if ($json AND $id == '') {
                $id = $json[$driver]['project']['id'];
            }
        } while ($id == '');

        do {
            $txt = 'Your token at ' . $driver ;
            if ($json) {
                $txt .= ' [' . $json[$driver]['api']['token'] . ']';
            }
            $token = $this->climate->input($txt)->prompt();
            if ($json AND $token == '') {
                $token = $json[$driver]['api']['token'];
            }
        } while ($token == '');

        $url = $this->climate->input('Api url, press enter to use the default of the driver ' . $driver)->prompt();

        $this->climate->comment('Testing connection with project ... ');

        if ($driver == 'gitlab') {
            if ($url <> '') {
                $hub = Gitlab::client($token, $url);
            } else {
                $hub = Gitlab::client($token);
            }
        }

        $project = $hub->getProject($id);

        $this->climate->info('Project ' . $project->name . ' found !!');

        $flow['default'] = [
            "command" => "mr",
            "args" => [
                "--target" => "dev",
                "--merge" => true,
                "--remove-source" => true,
                "--update-local" => true
            ]
        ];

        $createFlow = null;
        do {
            if (is_object($createFlow) AND $createFlow->confirmed()) {
                do {
                    $flowName = $this->climate->input('Flow name ')->prompt();
                } while ($flowName == '');

                $flow[$flowName] = ['command' => 'mr'];

                do {
                    $argsQuestion = $this->climate->checkboxes('Choose the arguments for mr command:', [
                        '--merge', '--push', '--no-push', '--remove-source', '--update-local', '--tag-after'
                    ]);
                    $args = $argsQuestion->prompt();
                } while ($args == '');


            }
            $createFlow = $this->climate->confirm('Create a new flow to several steps execution?');
        } while ($createFlow->confirmed() == 'y');

        $json = [
            "driver" => $driver,
        ];

        if ($driver == 'gitlab') {
            $json["gitlab"] = [
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
            ];

            if ($url <> '') {
                $json['gitlab']['api']['url'] = $url;
            }
        }

        $json['flow'] = $flow;

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