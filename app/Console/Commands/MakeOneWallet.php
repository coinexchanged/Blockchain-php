<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Currency;
use App\Setting;
use App\Users;
use App\UsersWallet;
use App\Utils\RPC;

class MakeOneWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make_wallet {id : id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '钱包项目上币执行脚本';

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

        $this->info('开始执行上币脚本--' . Carbon::now()->toDateTimeString());

        $currency = Currency::find($id);
        if (!empty($currency)) {

            Setting::updateValueByKey('currency_' . $id, 1);

            $count = Users::count();
            $i = 1;
            $this->info('共有 ' . $count . ' 个用户需要添加新的钱包地址');

            $address_url = config('app.wallet_api');
            $project_name = config('app.name');
            $http_client = new Client();

            foreach (Users::whereRaw('1')->cursor() as $user) {

                try {
                    DB::beginTransaction();

                    $users_wallet = UsersWallet::where('user_id', $user->id)->where('currency', $id)->first();
                    if (empty($users_wallet)) {

                        $this->info('开始生成第 ' . $i . '/' . $count . ' 个用户的钱包地址,用户 id 为：' . $user->id);

                        $response = $http_client->post($address_url, [
                            'form_params' => [
                                'userid' => $user->id,
                                'projectname' => $project_name,
                            ]
                        ]);
                        $result = json_decode($response->getBody()->getContents());
                        if ($result->code != 0) {
                            throw new \Exception('请求地址接口出错');
                        }
                        $address = $result->data;


                        $userWallet = new UsersWallet();
                        $userWallet->user_id = $user->id;
                        if ($currency->type == 'btc' || $currency->type == 'usdt') {
                            $userWallet->address = $address->btc_address;
                            $userWallet->private = encrypt($address->btc_private);
                        } elseif ($currency->type == 'eth' || $currency->type == 'erc20') {
                            $userWallet->address = $address->eth_address;
                            $userWallet->private = encrypt($address->eth_private);
                        } elseif ($currency->type == 'xrp') {
                            $userWallet->address = $address->xrp_address;
                            $userWallet->private = encrypt($address->xrp_private);
                        } else {
                            throw new \Exception('不支持的币类型');
                        }
                        $userWallet->currency = $currency->id;
                        $userWallet->create_time = time();
                        $userWallet->save(); //默认生成所有币种的钱包

                    } else {

                        throw new \Exception('第 ' . $i . '/' . $count . ' 个用户有此币种钱包,用户 id 为：' . $user->id);
                    }

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollback();
                    $this->error($exception->getMessage());
                }

                $i++;
            }

            Setting::updateValueByKey('currency_' . $id, 2);
        }

        $this->info('执行成功');
    }
}
