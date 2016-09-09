<?php

require dirname(__DIR__) . '/Ptgm-WebSocket/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once "ws-comunicacao.php";

 $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Comunicacao()
            )
        ),
        8080
    );

$server->run();
