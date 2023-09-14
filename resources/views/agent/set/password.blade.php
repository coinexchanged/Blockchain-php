
@extends('agent.layadmin')

@section('title', '设置密码')

@section('page-head')

@endsection

@section('page-content') 
<div class="layui-fluid">
  <div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
      <div class="layui-card">
        <div class="layui-card-header">修改密码</div>
        <div class="layui-card-body" pad15>
          
          <div class="layui-form" lay-filter="">
            <div class="layui-form-item">
              <label class="layui-form-label">当前密码</label>
              <div class="layui-input-inline">
                <input type="password" name="oldPassword" lay-verify="required" lay-verType="tips" class="layui-input">
              </div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">新密码</label>
              <div class="layui-input-inline">
                <input type="password" name="password" lay-verify="pass" lay-verType="tips" autocomplete="off" id="LAY_password" class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">6到16个字符</div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">确认新密码</label>
              <div class="layui-input-inline">
                <input type="password" name="repassword" lay-verify="repass" lay-verType="tips" autocomplete="off" class="layui-input">
              </div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">是否同步用户密码</label>
             
                <div class="layui-input-inline">
                    <input type="radio" name="is_tong" value="1" title="同步" >
                    <input type="radio" name="is_tong" value="0" title="不同步" checked >
                </div>
              
          </div>
            <div class="layui-form-item">
              <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="setmypass">确认修改</button>
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
            //设置密码
            form.on('submit(setmypass)', function(obj){
             //console.log(obj);
              
              //提交修改
              admin.req({
                url: '/agent/change_password'
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
                          window.location.reload();
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