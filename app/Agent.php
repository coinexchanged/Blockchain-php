<?php

/**
 * Created by PhpStorm.
 * User: zef
 * Date: 2018/11/23
 * Time: 10:23
 */

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\{Users, AgentMoneylog};

class Agent extends Model
{
    protected $table = 'agent';
    public $timestamps = false;
    const CREATED_AT = 'created_time';

    protected $appends = [
        'phone',
        'email',
        'parent_id',
        'nickname',
        'parent_agent_name',
        'agent_name',
        'son_level',
        'max_pro_loss',
        'max_pro_ser',
        'self_info'
    ];

    const AGENT_TYPE = 1; //后台调节法币账户余额

    public function getPhoneAttribute()
    {
        //$value = $this->attributes['username'];
        $is_admin = $this->attributes['is_admin'];
        if ($is_admin == 1) {
            return '';
        } else {
            return self::hasOne(Users::class, 'id', 'user_id')->value('phone');
        }
    }

    public function getEmailAttribute()
    {
   
        $is_admin = $this->attributes['is_admin'];
        if ($is_admin == 1) {
            return '';
        } else {
            return self::hasOne(Users::class, 'id', 'user_id')->value('email');
        }
    }

    public function getParentIdAttribute()
    {
        $value = $this->attributes['username'];
        $is_admin = $this->attributes['is_admin'];
        if ($value == 'admin' && $is_admin == 1) {
            return 0;
        } else {
            return self::hasOne(Users::class, 'id', 'user_id')->value('parent_id');
        }
    }

    public function getNicknameAttribute()
    {
        $value = $this->attributes['username'];
        $is_admin = $this->attributes['is_admin'];
        if ($is_admin == 1) {
            return '超管';
        } else {
            return self::hasOne(Users::class, 'id', 'user_id')->value('nickname');
        }
    }

