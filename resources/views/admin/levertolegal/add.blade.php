@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>修改购买上限</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">上限数量</label>
                <div class="layui-input-block">
                    <input type="text" name="value" autocomplete="off" class="layui-input" value="@if (isset($result['value'])){{$result['value']}}@endif" placeholder="" >
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submit">修改数据</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer','laydate'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
           var laydate = layui.laydate;

          laydate.render({
                elem: '#start_time',
                type: 'datetime'
            });
            laydate.render({
                elem: '#end_time',
                type: 'datetime'
            });
            form.on('submit(adminuser_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/carrules/updatesetingnum',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res)
                    {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });

        });


    </script>
@stop