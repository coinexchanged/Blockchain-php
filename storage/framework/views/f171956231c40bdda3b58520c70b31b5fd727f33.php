<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加机器人','<?php echo e(url('admin/robot/add')); ?>')">添加机器人</button>


    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="legal">
        {{d.is_legal==1 ? '<span >'+'是'+'</span>' : '' }}
        {{d.is_legal==0 ? '<span >'+'否'+'</span>' : '' }}

    </script>
    <script type="text/html" id="lever">
        {{d.is_lever==1 ? '<span >'+'是'+'</span>' : '' }}
        {{d.is_lever==0 ? '<span >'+'否'+'</span>' : '' }}

    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">

        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
        <a class="layui-btn layui-btn-xs" lay-event="open">拐点</a>
        
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
                ,url: '<?php echo e(url('admin/robot/list_data')); ?>' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {title:'操作',Width:200,toolbar: '#barDemo'},
                    {field: 'id', title: 'ID', Width:50, sort: true}
                    ,{field: 'huobi_currency', title: '基于币种', Width:60}
                    ,{field: 'buy_user_info', title: '变化倍数', minWidth:60}
                    ,{field: 'currency_info', title: '交易币', minWidth:50}
                    ,{field: 'legal_info', title: '法币', minWidth:50}
                    ,{field: 'number_min', title: '最小数量', minWidth:40}
                    ,{field: 'number_max', title: '最大数量', minWidth:40}
                    ,{field: 'float_number_down', title: '浮动下限', minWidth:40}
                    ,{field: 'float_number_up', title: '浮动上限', minWidth:40}
                    ,{field: 'second', title: '频率(秒)', minWidth:60}
                    ,{field:'is_start', title:'是否开启', minWidth:100, templet: '#switchTpl', unresize: true}
                ]]
            });

            $(document).off('mousedown','.layui-table-grid-down').on('mousedown','.layui-table-grid-down',function(event){
                table._tableTrCurr = $(this).closest('td');
            });

            $(document).off('click','.layui-table-tips-main [lay-event]').on('click','.layui-table-tips-main [lay-event]',function(event){
                var elem = $(this);
                var tableTrCurr = table._tableTrCurr;
                if(!tableTrCurr){
                    return;
                }
                var layerIndex = elem.closest('.layui-table-tips').attr('times');
                // 关闭当前这个显示更多的tip
                layer.close(layerIndex);
                table._tableTrCurr.find('[lay-event="' + elem.attr('lay-event') + '"]').first().click();
            });

            
            //监听热卖操作
            form.on('switch(sexDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'<?php echo e(url('admin/robot/start')); ?>',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });

            table.on('tool(test)', function(obj) {
                var data = obj.data;
                if (obj.event === 'del') {
                    layer.confirm('真的删除行么', function (index) {
                        $.ajax({
                            url: '<?php echo e(url('admin/robot/delete')); ?>',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if (obj.event === 'edit') {
                    layer_show('编辑机器人', '<?php echo e(url('admin/robot/add')); ?>?id=' + data.id);
                } else if (obj.event == 'execute') {
                    layer.confirm('确定执行上币脚本？', function (index) {
                        $.ajax({
                            url: '<?php echo e(url('admin/currency_execute')); ?>',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                layer.msg(res.message);
                            }
                        });
                    });
                } else if (obj.event == 'open')
                {
                    layer_show('预设的行情拐点', '<?php echo e(url('admin/robot/sche')); ?>?rid=' + data.id);
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>