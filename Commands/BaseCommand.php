<?php

namespace Tzflow\Commands;

use League\CLImate\CLImate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Description of BaseCommand
 *
 * @author nunomazer
 */
abstract class BaseCommand extends Command {

    protected $name = 'command:name';

    protected $description = 'Command description';

    protected $help = 'This is the help of this command';

    protected $climate = null;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->climate = new CLImate();
    }

    protected function whisper($text)
    {
        $this->climate->whisper($text);
    }

    protected function shout($text)
    {
        $this->climate->shout($text);
    }

    protected function line($text)
    {
        $this->climate->gray($text);
    }

    protected function comment($text)
    {
        $this->climate->comment($text);
    }

    protected function info($text)
    {
        $this->climate->info($text);
    }

    protected function error($text)
    {
        $this->climate->error($text);
    }

    protected function configure()
    {
        parent::configure();

        $this->setName($this->name);
        $this->setDescription($this->description);
        $this->setHelp($this->help);
    }

    /**
     * Print logo
     */
    protected function displayLogo() {
        $this->comment(".___________.  ______   .______      ________   _______ .______      ");
        $this->comment("|           | /  __  \  |   _  \    |       /  |   ____||   _  \     ");
        $this->comment("`---|  |----`|  |  |  | |  |_)  |   `---/  /   |  |__   |  |_)  |    ");
        $this->comment("    |  |     |  |  |  | |      /       /  /    |   __|  |      /     ");
        $this->comment("    |  |     |  `--'  | |  |\  \----. /  /----.|  |____ |  |\  \----.");
        $this->comment("    |__|      \______/  | _| `._____|/________||_______|| _| `._____|");
        $this->comment("");
        $this->climate->whisper()->flank(" developed with ♥ by < torzer.com > team - version ." . $this->version(), '*', 7);
        $this->line('');
    }

    protected function version() {
        return exec('git describe --tags --abbrev=0');
    }


    protected function getSource() {
        $source = exec('git rev-parse --abbrev-ref HEAD');
        if ($this->option('source')) {
            $source = $this->option('source');
        }

        return $source;
    }

}