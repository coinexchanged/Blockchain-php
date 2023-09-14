<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use App\DAO\PrizePool\CandySender;
use App\DAO\UserDAO;
use App\PrizePool;
use App\Setting;
use App\Users;
use App\UserReal;
use App\IdCardIdentity;
use App\Events\RealNameEvent;

class UserRealController extends Controller
{

    public function index()
    {
        return view("admin.userReal.index");
    }

    //用户列表
    public function list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $account = $request->get('account', '');
        $review_status_s = $request->get('review_status_s', 0);

        $list = new UserReal();
        if (!empty($account)) {
            $list = $list->whereHas('user', function ($query) use ($account) {
                $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
            });
        }
        if(!empty($review_status_s)){
            $list = $list->where('review_status',$review_status_s);
        }

        $list = $list->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function detail(Request $request)
    {

        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = UserReal::find($id);

        return view('admin.userReal.info', ['result' => $result]);
    }

    public function del(Request $request)
    {
        $id = $request->get('id');
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            $this->error("认证信息未找到");
        }
        try {

            $userreal->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
 
    /*
    //状态审核
    public function auth(Request $request)
    {
        $id = $request->get('id', 0);
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        if ($userreal->review_status == 1) {
            //从未认证到认证
            //查询users表判断是否为第一次实名认证
            $user = Users::find($userreal->user_id);
            $is_realname = $user->is_realname;
            if ($is_realname == 1) {
                //1:未实名认证过  2：实名认证过
                $real_zhitui = Users::where("is_realname", "=", 2)->where("parent_id", "=", $userreal->user_id)->count();//实名认证过的有效直推人数
                //获取下级的总人数
                $member = Users::get()->toArray();
                $real_teamnumber = $this->GetTeamMember($member, $userreal->user_id);//实名认证过的团队人数
                $candy_number = 10;
                $user->candy_number = $candy_number;
                $user->push_status = 1;
                $top_upnumber = $user->top_upnumber;

                //开始记录日志
                $candy_sender = new CandySender();
                PrizePool::send(
                    $candy_sender,
                    $sender = 0,
                    $user->candy_number,
                    $user,
                    $user,
                   //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                    $user->account_number . '实名认证通证奖励10个通证',
                    $attach_data = []
                );

                if (3 <= $real_zhitui) {
                    //给自己加通证 +20 
                    $user->candy_number = $user->candy_number + 20;
                    $user->push_status = 2;

                    $candy_sender = new CandySender();
                    PrizePool::send(
                        $candy_sender,
                        $sender = 0,
                        20,
                        $user,
                        $from_user = null,
                        //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                        '满足直推3人实名送20个通证',
                        $attach_data = []
                    );
                }
                if ($real_zhitui >= 5 && $real_teamnumber >= 15 && $top_upnumber >= 100) {
                    $user->candy_number = $user->candy_number + 50;
                    $user->push_status = 3;
                    $candy_sender = new CandySender();
                    PrizePool::send(
                        $candy_sender,
                        $sender = 0,
                        50,
                        $user,
                        $from_user = null,
                        //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                        '满足直推5人，实名认证团队15人充值达100美金；送50通证',
                        $attach_data = []
                    );
                }
                if ($real_zhitui >= 10 && $real_teamnumber >= 50 && $top_upnumber >= 1000) {
                    $user->candy_number = $user->candy_number + 300;
                    $user->push_status = 4;
                    $candy_sender = new CandySender();
                    PrizePool::send(
                        $candy_sender,
                        $sender = 0,
                        300,
                        $user,
                        $from_user = null,
                        //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                        '满足直推10人；实名认证，团队50人；充值达1000美金以上，送300通证',
                        $attach_data = []
                    );
                }
                if ($real_zhitui >= 30 && $real_teamnumber >= 100 && $top_upnumber >= 10000) {
                    $user->candy_number = $user->candy_number + 1300;
                    $user->push_status = 5;
                    $candy_sender = new CandySender();
                    PrizePool::send(
                        $candy_sender,
                        $sender = 0,
                        1300,
                        $user,
                        $from_user = null,
                        //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                        '满足直推30人，团队100人，充值1万送1300个',
                        $attach_data = []
                    );
                }
                if ($real_zhitui >= 50 && $real_teamnumber >= 200 && $top_upnumber >= 100000) {
                    $user->candy_number = $user->candy_number + 30000;
                    $user->push_status = 6;
                    $candy_sender = new CandySender();
                    PrizePool::send(
                        $candy_sender,
                        $sender = 0,
                        30000,
                        $user,
                        $from_user = null,
                        //PrizePool::CERTIFICATION,//const CERTIFICATION = 1; //实名认证奖励
                        '直推50人实名认证，团队200人，充值金额达10万USDT，送30000通证',
                        $attach_data = []
                    );
                }
                $user->real_teamnumber = $real_teamnumber;
                $user->is_realname = 2;
                $user->save();//自己实名认证获取通证结束

            }
            $userreal->review_status = 2;
        } else if ($userreal->review_status == 2) {
            $userreal->review_status = 1;
        } else {
            $userreal->review_status = 1;
        }
        try {
            $userreal->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    */

    public function auth(Request $request)
    {
        $id = $request->get('id', 0);
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        $user = Users::find($userreal->user_id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        if ($userreal->review_status == 1) {
            //从未认证到认证
            //查询users表判断是否为第一次实名认证
            $is_realname = $user->is_realname;
            if ($is_realname != 2) {
                //1:未实名认证过  2：实名认证过
             
                $user->is_realname = 2;

                $user->save();//自己实名认证获取通证结束
                //判断自己上级的的触发奖励
                //UserDAO::addCandyNumber($user);
            }
            $userreal->review_status = 2;
        } else if ($userreal->review_status == 2) {
            $userreal->review_status = 1;
        } else {
            $userreal->review_status = 1;
        }
        try {
            $userreal->save();
            //用户实名事件
            if ($userreal->review_status == 2) {
                event(new RealNameEvent($user, $userreal));
            }
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
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
                    if ($valuetwo['parent_id'] == $valueone) {
                        //实名认证通过的团队人数
                        $Teams[] = $valuetwo['id'];//找到我的下级立即添加到最终结果中
                        $othermids[] = $valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                        //                        array_splice($members,$key,1);//从所有会员中删除他
                        $state = true;
                    }
                }
            }
            $mids = $othermids;//foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);
        //$Teams=Users::where("parents_path","like","%$mid%")->where("is_realname","=",2)->count();
        $Teams = Users::whereIn("id", $Teams)->where("is_realname", "=", 2)->count();
        return $Teams;
    }
}
