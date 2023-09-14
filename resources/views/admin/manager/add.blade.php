@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>管理员</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" name="username" autocomplete="off" class="layui-input" value="{{ $admin_user['username'] }}" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">初始密码</label>
                <div class="layui-input-block">
                    <input type="password" name="password" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">角色</label>
                <div class="layui-input-block">
                    <select name="role_id" lay-filter="role_id">
                        <option value="">请选择</option>
                        @foreach($roles as $role)
                        <option value="{{$role->id}}" @if(isset($admin_user) && $admin_user['role_id'] == $role['id']) selected @endif>{{$role->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <input type="hidden" name="id" value="{{$admin_user['id']}}">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submit">立即提交</button>
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
            form.on('submit(adminuser_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/manager/add',
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