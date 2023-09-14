@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-form">
    <div class="layui-form-item">
        <input type="hidden" name="id" value="{{Request::get('id')}}">
        <label for="price" class="layui-form-label">发送价格</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" name="update_price" placeholder="请输入价格" value="" >
        </div>
    </div>
    <div class="layui-form-item">
        <label for="write_market" class="layui-form-label">写入行情</label>
        <div class="layui-input-block">
            <input type="radio" name="write_market" value="1" title="是" checked>
            <input type="radio" name="write_market" value="0" title="否">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit lay-filter="form">确定</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    layui.use(['form', 'layer'], function () {
        var form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        form.on('submit(form)', function (data) {
            console.log(data);
            if (data.field.update_price <= 0 || data.field.update_price == '' || data.field.update_price == null || data.field.update_price == undefined) {
                layer.msg('价格必须大于0');
                return false;
            }
            layer.confirm('您确定要向该笔交易发送价格吗?', {
                title: '操作确认'
            }, function (index) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: data.field,
                    success: function (res) {
                        layer.msg(res.message, {
                            time: 2000
                            ,end: function () {
                                if (res.type == 'ok') {
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index); //再执行关闭
                                    parent.layui.table.reload('data_table');
                                }
                            }
                        });
                    },
                    error: function (res) {
                        layer.msg('网络错误,请稍后重试');
                    }
                });
            });
            
        });
    });
</script>
@endsection