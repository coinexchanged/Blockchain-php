<?php
/**
 * Created by PhpStorm.
 * User: YSX
 * Date: 2019/3/11
 * Time: 15:51
 */
namespace App\CoinDriver;

use App\Model\Currency;
use GuzzleHttp\Client;

abstract class Coin
{

    /**
     *
     * @var Currency
     */
    public $currency;

    /**
     *
     * @var
     *
     */
    public $type;

    public function __construct($type)
    {
        $this->type = $type;
        $currency = Currency::where('type', $this->type)->first();
        $this->currency = $currency;
    }

    /**
     * 转账
     *
     * @param $from_address 转出地址            
     * @param $to_address 转入地址            
     * @param $number 数量            
     * @param $private_key 转出钱包的私钥            
     *
     * @return mixed
     */
    public abstract function transfer($from_address, $to_address, $number, $private_key);

    /**
     * 获取余额
     *
     * @param
     *            $address
     *            
     * @return mixed
     */
    public abstract function balance($address);

    /**
     * 更新钱包余额
     * 
     * @return mixed
     */
    public abstract function updateBalance($wallet);

    /**
     * 获取钱包余额
     *
     * @param
     *            $user_id
     *            
     * @return mixed
     */
    public static function getAddress($user_id)
    {}

    /**
     * 判断能不能更新余额
     *
     * @param
     *            $wallet
     *            
     * @return mixed
     *
     * @throws \Throwable
     */
    public function canUpdateBalance($wallet)
    {
        if ($wallet->gl_time > time() - 15 * 60) {
            exception('归拢时间内不能从链上监听余额');
        }
    }

    /**
     * 获取驱动
     *
     * @param
     *            $type
     *            
     * @return Coin
     */
    public static function newInstance($type)
    {
        $type = strtoupper($type);
        $path = "App\CoinDriver\\{$type}";
        $instance = new $path($type);
        return $instance;
    }

    public function http($url, $data = null, $method = 'GET')
    {
        $query = strtoupper($method) == 'GET' ? $data : null;
        $body = strtoupper($method) == 'POST' ? $data : null;
        
        $client = new Client();
        $response = $client->request($method, $url, [
            'query' => $query,
            'form_params' => $body
        ])
            ->getBody()
            ->getContents();
        
        return json_decode($response, true);
    }
}