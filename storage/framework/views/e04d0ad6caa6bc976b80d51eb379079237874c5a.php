<?php $__env->startSection('page-head'); ?>
    <style>
        [hide] {
            display: none;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div class="layui-form">
        <div class="layui-item">
            <div class="layui-inline" style="margin-left: 10px;">
                <label>用户账号</label>
                <div class="layui-input-inline">
                    <input type="text" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input"
                           value="">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label>真实姓名</label>
                <div class="layui-input-inline">
                    <input type="text" name="name" placeholder="真实姓名" autocomplete="off" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label>风控类型</label>
                <div class="layui-input-inline" style="width: 90px">
                    <select name="risk" lay-verify="required" id="risk">
                        <option value="-2">全部</option>
                        <option value="0">无</option>
                        <option value="-1">亏损</option>
                        <option value="1">盈利</option>
                    </select>
                </div>
                <button class="layui-btn layui-btn-primary" id="btn-set" type="button"
                        style="padding:0px; margin-left: -4px; width: 30px;">
                    <i class="layui-icon layui-icon-set-fill"></i>
                </button>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label>所属分组</label>
                <div class="layui-input-inline" style="width: 90px">
                    <select name="store_id" lay-verify="required" id="store_id">
                        <?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($store['id']); ?>"><?php echo e($store['name']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button class="layui-btn layui-btn-primary" id="btn-setstore" type="button"
                        style="padding:0px; margin-left: -4px; width: 30px;">
                    <i class="layui-icon layui-icon-set-fill"></i>
                </button>
            </div>
            <div class="layui-btn-group">
                <button class="layui-btn layui-btn-primary"
                        onclick="javascrtpt:window.location.href='<?php echo e(url('/admin/user/csv')); ?>'"><i
                            class="layui-icon  layui-icon-export"></i></button>
                <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"><i
                            class="layui-icon layui-icon-search"></i></button>
            </div>
        </div>
    </div>
    <table id="userlist" lay-filter="userlist"></table>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="users_wallet">钱包</a>
        <a class="layui-btn layui-btn-xs" lay-event="users_wallet_sync">同步钱包</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="lock_user">锁定</a>
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete" hide>删除</a>
    </script>
    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" {{
               d.status== 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="isAtelier">
        <input type="checkbox" name="is_atelier" value="{{d.id}}" lay-skin="switch" lay-text="是|否"
               lay-filter="is_atelier" {{ d.is_atelier== 1 ? 'checked' : '' }} disabled>
    </script>
    <script type="text/html" id="status_t">
        <a class="layui-btn layui-btn-xs {{ d.status == 1 ? 'layui-btn-danger' : 'layui-btn-primary' }} ">{{ d.status
            == 1 ? '已锁定' : '正　常' }}</a>
    </script>
    <script>
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element
                , layer = layui.layer
                , table = layui.table
                , $ = layui.$
                , form = layui.form
            var user_table = table.render({
                elem: '#userlist'
                , toolbar: true
                , url: '/admin/user/list'
                , page: true
                , limit: 100
                , limits: [20, 50, 100, 200, 500, 1000]
                , height: 'full-60'
                , cols: [[
                    {field: '', type: 'checkbox'}
                    , {field: 'id', title: 'ID', width: 100}
                    , {field: 'phone', title: '手机号', width: 150}
                    , {field: 'userreal_name', title: '真实姓名', width: 120}
                    , {field: 'account_number', title: '交易账号', width: 150, hide: true}
                    , {field: 'email', title: '邮箱', width: 150, hide: true}
                    , {field: 'card_id', title: '身份证号', width: 180, hide: true}
                    , {field: 'extension_code', title: '邀请码', width: 100}
                    , {field: 'pname', title: '上级用户', width: 100}
                    , {field: 'risk_name', title: '风控类型', width: 90}
                    , {field: 'status', title: '状态', width: 90, templet: "#status_t"}
                    // ,{field:'status', title:'是否锁定', width:90, templet: '#switchTpl'}
                    , {field: 'time', title: '注册时间', width: 200}
                    , {field: 'store_id', title: '所属商家', width: 150,templet:function(obj){
                        return obj.legal_store?obj.legal_store.name:'-';
                        }
                        }
                    , {fixed: 'right', title: '操作', width: 300, align: 'center', toolbar: '#barDemo'}
                ]]
            });

            $('input[name=account]').keypress(function (event) {
                if (event.charCode == 13) {
                    $('#mobile_search').click();
                }
            });

            /*$('#add_user').click(function(){layer_show('添加会员', '/admin/user/add');});*/

            form.on('submit(mobile_search)', function (obj) {
                user_table.reload({
                    where: obj.field
                });
                return false;
            });

            //监听锁定操作
            form.on('switch(status)', function (obj) {
                var id = this.value;
                $.ajax({
                    url: '<?php echo e(url('admin/user/lock')); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {id: id},
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
            });

            $('#btn-set').click(function () {
                var checkStatus = table.checkStatus('userlist');
                var risk = $('#risk').val();
                var ids = [];
                try {
                    if (checkStatus.data.length <= 0) {
                        throw '请先选择用户';
                    }
                    if (risk <= -2) {
                        throw '请选择风控类型';
                    }
                    checkStatus.data.forEach(function (item, index, arr) {
                        ids.push(item.id);
                    });
                    $.ajax({
                        url: '/admin/user/batch_risk'
                        , type: 'POST'
                        , data: {risk: risk, ids: ids}
                        , success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
                                    if (res.type == 'ok') {
                                        user_table.reload();
                                    }
                                }
                            });
                        }
                        , error: function (res) {
                            layer.msg('网络错误');
                        }
                    })

                } catch (error) {
                    layer.msg(error);
                }
            });

            $('#btn-setstore').click(function () {
                var checkStatus = table.checkStatus('userlist');
                var store_id = $('#store_id').val();
                var ids = [];
                try {
                    if (checkStatus.data.length <= 0) {
                        throw '请先选择用户';
                    }
                    // if (store_id <= -2) {
                    //     throw '请选择风控类型';
                    // }
                    checkStatus.data.forEach(function (item, index, arr) {
                        ids.push(item.id);
                    });
                    $.ajax({
                        url: '/admin/user/setStore'
                        , type: 'POST'
                        , data: {store_id: store_id, ids: ids}
                        , success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
                                    if (res.type == 'ok') {
                                        user_table.reload();
                                    }
                                }
                            });
                        }
                        , error: function (res) {
                            layer.msg('网络错误');
                        }
                    })

                } catch (error) {
                    layer.msg(error);
                }
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
                            url: "admin/user/del",
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
                } else if (layEvent === 'edit') { //编辑
                    layer_show('编辑会员', '/admin/user/edit?id=' + data.id);
                } else if (layEvent === 'users_wallet') {
                    var index = layer.open({
                        title: '钱包管理'
                        , type: 2
                        , content: '/admin/user/users_wallet?id=' + data.id
                        , maxmin: true
                    });
                    layer.full(index);
                } else if (layEvent === 'users_wallet_sync') {
                    layer.confirm('确定创建缺失的钱包吗？', res => {
                        $.getJSON('/admin/user/users_wallet_sync?id=' + data.id, function (res) {
                            layer.msg(res.message);
                        });
                    })
                } else if (layEvent == 'candy_change') {
                    var index = layer.open({
                        title: '通证调节'
                        , type: 2
                        , content: '/admin/user/candy_conf/' + data.id
                        , maxmin: true
                    });
                    layer.full(index);
                } else if (layEvent === 'lock_user') {
                    var index = layer.open({
                        title: '用户锁定'
                        , type: 2
                        , content: '/admin/user/lock?id=' + data.id
                        , maxmin: true
                        , area: ["380px", "430px"],
                    });
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>