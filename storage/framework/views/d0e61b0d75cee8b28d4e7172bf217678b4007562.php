<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
    <header class="larry-personal-tit">
        
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">值</label>
                <div class="layui-input-block">
                    <input type="text" name="value" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            
                
                
                    
                
            
            <div class="layui-form-item">
                <label class="layui-form-label">类型</label>
                <div class="layui-input-block">
                    <select name="type" lay-filter="role_id">
                        <option value="">请选择</option>

                        <option value="1" selected >倍数</option>
                        <option value="2" >手数</option>

                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">币类</label>
                <div class="layui-input-block">
                    <select name="currency_id" lay-filter="role_id">
                        <option value="">请选择</option>
                        <?php $__currentLoopData = $currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value->id); ?>" class="ww"><?php echo e($value->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        

                    </select>
                </div>
            </div>
            
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
            form.on('submit(adminuser_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/levermultiple/doadd',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });

        });


    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>