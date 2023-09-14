<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Utils\RPC;

class UserChat extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_chat';
    public $timestamps = false;
    protected $appends = ['from_avatar','from_nickname'];

    protected static $config = [];

    protected static $_socket_client = null;

    public static function getConfig()
    {
        isset(self::$config['worker_push_url']) || self::$config['worker_push_url'] = config('app.worker_push_url') ?? \Request::server('HTTP_HOST');
        isset(self::$config['text_worker_port']) || self::$config['text_worker_port'] = config('app.text_worker_port');
    }

    public function getFromAvatarAttribute()
    {
        return $this->hasOne('App\Users','id','from_user_id')->value('head_portrait');
    }

    public function getFromNicknameAttribute()
    {
        return $this->hasOne('App\Users','id','from_user_id')->value('account_number');
    }

    public static function sendChat($data)
    {
        if (empty($data)) {
            return "fail";
        }
        $worker_push_url = config('app.worker_push_url') ?? \Request::server('HTTP_HOST');
        $http_worker_port = config('app.http_worker_port');
        $push_api_url = "http://" . $worker_push_url . ":" . $http_worker_port . '/';
        return RPC::http_post($push_api_url, $data);
    }

    public static function sendText($data)
    {
        $client = self::getSocket();
        $data = (is_scalar($data) ? strval($data) : json_encode($data)) . "\n";
        try {
            //长连接不建议关闭连接
            //stream_socket_sendto($client, $data);
            fwrite($client, $data);
        } catch (\Throwable $th) {
            //尝试一次重新连接再发送
            try {
                //stream_socket_sendto($client, $data);
                //fclose($client);
                $client = self::getSocket(true);
                fwrite($client, $data);
            } catch (\Throwable $th) {
                dump($th->getMessage());
            }
        }
    }

    public static function getSocket($force_connect = false)
    {
        self::getConfig();
        if (self::$_socket_client == null || $force_connect) {
            self::$_socket_client = stream_socket_client('tcp://' . self::$config['worker_push_url'] . ':' . self::$config['text_worker_port'], $errno, $errstr);
        }
        if (!self::$_socket_client) {
            throw new \Exception($errstr, $errno);
        }
        return self::$_socket_client;
    }

    public static function getFace($value = "")
    {
        $data = array(
            "0"=>"微笑",
            "1"=>"嘻嘻",
            "2"=>"哈哈",
            "3"=>"可爱",
            "4"=>"可怜",
            "5"=>"抠鼻",
            "6"=>"吃惊",
            "7"=>"害羞",
            "8"=>"挤眼",
            "9"=>"闭嘴",
            "10"=>"鄙视",
            "11"=>"爱你",
            "12"=>"泪",
            "13"=>"偷笑",
            "14"=>"亲亲",
            "15"=>"生病",
            "16"=>"太开心",
            "17"=>"白眼",
            "18"=>"右哼哼",
            "19"=>"左哼哼",
            "20"=>"嘘",
            "21"=>"衰",
            "22"=>"委屈",
            "23"=>"吐",
            "24"=>"哈欠",
            "25"=>"抱抱",
            "26"=>"怒",
            "27"=>"疑问",
            "28"=>"馋嘴",
            "29"=>"拜拜",
            "30"=>"思考",
            "31"=>"汗",
            "32"=>"困",
            "33"=>"睡",
            "34"=>"钱"
        );
        if(empty($value))
            return $data;
        else{
            foreach ($data as $index=>$a){
                if($value == $a){
                    return "<img src=http://m.zhonghexinshang.net.cn/vendor/layim/src/images/face/".$index.".gif>";
                }
            }
        }
    }
}
