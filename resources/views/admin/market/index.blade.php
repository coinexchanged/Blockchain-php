@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加数据','{{url('admin/market_add')}}')">添加数据</button>      
    </div>
    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
     <!--   
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a> -->
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
                ,url: '{{url('admin/market_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60, sort: true}
                    ,{field: 'currency_name', title: '行情名称', Width:80}
                    ,{field: 'legal_name', title: '法币', minWidth:80}
                    ,{field: 'start_price', title: '开盘价格', minWidth:80}
                    ,{field: 'end_price', title: '收盘价格', minWidth:80}
                    ,{field: 'highest', title: '最高价', minWidth:80}
                    ,{field: 'mminimum', title: '最低价', minWidth:80}
                    ,{field: 'number', title: '总量', minWidth:80, templet: '#typetml'}
                    ,{field: 'day_time', title: '日期', minWidth:150, templet: '#typetml'}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            //监听热卖操作
            form.on('switch(sexDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/market_display')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/market_del')}}',
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
                    layer_show('编辑行情','{{url('admin/market_add')}}?id='+data.id);
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