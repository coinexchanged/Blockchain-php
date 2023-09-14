<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\{Agent, Setting, Users};

/**
 * 该类处理所有的代理商添加修改等操作
 * Class MemberController
 * @package App\Http\Controllers\Agent
 */
class MemberController extends Controller
{

    private $agent_max_level = 4;

    function __construct()
    {
        $this->agent_max_level = Setting::getValueByKey('agent_max_level',4);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {

            $username = $request->input("username", "");
            $password = $request->input("password", "");

            if (empty($username) || empty($password)) return $this->error("参数错误");
            $agent = DB::table('agent')->where("username", $username)->first();

            if ($agent == null || empty($agent)) return $this->error("代理商未找到");
            if ($agent->is_lock == 1) return $this->error("账号被锁定，禁止登录");
            if (Users::MakePassword($password) != $agent->password) {
                return $this->error("密码错误");
            }
            session()->put('agent_username', $agent->username);
            
            session()->put('agent_id', $agent->id);

            return $this->success('登录成功！');
        } else {
            return $this->error('非法操作！');
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $res = Agent::delSession($request);
        if ($res) {
            return $this->success('退出成功');
        } else {
            return $this->error('退出登录失败，请重试');
        }
    }

    //修改密码页面
    public function setPas()
    {
        return view("agent.set.password");
    }

    /**
     * 修改密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePWD(Request $request)
    {
        $post = $request->post();

        $oldPassword  = $post['oldPassword'];
        $password     = $post['password'];
        $repassword   = $post['repassword'];
        $is_tong   = $post['is_tong'];//是否同步用户密码
        $agent=Agent::getAgent();
        
        if(empty($agent)){
            return $this->error('代理商不存在');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('密码只能在6-16位之间');
        }
        $now_password = $agent->getOriginal('password');
       
        $encrypted_password = Users::MakePassword($password);
        
        if (Users::MakePassword($oldPassword) != $now_password) {
            return $this->error("老密码错误");
        }

        if($password !== $repassword) return $this->error('两次密码不一致');
        if($now_password == $encrypted_password)  return $this->error('不能和原密码一致');
        
        $agent->password = $encrypted_password;
       
        
        try {
            DB::beginTransaction();
                $agent->save();

                if($is_tong ==1){
                    if($agent->is_admin !=1){
                        //同步用户密码
                        $user=Users::find($agent->user_id);
                        if($user){
                            $user->password = $encrypted_password;
                            $user->save();
                        }

                    }
                    

                }
          
            DB::commit();
            return $this->success('修改成功！');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    //修改代理商基本信息
    public function setInfo()
    {
        $agent=Agent::getAgent();
        if(empty($agent)){
            return $this->error('代理商不存在');
        }
        // if($agent->is_admin == 1){
        //     abort(403, '超管无需修改');
            
        // }
        return view("agent.set.info",['agent'=>$agent]);
    }

    /**
     * 获取代理用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo(Request $request)
    {
        $access_token = $request->get('access_token', 0);

        $agent = Agent::with('user')->where('id', session($access_token))->first();

        if (!$agent)  return $this->error('非法参数！');

        return $this->ajaxReturn($agent);
    }


    public function saveUserInfo(Request $request)
    {
        $post = $request->post();

       
        $nickname  = $post['nickname'];
        $phone     = $post['phone'];
        $email     = $post['email'];
        $agent=Agent::getAgent();
        if($agent->is_admin == 1){
            return $this->error('超级代理商不用设置');
            
        }
        $user_id=$agent->user_id;
        $user = Users::where('id', $user_id)->first();
        $user->nickname = $nickname;
        $user->phone = $phone;
        $user->email = $email;
       
        try {
            $user->save();
            return $this->success('修改成功！');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }


    }

    /**代理商列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
    {

        $username = $request->input("username", "");
        $id = $request->input("id", 0);
        $is_lock = $request->input("is_lock", 2);
        $is_addson = $request->input("is_addson", 2);
        $parent_agent_id = $request->input("parent_agent_id", 0);

        $_self = Agent::getAgent();

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }

        $where = [];
        if (!empty($username)) {
            $where[] = ['username', '=', $username];

            $_search_us = Agent::getUserByUsername($username);

            if ($_search_us == null) {
                return $this->error('该代理商不存在');
            } else {
                $level_path_Arr = explode(',', $_search_us->agent_path);
                if (!in_array($_self->id, $level_path_Arr)) {
                    return $this->error('该代理商并不属于您的团队');
                }
            }
        }
        if ($id > 0) {
            $where[] = ['id', '=', $id];

            $_search_us = Agent::getAgentById($id);
            if ($_search_us === null) {
                return $this->error('该代理商不存在');
            } else {
                $level_path_Arr = explode(',', $_search_us->agent_path);
                if (!in_array($_self->id, $level_path_Arr)) {
                    return $this->error('该代理商并不属于您的团队');
                }
            }
        }
        if (in_array($is_lock, [0, 1])) {
            $where[] = ['is_lock', '=', $is_lock];
        }
        if (in_array($is_addson, [0, 1])) {
            $where[] = ['is_addson', '=', $is_addson];
        }

        if ($parent_agent_id > 0) {
            $where[] = ['parent_agent_id', '=', $parent_agent_id];
        } else {
            $where[] = ['parent_agent_id', '=', $_self->id];
        }

        $result = Agent::where('status', 1)->where($where)->paginate(10);

        return $this->layuiData($result);
    }

    /**
     * 添加下级代理商时，查询该用户是否存在，是否已经是代理商等
     */
    public function searchuser(Request $request)
    {

        if ($request->isMethod('post')) {
            $username = $request->input("username", "");

            $_self = Agent::getAgent();

            if ($_self === null) {
                return $this->outmsg('发生错误！请重新登录');
            }

            if (!empty($username) && $_self != null && !empty($_self)) {

                $user = Users::getByAccountNumber($username);
                if ($user != null) {

                    $agent = Agent::getUserByUsername($username);
                    if ($agent === null) {
                        $agent_max_level = Setting::getValueByKey('agent_max_level',4);
                        if (($_self->level == $agent_max_level && $_self->is_admin == 0)) {
                            return $this->notice("您是{$agent_max_level}级代理商，不能添加下级代理商");
                        } else if (($_self->is_addson == 0)) {
                            return $this->notice('您尚未拥有添加下级代理商的权限');
                        } else if (($_self->is_lock == 1)) {
                            return $this->notice('您的代理商帐号被锁定');
                        } else {
                            $returnData = [];
                            $returnData['user_id'] = $user->id;
                            $returnData['username'] = $user->account_number;
                            $returnData['son_level'] = 0;

                            if ($_self->level == 0 && $_self->is_admin == 1) {
                                $returnData['son_level'] = 1;
                            } else {
                                $returnData['son_level'] = $_self->level+1;
                            }

                            $returnData['max_pro_loss'] = $_self->pro_loss;
                            $returnData['max_pro_ser'] = $_self->pro_ser;

                            return $this->ajaxReturn($returnData);
                        }
                    } else {
                        return $this->notice('该用户已经是代理商');
                    }
                } else {
                    return $this->error('该用户不存在');
                }
            } else {
                return $this->error('该用户不存在');
            }
        } else {
            return $this->error('非法操作！');
        }
    }

    //验证下级代理商
    public function search_agent_son (Request $request) {

        if ($request->isMethod('post')) {
            $id = $request->input("id", 0);
            $username = $request->input("username", '');

            if($id <= 0 || $username==''){
                return $this->error('参数错误');
            }

            $_self = Agent::getAgent();

            $_son = Agent::getAgentById($id);

            if (empty($_self) || empty($_son)) {
                return $this->outmsg('发生错误！请重新登录');
            }
            $level_path_Arr = explode(',', $_son->agent_path);
            if (!in_array($_self->id, $level_path_Arr)) {
                return $this->error('该代理商并不属于您的团队');
            }
            
            
            if (($_self->level == $this->agent_max_level && $_self->is_admin == 0)) {
                return $this->notice("您是{$this->agent_max_level}级代理商，不能添加下级代理商");
            } else if (($_self->is_addson == 0)) {
                return $this->notice('您尚未拥有添加下级代理商的权限');
            } else if (($_self->is_lock == 1)) {
                return $this->notice('您的代理商帐号被锁定');
            }
            if ($_son->level == $this->agent_max_level){
                return $this->notice("该用户是{$this->agent_max_level}级代理商，不能添加下级代理商");
            }

            if ($_son != null && !empty($id) && !empty($username) && $_self != null && !empty($_self)) {

                $user = Users::getByAccountNumber($username);
                if ($user != null) {

                    $agent = Agent::getUserByUsername($username);
                    if ($agent === null) {

                            $returnData = [];
                            $returnData['user_id'] = $user->id;
                            $returnData['username'] = $user->account_number;
                            $returnData['son_level'] = 0;

                            if ($_son->level == 0 && $_son->is_admin == 1) {
                                $returnData['son_level'] = 1;
                            } else{
                                $returnData['son_level'] = $_son->level + 1;
                            }

                            $returnData['max_pro_loss'] = $_son->pro_loss;
                            $returnData['max_pro_ser'] = $_son->pro_ser;

                            return $this->ajaxReturn($returnData);
                        
                    } else {
                        return $this->notice('该用户已经是代理商');
                    }
                } else {
                    return $this->error('该用户不存在');
                }
            } else {
                return $this->error('该用户不存在');
            }
        } else {
            return $this->error('非法操作！');
        }
    }

    /**
     * 添加下级的代理商
     * @param Request $request
     */
    public function addSonAgent(Request $request)
    {

        $_self = Agent::getAgent();


        $id = $request->input('agent_id', 0);//下级代理商id
        $_son = Agent::getAgentById($id);

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }

        if ($_son->level == $this->agent_max_level){
            return $this->notice("该用户是{$this->agent_max_level}级代理商，不能添加下级代理商");
        }

        if ($_self->level == $this->agent_max_level){
            return $this->notice("您是{$this->agent_max_level}级代理商，不能添加下级代理商");
        } else if (($_self->is_addson == 0)) {
            return $this->notice('您尚未拥有添加下级代理商的权限');
        } else if (($_self->is_lock == 1)) {
            return $this->notice('您的代理商帐号被锁定');
        }

        //判断下级
        $username = $request->input('username', 0);
        $user_id = $request->input('user_id', 0);

        $id = $request->input('id', 0);
        if (DB::table('users')->where('account_number', $username)->where('id', $user_id)->first() === null) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }
        $ag = Agent::getUserByUsername($username);
        if ($ag !== null && $id == 0) {
            return $this->error("该用户已经是代理商！");
        }


