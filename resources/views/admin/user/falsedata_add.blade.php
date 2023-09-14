@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')

    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">地址</label>
                <div class="layui-input-block">
                    <input type="text" name="address" autocomplete="off" class="layui-input" value="@if(isset($data->address)){{$data->address}}@endif" placeholder="">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">金额</label>
                <div class="layui-input-block">
                    <input type="text" name="price" autocomplete="off" class="layui-input" value="@if(isset($data->price)){{$data->price}}@endif" placeholder="">
                </div>
            </div>


            <input type="hidden" name="id" value="@if(isset($data->id)){{$data->id}}@endif">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminrole_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            form.on('submit(adminrole_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/user/falsedata_add',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
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