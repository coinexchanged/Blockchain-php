@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <form class="layui-form layui-form-pane layui-inline" action="">
            <div class="layui-inline" style="margin-left: 10px;">
                <label >ID&nbsp;&nbsp;</label>
                <div class="layui-input-inline" style="width:90px;">
                    <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label >用户名&nbsp;&nbsp;</label>
                <div class="layui-input-inline"  style="width:130px;">
                    <input type="text" name="phone" placeholder="请输入" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label>订单状态&nbsp;&nbsp;</label>
                <div class="layui-input-inline" style="width:100px;">
                    <select name="status" id="status">
                        <option value="10">不限</option>
                        <option value="0">挂单中</option>
                        <option value="1">交易中</option>
                        <option value="2">平仓中</option>
                        <option value="3">已平仓</option>
                        <option value="4">已撤单</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label>交易类型&nbsp;&nbsp;</label>
                <div class="layui-input-inline" style="width:100px;">
                    <select name="type" id="type">
                        <option value="0">不限</option>
                        <option value="1">买入</option>
                        <option value="2">卖出</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
                <button class="layui-btn layui-btn-normal" onclick="javascrtpt:window.location.href='{{url('/admin/Leverdeals/csv')}}'">导出记录</button>
                <button class="layui-btn layui-btn-normal" id="insertNeedle">插针</button>
            </div>
        </form>
    </div>

    </div>

    <table id="userlist" lay-filter="userlist"></table>


    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="switchTpl2">
        <input type="checkbox" name="blacklist" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="blacklist" @{{ d.is_blacklist == 1 ? 'checked' : '' }}>
    </script>

    <script type="text/html" id="lockTpl">


        @{{#if(d.type == 1) { }}
        买入
        @{{#} else if(d.type == 2) { }}
        卖出
        @{{#}}}

    </script>
    <script type="text/html" id="addsonTpl">

        @{{#if(d.status == 0) { }}
        挂单中
        @{{#} else if(d.status == 1) { }}
        交易中
        @{{#} else if(d.status == 2) { }}
        平仓中
        @{{#} else if(d.status == 3) { }}
        已平仓
        @{{#} else if(d.status == 4) { }}
        已撤单

        @{{#}}}

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

                /*$('#add_user').click(function(){layer_show('添加会员', '/admin/user/add');});*/

                form.on('submit(mobile_search)',function(obj){
                    var id =  $("input[name='id']").val();
                    var phone =  $("input[name='phone']").val();
                    var status =  $("select[name='status']").val();
                    var type =  $("select[name='type']").val();

                    tbRend("{{url('/admin/Leverdeals/list')}}?id="+id+"&phone="+phone+"&type="+type+"&status="+status);
                    return false;
                });
                function tbRend(url) {
                    table.render({
                        elem: '#userlist'
                        , url: url
                        , page: true
                        ,height:'full-250'
                        ,toolbar:true
                        ,limit: 20
                        , cols: [[
                            { field: 'id', title: 'ID', width: 100}
                            ,{field: 'account_number', title: '用户名', minWidth: 150 , event : "getsons",style:"color: #fff;background-color: #5FB878;"}
                            ,{field: 'trade_fee', title: '交易手续费', width: 120}
                            ,{field: 'overnight_money', title: '隔夜费金额', width: 100}
                            ,{field: 'type', title: '交易类型', width: 90, templet: '#lockTpl'}
                            ,{field: 'symbol', title: '交易对', width: 100}
                            ,{field: 'status', title: '当前状态', sort: true, width: 170, templet: '#addsonTpl'}
                            ,{field: 'origin_price', title: '原始价格', width: 120}
                            ,{field: 'price', title: '开仓价格', width: 120}
                            ,{field: 'target_profit_price', title: '止盈价格', width: 120}
                            ,{field: 'stop_loss_price', title: '止损价格', width: 120}
                            ,{field: 'update_price', title: '当前价格', width: 120}
                            ,{field: 'share', title: '手数', sort: true, width: 90}
                            ,{field: 'multiple', title: '倍数', sort: true, width: 90}
                            ,{field: 'origin_caution_money', title: '初始保证金', width: 120}
                            ,{field: 'caution_money', title: '当前可用保证金', sort: true, width: 170}
                            ,{field: 'profits', title: '动态盈亏', width: 120,hide:false}
                            ,{field: 'time', title: '创建时间', width: 170}
                            ,{field: 'update_time', title: '价格刷新时间', sort: true, width: 170,hide:true}
                            ,{field: 'handle_time', title: '平仓时间', sort: true, width: 170}
                            ,{field: 'complete_time', title: '完成时间', width: 170}
                        ]]
                    });
                }
                tbRend("{{url('/admin/Leverdeals/list')}}");



                //监听加入黑名单
                // form.on('switch(blacklist)', function(obj){
                //     var id = this.value;

                //     $.ajax({
                //         url:'{{url('admin/user/blacklist')}}',
                //         type:'post',
                //         dataType:'json',
                //         data:{id:id},
                //         success:function (res) {
                //             layer.msg(res.message);

                //         }
                //     });
                // });

                //监听工具条
                table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data;
                    var layEvent = obj.event;
                    var tr = obj.tr;
                    if (layEvent === 'delete') { //删除
                        layer.confirm('真的要删除吗？', function (index) {
                            //向服务端发送删除指令
                            $.ajax({
                                url: "{{url('admin/user/del')}}",
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
                    }else if (layEvent === 'edit'){ //编辑
                        layer_show('编辑会员','{{url('admin/user/edit')}}?id='+data.id);
                    }else if (layEvent === 'users_wallet') {
                        var index = layer.open({
                            title: '账户管理'
                            , type: 2
                            , content: '{{url('/admin/user/users_wallet')}}?id=' + data.id
                            , maxmin: true
                        });
                        layer.full(index);
                    }
                });

                $('#insertNeedle').click(()=>{
                    var index = layer.open({
                        title: '插针管理'
                        , type: 2
                        , content: '{{url('/admin/needle/all_needle')}}'
                        , maxmin: true
                    });
                    layer.full(index);
                    return false;

                });

                $('#ajax_jie').click(function () {
                    $.ajax({
                        type: "POST",
                        url: "/agent/dojie",
                        data: {all:1},
                        dataType: "json",
                        success: function(data){
                            if (data.code == 0){
                                layer.msg('结算完成，详情请查看代理商后台的结算列表', {time: 5000, icon:6});
                            } else{
                                layer.msg('结算出现异常，请重新尝试', {time: 5000, icon:5});
                            }
                        }
                    });
                });





            });
        }
    </script>

@endsection
