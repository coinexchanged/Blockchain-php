<?php

namespace App\Console\Commands;

use App\AutoList;
use App\CurrencyQuotation;
use App\MarketHour;
use App\Setting;
use App\TransactionComplete;
use App\UsersWallet;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_order {id : id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '机器人自动下单';

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
        $id = $this->argument('id');
        $faker = Factory::create();
        DB::beginTransaction();
        try{
            while (!empty($auto = AutoList::find($id))){
                if (empty($auto->is_start)){
                    DB::rollback();
                    return $this->error('机器人已关闭-'.Carbon::now()->toDateTimeString());
                    break;
                }else{
                    $this->info('开启机器人-'.Carbon::now()->toDateTimeString());
                    //获取到当前区间
                    $price_area = AutoList::getPriceArea($auto->currency_id,$auto->legal_id);
                    if (!empty($price_area)){
                        $this->info('当前价格区间为 '.$price_area['min'].'-'.$price_area['max']);
                        $this->info('设置价格区间为 '.$auto->min_price.'-'.$auto->max_price);
                        //在价格区间生成记录
                        if (($auto->min_price <= $price_area['min']) && ($auto->max_price >= $price_area['max'])){
                            $new_complete = new TransactionComplete();
                            $new_complete->user_id = $auto->buy_user_id;
                            $new_complete->from_user_id = $auto->sell_user_id;
                            $new_complete->price = $faker->randomFloat(2,$price_area['min'],$price_area['max']);
                            $new_complete->number = $faker->randomFloat(2,$auto->min_number,$auto->max_number);
                            $new_complete->create_time = time();
                            $new_complete->currency = $auto->currency_id;
                            $new_complete->legal = $auto->legal_id;
                            $new_complete->save();
                            //买家法币扣除交易币增加
                            $buy_wallet_legal = UsersWallet::where('user_id',$auto->buy_user_id)->where('currency',$auto->legal_id)->lockForUpdate()->first();
                            if (!empty($buy_wallet_legal)){
                                $legal_decrement = bc_mul($new_complete->number,$new_complete->price,5);
                                $buy_wallet_legal->decrement('legal_balance',$legal_decrement);
                            }
                            $buy_wallet = UsersWallet::where('user_id',$auto->buy_user_id)->where('currency',$auto->currency_id)->lockForUpdate()->first();
                            if (!empty($buy_wallet)){
                                $buy_wallet->increment('change_balance',$new_complete->number);
                            }
                            //卖家法币增加交易币扣除
                            $sell_wallet_legal = UsersWallet::where('user_id',$auto->sell_user_id)->where('currency',$auto->legal_id)->lockForUpdate()->first();
                            if (!empty($sell_wallet_legal)){
                                $legal_increment = bc_mul($new_complete->number,$new_complete->price,5);
                                $sell_wallet_legal->increment('legal_balance',$legal_increment);
                            }
                            $sell_wallet = UsersWallet::where('user_id',$auto->sell_user_id)->where('currency',$auto->currency_id)->lockForUpdate()->first();
                            if (!empty($sell_wallet)){
                                $sell_wallet->decrement('change_balance',$new_complete->number);
                            }
                            $this->info($auto->legal_name.'/'.$auto->currency_name.' 生成价格为 '.$new_complete->price.' 数量为 '.$new_complete->number.' 的交易记录-'.Carbon::now()->toDateTimeString());

                            $total = TransactionComplete::where('currency', $auto->currency_id)
                                ->where('legal', $auto->legal_id)
                                ->where('create_time', '>=', strtotime(date('Y-m-d')))
                                ->sum('number');
                            $data = [
                                'legal_id' => $auto->legal_id,
                                'currency_id' => $auto->currency_id,
                                'volume' => $total,
                                'now_price' => $new_complete->price
                            ];
                            CurrencyQuotation::updateTodayPriceTable($data);//


                            MarketHour::batchWriteMarketData($auto->currency_id, $auto->legal_id, $new_complete->number, $new_complete->price, 4); //写入行情数据

                            DB::commit();
                        }
                    }else{
                        DB::rollback();
                        return $this->error('没有当前价格区间');
                    }
                    sleep($auto->need_second);
                }

            }

        }catch (\Exception $exception){
            DB::rollback();
            return $this->error($exception->getMessage());
        }



    }
}