    public function getRegTimeAttribute()
    {
        $value = $this->attributes['reg_time'];
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        } else {
            return '无';
        }
    }

    public function getLockTimeAttribute()
    {
        $value = $this->attributes['lock_time'];
        if ($value > 0) {
            return date('Y-m-d H:i:s', $value);
        } else {
            return '无';
        }
    }

    public function getProLossAttribute()
    {
        $value = $this->attributes['pro_loss'];
        if ($value > 0) {
            return $value;
        } else {
            return '0.00';
        }
    }

    public function getProSerAttribute()
    {
        $value = $this->attributes['pro_ser'];
        if ($value > 0) {
            return $value;
        } else {
            return '0.00';
        }
    }

    public function getParentAgentNameAttribute()
    {
        $value = $this->attributes['parent_agent_id'];
        if ($value > 0) {
            $p = self::getAgentById($value);
            return $p?$p->username:'';
        } else {
            return '无';
        }
    }

    public function getAgentNameAttribute()
    {
        $value = $this->attributes['level'];
        if ($value > 0) {
            $p = $value . '级代理商';
            return $p;
        }else if($value == 0){
            return '超级';
        }else {
            return '无';
        }
    }

    public static function getAllChildAgent($agent_id, $refresh = false)
    {
        if ($refresh) {
            $child_agents = self::getRealChildAgent($agent_id);
        } else {
            if (Cache::has("child_agents:{$agent_id}")) {
                $child_agents = Cache::get("child_agents:{$agent_id}");
            } else {
                $child_agents = self::getRealChildAgent($agent_id);
            }
        }
        return $child_agents;
    }

    private static function getRealChildAgent($agent_id)
    {
        $child_agents = self::whereRaw('find_in_set(?, agent_path)', [$agent_id])->get();
        Cache::put("child_agents:{$agent_id}", $child_agents, Carbon::now()->addMinutes(5));
        return $child_agents;
    }

    public function getSonLevelAttribute()
    {
        $level = $this->attributes['level'];
        $is_admin = $this->attributes['is_admin'];
        if ($level >= 0  &&  $is_admin >= 0) {

            if ($level == 0 && $is_admin == 1) {
                $son_level = 1;
            } else if ($is_admin == 0) {
                $son_level = $level + 1;
            }
            return $son_level;
        } else {
            return '无';
        }
    }

    public function getMaxProLossAttribute()
    {
        $value = $this->attributes['pro_loss'];
        $p_id = $this->attributes['parent_agent_id'];
        $p_info = self::getAgentById($p_id);
        if ($p_info != null) {
            return $p_info['pro_loss'];
        } else {
            return $value;
        }
    }

    public function getMaxProSerAttribute()
    {
        $value = $this->attributes['pro_ser'];
        $p_id = $this->attributes['parent_agent_id'];
        $p_info = self::getAgentById($p_id);
        if ($p_info != null) {
            return $p_info['pro_ser'];
        } else {
            return $value;
        }
    }

    public function getPasswordAttribute()
    {
        return '**********';
    }

    public function getSelfInfoAttribute()
    {
        $value = $this->attributes['level'];
        $agent_max_level = Setting::getValueByKey('agent_max_level', 4);
        if ($value > 0 && $value <= $agent_max_level) {
            $_level = $value . '级代理商';
            // } else if ($value == 2) {
            //     $_level = '二级代理商';
            // } else if ($value == 3) {
            //     $_level = '三级代理商';
            // } else if ($value == 4) {
            //     $_level = '四级代理商';
        } else {
            $_level = '超级代理商';
        }

        return $_level;
    }

    public function user()
    {
        if ($this->is_admin != 1) {
            return $this->belongsTo(Users::class, 'user_id', 'id');
        }
    }

    /**
     * @param $mobile
     * @return string
     */
    public static function getUserByUsername($username)
    {
        if (empty($username)) return "";
        return self::where("username", $username)->first();
    }

    /**
     * @param $mobile
     * @return string
     */
    public static function getAgentById($id = 0)
    {
        if ($id == 0) return "";
        return self::where("id", $id)->first();
    }

    public static function getAgentId()
    {
       
        return session()->get('agent_id');
        
    }

    /**
     * @return 获取该用户的代理商信息
     */
    public static function getAgent()
    {
        return self::getAgentById(self::getAgentId());
    }

    /**
     * @param $obj
     * @return mixed
     */
    public static function object2array($obj)
    {
        return json_decode(json_encode($obj), true);
    }

    /**
     * @param $obj
     */
    public static function updateSession($agent)
    {
        if ($agent != null) {
            $access_token = md5($agent->id . $agent->username . $agent->reg_time);
            session()->put($access_token, $agent->id);
            session()->put('access_token', $access_token);
            return $access_token;
        } else {
            return '';
        }
    }

    /**
     * @param $obj
     */
    public static function delSession($request)
    {

        if ($request->session()->has('agent_id')) {

            session()->put('agent_id', '');
            session()->put('agent_username', '');

            return true;
        } elseif (session()->has('access_token')) {
            $access_token = session()->get('access_token');
            session()->put($access_token, '');
            session()->put('access_token', '');
            return true;
        } else {
            return false;
        }
    }

    /**
     * 注册的时候，获取上级用户的上级代理商
     */
    public static function reg_get_agent_id_by_parentid($parent = 0)
    {
        if ($parent == 0) {
            //无上级是，获取admin的id
            $p  = self::where('is_admin', 1)->where('level', 0)->first();
            return $p->id;
        } else {
            $_p_info = DB::table('users')->where('id', $parent)->first();
            if ($_p_info != null && !empty($_p_info)) {
                //如果该用户是代理商，返回该用户的代理商id
                if ($_p_info->agent_id > 0) {
                    return $_p_info->agent_id;
                } else if ($_p_info->agent_id == 0  && $_p_info->agent_note_id > 0) {
                    //如果该用户不是代理商，但是用户属于某个代理商，返回所属代理商id
                    return $_p_info->agent_note_id;
                } else {
                    //其他情况，
                    $p  = self::where('is_admin', 1)->where('level', 0)->first();
                    return $p->id;
                }
            } else {
                //无上级是，获取admin的id
                $p  = self::where('is_admin', 1)->where('level', 0)->first();
                return $p->id;
            }
        }
    }



    /**
     * 获取用户的代理商管理数组
     * @return  array
     */
    public static function getUserParentAgent($user_id = 0)
    {

        if ($user_id == 0) {
            return [];
        } else {
            $agent_note = DB::table('users')->where("id", $user_id)->select("agent_note_id")->first();
            if (!empty($agent_note) && $agent_note->agent_note_id == 0) {
                return [];
            } else {
                $arr = [];
                $_zero = self::getAgentById($agent_note->agent_note_id);

                //上级代理商
                if ($_zero != null  && !empty($_zero)) {
                    if ($_zero->admin != 1) {
                        $_z = [];
                        $_z['agent_id'] = $_zero->id;
                        $_z['user_id'] = $_zero->user_id;
                        $_z['is_admin'] = $_zero->is_admin;
                        $_z['level'] = $_zero->level;
                        $_z['pro_loss'] = $_zero->pro_loss;
                        $_z['pro_ser'] = $_zero->pro_ser;
                        $arr[] = $_z;
                    }

                    //上上级代理商
                    if ($_zero->parent_agent_id > 0) {
                        $_two = self::getAgentById($_zero->parent_agent_id);
                        if ($_two->admin != 1) {
                            $_t = [];
                            $_t['agent_id'] = $_two->id;
                            $_t['user_id'] = $_two->user_id;
                            $_t['is_admin'] = $_two->is_admin;
                            $_t['level'] = $_two->level;
                            $_t['pro_loss'] = bcsub($_two->pro_loss, $_zero->pro_loss, 2);
                            $_t['pro_ser'] = bcsub($_two->pro_ser, $_zero->pro_ser, 2);
                            $arr[] = $_t;
                        }

                        //上上上级代理商
                        if ($_two->parent_agent_id > 0) {
                            $_three = self::getAgentById($_two->parent_agent_id);
                            if ($_three->admin != 1) {
                                $_th = [];
                                $_th['agent_id'] = $_three->id;
                                $_th['user_id'] = $_three->user_id;
                                $_th['is_admin'] = $_three->is_admin;
                                $_th['level'] = $_three->level;
                                $_th['pro_loss'] = bcsub($_three->pro_loss, $_two->pro_loss, 2);
                                $_th['pro_ser'] = bcsub($_three->pro_ser, $_two->pro_ser, 2);
                                $arr[] = $_th;
                            }

                            //上上上上级代理商
                            if ($_three->parent_agent_id > 0) {
                                $_four = self::getAgentById($_three->parent_agent_id);
                                if ($_four->admin != 1) {
                                    $_f = [];
                                    $_f['agent_id'] = $_four->id;
                                    $_f['user_id'] = $_four->user_id;
                                    $_f['is_admin'] = $_four->is_admin;
                                    $_f['level'] = $_four->level;
                                    $_f['pro_loss'] = bcsub($_four->pro_loss, $_three->pro_loss, 2);
                                    $_f['pro_ser'] = bcsub($_four->pro_ser, $_three->pro_ser, 2);
                                    $arr[] = $_f;
                                }
                            }
                        }
                    }
                }
                return $arr;
            }
        }
    }


    /**
     * 改变用户余额并记录日志
     *
     * @param App\User $user 要改变余额的用户模型实例
     * @param integer $type 类型:1.头寸收益,2.手续费收益
     * @param float $change 变动数量:正数为添加,负数为减少
     * @param integer $relate_id 关联Id
     * @param string $memo 备注
     * @return true|string
     */
    public static function change_agent_money($agent_id, $type, $change, $relate_id, $memo = '', $son_user_id = 0,$legal_id)
    {


        //记录余额专用流水明细
        $moneyLog = new AgentMoneylog();
        $moneyLog->agent_id = $agent_id;
        $moneyLog->type = $type;
        $moneyLog->relate_id = $relate_id;
        //$moneyLog->before = $before ?? 0;
        $moneyLog->change = $change;
        //$moneyLog->after = $after;
        $moneyLog->memo = $memo;
        $moneyLog->son_user_id = $son_user_id;
        $moneyLog->legal_id = $legal_id;

        $moneyLog->created_time = time();
        $moneyLog->save();
    }

    /**
     * 根据指定日期获取所在周的起始时间和结束时间
     */
    public static function get_weekinfo_by_date($date)
    {
        $idx = strftime("%u", strtotime($date));
        $mon_idx = $idx - 1;
        $sun_idx = $idx - 7;
        return array(
            'week_start_day' => strftime('%Y-%m-%d', strtotime($date) - $mon_idx * 86400),
            'week_end_day' => strftime('%Y-%m-%d', strtotime($date) - $sun_idx * 86400),
        );
    }
    /**
     * 根据指定日期获取所在月的起始时间和结束时间
     */
    public static function get_monthinfo_by_date($date)
    {
        $ret = array();
        $timestamp = strtotime($date);
        $mdays = date('t', $timestamp);
        return array(
            'month_start_day' => date('Y-m-1', $timestamp),
            'month_end_day' => date('Y-m-' . $mdays, $timestamp)
        );
    }
    /**
     * 获取指定日期之间的各个周
     */
    public static function get_weeks($sdate, $edate)
    {
        $range_arr = array();
        // 检查日期有效性
        self::check_date(array($sdate, $edate));
        // 计算各个周的起始时间
        do {
            $weekinfo = self::get_weekinfo_by_date($sdate);
            $end_day = $weekinfo['week_end_day'];
            $start = self::substr_date($weekinfo['week_start_day']);
            $end = self::substr_date($weekinfo['week_end_day']);
            $range = "{$start}/{$end}";
            $range_arr[] = $range;
            $sdate = date('Y-m-d', strtotime($sdate) + 7 * 86400);
        } while ($end_day < $edate);
        return $range_arr;
    }

    /**
     * 获取指定日期之间的各个月
     */
    public static function get_months($sdate, $edate)
    {
        $range_arr = array();
        do {
            $monthinfo = self::get_monthinfo_by_date($sdate);
            $end_day = $monthinfo['month_end_day'];
            $start = self::substr_date($monthinfo['month_start_day']);
            $end = self::substr_date($monthinfo['month_end_day']);
            $range = "{$start} ~ {$end}";
            $range_arr[] = $range;
            $sdate = date('Y-m-d', strtotime($sdate . '+1 month'));
        } while ($end_day < $edate);
        return $range_arr;
    }

    /**
     * 截取日期中的月份和日
     * @param string $date
     * @return string $date
     */
    public static function substr_date($date)
    {
        if (!$date) return FALSE;
        return date('Y-m-d', strtotime($date));
    }

    /**
     * 检查日期的有效性 YYYY-mm-dd
     * @param array $date_arr
     * @return boolean
     */
    public static function check_date($date_arr)
    {
        $invalid_date_arr = array();
        foreach ($date_arr as $row) {
            $timestamp = strtotime($row);
            $standard = date('Y-m-d', $timestamp);
            if ($standard != $row) $invalid_date_arr[] = $row;
        }
        if (!empty($invalid_date_arr)) {
            die("invalid date -> " . print_r($invalid_date_arr, TRUE));
        }
    }

    /**获取我自己四级代理商所有的id 待优化
     *
     */
    public static function getLevel4AgentId($agent_id, $id_list = [], $level = 0)
    {
        if ($level == 4) {
            return $id_list;
        }

        $agent_list = static::where('parent_agent_id', $agent_id)->get();

        if (!$agent_list) {
            return $id_list;
        }

        $level += 1;

        foreach ($agent_list as $agent) {
            $id_list[] = $agent->id;
            $id_list = static::getLevel4AgentId($agent->id, $id_list, $level);
        }
        return $id_list;
    }

    //获取用户的代理商关系
    public static function agentPath($parent = 0)
    {

        if ($parent == 0) {
            //无上级是，获取admin的id
            $p  = self::where('is_admin', 1)->where('level', 0)->first();
            return $p->id;
        } else {
            $_p_info = Users::find($parent);
            if (!empty($_p_info)) {
                if ($_p_info->agent_id > 0) {
                    //
                    $agent = self::find($_p_info->agent_id);

                    return $agent ? $agent->agent_path : $_p_info->agent_path;
                } else {

                    return $_p_info->agent_path;
                }
            } else {
                //无上级是，获取admin的id
                $p  = self::where('is_admin', 1)->where('level', 0)->first();
                return $p->id;
            }
        }
    }

    //结算测试
    public static function dojie($lever_ids) {

        LeverTransaction::where('status' , LeverTransaction::CLOSED)
            ->where('settled' , 0)
            ->whereIn('id',$lever_ids)
            ->chunk(100, function($lever_transactions) {
                foreach ($lever_transactions as $key => $trade) {
                    try {
                        DB::transaction(function () use ($trade) {
                            //取出该用户的代理商关系数组
                            $agent_path=$trade->agent_path;

                            
                            $_p_arr =explode(',', $agent_path);
                            //var_dump($_p_arr);

                            if (!empty($_p_arr)) {
                               
                                $da=[];
                                foreach($_p_arr as $k=>$v){
                                    $agent=Agent::getAgentById($v);
                                  
                                    $da[$k]['agent_id']=$v;
                                    $da[$k]['pro_loss']=$agent->pro_loss;
                                    $da[$k]['pro_ser']=$agent->pro_ser;

                                }

                                //var_dump($da);exit;

                                //极差收益
                                foreach ($da as $k=>$val){
                                     
                                    if($k==0){
                                        $pro_loss=$val['pro_loss'];
                                        $pro_ser=$val['pro_ser'];
                                    }else{
                                        $n=$k-1;
                                        $pro_loss=bc_sub($val['pro_loss'],$da[$n]['pro_loss']);
                                        $pro_ser=bc_sub($val['pro_ser'],$da[$n]['pro_ser']);
                                    }
 
                                    //头寸收益
                                    if ($pro_loss > 0){
                                        //盈亏收益 . 头寸收益是反的，需要取相反数
                                        $_base_money =bc_mul($trade->fact_profits , -1);
                                        $change = bc_mul($_base_money , $pro_loss/100);

                                        Agent::change_agent_money(
                                            $val['agent_id'] ,
                                            1  ,
                                            $change,
                                            $trade->id,
                                            '您的下级用户'.$trade->user_id.'的订单产生的头寸收益为'.$change.'。订单编号为'.$trade->id,
                                            $trade->user_id,
                                            $trade->legal
                                        );
                                    }

                                    //手续费收益
                                    if ($pro_ser >0){
                                        //手续费收益
                                        $change = bc_mul($trade->trade_fee ,$pro_ser/100);

                                        Agent::change_agent_money(
                                            $val['agent_id'],
                                            2  ,
                                            $change,
                                            $trade->id,
                                            '您的下级用户'.$trade->user_id.'的订单产生的手续费收益为'.$change.'。订单编号为'.$trade->id,
                                            $trade->user_id,
                                            $trade->legal
                                        );
                                    }

                                }
                            }
                            LeverTransaction::where('id' , $trade->id)->update(['settled' =>1]);
                        });

                    } catch (\Exception $e) {
                        echo 'File :' . $e->getFile() . PHP_EOL;
                        echo 'Line :' . $e->getLine() . PHP_EOL;
                        echo 'Msg :' . $e->getMessage(). PHP_EOL;
                    }
                }
            });


    }
}
