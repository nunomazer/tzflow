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

        $padding = $this->climate->padding(40);

        $this->climate->border();
        $this->climate->flank('tzflow.json configuration used in this folder', ' ', 10);
        $this->climate->border();

        $driver = config('driver');

        $data = [
            ['driver', $driver, 'background_light_red'],
            ['project.id', config($driver.'.project.id')],
        ];

        foreach ($data as $index => $item) {
            $pre = isset($item[2]) ? '<'.$item[2].'>' : '';
            $pos = isset($item[2]) ? '</'.$item[2].'>' : '';
            $padding->label('<bold>'.$item[0].'</bold>')->result($pre.' '.$item[1].' '.$pos);
        }

        $this->climate->br();

    }

}