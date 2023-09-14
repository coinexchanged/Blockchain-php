<?php

namespace App\Console\Commands;

use App\AccountLog;
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
use App\WalletLog;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AutoTransfer extends Command
{
    private static $work = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto_transfer {my_command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '翻译日志';

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
        $redis_key = 'auto_transfer_work';
        $command = $this->argument('my_command');

        if ($command == 'stop') {
            $this->comment(Redis::get($redis_key));
            Redis::set($redis_key, false);
        } else {
            Redis::set($redis_key, true);
            $this->comment($command);


            try {

                $i = 0;
                while (Redis::get($redis_key)) {
                    $i++;
                    DB::beginTransaction();

                    try {
                        $wlogs = WalletLog::where('transfered', 0)->limit(10)->orderby('id', 'ASC')->get();

                        foreach ($wlogs as $wlog) {

                            $enkey = base64_encode($wlog->memo . '@en');
                            $jpkey = base64_encode($wlog->memo . '@jp');
                            $krkey = base64_encode($wlog->memo . '@kr');
                            $hkkey = base64_encode($wlog->memo . '@hk');
                            $spakey = base64_encode($wlog->memo . '@spa');
                            if (Redis::get($enkey)) {
                                $wlog->memo_en = Redis::get($enkey);
                                Redis::setex($enkey, 60 * 5, $wlog->memo_en);
                            } else {
                                $wlog->memo_en = mtranslate($wlog->memo, 'en', 'zh');
                                sleep(1);
                                Redis::setex($enkey, 60 * 5, $wlog->memo_en);
                            }
                            if (Redis::get($jpkey)) {
                                $wlog->memo_jp = Redis::get($jpkey);
                                Redis::setex($jpkey, 60 * 5, $wlog->memo_jp);
                            } else {
                                $wlog->memo_jp = mtranslate($wlog->memo, 'jp', 'zh');
                                sleep(1);
                                Redis::setex($jpkey, 60 * 5, $wlog->memo_jp);
                            }

                            if (Redis::get($hkkey)) {
                                $wlog->memo_hk = Redis::get($hkkey);
                                Redis::setex($hkkey, 60 * 5, $wlog->memo_hk);
                            } else {
                                $wlog->memo_hk = mtranslate($wlog->memo, 'cht', 'zh');
                                sleep(1);
                                Redis::setex($hkkey, 60 * 5, $wlog->memo_hk);
                            }

                            if (Redis::get($spakey)) {
                                $wlog->memo_spa = Redis::get($spakey);
                                Redis::setex($spakey, 60 * 5, $wlog->memo_spa);
                            } else {
                                $wlog->memo_spa = mtranslate($wlog->memo, 'spa', 'zh');
                                sleep(1);
                                Redis::setex($spakey, 60 * 5, $wlog->memo_spa);
                            }

                            if (Redis::get($krkey)) {
                                $wlog->memo_kr = Redis::get($krkey);
                                Redis::setex($krkey, 60 * 5, $wlog->memo_kr);
                            } else {
                                $wlog->memo_kr = mtranslate($wlog->memo, 'kor', 'zh');
                                sleep(1);
                                Redis::setex($krkey, 60 * 5, $wlog->memo_kr);
                            }

                            $wlog->transfered = 1;
                            $wlog->save();

                            $this->comment($wlog->memo_en);
                            $this->comment($wlog->memo_jp);
                            $this->comment($wlog->memo_kr);
                            $this->comment($wlog->memo_hk);
                            $this->comment($wlog->memo_spa);
                            $this->comment("\r\n");
                        }

                        $alogs = AccountLog::where('transfered', 0)->limit(10)->orderby('id', 'ASC')->get();
                        foreach ($alogs as $alog) {

                            $enkey = base64_encode($alog->info . '@en');
                            $jpkey = base64_encode($alog->info . '@jp');
                            $krkey = base64_encode($alog->info . '@kr');
                            $hkkey = base64_encode($alog->info . '@hk');
                            $spakey = base64_encode($alog->info . '@spa');
                            if (Redis::get($enkey)) {
                                $alog->info_en = Redis::get($enkey);
                                Redis::setex($enkey, 60 * 5, $alog->info_en);
                            } else {
                                $alog->info_en = mtranslate($alog->info, 'en', 'zh');
                                sleep(1);
                                Redis::setex($enkey, 60 * 5, $alog->info_en);
                            }
                            if (Redis::get($jpkey)) {
                                $alog->info_jp = Redis::get($jpkey);
                                Redis::setex($jpkey, 60 * 5, $alog->info_jp);
                            } else {
                                $alog->info_jp = mtranslate($alog->info, 'jp', 'zh');
                                sleep(1);
                                Redis::setex($jpkey, 60 * 5, $alog->info_jp);
                            }

                            if (Redis::get($hkkey)) {
                                $alog->info_hk = Redis::get($hkkey);
                                Redis::setex($hkkey, 60 * 5, $alog->info_hk);
                            } else {
                                $alog->info_hk = mtranslate($alog->info, 'cht', 'zh');
                                sleep(1);
                                Redis::setex($hkkey, 60 * 5, $alog->info_hk);
                            }

                            if (Redis::get($spakey)) {
                                $alog->info_spa = Redis::get($spakey);
                                Redis::setex($spakey, 60 * 5, $alog->info_spa);
                            } else {
                                $alog->info_spa = mtranslate($alog->info, 'spa', 'zh');
                                sleep(1);
                                Redis::setex($spakey, 60 * 5, $alog->info_spa);
                            }

                            if (Redis::get($krkey)) {
                                $alog->info_kr = Redis::get($krkey);
                                Redis::setex($krkey, 60 * 5, $alog->info_kr);
                            } else {
                                $alog->info_kr = mtranslate($alog->info, 'kor', 'zh');
                                sleep(1);
                                Redis::setex($krkey, 60 * 5, $alog->info_kr);
                            }

                            $alog->transfered = 1;
                            $alog->save();

                            $this->comment($alog->info_en);
                            $this->comment($alog->info_jp);
                            $this->comment($alog->info_kr);
                            $this->comment($alog->info_hk);
                            $this->comment($alog->info_spa);
                            $this->comment("\r\n");
                        }

                        DB::commit();
                        sleep(1);
                    } catch (\Exception $exception) {
                        DB::rollback();
                    }
                }
            } catch (\Exception $exception) {
                DB::rollback();
                return $this->error($exception->getMessage());
            }
        }

    }
}
