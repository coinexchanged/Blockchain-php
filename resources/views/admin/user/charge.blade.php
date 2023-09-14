@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i
                                class="layui-icon">&#xe615;</i></button>
                </div>
            </div>


        </form>

    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否"
               lay-filter="sexDemo" @{{ d.is_recommend== 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">

        <a class="layui-btn layui-btn-xs" lay-event="show">查看</a>

    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'充值待确认'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'充值完成'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-black">'+'--'+'</span>' : '' }}

    </script>
    <script type="text/html" id="ophtml">
        @{{d.status==1 ? '
        <button type="button" onclick="pass('+d.id+')">通过</button>
        <button type="button" onclick="refuse('+d.id+')" data-id='+d.id+' class="btn-refuse">拒绝</button>' : '' }}


    </script>
    <script type="text/html" id="imagetml">
        <img onclick="show('@{{d.image}}')" src="@{{d.image}}" style="width:50px;">
    </script>

@endsection

@section('scripts')
    <script>

        layui.use(['table', 'form', 'layer'], function () {
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                , url: "{{url('admin/user/charge_list')}}" //数据接口
                , page: true //开启分页
                , id: 'mobileSearch'
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 80, sort: true}
                    , {field: 'account_number', title: '用户名', width: 100}
                    , {field: 'name', title: '虚拟币', width: 80}
                    , {field: 'user_account', title: '支付账号', minWidth: 110}
                    , {field: 'image', title: '转账截图', minWidth: 110, templet: '#imagetml'}
                    // ,{field: 'address', title: '提币地址', minWidth:100}
                    , {field: 'amount', title: '数量', minWidth: 80}
                    // ,{field: 'hes_account', title: '承兑商交易账号', minWidth:180}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    , {field: 'status', title: '交易状态', minWidth: 100, templet: '#statustml'}
                    , {field: 'created_at', title: '提币时间', minWidth: 180}
                    , {field: 'to_address', title: '充值账号', minWidth: 180}
                    , {field: 'remark', title: '备注', minWidth: 180},
                    , {field: 'status', title: '操作', minWidth: 100, templet: '#ophtml'}

                ]]
            });
            //监听热卖操作
            // form.on('switch(sexDemo)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'{{url('admin/product_hot')}}',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.msg);
            //             }
            //         }
            //     });
            // });
        })

        function show(url) {
            layer.open({
                title: '转账截图',
                type: 1,
                area: ['640px', '700px'],
                content: `<img src='${url}' style="width:640px;">` //这里content是一个普通的String
            });
        }

        function pass(id) {
            $.ajax({
                url: '{{url('admin/user/pass_req')}}',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    console.log(res);
                    if (res.type != 'ok') {
                        alert(res.message);
                        window.location.reload();
                    } else {
                        layer.msg('充值确认成功');
                        window.location.reload();
                    }
                }
            })
        }

        function refuse(id) {
            $.ajax({
                url: '{{url('admin/user/refuse_req')}}',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (res) {
                    if (res.type != 'ok') {
                        alert(res.message);
                        window.location.reload();
                    } else {
                        layer.msg('充值驳回成功');
                        window.location.reload();
                    }
                }
            })
        }

        //监听提交

    </script>

@endsection
