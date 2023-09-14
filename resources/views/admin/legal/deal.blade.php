@extends('admin._layoutNew')

@section('page-head')

@endsection

<script type="text/html" id="barDemo">

    @{{#if(d.is_sure == 0) { }}
    <a class="layui-btn layui-btn-xs" lay-event="cancel">
        取消
    </a>
    @{{#} else if(d.is_sure ==1){ }}
    <span class="layui-btn layui-btn-xs layui-btn-disabled">
           已确认
        </span>
    @{{#} else if(d.is_sure == 2){ }}
    <span class="layui-btn layui-btn-xs layui-btn-disabled">
           已取消
        </span>
    @{{#} else if(d.is_sure == 3){ }}
    <span class="layui-btn layui-btn-xs " lay-event="confirm">
           确认
        </span>
    <a class="layui-btn layui-btn-xs" lay-event="cancel">
        取消
    </a>
    @{{#}}}



    {{--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">取消</a>--}}

</script>


@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label >用户交易账号&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label >商家名称&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="seller_name" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>买入/卖出&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="type" id="type_type">
                        <option value="" class="ww">全部</option>
                        <option value="sell" class="ww">买入</option>
                        <option value="buy" class="ww">卖出</option>

                    </select>
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 50px;">
                <label>交易币&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <select name="currency_id" id="currency_id">
                        <option value="0" class="ww">全部</option>
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

<style>

    element.style {
        color: #fff;
        background-color: #01AAED;
        display: block;

    }
</style>



    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <!-- <script type="text/html" id="barDemo">
       
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script> -->
    <script type="text/html" id="type">
        @{{d.type=="buy" ? '<span class="layui-badge layui-bg-green">'+'卖出'+'</span>' : '' }}
        @{{d.type=="sell" ? '<span class="layui-badge layui-bg-red">'+'买入'+'</span>' : '' }}

    </script>
    <script type="text/html" id="is_sure">
        @{{d.is_sure==0 ? '<span class="layui-badge layui-bg-red">'+'未确认'+'</span>' : '' }}
        @{{d.is_sure==1 ? '<span class="layui-badge layui-bg-blue "  >'+'已确认'+'</span>' : '' }}
        @{{d.is_sure==2 ? '<span class="layui-badge layui-bg-orange">'+'已取消'+'</span>' : '' }}
        @{{d.is_sure==3 ? '<span class="layui-badge layui-bg-green">'+'已付款'+'</span>' : '' }}

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
                ,url: '{{url('admin/legal_deal/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'legal_deal_send_id', title: '交易需求id', width:150}
                    ,{field: 'seller_name', title: '商家名称', width:120}
                    ,{field: 'account_number', title: '用户交易账号', width:120}
                    ,{field: 'user_realname', title: '真实姓名', width:120}
                    ,{field: 'type', title: '买入/卖出', width:100, templet: '#type'}
                    ,{field: 'way_name', title: '支付方式', width:100}

                    ,{field: 'price', title: '单价', width:100 }
                    ,{field: 'number', title: '交易数量', width:100}
                    // ,{field: 'surplus_number', title: '剩余数量', width:100}
                    ,{field: 'currency_name', title: '交易币', width:100}
                    ,{field: 'deal_money', title: '交易总金额', width:100}

                    // ,{field: 'limitation', title: '限额', width:100, templet: '#limitation'}
                    ,{field: 'is_sure', title: '交易状态', width:100, templet: '#is_sure'}

                    ,{field: 'format_create_time', title: '交易时间', width:180}
                    ,{field: 'format_update_time', title: '确认时间', width:180}
                    ,{fixed: 'right', title: '操作', minWidth: 150, align: 'center', toolbar: '#barDemo'}
                    // ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });


             table.on('tool(test)', function(obj){
                 var data = obj.data;
                 if(obj.event === 'cancel')
                 {
                     layer.confirm('确定要取消吗', function(index){
                         $.ajax({
                             url:'{{url('/admin/admin_legal_pay_cancel')}}',
                             type:'post',
                             dataType:'json',
                             data:{id:data.id},
                             success:function (res) {
                                 if(res.type == 'error'){
                                     layer.msg(res.message);
                                 }else{
//                                     obj.del();
                                     layer.close(index);
                                     window.location.reload();
                                     layer.alert(res.message);
                                 }
                             }
                         });


                     });
                 }
                 else if(obj.event === 'confirm')
                 {
                     if(data.type=="buy")
                     {
                         layer.confirm('是否确认？', function(index)
                         {
                             $.ajax({
                                 url:'{{url('/admin/legal_deal_admin_user_sure')}}',
                                 type:'post',
                                 dataType:'json',
                                 data:{id:data.id},
                                 success:function (res) {
                                     if(res.type == 'error'){
                                         layer.msg(res.message);
                                     }else{
//                                     obj.del();
                                         layer.close(index);
                                         window.location.reload();
                                         layer.alert(res.message);
                                     }
                                 }
                             });


                         });
                     }
                     else if(data.type=="sell")
                     {
                         layer.confirm('是否确认？', function(index)
                         {
                             $.ajax({
                                 url:'{{url('/admin/legal_deal_admin_sure')}}',
                                 type:'post',
                                 dataType:'json',
                                 data:{id:data.id},
                                 success:function (res) {
                                     if(res.type == 'error'){
                                         layer.msg(res.message);
                                     }else{
//                                     obj.del();
                                         layer.close(index);
                                         window.location.reload();
                                         layer.alert(res.message);
                                     }
                                 }
                             });


                         });
                     }

                 }
             });

            //监听提交
            form.on('submit(mobile_search)', function(data)
            {
                var seller_name = data.field.seller_name
                    ,type = $('#type_type').val()
                    ,currency_id = $('#currency_id').val()
                    ,account_number = data.field.account_number


                table.reload('mobileSearch',{
                    where:{
                        account_number:account_number,
                        seller_name:seller_name,
                        type:type,
                        currency_id:currency_id,

                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection