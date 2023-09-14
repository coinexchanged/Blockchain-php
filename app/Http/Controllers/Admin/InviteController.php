<?php

namespace App\Http\Controllers\Admin;
use App\AccountLog;
use App\Users;
use App\Setting;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Input;

class InviteController extends Controller{



    //邀请返佣
    public function return(){
        return view("admin.invite.return");
    }

    public function returnList(Request $request){
        $limit = $request->get('limit',10);
        $account = $request->get('account','');

        $list = New AccountLog();
        
       if (!empty($account)) {
            $list = $list->whereHas('user', function($query) use ($account) {
            $query->where("phone",'like','%'.$account.'%')->orwhere('email','like','%'.$account.'%'); 
             } );
        }

        $list = $list->where('type', AccountLog::INVITATION_TO_RETURN)-> orderBy('id','desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

     public function del(Request $request){
        $id = $request->get('id');
        $accountlog = AccountLog::find($id);
        if(empty($accountlog)){
            $this->error("记录未找到");
        }
       
        try {
            $accountlog->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    //会员推荐关系图
    public function childs(){

         return view("admin.invite.childs");
    }


   public function getTree()
    {
        

        $data = Users::orderBy('id','asc')->get()->toArray();
        $list=$this->getSubTree($data);
        
        return response()->json(['code' => 0, 'data' => $list]);



    }

    public function getSubTree($data, $id = 0, $level = 0)
    {
        $list = array();
        foreach ($data as $key => $value) {
            $val=[];
            $val['parent_id'] = strval($value['parent_id']);
            if ($val['parent_id'] == $id) {
                $val['id'] = $value['id'];
                $val['name'] = $value['account'];
                $val['level'] = $level;
                $val['children'] =self::getSubTree($data, $value['id'], $level + 1);

                $list[]     = $val;

            }
        }

        return $list;
    }

     //邀请背景图
    public function bgIndex(){
        return view("admin.invite.bgIndex");
    }

    public function bgList(Request $request){
        $limit = $request->get('limit',10);
        
        $list = New InviteBg();

        $list = $list->orderBy('id','desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

     public function bgdel(Request $request){
        $id = $request->get('id');
        $bg = InviteBg::find($id);
        if(empty($bg)){
            $this->error("图片未找到");
        }
       
        try {
            $bg->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function edit(Request $request){
        
        $id = $request->get('id',0);
        if (empty($id)){
            $bg=New InviteBg();
            $bg->create_time=time();
        }else{
            $bg = InviteBg::find($id);
        }
        
        return view('admin.invite.edit',['res'=>$bg]);
    }

    public function doedit(){
        $id = Input::get("id");
        $pic= Input::get("pic");
        if(empty($pic)){
          return  $this->error('图片必须上传');
        }
        if (empty($id)){
            $bg=New InviteBg();
            $bg->create_time=time();
        }else{
            $bg = InviteBg::find($id);
        }
        $bg->pic=$pic;
        try {
            $bg->save();
            return $this->success('操作成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }


    }


    public function share(){
        $share_title = Setting::getValueByKey('share_title','');
        $share_content = Setting::getValueByKey('share_content','');
        $share_url = Setting::getValueByKey('share_url','');

        return view('admin.invite.share',['title'=>$share_title,'content'=>$share_content,'url'=>$share_url]);

    }

    public function postShare(Request $request){
        $title = Input::get("share_title");
        $content= Input::get("share_content");
        $url= Input::get("share_url");

        if(empty($title) || empty($content) || empty($url)){
            return $this->error('请填写完整信息');

        }
        $data = $request->all();
        
        try {

            foreach ($data as $key => $value) {
                Setting::updateValueByKey($key,$value);
            }
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }


    }





}
?>