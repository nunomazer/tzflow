<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 14:06
 */

namespace Tzflow\Gitlab;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torzer\GitlabClient\Gitlab;
use Tzflow\Commands\BaseCommand;

/**
 * Class Run an automated flow
 * @package Tzflow\Gitlab
 */
class Run
{
    public $command;
    public $service;
    public $input;

    public function handle(Service $service, BaseCommand $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->service = $service;
        $this->input = $input;

        $flowName = $input->getArgument('flow') ? $input->getArgument('flow') : "default";

        try {

            $commands = config('flow.'.$flowName);

            foreach ($commands as $key => $cmd) {

                $cmdName = $cmd['command'];

                $this->command->climate->info('Preparing flow <bold><yellow> ' . $flowName. ' </yellow></bold>');

                $app = $this->command->getApplication()->find($cmdName);

                $arguments = [
                    'command' => $cmdName,
                    '--no-logo' => true,
                ];

                $this->command->climate->br();
                $this->command->climate->border('=');
                $this->command->climate->backgroundDarkGray()->flank('Flow calling <bold><yellow> ' . $cmdName. ' </yellow></bold>');
                $this->command->climate->br();
                $this->command->climate->border('.');
                $this->command->climate->info('Arguments: ');

                foreach ($cmd['args'] as $index => $item) {
                    $arg = array_keys($item)[0];
                    $value = $item[$arg];

                    if ($arg <> 'command') {
                        if ($value === 'ask') {
                            $arguments[$arg] = $this->askArgumentValue($arg);
                        } else {
                            $arguments[$arg] = $value;
                        }

                        $pad = $this->command->climate->padding(30);

                        $pad->label($arg)->result($arguments[$arg]);
                    }
                }

                $this->command->climate->border('=');

                $argInput = new ArrayInput($arguments);


                $app->run($argInput, $output);

            }


        } catch (ClientException $ex) {
            $this->command->climate->info('');
            $this->command->climate->error('  Http status error: ' . $ex->getCode() . ' - ' . $ex->getResponse()->getReasonPhrase());
            $this->command->climate->error('  ' . $ex->getResponseBodySummary($ex->getResponse()));
            $this->command->climate->info('');
        } catch (\Exception $ex) {
            $this->command->climate->error($ex->getMessage());
        }
    }

    protected function askArgumentValue($arg)
    {
        if ($arg == '--tag-after') {
            $this->service->listTags();
        }

        $ask = $this->command->climate->input('Please enter the value for the argument/option:<bold><green>' . $arg . '</green></bold>');
        return $ask->prompt();
    }


}