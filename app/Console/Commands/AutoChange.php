<?php

namespace App\Console\Commands;

use App\AutoList;
use App\Currency;
use App\CurrencyQuotation;
use App\MarketHour;
use App\Setting;
use App\TransactionComplete;
use App\TransactionIn;
use App\TransactionOut;
use App\UserChat;
use App\Users;
use App\UsersWallet;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AutoChange extends Command
{
    private static $work = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_change {my_command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动促成币币交易';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $command = $this->argument('my_command');

        if ($command == 'stop') {
            $this->comment(Redis::get('auto_change_work'));
            Redis::set("auto_change_work", false);
        } else {
            Redis::set("auto_change_work", true);
            $this->comment($command);


            try {
                //获取所有的订单
//                $transasctionIns = TransactionIn::all()->toArray();
//                foreach ($transasctionIns as $transasctionin) {
//
//                }
                $i = 0;
                while (Redis::get("auto_change_work")) {
                    $i++;

                    DB::beginTransaction();
                    $transasctionIns = TransactionIn::all();
                    foreach ($transasctionIns as $transasctionin) {
                        $lastPrice = $this->getLastPrice($transasctionin->currency, $transasctionin->legal, 'sell');
                        $this->comment("当前卖出价格" . $lastPrice);
                        if ($lastPrice && $lastPrice > 0) {
                            if (bc_comp($transasctionin->price, $lastPrice, 8) >= 0) {
                                $has_num = $transasctionin->number;
                                $res = TransactionIn::transaction($transasctionin->price, $transasctionin->number, Users::find($transasctionin->user_id), $transasctionin->legal, $transasctionin->currency);
                                $transasctionin->delete();
                                UserChat::sendText(['type' => 'update_balance_'.$transasctionin->user_id]);
                                $this->comment('撮合一笔买入交易' . json_encode($res) . json_encode(Users::find($transasctionin->user_id)));
                            }
                        }
                    }

                    $transasctionOuts = TransactionOut::all();
                    foreach ($transasctionOuts as $transasctionout) {
                        $lastPrice = $this->getLastPrice($transasctionin->currency, $transasctionin->legal, 'buy');
                        if (bc_comp($lastPrice, $transasctionout->price, 8) >= 0) {
                            $has_num = $transasctionout->number;
                            $user_currency = UsersWallet::where("user_id", $transasctionout->user_id)
                                ->where("currency", $transasctionout->user_id)
                                ->lockForUpdate()
                                ->first();
                            TransactionOut::transaction($transasctionout->price, $transasctionout->number, Users::find($transasctionout->user_id), $user_currency, $transasctionin->legal, $transasctionin->currency);
                            $transasctionout->delete();
                            UserChat::sendText(['type' => 'update_balance_'.$transasctionout->user_id]);
                            $this->comment('撮合一笔卖出交易');
                        }
                    }

                    DB::commit();
//                    if ($i % 10 == 0) {
//
//                    }
                    $this->comment('下一次轮回');
                    sleep(1);
                }
            } catch (\Exception $exception) {
                DB::rollback();
                return $this->error($exception->getMessage());
            }
        }

    }

    /**
     * @param $currency_id
     * @param $legal_id
     * @param string $type sell or buy
     * @return int|mixed
     */
    private function getLastPrice($currency_id, $legal_id, $type = 'sell')
    {

        $currency = Currency::find($currency_id);
        $legal = Currency::find($legal_id);
        $symbol = strtolower($currency->name . $legal->name);
        $rkey = "market.{$symbol}.kline.1min";
        $con = Redis::get($rkey);
        $obj = json_decode($con);
        return $obj->tick->close;
    }
}
