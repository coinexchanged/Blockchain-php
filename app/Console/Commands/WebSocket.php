<?php

namespace App\Console\Commands;

use App\UserChat;
use Illuminate\Console\Command;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;
use function foo\func;

class WebSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket {worker_command} {--mode=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'websocket';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
//        $class_name = config('websocket.client.callback_class');
//        $class_name = \App\Utils\Workerman\WorkerCallback::class;
//        $process_num = config('websocket.client.process_num');
////        $process_num = 1;
//        $this->callback_class = new $class_name();
//        $this->worker = new Worker();
//        $this->worker->count = $process_num;
//        $this->worker->name = 'Huobi Websocket';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $this->initWorker();
//        $this->bindEvent();
//        $this->worker->runAll();

        $worker = new Worker();
        $worker->count = 1;
        $worker->name = 'Huobi Websocketddd';
        $worker->onWorkerStart = (function () {

            $server_address = 'ws://api.huobi.pro:443/ws';
            AsyncTcpConnection::$defaultMaxPackageSize = 1048576000;
            $connection = new AsyncTcpConnection($server_address);

            $connection->transport = 'ssl';
            $connection->onConnect = (function ($con) {

                $sub_data = json_encode([
                    'sub' => 'market.btcusdt.kline.1min',
                    'id' => 'market.btcusdt.kline.1min',
                    //'freq-ms' => 5000, //推送频率，实测只能是0和5000，与官网文档不符
                ]);
                $con->send($sub_data);

            });

            $connection->onMessage = (function ($con, $data) {
                $data = gzdecode($data);

                $data = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
//                var_dump($data);
//                return;
                if (isset($data->ping)) {
                    echo "回应ping\r\n";
                    $con->send(json_encode(['pong' => $data->ping]));
                } else {
                    UserChat::sendText($data);
                }
            });

            $connection->connect();
        });


        $worker->runAll();

    }

    protected function initWorker()
    {
//        global $argv;
//        $argv[1] = $command = $this->argument('worker_command');
//        $mode = $this->option('mode');
//        isset($mode) && $argv[2] = '-' . $mode;
    }

    protected function bindEvent()
    {
//        foreach ($this->events as $key => $event) {
//            method_exists($this->callback_class, $event) && $this->worker->$event = [$this->callback_class, $event];
//        }
    }
}
