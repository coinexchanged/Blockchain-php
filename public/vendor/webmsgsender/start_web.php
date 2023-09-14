<?php
use Workerman\Worker;
use Workerman\WebServer;
use Workerman\Lib\Timer;
use PHPSocketIO\SocketIO;
use Dotenv\Dotenv;

include __DIR__ . '/vendor/autoload.php';

$base_path = __DIR__ . '/../../../';
$dotenv = new Dotenv($base_path);
$dotenv->load();
$socket_io_port = getenv('SOCKET_IO_PORT') ?? 0;
$http_worker_port = getenv('HTTP_WORKER_PORT') ?? 0;
$web_server_port = getenv('WEB_SERVER_PORT') ?? 0;

// 启动一个webserver，用于吐html css js，方便展示
// 这个webserver服务不是必须的，可以将这些html css js文件放到你的项目下用nginx或者apache跑
$web = new WebServer('http://0.0.0.0:' . $web_server_port);
$web->addRoot('localhost', __DIR__ . '/web');

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}