        $rules = [
            'pro_loss' => 'required|numeric|min:0.01|max:' . $_son->pro_loss,   //验证下级代理商的头寸比例是否正确
            'pro_ser' => 'required|numeric|min:0.01|max:' . $_son->pro_ser, // //验证下级代理商的手续费比例是否正确
            'is_lock' => 'required|in:1,0',
            'is_addson' => 'required|in:1,0',
            'user_id' => 'required|integer|min:0',
            'id' => 'required|integer|min:0'
        ];

        $messages = [
            'pro_loss.required' => '头寸比例不能为空',
            'pro_loss.numeric' => '头寸比例只能为数字',
            'pro_loss.min' => '头寸比例最小值为0.01',
            'pro_loss.max' => '头寸比例最大值为' . $_son->pro_loss,
            'pro_ser.required' => '手续费比例不能为空',
            'pro_ser.numeric' => '手续费比例只能为数字',
            'pro_ser.min' => '手续费比例最小值为0.01',
            'pro_ser.max' => '手续费比例最大值为' . $_son->pro_ser,
            'is_lock.required' => '是否锁定不能为空',
            'is_lock.in' => '是否锁定参数错误',
            'is_addson.required' => '是否填新不能为空',
            'is_addson.in' => '是否填新参数错误',
            'user_id.required' => '参数类型错误',
            'user_id.integer' => '参数类型错误',
            'user_id.min' => '非法操作',
            'id.required' => '参数类型错误',
            'id.integer' => '参数类型错误',
            'id.min' => '非法操作'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);
        //以上验证通过后 继续验证 .  测试用的～ ：）
        $validator->after(function ($validator) use ($request) {
            $user = Users::getById($request->get('user_id'));
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
        });

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $user = Users::getById($request->get('user_id'));
        //判断添加下级的代理商的等级
        if ($_son->level == 0 && $_son->is_admin == 1) {
            $level = 1;
        }  else {
            $level = $_son->level + 1;
        }

