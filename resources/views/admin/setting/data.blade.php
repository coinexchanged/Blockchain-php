@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')

    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">数据来源</label>
                <div class="layui-input-block">
                    <input type="radio" name="dataFrom" autocomplete="off" class="layui-input" value="" title='实时交易数据'>
                    <input type="radio" name="dataFrom" autocomplete="off" class="layui-input" value="" title='第三方数据'>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
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
            var $ = layui.$;
            form.on('submit(website_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/admin/setting/postadd',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
                return false;
            });

        });


    </script>
@stop