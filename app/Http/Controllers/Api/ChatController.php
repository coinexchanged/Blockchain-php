<?php

namespace App\Http\Controllers\Api;

use App\ChatLog;
use App\Users;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GatewayWorker\Lib\Gateway;

class ChatController extends Controller
{
    //
    public $user_id;

    public function __construct()
    {
        //\GatewayWorker\Lib\Gateway::$registerAddress = '127.0.0.1:2501';
        //\App\Http\Controllers\Chat\ChatController::static_send(2,'123');
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值(ip不能是0.0.0.0)
        Gateway::$registerAddress = '127.0.0.1:2501';
        $this->user_id = Users::getUserId();
    }

    public function bind(Request $request)
    {
        $client_id = $request->get('client_id', 0);

        // 假设用户已经登录，用户uid和群组id在session中
        $uid = $this->user_id;
        //$group_id = $_SESSION['group'];
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        // 加入某个群组（可调用多次加入多个群组）
        //Gateway::joinGroup($client_id, $group_id);
        return $this->success('绑定成功');
    }

    public function send(Request $request)
    {
        $uid = $request->get('user_id', 0);
        $message = $request->get('message', '');
        $type = $request->get('type', 1);
        $trade_id = $request->get('trade_id', 1);

        $user_info = Users::getById($this->user_id);
        $send_data = json_encode([
            'type'=> $type,
            'data'=> $message,
            'user_info' => $user_info,
            'trade_id' => $trade_id,
        ]);
        // 向任意uid的网站页面发送数据
        Gateway::sendToUid($uid, $send_data);

        $with_user = $uid;
        $last_chat_log = ChatLog::where(function ($query)use($with_user){
            $query->where('from_user',$this->user_id)->where('to_user',$with_user);
        })->orWhere(function ($query)use($with_user){
            $query->where('from_user',$with_user)->where('to_user',$this->user_id);
        })->orderBy('created_at','desc')->first();
        //dd($last_chat_log);
        if($last_chat_log){
            $one_hour_ago = Carbon::now()->subHour();
            $last_chat_time = Carbon::parse($last_chat_log['created_at']);
            if($last_chat_time->lt($one_hour_ago)){//距离上次聊天已经过去一个小时
                ChatLog::unguard();
                ChatLog::create([
                    'type' => 4,
                    'content' => date("m月d日 H:i"),
                    'from_user' => $this->user_id,
                    'to_user' => $uid,
                    'trade_id' => $trade_id,
                ]);
                ChatLog::reguard();
            }
        }
        ChatLog::unguard();
        ChatLog::create([
            'type' => $type,
            'content' => $message,
            'from_user' => $this->user_id,
            'to_user' => $uid,
            'trade_id' => $trade_id,
        ]);
        ChatLog::reguard();
        return $this->success('发送成功');
    }

    public static function static_send($uid,$message)
    {
        Gateway::sendToUid($uid, $message);
    }

    /**
     * 聊天历史纪录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChatLog(Request $request)
    {
        $with_user = $request->get('user_id',0);
        $chat_logs = ChatLog::where(function ($query)use($with_user){
            $query->where('from_user',$this->user_id)->where('to_user',$with_user);
        })->orWhere(function ($query)use($with_user){
            $query->where('from_user',$with_user)->where('to_user',$this->user_id);
        })->orderBy('created_at')->where('created_at','>',Carbon::now()->subDays(7))->get();

        ChatLog::where('to_user',$this->user_id)->where('from_user',$with_user)->update(['readed' => 1]);;
        return $this->success(['login_user' => $this->user_id,'data' => $chat_logs]);
    }

    /**
     * 获取未读消息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadMsg(Request $request)
    {
        $trade_id = $request->get('id',0);
        $unread_number = self::unreadMsg($this->user_id, $trade_id);
        return $this->success($unread_number);
    }

    public static function unreadMsg($user_id, $trade_id = 0)
    {
        $unread_number = ChatLog::where('to_user', $user_id)
            ->where('readed',0)
            ->where(function ($query) use ($trade_id){
                if($trade_id){
                    $query->where('trade_id', $trade_id);
                }
            })->count();
        return $unread_number;
    }
}
