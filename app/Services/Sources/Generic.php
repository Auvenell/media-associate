<?php

namespace App\Services\Sources;

use phpseclib3\Net\SSH2 as NetSSH2;

class Generic
{
    public function genericSiteHandler($url) {
        $ssh = new NetSSH2(env('MAC_SERVER_IP', '192.168.1.236'));
        if (!$ssh->login(env('MAC_SERVER_USER'), env('MAC_SERVER_PASSWD'))) {
            exit('Login Failed');
        }

        $command = 'osascript Development/media-associate/site-capture.scpt' . ' ' . $url;
        echo $ssh->exec($command);
    }
}
