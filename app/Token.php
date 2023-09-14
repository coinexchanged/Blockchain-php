<?php

/**
 * Created by PhpStorm.
 * User: LDH
 */

namespace App;

use Illuminate\Database\Eloquent\Model;


class Token extends Model
{
    protected $table = 'tokens';
    public $timestamps = false;
    /**初始化时删除过期缓存
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->where('time_out', '<', time())->delete();
    }
    //获取token值
    public static function getToken()
    {
        {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $headers[str_replace('_', '-', substr($key, 5))] = $value;
                }
            }
            if (isset($headers["AUTHORIZATION"]) && $headers["AUTHORIZATION"] != '') {
                return $headers["AUTHORIZATION"];
            } else {
                return "";
            }  
        }
    }

    //清除token
    public static function clearToken($user_id)
    {
        self::where('user_id', $user_id)->delete();
    }
    //设置token
    public static function setToken($user_id)
    {
//        $token = new static();
       $token = new self();
        $token_str = md5($user_id . time() . mt_rand(0, 99999));

        $token->user_id = $user_id;
        $token->time_out = self::getTimeOut();
        $token->token = $token_str;

        return $token->save() ? $token_str : false;
    }
    //过期时间 只保留30天登录记录
    public static function getTimeOut($day = 1)
    {
        return time() + 60 * 60 * 24 * $day;
    }
    public static function getUserIdByToken($token){
        if(empty($token)){
            return false;
        }
        $user = self::where('token',$token)->first();
        if(empty($user)){
              return false;
        }
        return $user->user_id;
    }

     /**根据user_id删除token
     *
     * @param $user_id
     */
    public static function deleteTokenByUserId($user_id)
    {
        self::where('user_id', $user_id)->delete();
    }

    /**根据user_id token删除当前的token
     *
     * @param $user_id  $token
     */
    public static function deleteToken($user_id,$token)
    {
        self::where('user_id', $user_id)->where('token',$token)->delete();
    }

    public static function setTokenLang($lang = 'zh')
    {
        self::where('token', self::getToken())->update([
            'lang' => $lang,
        ]);
    }


}