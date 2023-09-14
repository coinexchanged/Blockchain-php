<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">代理商用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">是否锁定</label>
                        <div class="layui-input-block">
                            <select name="is_lock">
                                <option value="2">不限</option>
                                <option value="1">锁定</option>
                                <option value="0">未锁定</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">是否拉新</label>
                        <div class="layui-input-block">
                            <select name="is_addson">
                                <option value="2">不限</option>
                                <option value="1">允许拉新</option>
                                <option value="0">禁止拉新</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="layui-card-body">
                <div style="padding-bottom: 10px;">
                    <!--<button class="layui-btn layuiadmin-btn-useradmin" data-type="batchdel">删除</button>-->
                    <button class="layui-btn layuiadmin-btn-useradmin" data-type="add">添加下级代理商</button>
                </div>

                <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>

                <script type="text/html" id="table-useradmin-webuser">
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</a>
                    <? if($um['level']=='0'):?>
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="editaddress"><i class="layui-icon layui-icon-senior"></i>充值地址</a>
                    <? endif;?>
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="addsonagent"><i class="layui-icon layui-icon-add-1"></i>添加其下级代理</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="getsons"><i class="layui-icon layui-icon-friends"></i>查看其下级代理</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="this-sons"><i class="layui-icon layui-icon-group"></i>查看其所有会员</a>
                </script>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="lockTpl">
        {{#  if(d.is_lock == 1){ }}
        <i class="layui-icon layui-icon-ok" style="font-size: 21px; color: green;" lay-event="lock"></i>
        {{#  } else { }}
        <i class="layui-icon layui-icon-close" style="font-size: 21px; color: red;" lay-event="lock"></i>
        {{#  } }}
    </script>
    <script type="text/html" id="addsonTpl">
        {{# if(d.is_addson == 1){ }}
        <i class="layui-icon layui-icon-ok" style="font-size: 21px; color: green;" lay-event="addson"></i>
        {{#  } else { }}
        <i class="layui-icon layui-icon-close" style="font-size: 21px; color: red;" lay-event="addson"></i>
        {{#  } }}
    </script>

    <script>
        layui.use(['index','salesmen','table' ,'layer'], function(){
            // console.log(layui.setter.base)
            var $ = layui.$
                ,admin = layui.admin
                ,view = layui.view
                ,table = layui.table
                ,form = layui.form;

            form.render(null, 'layadmin-userfront-formlist');

            //监听搜索
            form.on('submit(LAY-user-front-search)', function(data){
                var field = data.field;
                //执行重载
                table.reload('LAY-user-manage', {
                    where: field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                    ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }

                        if (res.code === 1){
                            layer.msg(res.msg ,{icon : 5});
                        }
                    }
                });
            });

            //事件
            var active = {
                batchdel: function(){
                    var checkStatus = table.checkStatus('LAY-user-manage')
                        ,checkData = checkStatus.data; //得到选中的数据

                    if(checkData.length === 0){
                        return layer.msg('请选择数据');
                    }

                    layer.prompt({
                        formType: 1
                        ,title: '敏感操作，请验证口令'
                    }, function(value, index){
                        layer.close(index);

                        layer.confirm('确定删除吗？', function(index) {

                            //执行 Ajax 后重载
                            /*
                             admin.req({
                             url: 'xxx'
                             //,……
                             });
                             */
                            table.reload('LAY-user-manage');
                            layer.msg('已删除');
                        });
                    });
                }
                ,add: function(){
                    layer.prompt({title: '请输入下级代理商帐号', formType: 0, btn :['查询该用户' , '取消']}, function(value, index){
                        layer.close(index);
                        if (value.length == 0) {
                            layer.msg('用户名不能位空', {icon: 5 });
                        }else{
                            admin.req({
                                type : "POST",
                                url : '/agent/searchuser',
                                dataType : "json",
                                data : {username : value},
                                done : function(result) { //返回数据根据结果进行相应的处理
                                    layer.show('添加代理商', '/agent/salesmen/add', result.data);
                                }
                            });
                        }
                    });
                }
            };

            $('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        });
    </script>
    
<?php $__env->stopSection(); ?>

<div id="this_all_sons">
    <table id="LAY-user-sons" lay-filter="LAY-user-sons"></table>
</div>
<?php echo $__env->make('agent.layadmin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>