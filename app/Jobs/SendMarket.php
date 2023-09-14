<?php

namespace App\Jobs;

use App\Needle;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\UserChat;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use function GuzzleHttp\json_encode;

class SendMarket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $marketData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($market_data)
    {
        $this->marketData = $market_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

//        if (isset($this->marketData['type'])&&$this->marketData['type'] == 'kline') {
//            //30min  1week 60min 1mon  15min  5min  1day
//            $needles = Needle::where('symbol', $this->marketData['symbol'])->get();
//            foreach ($needles as $needle) {
//                $needle = $needle->toArray();
//                $needle['itime'] = strtotime($needle['itime']);
//
//                $curren = intval($this->marketData['time'] / 1000);
//
//                if ($this->marketData['period'] === '1min') {
//                    //一分钟
//                    $next = strtotime('+1 minutes', $curren);
//                } else if ($this->marketData['period'] === '5min') {
//                    $next = strtotime('+5 minutes', $curren);
//                } else if ($this->marketData['period'] === '15min') {
//                    $next = strtotime('+15 minutes', $curren);
//                } else if ($this->marketData['period'] === '60min') {
//                    $next = strtotime('+60 minutes', $curren);
//                } else if ($this->marketData['period'] === '1day') {
//                    $next = strtotime('+1 day', $curren);
//                } else if ($this->marketData['period'] === '1week') {
//                    $next = strtotime('+7 days', $curren);
//                } else if ($this->marketData['period'] === '1mon') {
//                    $next = strtotime('+1 month', $curren);
//                }
//                var_dump("当前：{$curren},下一个时间戳:{$next}");
//                if ($needle['itime'] >= $curren && $needle['itime'] < $next && $needle['itime']>=time()) {
//
//                    if ($this->marketData['period'] === '1min') {
//                        $this->marketData['open'] = $needle['open'];
//                        $this->marketData['high'] = $needle['high'];
//                        $this->marketData['close'] = $needle['close'];
//                        $this->marketData['low'] = $needle['low'];
//                    } else {
//                        $this->marketData['open'] = $this->marketData['open'] > $needle['open'] ? $this->marketData['open'] : $needle['open'];
//                        $this->marketData['high'] = $this->marketData['high'] > $needle['high'] ? $this->marketData['high'] : $needle['high'];
//                        $this->marketData['low'] = $this->marketData['high'] < $needle['low'] ? $this->marketData['low'] : $needle['low'];
//                        $this->marketData['close'] = $this->marketData['close'] < $needle['close'] ? $this->marketData['close'] : $needle['close'];
//                    }
//
//                    var_dump("修改了market：" . json_encode($this->marketData));
//                }
//            }
//
//        }
//        die;
        UserChat::sendText($this->marketData);
    }
}
