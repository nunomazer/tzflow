<?php
/**
 * Created by PhpStorm.
 * User: nunomazer
 * Date: 21/12/17
 * Time: 16:20
 */

namespace Tzflow;


class Git
{
    static function push($source, $console = null)
    {
        $console->climate->info('Pushing ' . $source . ' to origin ... wait ...');
        exec('git push origin ' . $source . ' --progress 2>&1', $out, $status);
        foreach ($out as $line) {
            $console->climate->out($line);
        }
        if ($status) {
            if ($console) {
                $continue = $console->climate->confirm('Continue?');
                if ($continue->confirmed() == false) {
                    $console->climate->yellow('Command cancelled!');
                    return false;
                }
            }
        }

        return true;
    }
}