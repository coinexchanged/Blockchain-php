@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
      

        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label >用户名&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>买入/卖出&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="type" id="type_type">
                        <option value="" class="ww">全部</option>
                        <option value="1" class="ww">买入</option>
                        <option value="2" class="ww">卖出</option>
                       
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

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        {{--<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">调节账户</a>--}}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
<script type="text/html" id="type">
@{{d.type==1 ? '<span class="layui-badge layui-bg-green">'+'买入'+'</span>' : '' }}
@{{d.type==2 ? '<span class="layui-badge layui-bg-red">'+'卖出'+'</span>' : '' }}

</script>
<script type="text/html" id="is_check">
@{{d.is_check==0 ? '<span class="layui-badge layui-bg-gray">'+'否'+'</span>' : '' }}
@{{d.is_check==1 ? '<span class="layui-badge">'+'商家设限'+'</span>' : '' }}

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
                ,url: '{{url('admin/legal/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'account_number', title: '用户名', width:120}
                    ,{field: 'type', title: '买入/卖出', width:100, templet: '#type'}
                    ,{field: 'price', title: '单价', width:100 }
                    ,{field: 'number', title: '数量', width:100}
                    ,{field: 'currency_name', title: '交易币', width:80}
                    ,{field: 'min_amount', title: '最低交易额', width:100}
                    ,{field: 'max_amount', title: '最高交易额', width:100}
                    ,{field: 'is_check', title: '是否设限', width:180, templet: '#is_check'}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    // ,{field: 'sure_name', title: '交易状态', minWidth:100}
                    ,{field: 'create_time', title: '创建时间', width:180}
                    // ,{field: 'update_time', title: '确认时间', minWidth:180}
                    // ,{field: 'create_time', title: '添加时间', minWidth:80}
                    // ,{field: 'wechat_nickname', title: '微信昵称', minWidth:80}
                    // ,{field: 'wechat_account', title: '微信账号', minWidth:80}
                    // ,{field: 'ali_nickname', title: '支付宝昵称', minWidth:80}
                    // ,{field: 'ali_account', title: '支付宝账号', minWidth:80}
                    // ,{field: 'bank_name', title: '银行名称', minWidth:80}
                    // ,{field:'is_recommend', title:'热卖', minWidth:85, templet: '#switchTpl', unresize: true}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

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
                var account_number = data.field.account_number
                    ,type = $('#type_type').val()
                    ,currency = $('#currency').val()
                table.reload('mobileSearch',{
                    where:{
                        account_number:account_number,
                        type:type,
                        currency:currency,

                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection