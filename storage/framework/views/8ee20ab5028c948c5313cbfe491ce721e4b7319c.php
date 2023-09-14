<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">交易账号</label>
            <div class="layui-input-block">
                <input type="text" name="account_number" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->account_number); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->name); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">商家电话</label>
            <div class="layui-input-block">
                <input type="text" name="mobile" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->mobile); ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">选择法币</label>
            <div class="layui-input-block">
                <select name="currency_id" lay-filter="type">
                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($currency->id); ?>" <?php if($result->currency_id == $currency->id): ?> selected <?php endif; ?>><?php echo e($currency->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        
            
            
                
            
        


        <div class="layui-form-item">
            <label class="layui-form-label">微信昵称</label>
            <div class="layui-input-block">
                <input type="text" name="wechat_nickname"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->wechat_nickname); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">微信账号</label>
            <div class="layui-input-block">
                <input type="text" name="wechat_account"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->wechat_account); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝昵称</label>
            <div class="layui-input-block">
                <input type="text" name="ali_nickname"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->ali_nickname); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝账号</label>
            <div class="layui-input-block">
                <input type="text" name="ali_account"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->ali_account); ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">选择银行</label>
            <div class="layui-input-block">
                <select name="bank_id" lay-filter="type">
                    <?php $__currentLoopData = $banks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($bank->id); ?>" <?php if($result->bank_id == $bank->id): ?> selected <?php endif; ?>><?php echo e($bank->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行账号</label>
            <div class="layui-input-block">
                <input type="text" name="bank_account"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->bank_account); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户支行</label>
            <div class="layui-input-block">
                <input type="text" name="bank_address"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->bank_address); ?>">
            </div>
        </div>


        <input type="hidden" name="id" value="<?php echo e($result->id); ?>">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>


        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'<?php echo e(url('admin/seller_add')); ?>'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>