<?php

namespace Tzflow\Commands;

use League\CLImate\CLImate;
use Povils\Figlet\Figlet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of BaseCommand
 *
 * @author nunomazer
 */
abstract class BaseCommand extends Command
{

    protected $name = 'command:name';

    protected $description = 'Command description';

    protected $help = 'This is the help of this command';

    public $climate = null;

    /**
     * From here to be used by concret classes
     */

    public $service = null;

    public $driver = 'gitlab';

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->climate = new CLImate();

        if ($name <> 'init' && $name) {

            $this->driver = config('driver', 'gitlab');

            $serviceClass = 'Tzflow\\' . ucfirst($this->driver) . '\\Service';

            $this->service = new $serviceClass();

        }
    }

    public function line($text)
    {
        $this->climate->out($text);
    }

    protected function configure()
    {
        parent::configure();

        $this->setName($this->name);
        $this->setDescription($this->description);
        $this->setHelp($this->help);

        $this->addOption(
            'no-logo',
            null,
            InputOption::VALUE_NONE,
            'Do not show logo information'
        );
    }

    /**
     * Print logo
     */
    public function displayLogo()
    {
//        $this->climate->comment(".___________.  ______   .______      ________   _______ .______      ");
//        $this->climate->comment("|           | /  __  \  |   _  \    |       /  |   ____||   _  \     ");
//        $this->climate->comment("`---|  |----`|  |  |  | |  |_)  |   `---/  /   |  |__   |  |_)  |    ");
//        $this->climate->comment("    |  |     |  |  |  | |      /       /  /    |   __|  |      /     ");
//        $this->climate->comment("    |  |     |  `--'  | |  |\  \----. /  /----.|  |____ |  |\  \----.");
//        $this->climate->comment("    |__|      \______/  | _| `._____|/________||_______|| _| `._____|");
//        $this->climate->comment("");
        $figlet = new Figlet();
        $figlet->setFont('univers');
        $this->climate->comment($figlet->render('tzflow'));
        $this->climate->backgroundDarkGray()->flank("developed with <red>♥</red> by <bold><green>http://torzer.com</green></bold> team", '*', 10);
        // - version ." . $this->version(), '*', 7);
        $this->line('');
    }

    public function displayHeader($text)
    {
        //$borderLenght = strlen($text)+5*3*2+2;

//        $this->climate->border('-', $borderLenght);
//        $this->climate->br();
        $this->climate->flank('<bold>' . $text . '</bold>', '=');
        $this->climate->br();
//        $this->climate->border('-', $borderLenght);
        $this->climate->br();
    }

    public function handle($headerText = 'Executing command', InputInterface $input, OutputInterface $output)
    {
        $showLogo = ! $input->getOption('no-logo');

        if ($showLogo) {
            $this->climate->clear();
            $this->displayLogo();
        }
        $this->displayHeader($headerText);

        if ($this->service) {
            $this->service->handle($this, $input, $output);
        }
    }

    protected function version()
    {
        return exec('git describe --tags --abbrev=0');
    }


    public function getSource(InputInterface $input)
    {
        $source = exec('git rev-parse --abbrev-ref HEAD');
        if ($input->getOption('source')) {
            $source = $input->getOption('source');
        }

        return $source;
    }

}