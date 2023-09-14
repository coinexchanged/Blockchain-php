@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">用户手机号或邮箱</label>
            <div class="layui-input-block">
                <input type="text" name="account" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account}}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">真实姓名</label>
            <div class="layui-input-block">
                <input type="text" name="email" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}" disabled>
            </div>
        </div>
       

        <div class="layui-form-item">
            <label class="layui-form-label">身份证号码</label>
            <div class="layui-input-block">
                <input type="text" name="card_id" autocomplete="off" placeholder="" class="layui-input" value="{{$result->card_id}}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">正面照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->front_pic)){{$result->front_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->front_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div>
         <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">反面照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->reverse_pic)){{$result->reverse_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->reverse_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div>
        <!-- <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">手持身份证照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->hand_pic)){{$result->hand_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->hand_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div> -->
        
        
    </form>

@endsection

