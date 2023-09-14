<?php

namespace App\Utils\Workerman;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use App\MarketHour;

class WorkerCallback
{
    protected $events = [
        'onWorkerStart',
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWorkerReload'
    ];

    protected $interval = 1; //行情处理时间间隔，单位秒，支持小数
    protected $wsConnection; //websocket client连接
    protected $worker;

    public function __construct()
    {
        $this->registerEvent();
    }

    public function registerEvent()
    {
        foreach ($this->events as $key => $event) {
            method_exists($this, $event) && $this->$event = [$this, $event];
        }
    }

    public function onWorkerStart($worker)
    {

//        $con = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');
//        $con->transport = 'ssl';
//        $con->onConnect = (function ($con) use ($worker) {
//            $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
//            $topic = "market.btcusdt.kline.{$periods[$worker->id]}";
//            $data = ['sub' => $topic, 'id' => $topic];
//            $con->send(json_encode($data));
//        });
//        $con->onMessage = (function ($con, $data) use ($worker) {
//            $data = gzdecode($data);
//            $data = json_decode($data);
//            if (isset($data->ping)) {
//                $send_data = [
//                    'pong' => $data->ping,
//                ];
//                $send_data = json_encode($send_data);
//                $con->send($send_data);
//                echo $worker->id . '回复心跳反应' . "\r\n";
//            } else {
////                var_dump($data);
//            }
//        });
//        $con->connect();

        $this->worker = $worker;
        echo '进程' . $worker->id .'启动'. PHP_EOL;
        if ($worker->id < 8) {
            $periods = ['1min', '5min', '15min', '30min', '60min', '1day', '1mon', '1week']; //['1day', '1min'];
            $period = $periods[$worker->id];
            $worker->name = 'huobi.ws:' . 'market.kline.' . $period;
        } else {
            $worker->name = 'huobi.ws:' . 'market.depth.step0';
        }
        $ws_con = new WsConnection($worker->id);
        $this->wsConnection = $ws_con;
        $ws_con->connect();

    }

    public function onWorkerReload($worker)
    {
    }

    public function onConnect($connection)
    {
    }

    public function onClose($connection)
    {
    }

    public function onError($connection, $code, $msg)
    {
    }

    public function onMessage($connection, $data)
    {
    }
}
