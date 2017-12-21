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

class Info extends BaseCommand
{
    protected $name = 'info';
    protected $description = 'Shows base infortmation of this application';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayLogo();
    }

}