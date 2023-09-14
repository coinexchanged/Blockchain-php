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
$text_worker_port = getenv('TEXT_WORKER_PORT') ?? 0;
$socket_use_ssl = getenv('SOCKET_USE_SSL') ?? 0;
$socket_ssl_cert = getenv('SOCKET_SSL_CERT') ?? '';
$socket_ssl_pk = getenv('SOCKET_SSL_PK') ?? '';

// 全局数组保存uid在线数据
$uidConnectionMap = array();
// 记录最后一次广播的在线用户数
$last_online_count = 0;
// 记录最后一次广播的在线页面数
$last_online_page_count = 0;
// PHPSocketIO服务
if ($socket_use_ssl) {
    $context = array(
        'ssl' => array(
            'local_cert'  => $socket_ssl_cert, // 也可以是crt文件
            'local_pk'    => $socket_ssl_pk,
            'verify_peer' => false,
        )
    );
    $sender_io = new SocketIO($socket_io_port, $context);
} else {
    $sender_io = new SocketIO($socket_io_port);
}
// 客户端发起连接事件时，设置连接socket的各种事件回调
$sender_io->on('connection', function ($socket) {
    // 当客户端发来登录事件时触发
    $socket->on('login', function ($uid) use ($socket) {
        global $uidConnectionMap, $last_online_count, $last_online_page_count;
        // 已经登录过了
        if (isset($socket->uid)) {
            return;
        }
        // 更新对应uid的在线数据
        $uid = (string)$uid;
        if (!isset($uidConnectionMap[$uid])) {
            $uidConnectionMap[$uid] = 0;
        }
        // 这个uid有++$uidConnectionMap[$uid]个socket连接
        ++$uidConnectionMap[$uid];
        // 将这个连接加入到uid分组，方便针对uid推送数据
        $socket->join($uid);
        $socket->uid = $uid;
        // 更新这个socket对应页面的在线数据
        //$socket->emit('update_online_count', "当前<b>{$last_online_count}</b>人在线，共打开<b>{$last_online_page_count}</b>个页面");
    });

    // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
    $socket->on('disconnect', function () use ($socket) {
        if (!isset($socket->uid)) {
            return;
        }
        global $uidConnectionMap, $sender_io;
        // 将uid的在线socket数减一
        if (--$uidConnectionMap[$socket->uid] <= 0) {
            unset($uidConnectionMap[$socket->uid]);
        }
    });
});

// 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
$sender_io->on('workerStart', function () use ($http_worker_port, $text_worker_port, $sender_io) {
    // 监听一个http端口
    $inner_http_worker = new Worker('http://0.0.0.0:' . $http_worker_port);
    // 当http客户端发来数据时触发
    $inner_http_worker->onMessage = function ($http_connection, $data) use ($sender_io) {
        global $uidConnectionMap;
        $_POST = $_POST ? $_POST : $_GET;
        // 推送数据的url格式 type=publish&to=uid&content=xxxx
        if (@$_POST['type']) {
            global $sender_io;
            $to = @$_POST['to'];
            $_POST['content'] = htmlspecialchars(@$_POST['content']);
            // 有指定uid则向uid所在socket组发送数据
            if ($to) {
                $sender_io->to($to)->emit($_POST['type'], $_POST);
            } else {
                $sender_io->emit($_POST['type'], $_POST);
            }
            // http接口返回，如果用户离线socket返回fail
            if ($to && !isset($uidConnectionMap[$to])) {
                return $http_connection->send('offline');
            } else {
                return $http_connection->send('ok');
            }
        }
        return $http_connection->send('fail');
    };

    $inner_text_worker = new Worker('text://0.0.0.0:' . $text_worker_port);

    $inner_text_worker->onMessage = function ($text_connection, $data) use ($sender_io) {
        global $uidConnectionMap;
        /*
        echo date('Y-m-d H:i:s ') . '接收到长连接数据'. PHP_EOL;
        var_dump($data);
        */
        $data = @json_decode($data, true);
        $to =  $data['to'] ?? null;
        if ($to) {
            if (isset($uidConnectionMap[$to])) {
                $sender_io->to($to)->emit($data['type'] ?? 'default', $data);
            } else {
                return $text_connection->send(json_encode([
                    'type' => 'error',
                    'message' => 'offline',
                ]));
            }
        } else {
            $sender_io->emit($data['type'] ?? 'default', $data);
            return $text_connection->send(json_encode([
                'type' => 'ok',
                'message' => 'success',
            ]));
        }
    };
    // 执行监听
    $inner_text_worker->listen();
    $inner_http_worker->listen();

    // 一个定时器，定时向所有uid推送当前uid在线数096及在线页面数
    // Timer::add(1, function () {
    //     global $uidConnectionMap, $sender_io, $last_online_count, $last_online_page_count;
    //     $online_count_now = count($uidConnectionMap);
    //     $online_page_count_now = array_sum($uidConnectionMap);
    //     // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
    //     if ($last_online_count != $online_count_now || $last_online_page_count != $online_page_count_now) {
    //         $sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
    //         $last_online_count = $online_count_now;
    //         $last_online_page_count = $online_page_count_now;
    //     }
    // });
});

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
