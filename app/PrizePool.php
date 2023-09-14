<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Dao\PrizePool\PrizeSender;
use App\DAO\PrizePool\PrizeReceiver;
use App\DAO\PrizePool\PrizeCalculator;

class PrizePool extends Model
{
    //奖励场景
    const CERTIFICATION = 1; //实名认证奖励
    const LEVER_TRADE_FEE = 2; //交易奖励(手续费)
    const LEVER_TRADE_FEE_ATELIER = 3; //工作室奖励
    //奖励种类
    const REWARD_CANDY = 0; //奖励通证
    const REWARD_CURRENCY = 1; //奖励数字货币
    //奖励货币类型
    const CURRENCY_NONE = 0;
    const CURRENCY_LEGAL = 1; //法币币
    const CURRENCY_MATCH = 2; //撮合币
    const CURRENCY_LEVER = 3; //杠杆币

    protected $table = 'prize_pool';
    public $timestamps = false;

    protected $appends = [
        'to_user_name',
        'from_user_name',
        'scene_name',
    ];

    protected static $sceneList = [
        self::CERTIFICATION => '实名认证奖励',
        self::LEVER_TRADE_FEE => '交易手续费结算',
        self::LEVER_TRADE_FEE_ATELIER => '工作室奖励',
    ];

    protected static $rewardTypeList = [
        self::REWARD_CANDY => '通证',
        self::REWARD_CURRENCY => '数字货币',
    ];

    public static function enumScene()
    {
        return self::$sceneList;
    }

    public static function enumRewardType()
    {
        return self::$rewardTypeList;
    }

    /**
     * 计算奖励
     *
     * @param PrizeCalculator $sender 发奖实例
     * @param integer $scene
     * @param float $reward_qty
     * @param \App\Users $to_user
     * @param \App\Users $from_user
     * @param string $memo
     * @param array $attach_data
     * @return \App\PrizePool|null 返回奖池记录
     */
    public static function calculate(PrizeCalculator $sender, $scene, $reward_qty, $to_user, $from_user, $memo, $attach_data)
    {
        return $sender->calculate($scene, $reward_qty, $to_user, $from_user, $memo, $attach_data);
    }

    /**
     * 发放奖励
     *
     * @param PrizeSender $receiver
     * @param \App\PrizePool $prize_pool
     * @return bool
     */
    public static function send(PrizeSender $receiver, $prize_pool)
    {
        if (!$prize_pool) {
            return false;
        }
        return $receiver->send($prize_pool);
    }

    public function toUser()
    {
        return $this->belongsTo('App\Users', 'to_user_id', 'id')->withDefault();
    }
    
    public function fromUser()
    {
        return $this->belongsTo('App\Users', 'from_user_id', 'id')->withDefault();
    }

    public function getCreateTimeAttribute($value)
    {
        return empty($value) ? '' : date('Y-m-d H:i:s', $value);
    }

    public function getToUserNameAttribute()
    {
        return $this->toUser()->value('account_number');
    }

    public function getFromUserNameAttribute()
    {
        return $this->fromUser()->value('account_number');
    }

    public function getSceneNameAttribute()
    {
        $scene_list = self::$sceneList;
        $scene = $this->getAttribute('scene');
        $value = array_key_exists($scene, $scene_list) ? $scene_list[$scene] : '未知';
        return $value;
    }

    public function getRewardTypeAttribute($value)
    {
        $reward_type_list = self::$rewardTypeList;
        $reward_type = $value;
        $value = array_key_exists($reward_type, $reward_type_list) ? $reward_type_list[$reward_type] : '未知';
        return $value;
    }
}
