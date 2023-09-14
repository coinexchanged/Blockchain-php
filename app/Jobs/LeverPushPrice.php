<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Api\LeverController;
use App\UserChat;

class LeverPushPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            extract($this->params);
            $lever = new LeverController();
            $lever_transaction = $lever->getLastLeverTransaction($legal_id, $currency_id);
            $push_data = array_merge([
                'type' => 'lever_data'
            ], $this->params, [
                'in' => $lever_transaction['in']->toJson(),
                'out' => $lever_transaction['out']->toJson(),
            ]);
            //SendMarket::dispatch($push_data)->onQueue('send.price');
            //$result = UserChat::sendText($push_data);
        } catch (\Exception $e) {
            echo '文件:' . $e->getFile() . PHP_EOL;
            echo '行号:' . $e->getLine() . PHP_EOL;
            echo '错误:' . $e->getMessage() . PHP_EOL;
        }
    }
}
