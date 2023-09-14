<?php

namespace App\Console\Commands;

use App\Currency;
use App\Setting;
use App\Users;
use App\UsersWallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExecuteCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'execute_currency {id : id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '上币执行脚本';

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
        try {
            $is_execute = Setting::getValueByKey('currency_' . $id, 0);
            if ($is_execute == 1) {
                throw new \Exception('该币上币脚本正在运行中,请不要重复执行');
            }
            Setting::updateValueByKey('currency_' . $id, 1);
            $currency = Currency::find($id);
            if (empty($currency)) {
                throw new \Exception('币种不存在');
            }
            if (!in_array($currency->type, ['btc', 'usdt', 'eth', 'erc20', 'xrp'])) {
                throw new \Exception('不支持的币种');
            }
            $address_url = '/v3/wallet/address';
            $project_name = config('app.name');
            $http_client = app('LbxChainServer');
            $this->info('开始执行按币种生成钱包脚本--' . Carbon::now()->toDateTimeString());
            // 查询没有该币种钱包的用户
            $user_query = Users::whereNotExists(function ($query) use ($id) {
                $query->select(DB::raw(1))
                      ->from('users_wallet')
                      ->where('currency', $id)
                      ->whereRaw('users_wallet.user_id = users.id');
            });
            $count = $user_query->count();
            $this->info('共有 ' . $count . ' 个用户需要添加新的钱包地址');
            $i = 1;
            foreach ($user_query->cursor() as $user) {
                if (UsersWallet::where('user_id',$user->id)->where('currency', $id)->exists()) {
                    $this->error('第 ' . $i . '/' . $count . ' 个用户有此币种钱包,用户 id 为：' . $user->id);
                    continue;
                }
                $this->info('开始生成第 ' . $i . '/' . $count . ' 个用户的钱包地址,用户 id 为：' . $user->id);
                $response = $http_client->post($address_url, [
                    'form_params' => [
                        'userid' => $user->id,
                        'projectname' => $project_name,
                    ]
                ]);
                $result = json_decode($response->getBody()->getContents());
                if ($result->code != 0) {
                    return false;
                }
                $walllet_data = $result->data;
                if ($currency->type == 'btc') {
                    $address = $walllet_data->btc_address;
                    $private = $walllet_data->btc_private;
                } elseif ($currency->type == 'usdt') {
                    $address = $walllet_data->usdt_address;
                    $private = $walllet_data->usdt_private;
                } elseif ($currency->type == 'eth') {
                    $address = $walllet_data->eth_address;
                    $private = $walllet_data->eth_private;
                } elseif ($currency->type == 'erc20') {
                    $address = $walllet_data->erc20_address;
                    $private = $walllet_data->erc20_private;
                } elseif ($currency->type == 'xrp') {
                    $address = $walllet_data->xrp_address;
                    $private = $walllet_data->xrp_private;
                } else {
                    $this->error('暂不支持生成该币种的钱包');
                    continue;
                }
                UsersWallet::unguarded(function () use ($address, $private, $user, $currency) {
                    UsersWallet::create([
                        'user_id' => $user->id,
                        'currency' => $currency->id,
                        'address' => $address,
                        'private' => $private,
                        'create_time' => time(),
                    ]);
                });                   
                $i++;
            }
            Setting::updateValueByKey('currency_'.$id, 0);
            $this->info('执行成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
