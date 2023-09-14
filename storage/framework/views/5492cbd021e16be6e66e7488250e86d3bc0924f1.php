<?php $__env->startSection('title', '设置资料'); ?>

<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
  
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
                <input type="text"  value="<?php echo e($agent->self_info); ?>" readonly class="layui-input">
               
              </div>
              <div class="layui-form-mid layui-word-aux">当前角色不可更改为其它角色</div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">用户名</label>
              <div class="layui-input-inline">
                <input type="text" name="username" value="<?php echo e($agent->username); ?>" readonly class="layui-input">
              </div>
              <div class="layui-form-mid layui-word-aux">不可修改。一般用于后台登入名</div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">昵称</label>
              <div class="layui-input-inline">
                <input type="text" name="nickname" value="<?php echo e($agent->nickname); ?>" lay-verify="nickname" autocomplete="off" placeholder="请输入昵称" class="layui-input">
              </div>
            </div>
            
            <div class="layui-form-item">
              <label class="layui-form-label">手机</label>
              <div class="layui-input-inline">
                <input type="text" name="phone" value="<?php echo e($agent->phone); ?>" lay-verify="phone" autocomplete="off" class="layui-input">
              </div>
            </div>
            <div class="layui-form-item">
              <label class="layui-form-label">邮箱</label>
              <div class="layui-input-inline">
                <input type="text" name="email" value="<?php echo e($agent->email); ?>" lay-verify="email" autocomplete="off" class="layui-input">
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

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('agent.layadmin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>