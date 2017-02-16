<?php

require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

class Request
{
    public static function get(string $url, $ua = null, $ref = null) : array
    {
        if(!is_string($url) || strlen($url) == 0)
        {
            return
            [
                'status'    =>  400,
                'type'      =>  null,
                'body'      =>  null
            ];
        }

        $ua = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)';
        $ref = $url;

        $client = new Client(['verify' => false]);
        try
        {
            $response = $client->request
            (
                'GET',
                $url,
                [
                    'headers'   =>
                    [
                        'User-Agent'    =>  $ua,
                        'Referer'       =>  $ref
                    ],
                    'timeout'   =>  5
                ]
            );

            return
            [
                'status'    =>  $response->getStatusCode(),
                'type'      =>  $response->getHeader('Content-Type'),
                'body'      =>  $response->getBody()
            ];
        }
        catch(\Exception $e)
        {
            return
            [
                'status'    =>  500,
                'type'      =>  null,
                'body'      =>  null
            ];
        }
    }
}

class Command
{
    public static function update()
    {
        Command::stop();
        chdir('../tomori');
        exec('git pull', $out, $ret);
        chdir('../ayumi');
        exec('git pull', $out, $ret);
        chdir('../yusa');
        Command::start();
    }

    public static function stop()
    {
        exec('ps aux | grep php', $out, $ret);
        if(count($out) > 0)
        {
            $ps = [];
            foreach($out as $line)
            {
                if(strpos($line, 'tomori') !== false || strpos($line, 'ayumi') !== false)
                {
                    $ps[] = $line;
                }
            }
            if(count($ps) > 0)
            {
                foreach($ps as $p)
                {
                    $i = explode(' ', preg_replace('/[\s]{2,}/', ' ', $p));
                    $pid = $i[1];
                    exec('kill ' . $pid, $out, $ret);
                }
            }
        }
    }

    public static function start()
    {
        chdir('../tomori');
        exec('nohup php tomori.php > /dev/null 2>&1 &', $out, $ret);
        chdir('../ayumi');
        exec('nohup php ayumi.php > /dev/null 2>&1 &', $out, $ret);
        chdir('../yusa');
    }
}

$url = getenv('URL');
if(strlen($url) < 1)
{
    exit(-1);
}
$response = Request::get($url);
$order = null;
if($response['status'])
{
    $html = $response['body'];
    $html = str_replace("\r", '', $html);
    $html = explode("\n", $html);
    $tweet = [];
    foreach($html as $line)
    {
        if(strpos($line, '<div class="dir-ltr" dir="ltr">') !== false)
        {
            $tweet[] = trim(str_replace('<div class="dir-ltr" dir="ltr">', '', $line));
        }
    }
    if(count($tweet) >= 2)
    {
        $order = $tweet[1];
    }
}
if($order != null)
{
    if(file_exists('order.log'))
    {
        $old = file_get_contents('order.log');
        if($order === $old)
        {
            exit(1);
        }
    }
    
    $command = explode(' ', $order);
    switch($command[0])
    {
        case 'update':
            Command::update();
            break;
        case 'stop':
            Command::stop();
            break;
        case 'start':
            Command::start();
            break;
    }

    file_put_contents('order.log', $order);
}
