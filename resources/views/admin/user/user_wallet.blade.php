@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <!-- <div class="layui-form"> -->
        <table id="userlist" lay-filter="userlist">
            <input type="hidden" name="user_id" value="{{$user_id}}">
        </table>

        <script type="text/html" id="barDemo">
            <!-- <a class="layui-btn layui-btn-xs" lay-event="edit">提币地址管理</a> -->
            <a class="layui-btn layui-btn-xs" lay-event="conf">调节账户</a>
            <!-- @{{d.currency_name=='BTC' || d.currency_name=='USDT'? '<a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="balance">余额归拢</a>' : '' }}
            @{{d.currency_name=='BTC' ? '<a class="layui-btn layui-btn-xs layui-btn-primary" lay-event="btc">打入BTC</a>' : '' }} -->
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>
        <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }} >
      </script>

@endsection

        @section('scripts')
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
                                elem: '#userlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    {field: 'id', title: 'ID', width: 150}
                                    ,{field:'currency_name',title: '币种',width: 150}
                                    ,{field:'address',title:'充币地址', width:200}

                                    ,{field:'micro_balance',title:'秒合约余额', width:150}
                                    ,{field:'lock_micro_balance',title:'秒合约锁定余额', width:150}

                                    ,{field:'lever_balance',title:'杠杆余额', width:150}
                                    ,{field:'lock_lever_balance',title:'杠杆锁定余额', width:150}

                                    ,{field:'legal_balance',title:'法币余额', width:150}
                                    ,{field:'lock_legal_balance',title:'法币锁定余额', width:150}

                                    ,{field:'change_balance',title:'闪兑余额', width:150}
                                    ,{field:'lock_change_balance',title:'闪兑锁定余额', width:150}


                                    ,{field:'old_balance',title:'链上余额', width:150}
                                    ,{field:'create_time',title:'时间', width:200}
                                    ,{fixed: 'right', title: '操作', width: 280, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        var user_id = $("input[name='user_id']").val()
                        tbRend("{{url('/admin/user/walletList')}}?user_id=" + user_id);
                        
                        //监听锁定操作
                        form.on('switch(sexDemo)', function(obj){
                            var id = this.value;
                            
                            $.ajax({
                                url:'{{url('admin/user/wallet_lock')}}',
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
                                        url: '{{url('admin/user/delw')}}',
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
                            } else if (layEvent === 'conf') { 
                                var index = layer.open({
                                    title: '调节账户'
                                    , type: 2
                                    , content: '{{url('/admin/user/conf')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            } else if (layEvent === 'edit') { //编辑
                                var index = layer.open({
                                    title: '管理提币地址'
                                    , type: 2
                                    , content: '{{url('/admin/user/address')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            } else if (layEvent === 'balance') { //余额归拢
                                $.ajax({
                                    url: '{{url('admin/user/balance')}}',
                                    type: 'post',
                                    dataType: 'json',
                                    data: {id: data.id},
                                    success: function (res) {
                                        if (res.type == 'ok') {
                                            layer.alert(res.message);
                                        } else {
                                            layer.close(index);
                                            layer.alert(res.message);
                                        }
                                    }
                                });
                            } else if (layEvent === 'btc') { //打入btc
                                $.ajax({
                                    url: '{{url('admin/send/btc')}}',
                                    type: 'post',
                                    dataType: 'json',
                                    data: {id: data.id},
                                    success: function (res) {
                                        if (res.type == 'ok') {
                                            layer.alert(res.message);
                                        } else {
                                            layer.close(index);
                                            layer.alert(res.message);
                                        }
                                    }
                                });
                            }
                        });
                    });
                }
            </script>

@endsection