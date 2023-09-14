<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/2/21
 * Time: 16:35
 */
namespace App\CoinDriver;

use App\Model\AccountLog;
use App\Model\UserWallet;

class ETH extends Coin
{

    protected $api = '';

    /**
     *
     * @param
     *            $address
     *            
     * @return float|int|mixed
     * @throws \Exception
     */
    public function balance($address)
    {
        $api = 'http://47.92.148.83:82/wallet/eth/balance';
        
        $response = $this->http($api, [
            'address' => $address
        ]);
        
        if ($response['errcode'] != 0) {
            exception('errorinfo');
        }
        $lessen = pow(10, $this->currency->decimal_scale);
        return $response['data']['balance'] / $lessen;
    }

    /**
     * 更新钱包余额
     * 
     * @return mixed
     * @throws \Throwable
     */
    public function updateBalance($wallet)
    {
        $this->canUpdateBalance($wallet);
        $balance = $this->balance($wallet->address);
        
        if ($balance > $wallet->old_balance) {
            
            $wallet->old_balance = $balance;
            $number = $balance - $wallet->old_balance;
            $wallet->save();
            
            UserWallet::changeBalance($wallet->user_id, $wallet->currency_id, $number, AccountLog::UPDATED_BALACNE);
        }
    }

    /**
     * 转账
     *
     * @param
     *            $from_address
     * @param
     *            $to_address
     * @param
     *            $number
     * @param
     *            $private_key
     *            
     * @return mixed
     */
    public function transfer($from_address, $to_address, $number, $private_key)
    {
        // TODO: Implement transfer() method.
    }
}