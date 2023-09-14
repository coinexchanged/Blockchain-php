<?php

/**
 * swl
 *
 * 20180705
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utils\RPC;
use App\{Currency, LbxHash, UsersWallet};
use App\DAO\BlockChain;

class UpdateHashStatus extends Command
{
    protected $signature = 'update_hash_status';
    protected $description = '更新链上哈希状态';

    public function handle()
    {
        $datas = LbxHash::whereIn('type', [0, 2]) //type业务类型:0.归拢,1.提币 2.打入手续费 
            ->where("status", 0) //status 0 未处理  1处理成功   2处理失败
            ->get();
        $this->comment("开始执行");
        foreach ($datas as $d) {
            $this->updateHashStatus($d);
        }
        $this->comment("结束任务");
    }

    public function updateHashStatus($data) //测试计划任务更新链上余额
    {   
        if (empty($data->txid)) {
            return false;
        }
        echo 'id:' . $data->id . ',正在检测Hash:' . $data->txid . PHP_EOL;
        try {
            DB::beginTransaction();
            $user_wallet = UsersWallet::lockForUpdate()->find($data->wallet_id);
            $currency = Currency::find($user_wallet->currency);
            if (empty($currency)) {
                throw new \Exception('币种不存在');
            }
            $currency_type = $currency->type;
            if (!in_array($currency_type, ['usdt', 'btc', 'eth', 'erc20'])) {
                throw new \Exception('不支持的币种');
            }
            $currency_type == 'erc20' &&  $currency_type = 'eth';
            if ($data->type == 0) {
                // 只有归拢的才查询链上余额并更新余额
                try {
                    BlockChain::updateWalletBalance($user_wallet);
                } catch (\Exception $ex) {
                    echo $ex->getMessage() . PHP_EOL;
                }
            } elseif ($data->type == 2) {
                if ($currency->type == 'usdt') {
                    $currency_type = 'btc';
                } elseif ($currency->type == 'erc20') {
                    $currency_type = 'eth';
                }
            }

            $chain_client = app('LbxChainServer');
            $uri = "/wallet/" . $currency_type . '/tx';
            $response = $chain_client->request('get', $uri, [
                'query' => [
                    'hash' => $data->txid,
                ]
            ]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);

            //记录请求日志
            file_exists(base_path('storage/logs/blockchain/')) || @mkdir(base_path('storage/logs/blockchain/'));
            Log::useDailyFiles(base_path('storage/logs/blockchain/blockchain'), 7);
            Log::critical($uri, $result);
            if (isset($result["code"]) && $result["code"] == 0) {
                if ($data->type == 0) {
                    //当业务类型为归拢时,要减去子账号的链上余额
                    $new_balance = bc_sub($user_wallet->old_balance, $data->amount);
                    $user_wallet->old_balance = bc_comp($new_balance, 0) > 0 ? $new_balance: 0;
                } elseif ($data->type == 2) {
                    //如果代币和主币的手续费钱包分开了,就没必要做任何处理了,否则应该更新主币钱包的链上余额以避免手续费被到账的问题
                }
                $data->status = 1; //0 未处理  1处理成功   2处理失败
                $user_wallet->save();
            } elseif (isset($result["code"]) && $result["code"] > 1) {
                //失败的情况
                $data->status = 2;
            } else {
                //1为等待链上确认中 ，等待下次处理
                throw new \Exception('等待链上确认中');
            }
            $data->save();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->comment($ex->getFile());
            $this->comment($ex->getLine());
            $this->comment($ex->getMessage());
        }
    }
}
