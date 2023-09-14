@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>权限管理</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            @foreach($modules as $module)
            <div class="layui-form-item">
                <label class="layui-form-label">{{$module->name}}</label>
                <input type="checkbox" value="{{$module->module}}" lay-skin="primary" title="全选" lay-filter="allCh">
                <div class="layui-input-block">
                    @foreach($module->actions as $action)
                    <input type="checkbox" lay-skin="primary" name="permission[{{$module->module}}][]" @if(isset($permissions[$module->module]) && in_array($action->action, $permissions[$module->module])) checked @endif title="{{$action->name}}" value="{{$action->action}}">
                    @endforeach
                </div>

            </div>
            @endforeach
                <input type="hidden" value="{{$admin_role->id}}" name="id">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="permission_submit">立即提交</button>
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
            form.on('submit(permission_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/manager/role_permission',
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


            form.on('checkbox(allCh)',function(data){
                var index = data.value;
                $("input[name='permission[" + index + "][]']").each(function (i,obj) {
                    obj.checked = data.elem.checked;
                });
                form.render('checkbox');
            });

        });
    </script>
@stop