<?php

exec('composer install', $out, $ret);
exec('echo "*/5 * * * * php ' . getcwd() . '/yusa.php" > cron.conf', $out, $ret);
exec('crontab cron.conf', $out, $ret);
exec('echo "DIR=' . getcwd() . '/" >> .env', $out, $ret);
echo PHP_EOL. 'Install Success!' . PHP_EOL;
