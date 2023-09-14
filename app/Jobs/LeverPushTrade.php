<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\LeverTransaction;
use App\UsersWallet;
use App\UserChat;

class LeverPushTrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $legal_id;
    protected $currency_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $legal_id, $currency_id)
    {
        $this->user_id = $user_id;
        $this->legal_id = $legal_id;
        $this->currency_id = $currency_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo  '法币id:' . $this->legal_id . ',交易币id:' .$this->currency_id . ',user_id:' . $this->user_id . PHP_EOL;
        try {
            $start = microtime(true);
            //推送用户风险率和订单
            $user_wallet = UsersWallet::where('user_id', $this->user_id)
                ->where('currency', $this->legal_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception($this->user_id.':钱包不存在');
            }
            //取盈亏总额
            list(
                'caution_money_total' => $caution_money_all,
                'origin_caution_money_total' => $origin_caution_money_all,
                'profits_total' => $profits_all
            ) = LeverTransaction::getUserProfit($this->user_id, $this->legal_id);
            //取该交易对盈亏总额
            list(
                'caution_money_total' => $caution_money,
                'origin_caution_money_total' => $origin_caution_money,
                'profits_total' => $profits
            ) = LeverTransaction::getUserProfit($this->user_id, $this->legal_id, $this->currency_id);
            //取风险率
            $hazard_rate = LeverTransaction::getWalletHazardRate($user_wallet);

            //取用户所有持仓交易
            $lever_transaction_all = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
                ->where('legal', $this->legal_id)
                ->where('user_id', $this->user_id)
                ->orderBy('id', 'desc')
                ->get();
            //委托中交易
            $lever_transaction_entrust = LeverTransaction::where('status', LeverTransaction::ENTRUST)
                ->where('legal', $this->legal_id)
                ->where('user_id', $this->user_id)
                ->orderBy('id', 'desc')
                ->get();
            //取交易对持仓交易
            $lever_transaction_cur = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
                ->where('legal', $this->legal_id)
                ->where('currency', $this->currency_id)
                ->where('user_id', $this->user_id)
                ->orderBy('id', 'desc')
                ->get();
            $push_data = [
                'type' => 'lever_trade',
                'to' => $this->user_id,
                'hazard_rate' => $hazard_rate, //风险率
                'caution_money_all' => $caution_money_all, //总保证金
                'origin_caution_money_all' => $origin_caution_money_all, //原始总保证金
                'profits_all' => $profits_all, //总盈亏
                'caution_money' => $caution_money, //当前交易对的保证金
                'origin_caution_money' => $origin_caution_money, //当前交易对的原始保证金
                'profits' => $profits, //当前交易对的盈亏金额
                'trades_all' => $lever_transaction_all->toJson(), //所有交易
                'trades_entrust' => $lever_transaction_entrust->toJson(), //取所有委托交易
                'trades_cur' => $lever_transaction_cur->toJson(), //当前交易对的交易
            ];
            SendMarket::dispatch($push_data)->onQueue('send.trade');
            $end = microtime(true);
            //$result = UserChat::sendChat($push_data);
        } catch (\Exception $e) {
            echo '文件:' . $e->getFile() . PHP_EOL;
            echo '行号:' . $e->getLine() . PHP_EOL;
            echo '错误:' . $e->getMessage() . PHP_EOL;
            return ;
        }
        echo '共计耗时:' . ($end - $start) . '秒'. PHP_EOL;
    }
}
