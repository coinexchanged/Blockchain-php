<?php
/**
 * Created by Vscode
 * User: LDH
 * 投诉建议
 *  */
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Session;
use App\FeedBack;
use App\Users;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class FeedBackController extends Controller
{ 
    //反馈信息列表
    public function myFeedBackList(Request $request){
        $limit = Input::get('limit', 10);
        $page = Input::get('page', 1);
        $user_id = Users::getUserId();
        $feedBackList = FeedBack::where('user_id', $user_id)
        ->orderBy('id', 'desc')
        ->paginate($limit, ['*'], 'page', $page);
        foreach ($feedBackList->items() as &$value) {
            unset($value->replay_content);
        }
        return $this->success(array(
            "list" => $feedBackList->items(), 'count' => $feedBackList->total(),
            "page" => $page, "limit" => $limit
        ));
    }
    //反馈信息内容，包括回复信息
    public function feedBackDetail(){
        $id = Input::get('id', 10);
        $feedBack = FeedBack::find($id);
        return $this->success($feedBack);
    }
    //提交反馈信息
    public function feedBackAdd(){
        $user_id = Users::getUserId();
        $content = Input::get('content', '');
        if(empty($content)){
            return $this->error('内容不能为空');
        }
        $img = Input::get('img', '');
        try{
            $feedBack = new FeedBack();
            $feedBack->user_id = $user_id;
            $feedBack->content = $content;
            $feedBack->is_reply = 0;
            $feedBack->img = $img;
            $feedBack->create_time = time();
            $feedBack->save();
            return $this->success('提交成功，我们会尽快给你回复');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
}