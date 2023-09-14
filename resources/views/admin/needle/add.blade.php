@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    <form class="layui-form" method="POST">
        <div class="layui-form-item">
            <label class="layui-form-label">插入时间</label>
            <div class="layui-input-block">
                <input class="layui-input itime" name="itime"  lay-verify="required" placeholder="请选择时间" type="text" value="{{date('Y-m-d H:i')}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开仓价格</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="open" lay-verify="required" placeholder="开仓价格" type="text" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最高价格</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="high" lay-verify="required" placeholder="最高价格" type="text" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最低价格</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="low" lay-verify="required" placeholder="最低价格" type="text" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">收仓价格</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="close" lay-verify="required" placeholder="收仓价格" type="text" value="@if (isset($news['title'])){{$news['title']}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">基础币种</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="base" lay-verify="required" placeholder="基础币种" type="text" value="@if (isset($news['title'])){{$news['title']}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">引用币种</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="target" lay-verify="required" placeholder="引用币种" type="text" value="@if (isset($news['title'])){{$news['title']}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript">
        layui.use(['element', 'form', 'layedit', 'laypage','laydate', 'layer'], function() {
            var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage,laydate=layui.laydate;
            laydate.render({
                elem:'.itime',
                type:'datetime',
                format:'yyyy-MM-dd HH:mm'
            });


            $('#newsAdd').click(function() {


                var index = layer.open({
                    title:'添加针'
                    ,type:2
                    ,content: '/admin/needle/add'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                layer.full(index);

            });


            form.on('submit(submit)', function(dataObj){
                var serData = $(dataObj.form).serialize();

                $.ajax({
                    type: 'POST'
                    ,url: window.location.href
                    ,data: serData
                    ,success: function(data) {
                        if(data.type == 'ok') {
                            layer.msg(data.message, {
                                icon: 1,
                                time: 1000,
                                end: function() {
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index);
                                    parent.window.location.reload();
                                }
                            });
                        } else {
                            layer.msg(data.message, {icon:2});
                        }
                    }
                    ,error: function(data) {
                        //重新遍历获取JSON的KEY
                        var str = '服务器验证失败！';
                        for(var o in data.responseJSON.errors) {
                            str += data.responseJSON.errors[o];
                        }
                        layer.msg(str, {icon:2});
                    }
                });
                parent.layui.layer.close();
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });
        });
    </script>
@endsection
