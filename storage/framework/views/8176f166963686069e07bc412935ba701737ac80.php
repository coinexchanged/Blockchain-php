<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    <div class="layui-form">
        <table id="ltclist" lay-filter="ltclist"></table>

        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="edit">修改</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>

<?php $__env->stopSection(); ?>

        <?php $__env->startSection('scripts'); ?>
            <script>
                window.onload = function() {
                    document.onkeydown=function(event){
                        var e = event || window.event || arguments.callee.caller.arguments[0];
                        if(e && e.keyCode==13){ // enter 键
                            $('#mobile_search').click();
                        }
                    };
                    layui.use(['element', 'form', 'layer', 'table'], function () {
                        var element = layui.element;
                        var layer = layui.layer;
                        var table = layui.table;
                        var $ = layui.$;
                        var form = layui.form;
                        function tbRend(url) {
                            table.render({
                                elem: '#ltclist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID', minWidth: 50}
                                    , {field:'user_id',title: '账号ID',minWidth: 150}
                                    , {field:'currency_name',title:'期货名称', minWidth:150}
                                    , {field:'days',title:'产品期限', minWidth:150, templet:function(obj){
                                            return obj.days + "(天)";
                                        }
                                    }
                                    , {field:'money',title:'购买金额', minWidth:150}
                                    , {field:'interest',title:'最终收益', minWidth:200}
                                    , {field:'time',title:'下单时间', minWidth:150, templet:function(obj){
                                            return layui.util.toDateString(obj.time, 'yyyy-MM-dd');
                                        }
                                    }
                                    , {field:'totime',title:'结算时间', minWidth:150, templet:function(obj){
                                            return layui.util.toDateString(obj.totime, 'yyyy-MM-dd');
                                        }
                                    }
//                                    , {fixed: 'right', title: '操作', minWidth: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("<?php echo e(url('/admin/ltc/buyList')); ?>");
                        //监听工具条
                        table.on('tool(ltclist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;
                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: '<?php echo e(url('admin/ltc/del')); ?>',
                                        type: 'post',
                                        dataType: 'json',
                                        data: {id: data.id},
                                        success: function (res) {
                                            if (res.type == 'ok') {
                                                layer.alert(res.message);
                                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                                layer.close(index);

                                            } else {
                                                layer.close(index);
                                                layer.alert(res.message);
                                            }
                                        }
                                    });
                                });
                            } else if (layEvent === 'edit') { //编辑
                                var index = layer.open({
                                    title: '修改订单'
                                    , type: 2
                                    , content: '<?php echo e(url('/admin/ltc/edit')); ?>?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            }
                        });
                    });
                }
            </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>