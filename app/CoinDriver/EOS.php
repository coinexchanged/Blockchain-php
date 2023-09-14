<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/15
 * Time: 17:01
 */
namespace App\CoinDriver;

use App\AccountLog;
use App\UsersWallet;

class EOS extends Coin
{

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

    /**
     * 获取余额
     *
     * @param
     *            $address
     *            
     * @return mixed
     */
    public function balance($address)
    {
        // TODO: Implement balance() method.
        // TODO: Implement balance() method.
        $api = 'http://47.92.148.83:82/wallet/eos/balance';
        
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
     */
    public function updateBalance($wallet)
    {
        // TODO: Implement updateBalance() method.
        $this->canUpdateBalance($wallet);
        $balance = $this->balance($wallet->address);
        
        if ($balance > $wallet->old_balance) {
            
            $wallet->old_balance = $balance;
            $number = $balance - $wallet->old_balance;
            $wallet->save();
            
            UsersWallet::changeBalance($wallet->user_id, $wallet->currency_id, $number, AccountLog::UPDATED_BALACNE);
        }
    }
}