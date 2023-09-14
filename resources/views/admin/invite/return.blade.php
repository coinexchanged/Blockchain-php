@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div class="layui-inline btn-group layui-btn-group">
           <button class="layui-btn layui-btn-primary cateManage" id="share">邀请分享设置</button>
           <button class="layui-btn layui-btn-primary" id="childs">会员推荐关系图</button>
    </div>
   <div class="layui-inline">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline">
            <input type="text" name="account" placeholder="请输入用户手机号或邮箱" autocomplete="off" class="layui-input" value="">
        </div>
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>
    <div class="layui-form">
        <table id="accountlist" lay-filter="accountlist"></table>
        <script type="text/html" id="barDemo">
            <a class="layui-btn layui-btn-xs" lay-event="viewDetail">删除</a>
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

                        $('#share').click(function(){layer_show('邀请分享设置', '/admin/invite/share');});
                        $('#childs').click(function(){layer_show('会员关系图', '/admin/invite/childs');});
                        $('#bg').click(function(){layer_show('邀请背景图管理', '/admin/invite/bgpic');});

                        form.on('submit(mobile_search)',function(obj){
                            var account =  $("input[name='account']").val()
                            tbRend("{{url('/admin/invite/return_list')}}?account="+account);
                            return false;
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#accountlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    { field: 'id', title: 'ID',  minWidth: 100}
                                    /*, {field:'account',title: '用户账号',minWidth: 150}*/
                                    , {field:'account_number',title: '用户交易号',minWidth: 150}
                                    , {field:'value',title:'返佣值', minWidth:100}
                                    , {field:'info',title:'记录备注', minWidth:300}
//                                    , {field:'type',title:'类型', width:100}
                                    , {field:'created_time',title:'创建时间', minWidth:300}
                                  ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                            tbRend("{{url('/admin/invite/return_list')}}");
                        
                        //监听工具条
                        table.on('tool(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: "{{url('admin/invite/del')}}",
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
                            }
                        });
                    });
                }
            </script>

@endsection