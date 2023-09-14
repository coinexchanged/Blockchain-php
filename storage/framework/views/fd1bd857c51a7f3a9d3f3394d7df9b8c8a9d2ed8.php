<?php $__env->startSection('page-head'); ?>
<style>
    .hide {
        display: none;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this">基础参数</li>
                <li>秒合约参数</li>
                <li>提币参数</li>
                <li>链上参数</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-form-item">
                        <label class="layui-form-label">币种名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->name); ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">请确保在交易所中该币种名称是惟一的</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">排序</label>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="sort" name="sort" value="<?php echo e($result->sort); ?>" placeholder="排序为升序">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">价值</label>
                        <div class="layui-input-inline">
                        <input type="number" class="layui-input" id="price" name="price" value="<?php echo e($result->price); ?>" placeholder="价值">
                        </div>
                        <div class="layui-form-mid layui-word-aux">$</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">折合人民币比例</label>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="rmb_relation" name="rmb_relation" value="<?php echo e($result->rmb_relation); ?>" placeholder="百分比">
                        </div>
                        <!-- <div class="layui-form-mid layui-word-aux">%</div> -->
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">账户资产类型</label>
                        <div class="layui-input-block">
                            <input type="checkbox" name="is_legal" title="法币交易" value="1" lay-skin="primary" <?php if($result->is_legal ==1): ?> checked <?php endif; ?>>
                            <input type="checkbox" name="is_lever" title="合约交易" value="1" lay-skin="primary" <?php if($result->is_lever ==1): ?> checked <?php endif; ?>> 
                            <input type="checkbox" name="is_micro" title="秒交易" value="1" lay-filter="microtrade" lay-skin="primary" <?php if($result->is_micro ==1): ?> checked <?php endif; ?>>
                            <input type="checkbox" name="is_match" title="闪兑交易" value="1" lay-skin="primary" <?php if($result->is_match ==1): ?> checked <?php endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"></div>
                    </div>
                    <div class="layui-form-item layui-form-text">
                        <label class="layui-form-label">币种 logo</label>
                        <div class="layui-input-block">
                            <button class="layui-btn" type="button" id="upload_test">选择图片</button>
                            <br>
                            <img src="<?php if(!empty($result->logo)): ?><?php echo e($result->logo); ?><?php endif; ?>" id="img_thumbnail" class="thumbnail" style="display: <?php if(!empty($result->logo)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                            <input type="hidden" name="logo" id="thumbnail" value="<?php if(!empty($result->logo)): ?><?php echo e($result->logo); ?><?php endif; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-tab-item">
                    <div id="micro_trade_fee" class="layui-form-item <?php echo e($result->is_micro == 1 ? '' : 'hide'); ?>">
                        <label class="layui-form-label">秒合约手续费</label>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="" name="micro_trade_fee" value="<?php echo e($result->micro_trade_fee); ?>" placeholder="百分比">
                        </div>
                        <div class="layui-form-mid layui-word-aux">%</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">下单数量范围</label>
                            <div class="layui-input-inline">
                                <input type="number" class="layui-input" name="micro_min" value="<?php echo e($result->micro_min); ?>" placeholder="最小数量">
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline">
                                <input type="number" class="layui-input" name="micro_max" value="<?php echo e($result->micro_max); ?>" placeholder="最大数量">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">最大持仓笔数</label>
                            <div class="layui-input-inline">
                                <input type="number" class="layui-input" name="micro_holdtrade_max" value="<?php echo e($result->micro_holdtrade_max); ?>" placeholder="最大持仓笔数">
                            </div>
                            <div class="layui-form-mid layui-word-aux">用户最大可持仓笔数,为0不限制</div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">是否购买保险</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="insurancable" value="1" title="打开" <?php if(isset($result->insurancable)): ?> <?php echo e($result->insurancable == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="insurancable" value="0" title="关闭" <?php if(isset($result->insurancable)): ?> <?php echo e($result->insurancable == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">提币数量</label>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="end_time" name="min_number" value="<?php echo e($result->min_number); ?>" placeholder="最小数量">
                        </div>
                        <div class="layui-form-mid">-</div>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="end_time" name="max_number" value="<?php echo e($result->max_number); ?>" placeholder="最大数量">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">提币费率</label>
                        <div class="layui-input-inline">
                            <input type="number" class="layui-input" id="end_time" name="rate" value="<?php echo e($result->rate); ?>" placeholder="百分比">
                        </div>
                        <div class="layui-form-mid"></div>
                    </div>
                </div>

                <div class="layui-tab-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">基于币种</label>
                        <div class="layui-input-inline">
                            <input type="text" id="type" name="type" class="layui-input" value="<?php echo e($result->type??''); ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">新添加币种必须指定正确所属区块链币种</div>
                    </div>
                    <!-- <div class="layui-form-item">
                            <label class="layui-form-label">总账号</label>
                            <div class="layui-input-block">
                            <input type="text" name="total_account"  autocomplete="off" placeholder="运营者的钱包地址" class="layui-input" value="<?php echo e($result->total_account); ?>">
                            </div>
                    </div>
                    <div class="layui-form-item">
                            <label class="layui-form-label">私钥</label>
                            <div class="layui-input-block">
                            <input type="password" name="key"  autocomplete="off" placeholder="运营者的钱包私钥" class="layui-input" value="<?php echo e($result->key); ?>">
                            </div>
                    </div> -->
                    <div class="layui-form-item">
                            <label class="layui-form-label">合约地址</label>
                            <div class="layui-input-block">
                            <input type="text" name="contract_address"  autocomplete="off" placeholder="仅ERC20代币才需要填写" class="layui-input" value="<?php echo e($result->contract_address); ?>">
                            </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">发币位数</label>
                        <div class="layui-input-inline">
                            <input type="text" name="decimal_scale"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->decimal_scale ?? 18); ?>">
                        </div>
                        <div class="layui-form-mid layui-word-aux">请务必保证与区域链上小数位一致</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">链上手续费</label>
                        <div class="layui-input-block">
                            <div class="layui-input-inline">
                                <input type="text" name="chain_fee"  autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($result->chain_fee ?? 0); ?>">
                            </div>
                            <div class="layui-form-mid layui-word-aux">链上归拢、提币的手续费</div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">安全验证码</label>
                        <div class="layui-input-inline">
                            <input type="text" name="verificationcode" placeholder="" autocomplete="off" class="layui-input">
                        </div>
                        <button type="button" class="layui-btn layui-btn-primary" id="get_code">获取验证码</button>
                    </div>
                    <?php if(isset($result->id) && $result->id > 0): ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">总账号地址</label>
                        <div class="layui-input-block">
                            <button id="set_out_address" class="layui-btn layui-btn-warm layui-btn-sm" type="button" data-id="<?php echo e($result->id); ?>">转出地址</button>
                            <button id="set_in_address" class="layui-btn layui-btn-danger layui-btn-sm" type="button" data-id="<?php echo e($result->id); ?>">归拢地址</button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
       
        <input id="currency_id" type="hidden" name="id" value="<?php echo e($result->id); ?>">
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
    layui.use(['upload', 'form', 'laydate', 'element', 'layer'], function () {
        var upload = layui.upload 
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
            ,laydate = layui.laydate
            ,index = parent.layer.getFrameIndex(window.name)
            ,element = layui.element
        var uploadInst = upload.render({
            elem: '#upload_test' //绑定元素
            ,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
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
        var currency_id = $('#currency_id').val();

        //监听提交
        form.on('submit(demo1)', function(data){
            var data = data.field;
            $.ajax({
                url:'<?php echo e(url('admin/currency_add')); ?>'
                ,type:'post'
                ,dataType:'json'
                ,data: data
                ,success: function(res) {
                    layer.msg(res.message, {
                        time: 2000
                        ,end: function () {
                            if(res.type == 'ok') {
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        }
                    });
                    
                }
            });
            return false;
        });
        form.on('checkbox(microtrade)', function (data) {
            if (data.elem.checked) {
                $('#micro_trade_fee').removeClass('hide');
            } else {
                $('#micro_trade_fee').addClass('hide');
            }
        });
       
        //获取验证码
        $('#get_code').click(function () {
            var that_btn = $(this);
            $.ajax({
                url: '/admin/safe/verificationcode'
                ,type: 'GET'
                ,success: function (res) {
                    if (res.type == 'ok') {
                        that_btn.attr('disabled', true);
                        that_btn.toggleClass('layui-btn-disabled');
                    }
                    layer.msg(res.message, {
                        time: 3000
                    });
                }
                ,error: function () {
                    layer.msg('网络错误');
                }
            });
        });
        // 设置转出地址
        $('#set_out_address').click(function () {
            parent.layui.layer.open({
                title: '设置转出地址'
                ,type: 2
                ,content: '/admin/currency/set_out_address/' + currency_id
                ,area: ['490px', '350px']
            });
        });
        // 设置转入地址
        $('#set_in_address').click(function () {
            parent.layui.layer.open({
                title: '设置转入地址'
                ,type: 2
                ,content: '/admin/currency/set_in_address/' + currency_id
                ,area: ['490px', '250px']
            });
        });
        
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>