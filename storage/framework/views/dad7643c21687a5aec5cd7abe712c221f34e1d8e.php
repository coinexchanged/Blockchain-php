<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    <div class="layui-inline">
        <label class="layui-form-label">用户帐号</label>
        <div class="layui-input-inline">
            <input type="text" name="account" placeholder="用户手机号或邮箱" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>审核状态</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="review_status_s" id="review_status_s" class="layui-input">
                    <option value="0">所有</option>
                    <option value="1">未审核</option>
                    <option value="2">已审核</option>
                </select>
            </div>
        </div>
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>
    
        <table id="userlist" lay-filter="userlist"></table>

        <script type="text/html" id="barDemo">
            
           
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="detail">查看</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>
      
        <script type="text/html" id="switchTpl">
        <input type="checkbox" name="review_status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.review_status == 2 ? 'checked' : '' }} >
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


                        form.on('submit(mobile_search)',function(obj){
                            var account =  $("input[name='account']").val();
                            var review_status_s =  $("select[name='review_status_s']").val();

                            tbRend("<?php echo e(url('/admin/user/real_list')); ?>?account="+account+"&review_status_s="+review_status_s);
                            return false;
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#userlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID', width: 100}
                                    , {field:'account',title: '用户账号',width: 150}
                                    , {field:'name',title: '真实姓名',width: 150}
                                    
                                    ,{field:'review_status', title:'是否审核', width:150, templet: '#switchTpl'}
                                    , {field:'create_time',title:'申请时间', width:200} 
                                    , {fixed: 'right', title: '操作', width: 280, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("<?php echo e(url('/admin/user/real_list')); ?>");

                        //监听审核操作
                        form.on('switch(sexDemo)', function(obj){
                            var id = this.value;
                            
                            $.ajax({
                                url:'<?php echo e(url('admin/user/real_auth')); ?>',
                                type:'post',
                                dataType:'json',
                                data:{id:id},
                                success:function (res) {
                                    layer.msg(res.message);
                                   
                                }
                            });
                        });

                        //监听工具条
                        table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;
                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: "<?php echo e(url('admin/user/real_del')); ?>",
                                        type: 'post',
                                        dataType: 'json',
                                        data: {id: data.id},
                                        success: function (res) {
                                            if (res.type == 'ok') {
                                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                                layer.close(index);
                                            } else {
                                                layer.close(index);
                                                layer.alert(res.message);
                                            }
                                        }
                                    });
                                });
                            }else if (layEvent === 'detail'){ //详情
                                layer_show('认证详情','<?php echo e(url('admin/user/real_info')); ?>?id='+data.id);
                            }
                        });
                    });
                }
            </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>