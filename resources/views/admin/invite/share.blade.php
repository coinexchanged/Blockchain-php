@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')

    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            
            <div class="layui-form-item">
                <label class="layui-form-label">分享标题</label>
                <div class="layui-input-block">
                    <input type="text" name="share_title" autocomplete="off" class="layui-input" value="{{$title}}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">分享内容</label>
                <div class="layui-input-block">
                    <input type="text" name="share_content" autocomplete="off" class="layui-input" value="{{$content}}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">分享链接</label>
                <div class="layui-input-block">
                    <input type="text" name="share_url" autocomplete="off" class="layui-input" value="{{$url}}">
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
        layui.use(['form','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
            form.on('submit(website_submit)', function (data) {
                var data = data.field;
                console.log(data);
                $.ajax({
                    url: '/admin/invite/share',
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