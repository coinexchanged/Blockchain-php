@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
   
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
      

        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label >交易帐号&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>交易类型&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="type" id="type">
                        <option value="" class="ww">全部</option>
                        <option value="1" class="ww">法币交易</option>
                        <option value="2" class="ww">币币交易</option>
                       
                    </select>
                </div>
            </div>
             <div class="layui-inline" style="margin-left: 50px;">
                <label>交易状态&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="status" id="status">
                        <option value="" class="ww">全部</option>
                        <option value="1" class="ww">正在交易</option>
                        <option value="2" class="ww">交易完成</option>
                        <option value="3" class="ww">交易取消</option>
                       
                    </select>
                </div>
            </div>

            <div class="layui-inline" style="margin-left: 50px;">
                <label>交易币&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="currency" id="currency">
                         <option value="" class="ww">全部</option>
                        @foreach ($currency as $value)
                        <option value="{{$value->id}}" class="ww">{{$value->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>



        </form>
    </div>

    
        <table id="transactionlist" lay-filter="transactionlist"></table>

        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="conf">调节账户</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
        </script>
        <script type="text/html" id="typetml">
        @{{d.type==1 ? '<span class="layui-badge layui-bg-green">'+'法币交易'+'</span>' : '' }}
        @{{d.type==2 ? '<span class="layui-badge layui-bg-red">'+'币币交易'+'</span>' : '' }}

        </script>
        <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'正在交易'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'交易完成'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-black">'+'交易取消'+'</span>' : '' }}

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
                                elem: '#transactionlist'
                                ,url: url
                                ,page: true
                                ,limit: 20
                                ,cols: [[
                                    {field: 'id', title: 'ID', width: 100}
                                    ,{field:'from_account',title: '转出用户',width: 150}
                                    ,{field:'to_account',title:'转入用户', width:150}
                                    ,{field:'price',title:'单价', width:100}
                                    ,{field:'number',title:'数量', width:100}
                                    ,{field:'total',title:'交易金额', width:100}
                                    ,{field:'remarks',title:'备注', width:200}
                                    ,{field:'time',title:'时间', width:200}
                                    ,{field: 'currency_name', title: '交易币', width:80}
                                    ,{field: 'type', title: '交易类型', width:100, templet: '#typetml'}
                                    ,{field: 'status', title: '交易状态', width:100, templet: '#statustml'}
//                                    , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("{{url('/admin/transaction/list')}}");
                        //监听工具条
                        table.on('tool(transactionlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;
                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: '{{url('')}}',
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
                            } else if (layEvent === 'conf') { //编辑
                                var index = layer.open({
                                    title: '账户调节'
                                    , type: 2
                                    , content: '{{url('/admin/user/conf')}}?id=' + data.id
                                    , maxmin: true
                                });
                                layer.full(index);
                            }
                        });

                        //监听提交
                        form.on('submit(mobile_search)', function(data){
                            var account_number = data.field.account_number
                                ,type = $('#type').val()
                                ,currency = $('#currency').val()
                                ,status = $('#status').val()

                            table.reload('transactionlist',{
                                where:{
                                    account_number:account_number,
                                    type:type,
                                    currency:currency,
                                    status:status,

                                },
                                page: {curr: 1}         //重新从第一页开始
                            });
                            return false;
                        });
                    });
                }
            </script>

@endsection