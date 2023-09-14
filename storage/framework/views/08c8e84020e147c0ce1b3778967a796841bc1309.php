<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <!-- <div class="layui-inline" style="margin-left: 50px;">
                <label >交易账号&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div> -->
            <div class="layui-inline" style="margin-left: 50px;">
                <label >商家名称&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="seller_name" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>出售/求购&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="type" id="type_type">
                        <option value="" class="ww">全部</option>
                        <option value="buy" class="ww">求购</option>
                        <option value="sell" class="ww">出售</option>

                    </select>
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>交易币&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="currency_id" id="currency_id">
                        <option value="0" class="ww">全部</option>
                        <?php $__currentLoopData = $currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value->id); ?>" class="ww"><?php echo e($value->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>



        </form>
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="back">撤回发布</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除发布</a>
        
        <a class="layui-btn layui-btn-xs" lay-event="is_shelves" style="background-color: rgba(0,0,0,0)">
            <input type="checkbox" name="is_shelves" title="打勾下架"  {{d.is_shelves==2 ? 'checked' : '' }}  >
        </a>

        
    </script>

    <script type="text/html" id="type">
        {{d.type=="buy" ? '<span class="layui-badge layui-bg-green">'+'求购'+'</span>' : '' }}
        {{d.type=="sell" ? '<span class="layui-badge layui-bg-red">'+'出售'+'</span>' : '' }}

    </script>
    <script type="text/html" id="is_done">
        {{d.is_done==0 ? '<span class="layui-badge layui-bg-gray">'+'未完成'+'</span>' : '' }}
        {{d.is_done==1 ? '<span class="layui-badge">'+'已完成'+'</span>' : '' }}

    </script>
    <script type="text/html" id="limitation">
        <span class="layui-badge layui-bg-gray">{{d.limitation.min}}--{{d.limitation.max}}</span>

    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '<?php echo e(url('admin/legal/list')); ?>' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'seller_name', title: '商家名称', width:120}
                    ,{field: 'type', title: '出售/求购', width:100, templet: '#type'}
                    ,{field: 'way_name', title: '支付方式', width:100}

                    ,{field: 'price', title: '单价', width:100 }
                    ,{field: 'total_number', title: '数量', width:100}
                    ,{field: 'surplus_number', title: '剩余数量', width:100}
                    ,{field: 'currency_name', title: '交易币', width:80}

                    // ,{field: 'limitation', title: '限额', width:100, templet: '#limitation'}
                    ,{field: 'is_done', title: '是否完成', width:100, templet: '#is_done'}

                    ,{field: 'create_time', title: '创建时间', width:180}

                    ,{title:'操作',minWidth:300,toolbar: '#barDemo'}

                ]]
            });


            table.on('tool(test)', function(obj){
                var data = obj.data;
                console.log(obj);
                if(obj.event === 'del'){
                    layer.confirm('删除发布会删除此发布信息下的所有交易,请谨慎操作', function(index){
                        var loading = layer.load(1, {
                            shade: [0.1,'#fff'] //0.1透明度的白色背景
                        });
                        $.ajax({
                            url:'<?php echo e(url('admin/send/del')); ?>',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.close(loading);
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                }
                else if(obj.event === 'back')
                {
                    layer.confirm('撤回发布会把此发布信息下的所有未完成交易取消', function(index){
                        var loading = layer.load(1, {
                            shade: [0.1,'#fff'] //0.1透明度的白色背景
                        });
                        $.ajax({
                            url:'<?php echo e(url('admin/send/back')); ?>',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.close(loading);
                                layer.msg(res.message);

                                layer.close(index);

                            }
                        });


                    });
                }
                else if(obj.event === 'is_shelves')
                {
                    $.ajax({
                        url:'<?php echo e(url('admin/send/is_shelves')); ?>',
                        type:'post',
                        dataType:'json',
                        data:{is_shelves:data.is_shelves,id:data.id},
                        success:function (res) {
                            layer.msg(res.message);

                        }
                    });








                    
                        
                            
                        
                        
                            
                            
                            
                            
                            
                                
                                

                                

                            
                        
                    
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var seller_name = data.field.seller_name
                    ,type = $('#type_type').val()
                    ,currency_id = $('#currency_id').val()


                table.reload('mobileSearch',{
                    where:{
                        seller_name:seller_name,
                        type:type,
                        currency_id:currency_id,

                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>