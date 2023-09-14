<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
    <header class="larry-personal-tit">
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label for="currency_id" class="layui-form-label">币种</label>
                <div class="layui-input-block">
                    <select name="currency_id" lay-verify="required" lay-search>
                        <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($currency->id); ?>" <?php if((isset($result) && $result->currency_id == $currency->id)): ?> selected <?php endif; ?>><?php echo e($currency->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">产品期限</label>
                <div class="layui-input-block">
                    <input type="text" name="days" autocomplete="off" class="layui-input" value="<?php if(isset( $result->days)): ?><?php echo e($result->days); ?><?php endif; ?>" placeholder="请输入产品期限">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">产品利率</label>
                <div class="layui-input-block">
                    <input type="text" name="rates" autocomplete="off" class="layui-input" value="<?php if(isset( $result->rates)): ?><?php echo e($result->rates); ?><?php endif; ?>" placeholder="请输入产品利率">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">起投金额</label>
                <div class="layui-input-block">
                    <input type="text" name="pricemin" autocomplete="off" class="layui-input" value="<?php if(isset( $result->pricemin)): ?><?php echo e($result->pricemin); ?><?php endif; ?>" placeholder="请输入起投金额">
                </div>
            </div>

            <!--<div class="layui-form-item layui-form-text">-->
            <!--    <label class="layui-form-label">缩略图</label>-->
            <!--    <div class="layui-input-block">-->
            <!--        <button class="layui-btn" type="button" id="upload_test">选择图片</button>-->
            <!--        <br>-->
            <!--        <img src="<?php if(!empty($result->img)): ?><?php echo e($result->img); ?><?php endif; ?>" id="img_thumbnail" class="thumbnail" style="display: <?php if(!empty($result->img)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">-->
            <!--        <input type="hidden" name="img" id="img" value="<?php if(!empty($result->img)): ?><?php echo e($result->img); ?><?php endif; ?>">-->
            <!--    </div>-->
            <!--</div>-->
            <div class="layui-form-item">
                <label class="layui-form-label">产品状态</label>
                <div class="layui-input-block">
                    <select name="state" lay-verify="required" lay-search>
                        <option value="1" <?php if((isset($result) && $result->state == 1)): ?> selected <?php endif; ?>>启用</option>
                        <option value="0" <?php if((isset($result) && $result->state == 0)): ?> selected <?php endif; ?>>停用</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="id" value="<?php if(isset( $result->id)): ?><?php echo e($result->id); ?><?php endif; ?>">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="ltc_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
    <script>
        layui.use('upload', function(){
            var upload = layui.upload;

            //执行实例
            var uploadInst = upload.render({
                elem: '#upload_test' //绑定元素
                ,url: '<?php echo e(URL("api/upload")); ?>' //上传接口
                ,done: function(res){
                    //上传完毕回调
                    if (res.type == "ok"){
                        $("#thumbnail").val(res.message)
                        $("#img_thumbnail").show()
                        $("#img_thumbnail").attr("src",res.message)
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
        });
    </script>
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
            form.on('submit(ltc_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/ltc/postAdd',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.alert(res.message);
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