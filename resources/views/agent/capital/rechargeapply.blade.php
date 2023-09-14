@extends('agent.layadmin')

@section('title', '充币列表')

@section('page-head')

@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto"
                 lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="status">
                                <option value="0" class="ww">全部</option>
                                <option value="1">申请中</option>
                                <option value="2">已通过</option>
                                <option value="3">已拒绝</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="currency_id">
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
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
                        <label class="layui-form-label">所属代理</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="belong_agent">
                                <option value="">全部</option>
                                @foreach ($son_agents as $son)
                                    <option value="{{$son->username}}">{{$son->username}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit
                                lay-filter="LAY-user-front-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="layui-card-body">
                <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>

            </div>
        </div>
    </div>


    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'充值待确认'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'充值完成'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-black">'+'--'+'</span>' : '' }}

    </script>
    <script type="text/html" id="ophtml">
        @{{d.status==1 ? '
        <button class="layui-btn  layuiadmin-btn-useradmin" type="button" onclick="pass('+d.id+')">通过</button>
        <button class="layui-btn  layuiadmin-btn-useradmin" type="button" onclick="refuse('+d.id+')" class="btn-refuse">
            拒绝
        </button>' : '' }}


    </script>
    <script type="text/html" id="imagetml">
        <img onclick="show('@{{d.image}}')" src="@{{d.image}}" style="width:50px;">
    </script>
@endsection

@section('scripts')

    <script>
        let $;
        layui.use(['index', 'table', 'layer'], function () {
            $ = layui.$
                , admin = layui.admin
                , view = layui.view
                , table = layui.table
                , form = layui.form


            //充币管理
            table.render({
                elem: '#LAY-user-manage'
                , method: 'get'
                , url: '/agent/capital/apply'
                , toolbar: true
                , totalRow: true
                , cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    , {field: 'id', width: 60, title: 'ID', sort: true}
                    , {field: 'currency_name', title: '币种', width: 90}
                    , {field: 'account_number', title: '用户名', width: 120, totalRowText: '小计'}
                    , {field: 'belong_agent_name', title: '所属代理', width: 120}
                    , {field: 'amount', title: '充币数量', width: 150, totalRow: true}
                    , {field: 'remark', title: '备注', width: 200}
                    , {field: 'image', title: '图片', width: 200, templet: '#imagetml'}
                    , {field: 'status', title: '状态', width: 200, templet: '#statustml'}
                    , {field: 'created_at', title: '申请时间', width: 170}
                    , {field: 'id', title: '操作', width: 170, templet: '#ophtml'}
                ]]
                , page: true
                , limit: 30
                , height: 'full-240'
                , text: '对不起，加载出现异常！'

                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    if (res !== 0) {
                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }
                    }
                }
            });

            form.render(null, 'layadmin-userfront-formlist');

            //监听搜索
            form.on('submit(LAY-user-front-search)', function (data) {
                var field = data.field;

                //执行重载
                table.reload('LAY-user-manage', {
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

        function show(url) {
            layer.open({
                title: '转账截图',
                type: 1,
                area: ['640px', '700px'],
                content: `<img src='${url}' style="width:640px;">` //这里content是一个普通的String
            });
        }

        function pass(id) {
            layer.confirm('确认已经充值到账？',function(){
                $.ajax({
                    url: '{{url('agent/recharge/pass')}}',
                    type: 'post',
                    dataType: 'json',
                    data: {id: id},
                    success: function (res) {
                        console.log(res);
                        if (res.code != 'ok') {
                            alert(res.msg);
                            window.location.reload();
                        } else {
                            layer.msg('充值确认成功');
                            window.location.reload();
                        }
                    }
                })
            })

        }

        function refuse(id) {
            layer.confirm('确认驳回吗？', () => {
                $.ajax({
                    url: '{{url('agent/recharge/refuse')}}',
                    type: 'post',
                    dataType: 'json',
                    data: {id: id},
                    success: function (res) {
                        if (res.code != 'ok') {
                            alert(res.msg);
                            window.location.reload();
                        } else {
                            layer.msg('充值驳回成功');
                            window.location.reload();
                        }
                    }
                })
            })

        }
    </script>
@endsection
