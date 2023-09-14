<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\UsersWallet;
use App\Currency;

class RegenerateWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'regenerate:wallet {id : id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新生成钱包';

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
        $currency = Currency::find($id);
        if (!$currency) {
            throw new \Exception('币种不存在');
        }
        $http_client = app('LbxChainServer');
        $wallets = UsersWallet::where('currency', $id)->get();
        $wallets->each(function($item, $key) use ($currency, $http_client) {
            $response = $http_client->request('post', '/v3/wallet/address', [
                'form_params' => [
                    'userid' => $item->user_id,
                    'projectname' => 'new_bvex',
                ],
            ]);
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->code) && $result->code != 0) {
                echo '用户id:' . $item->user_id . ',请求失败' . PHP_EOL;
                return ;
            }
            $result = $result->data;
            echo '用户id:' .$item->user_id . ',原地址:' . $item->address;
            if ($currency->type == 'btc') {
                $item->address = $result->btc_address;
                $item->private = $result->btc_private;
            } elseif ($currency->type == 'usdt') {
                $item->address = $result->usdt_address;
                $item->private = $result->usdt_private;
            } elseif ($currency->type == 'eth') {
                $item->address = $result->eth_address;
                $item->private = $result->eth_private;
            } elseif ($currency->type == 'erc20') {
                $item->address = $result->erc20_address;
                $item->private = $result->erc20_private;
            } elseif ($currency->type == 'xrp') {
                $item->address = $result->xrp_address;
                $item->private = $result->xrp_private;
            } else {
                return;
            }
            $item->save();
            echo ',新地址:' . $item->address . PHP_EOL;
        });
    }
}
