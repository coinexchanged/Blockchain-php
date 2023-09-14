<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\{
    Address, AccountLog, Currency,BindBoxSuccessOrder, UsersInsurance, InsuranceType, InsuranceClaimApply, Setting, Users, UserCashInfo, UserReal, UsersWallet,BindBox,BindBoxOrder,BindBoxQuotationLog,BindBoxRaityHouse
};
use App\Utils\Hash;

class BindBoxController extends Controller
{
    public function index()
    {
        return view("admin.bindBox.index");
    }
    
    public function lists(Request $request)
    {
        $limit = $request->get('limit');
        $code = $request->get('code', '');
        $status = $request->get('status', '');
        $pay_type = $request->get('pay_type', '');
        $name = $request->get('name', '');
        $rarity= $request->get('rarity', '');
        $user_id = $request->get('author', null);
        

        $list = BindBox::where(function ($query) use ($code) {
            if (!empty($code)) {
                $query->where('code', $code);
            }
        })->where(function ($query) use ($rarity) {
            if ($rarity != '') {
                $query->where('rarity', $rarity);
            }
        })->where(function ($query) use ($name) {
            if ($name != '') {
                $query->where('name', $name);
            }
        })->where(function ($query) use ($pay_type) {
            if ($pay_type != '') {
                $query->where('pay_type', $pay_type);
            }
        })->where(function ($query) use ($status) {
            if ($status != '') {
                $query->where('status', $status);
            }
        })->where(function ($query) use ($user_id) {
            if (!empty($user_id)) {
                    $query->where('author', $user_id);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        
        return $this->layuiData($list);
    }
    
    public function add(){
        
        $currency = Currency::where('is_display',1)->get();
        return view("admin.bindBox.add",['currency'=>$currency]);
    }
    
    //添加NFT
    public function addNFT(Request $request){
        $author = $request->post('author');
        $_author = Users::where("id",$author)->first();
        
        if(!$_author){
          return $this->error('用户不存在');  
        }
        
        $type = $request->post('type');
        $currency = $request->post('currency');
        $name = $request->post('name');
        $author_description = $request->post('author_description');
        $description = $request->post('description');
        $image = $request->post('image');
        $price = $request->post('price');
        $per_increase = $request->post('per_increase');
        $pay_type = $request->post('pay_type');
        $start_time = $request->post('start_time');
        $end_time = $request->post('end_time');
        $rarity = $request->post('rarity');
        $margin = $request->post('margin');
        $rarity_house_id = $request->post('rarity_house_id');
        
        if(!$type || !$currency || !$name || !$image ||!$price ||!$pay_type ||!$rarity || !$start_time|| !$end_time){
            return $this->error('必填项！');  
        }
        
        if( strtotime($start_time) < time() ){
            return $this->error("The start time cannot be less than the current time!");
        }
        
        if( strtotime($start_time) > strtotime($end_time) ){
            return $this->error("The start time cannot be less than the end_time!");
        }
        
        if($pay_type == 1){ //一口价
            if($price<=0){
                return $this->error('价格不能为空'); 
            }
        }elseif($pay_type == 2){//竞拍
            if($price<=0){
                return $this->error('竞拍起始价不能为0'); 
            }
            if($per_increase<=0){
                return $this->error('竞拍加价价格不能小于0'); 
            }
            if($margin<=0){
                return $this->error('竞拍商品未设置保证金!'); 
            }
        }elseif($pay_type == 3){ //盲盒
            if($price<=0){
                return $this->error('价格不能为空'); 
            }
            if($rarity_house_id<=0){
                return $this->error('未选择开出的盲盒'); 
            }
        }else{
            return $this->error('交易类型错误'); 
        }
        
        $nft = new  BindBox();
        $nft->code = $this->getCode();
        $nft->type = $type;
        $nft->currency_id = $currency;
        $nft->name = $name;
        $nft->price = $price;
        $nft->image = $image;
        $nft->author_description = $author_description??'';
        $nft->author = $author;
        $nft->owner = $author;
        $nft->status = 1;
        $nft->start_time = $start_time;
        $nft->end_time = $end_time;
        $nft->created = date('Y-m-d H:i:s');
        $nft->updated = date('Y-m-d H:i:s');
        $nft->pay_type = $pay_type;
        $nft->margin = $margin ?? 0;
        $nft->per_increase = $per_increase??0;
        $nft->rarity_house_id = $rarity_house_id??0;
        if($pay_type == 2){
           $nft->resell_nft_status = 1;//新添加的默认为竞拍中
           
        }
        if($pay_type == 3){
            $nft->rarity_status = 0;//盲盒状态未开启
            $bind_box_raity_house = BindBoxRaityHouse::where(['id'=>$rarity_house_id,'status'=>0])->first();
            if(!$bind_box_raity_house){
                return $this->error('选择的盲盒不可用'); 
                }
            $bind_box_raity_house->status =1; //此盲盒已被使用
            $bind_box_raity_house->save();
        }
        $nft->chain = 'HX'; //汇信链
        $nft->rarity = $rarity;//稀有度
        $nft->save();
        
        return $this->success('操作成功');
    }
    
    public function edit(Request $request){
        $nft = BindBox::where('id',$request->get('id'))->first();
        
        return view("admin.bindBox.edit",['results'=>$nft]);
    }
    
    //编辑NFT
    public function editNFT(Request $request){
        $code =  $request->post('code');
        $price = $request->post('price');
        $rarity = $request->post('rarity');
        $start_time = $request->post('start_time');
        $end_time = $request->post('end_time');
        $per_increase = $request->post('per_increase');
       
       if(!$price || !$rarity ||!$start_time || !$end_time ||!$code){
           return $this->error('参数错误!');
       }
        $bind_box = BindBox::where('code',$code)->first();
            if($bind_box->lock_order ==1 ){
                return $this->error('等待支付，不可编辑');
            }
        
            if($bind_box->resell_nft_status ==1 ){
                return $this->error('NFT竞拍中不可编辑');
            }
        $bind_box->price = $price;
        $bind_box->start_time = $start_time;
        $bind_box->end_time = $end_time;
        $bind_box->per_increase = $per_increase;
        $bind_box->rarity = $rarity;
        $bind_box->save();
        
        return $this->success('操作成功');
    }
    
    
    public function order(){
        $currency = Currency::where('is_display',1)->get();
        
        return view("admin.bindBox.order",['currency'=>$currency]);
    }
    
    //NFT交易记录
    public function orderList(Request $request){
        $limit = $request->get('limit');
        $code = $request->get('code');
        $buyer_id = $request->get('buyer_id');
        $currency_id = $request->get('currency_id');
        

        $list = BindBoxOrder::where(function ($query) use ($code) {
            if (!empty($code)) {
                $query->where('code',  $code);
            }
        })->where(function ($query) use ($currency_id) {
            if (!empty($currency_id)) {
                    $query->where('currency_id', $currency_id);
            }
        })->where(function ($query) use ($buyer_id) {
            if (!empty($buyer_id)) {
                    $query->where('buyer_id', $buyer_id);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        
        return $this->layuiData($list);
    }
    
    public function quotation(){
        $currency = Currency::where('is_display',1)->get();
        
        return view("admin.bindBox.quotation",['currency'=>$currency]);
    }
    
    //出价记录
    public function quotationList(Request $request){
        $limit = $request->get('limit');
        $code = $request->get('code');
        $status = $request->get('status');
        $buyer_id = $request->get('buyer_id');
        $currency_id = $request->get('currency_id');
        

        $list = BindBoxQuotationLog::where(function ($query) use ($code) {
            if (!empty($code)) {
                $query->where('code',  $code);
            }
        })->where(function ($query) use ($currency_id) {
            if (!empty($currency_id)) {
                    $query->where('currency_id', $currency_id);
            }
        })->where(function ($query) use ($status) {
            if (!empty($status)) {
                    $query->where('status', $status);
            }
        })->where(function ($query) use ($buyer_id) {
            if (!empty($buyer_id)) {
                    $query->where('buyer_id', $buyer_id);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        
        return $this->layuiData($list);
        
    }
    
    //获取盲盒仓库
    public function getRarityHouse(Request $request){
        $rarity = $request->post('rarity');//稀有度
        $rarity_house = BindBoxRaityHouse::where(['rarity'=>$rarity,'status'=>0])->get();
        
        return json_encode(['rarity_house'=>$rarity_house]);
    }
    
    public function rarity_house(){
        
        return view("admin.bindBox.rarity_house");
    }
    
    //盲盒列表
    public function rarity_house_list(Request $request){
        
        $limit = $request->get('limit');
        $name = $request->get('name');
        $rarity = $request->get('rarity');
        

        $list = BindBoxRaityHouse::where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like' ,"%$name%");
            }
        })->where(function ($query) use ($rarity) {
            if (!empty($rarity)) {
                    $query->where('rarity', $rarity);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        
        return $this->layuiData($list);
    }
    
    public function add_rarity_house_view(){
        
        return view("admin.bindBox.add_rarity_house_view");
    }
    
    public function add_rarity_house(Request $request){
        
        $file = $request->post('file');
        $rarity = $request->post('rarity');
        $name = $request->post('name');
        
        if(!$file || !$rarity){
            return $this->error('必填项不能为空');
        }
        $BindBoxRaityHouse = new BindBoxRaityHouse();
        $BindBoxRaityHouse->file= $file;
        $BindBoxRaityHouse->rarity = $rarity;
        $BindBoxRaityHouse->name = $name??'未命名';
        $BindBoxRaityHouse->status = 0;
        $BindBoxRaityHouse->created = date('Y-m-d H:i:s',time());
        $BindBoxRaityHouse->save();
        
        return $this->success('操作成功');
    }
    
    public function  edit_rarity_house_view(Request $request){
        $id = $request->get('id');
        $BindBoxRaityHouse = BindBoxRaityHouse::where('id',$id)->first();
        
        return view("admin.bindBox.edit_rarity_house_view",['results'=>$BindBoxRaityHouse]);
    }
    
    public function edit_rarity_house(Request $request){
        $id = $request->post('id');
        $file = $request->post('file');
        $rarity = $request->post('rarity');
        $name = $request->post('name');
        
        if(!$file || !$rarity){
            return $this->error('必填项不能为空');
        }
        
        $BindBoxRaityHouse = BindBoxRaityHouse::where('id',$id)->first();
        // echo '<pre>';
        // var_dump($BindBoxRaityHouse);exit;
        $bind_box = BindBox::where('rarity_house_id',$id)->where('rarity',$BindBoxRaityHouse->rarity)->first();
        
        if($BindBoxRaityHouse->status == 1 && $bind_box){ // 已被使用 并且开启  不可编辑
            return $this->error('盲盒已开启，不可编辑');
        }elseif($bind_box && $BindBoxRaityHouse->status == 0){ //盲盒已经被购买  未开启
            $BindBoxRaityHouse->file= $file;
            $BindBoxRaityHouse->save();
        }else{//未被使用
            $BindBoxRaityHouse->name= $name;
            $BindBoxRaityHouse->file= $file;
            $BindBoxRaityHouse->rarity = $rarity;
            $BindBoxRaityHouse->save();
        }
        $limit = $request->get('limit');
        $name = $request->get('name');
        $rarity = $request->get('rarity');
        

        $list = BindBoxRaityHouse::where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like' ,"%$name%");
            }
        })->where(function ($query) use ($rarity) {
            if (!empty($rarity)) {
                    $query->where('rarity', $rarity);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }
    
    public function success_order(){
        
        return view("admin.bindBox.success_order");
    }
    
    public function success_order_list(Request $request){
        
        $limit = $request->get('limit');
        $user_id = $request->get('user_id');
        $is_pay = $request->get('is_pay');
        $code = $request->get('code');
        

        $list = BindBoxSuccessOrder::where(function ($query) use ($user_id) {
            if (!empty($user_id)) {
                $query->where('user_id', 'like' ,"%$user_id%");
            }
        })->where(function ($query) use ($code) {
            if (!empty($code)) {
                    $query->where('code', $code);
            }
        })->where(function ($query) use ($is_pay) {
            if (!empty($is_pay)) {
                    $query->where('is_pay', $is_pay);
            }
        })->orderBy('id', 'desc')->orderBy('is_expired', 'ASC')->paginate($limit);
        
        
        return $this->layuiData($list);
    }
    
    private function getCode(){
        $code = '0x' . Hash::createCode();
        $count = BindBox::where('code',$code)->count();
        if($count>0){
            $this->getCode();
        }else{
            return $code;
        }
        
    }
    
    public function del(Request $request){
        $id = $request->post('id');
        if(is_array($id)){
            $ids = $id;
        }else{
            $ids = [$id];
        }
        
        $bind_boxs = BindBox::whereIn('id',$ids)->get();
        if(!$bind_boxs){
            return $this->error('NFT不存在');
        }
        foreach ($bind_boxs as $bind_box){
            if($bind_box->lock_order ==1 ){
                return $this->error('等待支付，不可删除');
            }
        
            if($bind_box->resell_nft_status ==1 ){
                return $this->error('NFT竞拍中不可删除');
            }
            $bind_box->delete();
        }
        return $this->success('操作成功');
    }
    
}