        if ($id > 0) {
            $agent = Agent::find($id);
        } else {
      
            //添加代理商时  用户授权码
            // $authorization_code = $request->input('authorization_code', '');
            
            // if(!Cache::has('authorization_code_'.$user->id)){
            //     return $this->error('用户授权码已失效,请重新生成');
            // }
            // $user_code=Cache::get('authorization_code_'.$user->id);


            // if(!$authorization_code || ($authorization_code != $user_code) ){
            //       return $this->error('用户授权码不正确');
            // }
            $agent = new Agent();
            $agent->reg_time = time();
        }
        $agent->user_id = $user_id;
        $agent->username = $username;
        $agent->password = $user->password;
        $agent->parent_agent_id = $_son->id;  //上级代理商id，有别于user表中的parent_id。  这个id取的是agent产生的id,并不是users表中的id。特别要注意！
        $agent->level = $level;
        $agent->is_admin = 0;
        $agent->is_lock = $request->input('is_lock', 0);
        $agent->is_addson = $request->input('is_addson', 1);
        $agent->pro_loss = $request->input('pro_loss', 0.00);
        $agent->pro_ser = $request->input('pro_ser', 0.00);
        $agent->status = 1;

        try {
            DB::beginTransaction();
            if (!$agent->save()) {
                DB::rollBack();
                return $this->error("操作失败！请重试");
            }
            if ($_son->is_admin == 1) {
                $agent->agent_path = $agent->id . ',' . $_son->id;
            } else {
                $agent->agent_path = $agent->id . ',' . $_son->agent_path; //上级代理商id的字符串拼接，这个id取的是agent产生的id,并不是users表中的id。特别要注意！
            }
            if ($agent->save()) {

                //更新该用户的代理商id
                $_users = Users::lockForUpdate()->find($user_id);
                $_users->agent_id = $agent->id;
                $_users->save();

                DB::commit();
                return $this->success("操作成功");
            } else {
                DB::rollBack();
                return $this->error("操作失败！请重试");
            }
        } catch (\Exception $ex) {                  //\Exception 捕获所有异常
            DB::rollBack();
            return $this->error($ex->getMessage()); // getMessage() 异常信息
        }
    }





    /**
     * 添加 编辑代理商
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAgent(Request $request)
    {

        $_self = Agent::getAgent();

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }
        //判断下级
        $username = $request->input('username', 0);
        $user_id = $request->input('user_id', 0);

        $id = $request->input('id', 0);//编辑
        if (DB::table('users')->where('account_number', $username)->where('id', $user_id)->first() === null) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }
        $ag = Agent::getUserByUsername($username);
        if ($ag !== null && $id == 0) {
            return $this->error("该用户已经是代理商！");
        }

        //判断自己
        if (($_self->level == $this->agent_max_level && $_self->is_admin == 0 && $id == 0)) {
            return $this->notice("您是{$this->agent_max_level}级代理商，不能添加下级代理商");
        } else if (($_self->is_addson == 0)) {
            return $this->notice('您尚未拥有添加下级代理商的权限');
        } else if (($_self->is_lock == 1)) {
            return $this->notice('您的代理商帐号被锁定');
        }

        $rules = [
            'pro_loss' => 'required|numeric|min:0.00|max:' . $_self->pro_loss,   //验证下级代理商的头寸比例是否正确
            'pro_ser' => 'required|numeric|min:0.00|max:' . $_self->pro_ser, // //验证下级代理商的手续费比例是否正确
            'is_lock' => 'required|in:1,0',
            'is_addson' => 'required|in:1,0',
            'user_id' => 'required|integer|min:0',
            'id' => 'required|integer|min:0'
        ];

        $messages = [
            'pro_loss.required' => '头寸比例不能为空',
            'pro_loss.numeric' => '头寸比例只能为数字',
            'pro_loss.min' => '头寸比例最小值为0.01',
            'pro_loss.max' => '头寸比例最大值为' . $_self->pro_loss,
            'pro_ser.required' => '手续费比例不能为空',
            'pro_ser.numeric' => '手续费比例只能为数字',
            'pro_ser.min' => '手续费比例最小值为0.01',
            'pro_ser.max' => '手续费比例最大值为' . $_self->pro_ser,
            'is_lock.required' => '是否锁定不能为空',
            'is_lock.in' => '是否锁定参数错误',
            'is_addson.required' => '是否填新不能为空',
            'is_addson.in' => '是否填新参数错误',
            'user_id.required' => '参数类型错误',
            'user_id.integer' => '参数类型错误',
            'user_id.min' => '非法操作',
            'id.required' => '参数类型错误',
            'id.integer' => '参数类型错误',
            'id.min' => '非法操作'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);
        //以上验证通过后 继续验证 .  测试用的～ ：）
        $validator->after(function ($validator) use ($request) {
            $user = Users::getById($request->get('user_id'));
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
        });

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $user = Users::getById($request->get('user_id'));
        if ($id > 0) {
            $agent = Agent::find($id);
            //编辑下一级代理商密码
            if($agent->parent_agent_id == $_self->id){
                $agent_password = $request->input('agent_password', '');
                if($agent_password){
                    $agent->password = Users::MakePassword($agent_password);
                }
            }

        } else {

            //添加代理商时  用户授权码
            // $authorization_code = $request->input('authorization_code', '');
            
            // if(!Cache::has('authorization_code_'.$user->id)){
            //     return $this->error('用户授权码已失效,请重新生成');
            // }
            // $user_code=Cache::get('authorization_code_'.$user->id);


            // if(!$authorization_code || ($authorization_code != $user_code) ){
            //       return $this->error('用户授权码不正确');
            // }

            $agent = new Agent();
            $agent->reg_time = time();
            $agent->parent_agent_id = $_self->id;  //上级代理商id，有别于user表中的parent_id。  这个id取的是agent产生的id,并不是users表中的id。特别要注意！

            //判断添加下级的代理商的等级
            if ($_self->level == 0 && $_self->is_admin == 1) {
                $level = 1;
            } else{
                $level =$_self->level +1;
            }
            $agent->level = $level;
            $agent->password = $user->password;
        }

        $agent->user_id = $user_id;
        $agent->username = $username;
        
        $agent->is_admin = 0;
        $agent->is_lock = $request->input('is_lock', 0);
        $agent->is_addson = $request->input('is_addson', 1);
        $agent->pro_loss = $request->input('pro_loss', 0.00);
        $agent->pro_ser = $request->input('pro_ser', 0.00);
        $agent->status = 1;

        try {
            DB::beginTransaction();
            if (!$agent->save()) {
                DB::rollBack();
                return $this->error("操作失败！请重试");
            }
            if ($_self->is_admin == 1) {
                $agent->agent_path = $agent->id . ',' . $_self->id;
            } else {
                $agent->agent_path = $agent->id . ',' . $_self->agent_path; //上级代理商id的字符串拼接，这个id取的是agent产生的id,并不是users表中的id。特别要注意！
            }
            if ($agent->save()) {

                //更新该用户的代理商id
                $_users = Users::lockForUpdate()->find($user_id);
                $_users->agent_id = $agent->id;
                $_users->save();

                DB::commit();
                return $this->success("操作成功");
            } else {
                DB::rollBack();
                return $this->error("操作失败！请重试");
            }
        } catch (\Exception $ex) { 
            DB::rollBack();                 //\Exception 捕获所有异常
            return $this->error($ex->getMessage()); // getMessage() 异常信息
        }
    }

    public function updateAgent(Request $request)
    {
        //判断下级
        $agentid = $request->input('agentid', 0);
        $_h = Agent::getAgentById($agentid);
        if ($_h == null || $_h->id <= 0) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }

        $rules = [
            'agentid' => 'required|numeric|min:1|max:999999999',   //id必须是数字
            'name' => 'required|in:is_lock,is_addson', //必须是指定的字段
            'value' => 'required|in:1,0'   //必须是指定的值
        ];

        $messages = [
            'agentid.required' => '用户id不能为空',
            'agentid.numeric' => '用户id只能为数字',
            'agentid.min' => '用户id最小值为1',
            'agentid.max' => '用户id最大值为999999999',
            'name.required' => '修改属性不能为空',
            'name.in' => '修改属性参数错误',
            'value.required' => '修改属性值不能为空',
            'value.in' => '修改属性值参数错误'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $agent = new Agent();
        $name = $request->input('name', 0);
        $value = $request->input('value', 0);

        if ($name == 'is_lock' && $value == 1) {
            $lock = time();
        } else {
            $lock = 0;
        }

        $res = $agent->where('id', $agentid)->update([$name => $value, 'lock_time' => $lock]);

        if ($res) {
            return $this->success('更新成功');
        } else {
            return $this->error('更新失败，请重新尝试');
        }
    }

  

    /**
     * @param $san_user
     *
     */
    public function sel_arr($arr = array())
    {
        if (!empty($arr)) {
            $new_arr = [];
            foreach ($arr as $k => $val) {
                $new_arr[] = $val->user_id;
            }
            return $new_arr;
        } else {
            return [];
        }
    }

    /**
     * @param $san_user
     *
     */
    public function sel_agent_arr($arr = array())
    {
        if (!empty($arr)) {
            $new_arr = [];
            foreach ($arr as $k => $val) {
                $new_arr[] = $val->id;
            }
            return $new_arr;
        } else {
            return [];
        }
    }

    public function allChildAgent()
    {
        $agents = Agent::getAllChildAgent(Agent::getAgentId());
        return $this->ajaxReturn($agents);
    }
}
