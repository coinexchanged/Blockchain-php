<?php
namespace App\DAO;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\{Users, PrizePool, Setting};
use App\DAO\PrizePool\CandySender;
use App\Events\RealNameEvent;

class UserDAO
{
    /**
     * 检查是否符合升级工作室条件,若符合自动升级
     *
     * @param App\Users $user
     * @return boolean
     */
    public static function checkUpgradeAtelierCondition(&$user) : bool
    {
        $user->refresh();
        //检查自身是否已实名或已经是工作室
        if ($user->is_realname != 2 || $user->is_atelier == 1) {
            return false;
        }
        $upgrade_atelier_must_has_son = Setting::getValueByKey('upgrade_atelier_must_has_son'); //升级工作室直推实名人数要求
        $upgrade_atelier_must_team_son = Setting::getValueByKey('upgrade_atelier_must_team_son'); //升级工作室团队实名人数要求
        $upgrade_atelier_must_team_recharge = Setting::getValueByKey('upgrade_atelier_must_team_recharge'); //升级工作室团队充值金额
        //检查直推实名人数
        if ($user->zhitui_real_number < $upgrade_atelier_must_has_son) {
            return false;
        }
        //检查团队实名人数
        if ($user->real_teamnumber < $upgrade_atelier_must_team_son) {
            return false;
        }
        //检查团队充值金额
        if ($user->top_upnumber < $upgrade_atelier_must_team_recharge) {
            return false;
        }
        $user->is_atelier = 1;
        return $user->save();
    }

    /**
     * 检查用户的上级是否有通证变动
     * @param App\Users $user 用户查询的对象
     */
    public static function addCandyNumber($user)
    {
        $parents = self::getParentsPathDesc($user);
        foreach ($parents as $key => $user_id) {
            $current_user = Users::find($user_id);//检查该上级是否实名认证过
            if ($current_user->is_realname == 2) {
                //该上级实名认证过
                self::checkUserRealNameReward($current_user); //检查是否符合发奖条件，符合就发放奖励
                self::checkUpgradeAtelierCondition($current_user); //检查是否符合升级工作室条件
            } else {
                //该上级还没实名认证
                continue;
            }
        }
    }

