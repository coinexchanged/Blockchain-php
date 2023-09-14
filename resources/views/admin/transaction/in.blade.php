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
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>



        </form>
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        {{--<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">调节账户</a>--}}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

@endsection

@section('scripts')
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/in_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:80, sort: true}
                    ,{field: 'account_number', title: '用户名', minWidth:100}
                    ,{field: 'price', title: '价格', minWidth:100}
                    ,{field: 'number', title: '数量', minWidth:100}
                    // ,{field: 'way_name', title: '交易方式', minWidth:100}
                    // ,{field: 'deal_account', title: '交易账号', minWidth:180}
                    // ,{field: 'hes_account', title: '承兑商交易账号', minWidth:180}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    // ,{field: 'sure_name', title: '交易状态', minWidth:100}
                    ,{field: 'create_time', title: '创建时间', minWidth:180}
                    // ,{field: 'update_time', title: '确认时间', minWidth:180}
                    // ,{field: 'create_time', title: '添加时间', minWidth:80}
                    // ,{field: 'wechat_nickname', title: '微信昵称', minWidth:80}
                    // ,{field: 'wechat_account', title: '微信账号', minWidth:80}
                    // ,{field: 'ali_nickname', title: '支付宝昵称', minWidth:80}
                    // ,{field: 'ali_account', title: '支付宝账号', minWidth:80}
                    // ,{field: 'bank_name', title: '银行名称', minWidth:80}
                    // ,{field:'is_recommend', title:'热卖', minWidth:85, templet: '#switchTpl', unresize: true}
                    // ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
           

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if(obj.event === 'edit'){
                    layer_show('编辑承兑商','{{url('')}}?id='+data.id);
                } else if(obj.event === 'detail'){
                    layer_show('调节账户','{{url('')}}?id='+data.id,800,600);
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection