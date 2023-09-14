@extends('agent.layadmin')

@section('title', '设置资料')

@section('page-head')

@endsection

@section('page-content')
  
<div class="layui-fluid">
  <div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
      <div class="layui-card">
        <div class="layui-card-header">设置我的资料</div>
        <div class="layui-card-body" pad15>
          
          <div class="layui-form" lay-filter="">
           
            <div class="layui-form-item">
              <label class="layui-form-label">我的角色</label>
              <div class="layui-input-inline">
                <input type="text"  value="{{$agent->self_info}}" readonly class="layui-input">
               
              </div>
              <div class="layui-form-mid layui-word-aux">当前角色不可更改为其它角色</div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">用户名</label>
              <div class="layui-input-inline">
                <input type="text" name="username" value="{{$agent->username }}" readonly class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">不可修改。一般用于后台登入名</div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">昵称</label>
              <div class="layui-input-inline">
                <input type="text" name="nickname" value="{{$agent->nickname}}" lay-verify="nickname" autocomplete="off" placeholder="请输入昵称" class="layui-input">
              </div>
            </div>
            
            <div class="layui-form-item">
              <label class="layui-form-label">手机</label>
              <div class="layui-input-inline">
                <input type="text" name="phone" value="{{$agent->phone}}" lay-verify="phone" autocomplete="off" class="layui-input">
              </div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">邮箱</label>
              <div class="layui-input-inline">
                <input type="text" name="email" value="{{$agent->email}}" lay-verify="email" autocomplete="off" class="layui-input">
              </div>
            </div>
            
            <div class="layui-form-item">
              <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="setmyinfo">确认修改</button>
                <button type="reset" class="layui-btn layui-btn-primary">重新填写</button>
              </div>
            </div>
          
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
layui.use(['index','form','layer','admin'], function(){
          
            var $ = layui.jquery;
            var form = layui.form;
            var admin = layui.admin;
            //设置我的资料
            form.on('submit(setmyinfo)', function(obj){
              //layer.msg(JSON.stringify(obj.field));

              //提交修改
              admin.req({
                  url: '/agent/save_user_info'
                  ,data: obj.field
                  ,type:'post'
                  ,success: function(res){
                      console.log(res);
                      if (res.code == 0){
                          //登入成功的提示与跳转
                          layer.msg(res.msg, {
                              icon: 1
                              ,time: 2000
                          });
                      }else{
                          layer.msg(res.msg, {
                              icon: 5
                          });
                      }
                  }
              });
              return false;
            });



          

          });



</script>
@endsection