<?php

$composer = 'composer install';
exec($composer, $arr, $ret);
exec('echo "*/10 * * * * php ' . getcwd() . '/yusa.php" > cron.conf', $out, $ret);
exec('crontab cron.conf', $out, $ret);
exec('echo "DIR=' . getcwd() . '/" >> .env', $out, $ret);
echo PHP_EOL. 'Install Success!' . PHP_EOL;
