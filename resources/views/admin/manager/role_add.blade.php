@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>角色</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">角色名称</label>
                <div class="layui-input-block">
                    <input type="text" name="name" autocomplete="off" class="layui-input" value="{{ $admin_role['name'] }}" placeholder="">
                </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">超级管理员</label>
                <div class="layui-input-block">
                    <input type="radio" name="is_super" value="1" title="是" {{$admin_role['is_super']?'checked':''}}>
                    <input type="radio" name="is_super" value="0" title="否" {{!$admin_role['is_super']?'checked':''}}>
                </div>
            </div>


            <input type="hidden" name="id" value="{{$admin_role['id']}}">
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
                    url: '/admin/manager/role_add',
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