    /**
     * 检测用户是否符合实名奖励
     *
     * @param App\Users $user
     * @return bool
     */
    public static function checkUserRealNameReward(&$user)
    {
        if (!$user) {
            return false;
        }
        //获取setting值
        $real_name_candy = Setting::getValueByKey('real_name_candy', '');
        $zhitui2_number = Setting::getValueByKey('zhitui2_number', '');
        $zhitui2_candy = Setting::getValueByKey('zhitui2_candy', '');
        $zhitui3_number = Setting::getValueByKey('zhitui3_number', '');
        $zhitui3_real_teamnumber = Setting::getValueByKey('zhitui3_real_teamnumber', '');
        $zhitui3_top_upnumber = Setting::getValueByKey('zhitui3_top_upnumber', '');
        $zhitui3_candy = Setting::getValueByKey('zhitui3_candy', '');
        $zhitui4_number = Setting::getValueByKey('zhitui4_number', '');
        $zhitui4_real_teamnumber = Setting::getValueByKey('zhitui4_real_teamnumber', '');
        $zhitui4_top_upnumber = Setting::getValueByKey('zhitui4_top_upnumber', '');
        $zhitui4_candy = Setting::getValueByKey('zhitui4_candy', '');
        $zhitui5_number = Setting::getValueByKey('zhitui5_number', '');
        $zhitui5_real_teamnumber = Setting::getValueByKey('zhitui5_real_teamnumber', '');
        $zhitui5_top_upnumber = Setting::getValueByKey('zhitui5_top_upnumber', '');
        $zhitui5_candy = Setting::getValueByKey('zhitui5_candy', '');
        $zhitui6_number = Setting::getValueByKey('zhitui6_number', '');
        $zhitui6_real_teamnumber = Setting::getValueByKey('zhitui6_real_teamnumber', '');
        $zhitui6_top_upnumber = Setting::getValueByKey('zhitui6_top_upnumber', '');
        $zhitui6_candy = Setting::getValueByKey('zhitui6_candy', '');

        $only = 1;
        $user->refresh();
        $push_status = $user->push_status;

        //实名认证过的有效直推人数
        $real_zhitui = Users::where("is_realname", 2)
            ->where("parent_id", $user->id)
            ->count();
        
        $user->zhitui_real_number = $real_zhitui ?: 0; //更新直推实名人数
        $user->real_teamnumber += 1;

        if ($push_status == 1) {
            if ($real_zhitui >= $zhitui2_number) {
                $user->candy_number += $zhitui2_candy;
                $user->push_status = 2;
                $log_candy_number = $zhitui2_candy;
                $only = 2;
            }
        } elseif ($push_status == 2) {
            if ($real_zhitui >= $zhitui3_number && $user->real_teamnumber >= $zhitui3_real_teamnumber && $user->top_upnumber >= $zhitui3_top_upnumber) {
                $user->candy_number += $zhitui3_candy;
                $user->push_status = 3;
                $log_candy_number = $zhitui3_candy;
                $only = 2;
            }
        } elseif ($push_status == 3) {
            if ($real_zhitui >= $zhitui4_number && $user->real_teamnumber >= $zhitui4_real_teamnumber && $user->top_upnumber >= $zhitui4_top_upnumber) {
                $user->candy_number += $zhitui4_candy;
                $user->push_status = 4;
                $log_candy_number = $zhitui4_candy;
                $only = 2;
            }
        } elseif ($push_status == 4) {
            if ($real_zhitui >= $zhitui5_number && $user->real_teamnumber >= $zhitui5_real_teamnumber && $user->top_upnumber >= $zhitui5_top_upnumber) {
                $user->candy_number += $zhitui5_candy;
                $user->push_status = 5;
                $log_candy_number = $zhitui5_candy;
                $only = 2;
            }
        } elseif ($push_status == 5) {
            if ($real_zhitui >= $zhitui6_number && $user->real_teamnumber >= $zhitui6_real_teamnumber && $user->top_upnumber >= $zhitui6_top_upnumber) {
                $user->candy_number += $zhitui6_candy;
                $user->push_status = 6;
                $log_candy_number = $zhitui6_candy;
                $only = 2;
            }
        }
        try {
            DB::beginTransaction();
            //当天新增实名认证团队人数
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            if($user->new_isreal_time<$beginToday)
            {
                $user->today_real_teamnumber=1;
            }
            else
            {
                $user->today_real_teamnumber=$user->today_real_teamnumber+1;
            }
            $user->new_isreal_time=time();



            $result = $user->save();
            if (!$result) {
                throw new \Exception('用户' . $user->account_number . '更新信息失败');
            }
            //开始记录日志
            if ($only == 2) {
                $prize_pool = new PrizePool();
                $prize_pool->scene = PrizePool::CERTIFICATION;//const CERTIFICATION = 1; //实名认证奖励
                $prize_pool->reward_type = PrizePool::REWARD_CANDY;//const REWARD_CANDY = 0; //奖励通证
                $prize_pool->reward_qty = $log_candy_number;
                $prize_pool->from_user_id = $user->id;
                $prize_pool->to_user_id = $user->id;
                $prize_pool->status = 1;
                $prize_pool->memo = '下级直推' . $real_zhitui . '人，实名认证团队' . $user->real_teamnumber . '人，充值金额' . $user->top_upnumber . '美金，触发通证奖励' . $log_candy_number;
                $prize_pool->create_time = time();
                $prize_pool->receive_time = time();
                $result = $prize_pool->save();
                if (!$result) {
                    throw new \Exception('用户' . $user->account_number . '奖励记录失败');
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 取用户的上级工作室
     *
     * @param App\Users $user
     * @return App\Users||null
     */
    public static function getParentsAtelier($user)
    {
        $parents = UserDAO::getParentsPathDesc($user);
        //将所有上级工作室查出来
        $parent_atelier = Users::where('is_atelier', 1)
            ->whereIn('id', $parents)
            ->get();
        if (count($parent_atelier) <= 0) {
            return $parent_atelier;
        }
        $parents_sort = array_flip($parents);
        //检测有没有达到封页
        $sorted = $parent_atelier->sortBy(function ($item, $key) use ($parents_sort) {
            $sort = $parents_sort[$item->id];
            return $sort;
        });
        $sorted = $sorted->values();
        return $sorted;
    }

    //递归查询用户下级所有人数
    public function GetTeamMember($members, $mid)
    {
        $Teams = array();//最终结果
        $mids = array($mid);//第一次执行时候的用户id
        do {
            $othermids = array();
            $state = false;
            foreach ($mids as $valueone) {
                foreach ($members as $key => $valuetwo) {
                    if ($valuetwo['parent_id'] == $valueone && $valuetwo['is_realname'] == 2) //实名认证通过的团队人数
                    {
                        $Teams[] = $valuetwo['id'];//找到我的下级立即添加到最终结果中
                        $othermids[] = $valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                        //                        array_splice($members,$key,1);//从所有会员中删除他
                        $state = true;
                    }
                }
            }
            $mids = $othermids;//foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);
        $Teams = Users::whereIn("id", $Teams)->where("is_realname", "=", 2)->count();

        return $Teams;
    }

    /**
     * 更新用户团队充值业绩金额
     * @param integer $id 用户的id值，用于查询更新该用户上级的团队充值金额
     * @return $number 充值金额值
     * * @param integer $qty 要取的上级代数,不传或传null则取全部
     */
    public static function updateTopUpnumber($id, $number, $qty = null)
    {
        $user = Users::find($id);
        $parents = self::getParentsPathDesc($user, $qty);
        $result = Users::whereIn('id', $parents)->increment('top_upnumber', $number);
        //此处应再遍历检查一下$parents是否符合升级条件
        foreach ($parents as $key => $current_user_id) {
            $current_user = Users::find($current_user_id);
            if (!$current_user) {
                continue;
            }
            self::checkUserRealNameReward($current_user);
            self::checkUpgradeAtelierCondition($current_user);
        }
        return $result;
    }


    /**
     * 查询用户的指定代数的上级(根据parents_path信息)
     *
     * @param App\Users $user 用户模型实例
     * @param integer $qty 要取的上级代数,不传或传null则取全部
     * @return array 返回包含上级id的数组
     */
    public static function getParentsPathDesc($user, $qty = null)
    {
        $path = $user->parents_path;
        if ($path == null || empty($path)) {
            return [];
        }
        $parents = explode(',', $path);
        $parents = array_filter($parents);
        krsort($parents);
        $parents = array_slice($parents, 0, $qty);
        return $parents;
    }

    /**
     * 递归查询上级
     *
     * @param App\Users $user 用户模型实例
     * @return array
     */
    public static function getRealParents($user)
    {
        $found_parent_node = [];
        $parents = self::findParent($user, $found_parent_node);
        return $parents;
    }

    /**
     * 递归查询上级(字符串)
     *
     * @param App\Users $user 用户模型实例
     * @return string 返回逗号间隔的path
     */
    public static function getRealParentsPath($user)
    {
        $parents = self::getRealParents($user);
        if (count($parents) > 0) {
            return implode(',', $parents);
        }
        return '';
    }

    private static function findParent($user, &$found_parent_node)
    {
        $parent_id = $user->parent_id;

        if ($parent_id) {
            //检测节点关系是否有死循环
            if (in_array($parent_id, $found_parent_node)) {
                $context = [
                    'user_id' => $user->id,
                    'parent_id' => $parent_id,
                    'found_parent_node' => $found_parent_node,
                ];
                //记录错误日志
                Log::useDailyFiles(base_path('storage/logs/user/'), 7);
                Log::critical('id:' . $user->id . '的用户,上级关系存在死循环', $context);
                return [];
            }
            array_unshift($found_parent_node, $parent_id);
            $parent = Users::find($parent_id);
            $result = self::findParent($parent, $found_parent_node);
            unset($parent);
            array_push($result, $parent_id);
            return $result;
        } else {
            return [];
        }
    }

    /**
     * 检查用户是否符合对应级别的升级,若符合就升级(不降级)
     *
     * @param App\Users $user 要升级的用户模型实例
     * @param App\Users $from_user 触发者用户模型实例
     * @return void 无返回值
     */
    public static function upgradeCheck($user)
    {
        $before_level = $user->level_id;
        $new_level = 2;
        if ($user->is_disable == 1) {
            $new_level = 1;
        } else if ($user->total_integral >= 1000000) {
            $new_level = 5;
        } else if ($user->total_topup >= 10000) {
            $new_level = 4;
        } else if ($user->total_topup >= 300) {
            $new_level = 3;
        }

        //查询等级对应的id
        $level = Level::where('code', $new_level)->first();
        //不掉级处理
        if ($before_level < $new_level) {
            try {
                DB::transaction(function () use ($user, $level, $before_level, $new_level) {
                    $user_upgrade_log = new UserUpgradeLog();
                    $user_upgrade_log->user_id = $user->id;
                    $user_upgrade_log->from_user_id = $user->id;
                    $user_upgrade_log->before_level = $before_level;
                    $user_upgrade_log->after_level = $new_level;
                    $user_upgrade_log->memo = '用户等级变更:由[' . self::get_level_name($before_level) . ']升级到[' . self::get_level_name($new_level) . ']';
                    $user_upgrade_log->created_time = time();
                    $result = $user_upgrade_log->save();
                    if (!$result) {
                        throw new \Exception('记录用户升级日志失败');
                    }
                    $user->level_id = $level->id;
                    $result = $user->save();
                    if (!$result) {
                        throw new \Exception('变更用户等级失败');
                    }
                });
            } catch (\Exception $e) {
                echo '<pre>';
                echo '错误:' . $e->getMessage() . PHP_EOL . ',文件:' . $e->getFile() . PHP_EOL . '行号:' . $e->getLine();
                return;
            }
//            $parent = Users::find($user->parent_id);
//            if ($parent) {
//                self::upgradeCheck($parent, $user);
//            }
        }
    }


    public static function get_level_name($id = 2)
    {

        $name = '';
        switch ($id) {
            case 1:
                $name = '限制会员';
                break;
            case 2:
                $name = '临时会员';
                break;
            case 3:
                $name = '正式会员';
                break;
            case 4:
                $name = '五星会员';
                break;
            case 5:
                $name = 'VIP会员';
                break;
            default:
                $name = '临时会员';
        }

        return $name;
    }
}
