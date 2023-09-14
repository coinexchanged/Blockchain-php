<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">基于币种</label>
            <div class="layui-input-inline">
                <input type="text" name="huobi_currency" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->huobi_currency)): ?><?php echo e($result->huobi_currency); ?><?php endif; ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">变换倍数</label>
            <div class="layui-input-inline">
                <input type="number" name="mult" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->mult)): ?><?php echo e($result->mult); ?><?php endif; ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易币</label>
            <div class="layui-input-inline">
                <select name="currency_id" lay-filter="" lay-search>
                    <option value=""></option>
                    <?php if(!empty($currencies)): ?>
                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($currency->id); ?>" <?php if($currency->id == $result->currency_id): ?> selected <?php endif; ?>><?php echo e($currency->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">法币</label>
            <div class="layui-input-inline">
                <select name="legal_id" lay-filter="">
                    <option value=""></option>
                    <?php if(!empty($currencies)): ?>
                    <?php $__currentLoopData = $legals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $legal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($legal->id); ?>" <?php if($legal->id == $result->legal_id): ?> selected <?php endif; ?>><?php echo e($legal->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">法币</label>
            <div class="layui-input-inline">
                <select name="legal_id" lay-filter="">
                    <option value=""></option>
                    <?php if(!empty($currencies)): ?>
                        <?php $__currentLoopData = $legals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $legal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($legal->id); ?>" <?php if($legal->id == $result->legal_id): ?> selected <?php endif; ?>><?php echo e($legal->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">卖</label>
            <div class="layui-input-inline">
                <select name="sell" lay-filter="">
                    <option value="1" <?php if($result->sell == '1'): ?> selected <?php endif; ?>>开启</option>
                    <option value="0" <?php if($result->sell == '0'): ?> selected <?php endif; ?>>关闭</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">买</label>
            <div class="layui-input-inline">
                <select name="buy" lay-filter="">
                    <option value="1" <?php if($result->buy == '1'): ?> selected <?php endif; ?>>开启</option>
                    <option value="0" <?php if($result->buy == '0'): ?> selected <?php endif; ?>>关闭</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机下限</label>
            <div class="layui-input-inline">
                <input type="text" name="number_min" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->number_min)): ?><?php echo e($result->number_min); ?><?php endif; ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机上限</label>
            <div class="layui-input-inline">
                <input type="text" name="number_max" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->number_max)): ?><?php echo e($result->number_max); ?><?php endif; ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">价格浮动下限</label>
            <div class="layui-input-inline">
                <input type="text" name="float_number_down" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->float_number_down)): ?><?php echo e($result->float_number_down); ?><?php endif; ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格浮动上限</label>
            <div class="layui-input-inline">
                <input type="text" name="float_number_up" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->float_number_up)): ?><?php echo e($result->float_number_up); ?><?php endif; ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">下单频率</label>
            <div class="layui-input-inline">
                <input type="text" name="second" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php if(!empty($result->second)): ?><?php echo e($result->second); ?><?php endif; ?>">
            </div>
        </div>

        <input type="hidden" name="id" value="<?php if(!empty($result->id)): ?><?php echo e($result->id); ?><?php endif; ?>">
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
                    url:'<?php echo e(url('admin/robot/add')); ?>'
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