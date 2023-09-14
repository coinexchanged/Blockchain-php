<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;

class WebSocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:client {worker_command} {--mode=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'websocket client';

    protected $worker;

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

    protected $callback_class;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $class_name = config('websocket.client.callback_class');
        $class_name = \App\Utils\Workerman\WorkerCallback::class;
        $process_num = config('websocket.client.process_num');
//        $process_num = 8;
//        $process_num = 1;
        $this->callback_class = new $class_name();
        $this->worker = new Worker();
        $this->worker->count = $process_num;
        $this->worker->name = 'Huobi Websocket';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initWorker();
        $this->bindEvent();
        $this->worker->runAll();
    }

    protected function initWorker()
    {
        global $argv;
        $argv[1] = $command = $this->argument('worker_command');
        $mode = $this->option('mode');
        isset($mode) && $argv[2] = '-' . $mode;
    }

    protected function bindEvent()
    {
        foreach ($this->events as $key => $event) {
            method_exists($this->callback_class, $event) && $this->worker->$event = [$this->callback_class, $event];
        }
    }
}
