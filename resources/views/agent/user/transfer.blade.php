@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">开始日期</label>
                    <div class="layui-input-block">
                        <input type="text" name="start" id="datestart" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">结束日期</label>
                    <div class="layui-input-block">
                        <input type="text" name="end" id="dateend" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">ID</label>
                    <div class="layui-input-block">
                        <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">用户名</label>
                    <div class="layui-input-block">
                        <input type="text" name="account_number" placeholder="请输入" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="san-user-search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                    <!--<button class="layui-btn layuiadmin-btn-useradmin"  onclick="javascript:window.location.href='order/users_excel'">导出Excel</button>-->
                </div>
            </div>
        </div>



        <div class="layui-card-body">
            <div style="padding-bottom: 10px;">
                <!--<button class="layui-btn layuiadmin-btn-useradmin" data-type="batchdel">删除</button>-->
                <!--<button class="layui-btn layuiadmin-btn-useradmin" data-type="add">用户</button>-->
            </div>

            <table id="san-user-manage" lay-filter="san-user-manage"></table>
            

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/html" id="lockTpl">
  @{{#  if(d.type == 1){ }}
  <span class="layui-badge layui-bg-red">入金</span>
  @{{#  } else { }}
  <span class="layui-badge layui-bg-blue">出金</span>
  @{{#  } }}
</script>
<script>
    layui.use(['index','table', 'layer' , 'laydate','form'], function () {
        var $ = layui.$
            , admin = layui.admin
            , view = layui.view
            , table = layui.table
            , layer = layui.layer
            , laydate = layui.laydate
            , form = layui.form;


        //日期
        laydate.render({
            elem: '#datestart'
        });
        laydate.render({
            elem: '#dateend'
        });



        var router = layui.router();
        parent_id = router.search.parent_id || 0;

        //console.log(parent_id);

        load(parent_id);

        function load(parent_id) {
            parent_id = parent_id || 0;

            table.render({
                elem: '#san-user-manage'
                , url: '/agent/user/huazhuan_lists?parent_id=' + parent_id //模拟接口
                , cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'id', width: 60, title: 'ID', sort: true}
                    , {field: 'account_number', title: '用户名', minWidth: 150}
                    , {field: 'number', title: '划转数量', minWidth: 150}
                    , {field: 'type', title: '划转类型', minWidth: 150, templet: '#lockTpl'}
                    , {field: 'add_time', title: '划转时间', sort: true, width: 170}
//                    , {title: '操作', width: 100, align: 'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
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
                   // ,parent_id : parent_id
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
        }




        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(san-user-search)', function (data) {
            var field = data.field;
            //console.log(field);


            //执行重载
            table.reload('san-user-manage', {
                where: field
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }
                    if (res.code === 1) {
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            });
        });
    });

</script>
@endsection