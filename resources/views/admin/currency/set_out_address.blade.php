@extends('admin._layoutNew')

@section('page-head')
@endsection

@section('page-content')
<form class="layui-form" action="">
    <div class="layui-form-item">
        <label class="layui-form-label">转出地址</label>
        <div class="layui-input-block">
            <input type="text" name="total_account"  autocomplete="off" placeholder="转出的钱包地址" class="layui-input" value="{{$currency->total_account}}">
        </div>
        <div class="layui-form-mid layui-word-aux">打手续费、提币的钱包地址,<span style="color: #f00">为了安全不建议留太多币,建议随用随存</span></div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">转出私钥</label>
        <div class="layui-input-block">
            <input type="password" name="key"  autocomplete="off" placeholder="转出钱包私钥" class="layui-input" value="{{$currency->key}}">
        </div>
        <div class="layui-form-mid layui-word-aux">打手续费、提币的对应钱包地址的私钥</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">安全验证码</label>
        <div class="layui-input-inline">
            <input type="text" name="verificationcode" placeholder="" autocomplete="off" class="layui-input">
        </div>
        <button type="button" class="layui-btn layui-btn-primary" id="get_code">获取验证码</button>
    </div>
    <input id="currency_id" type="hidden" name="id" value="{{$currency->id}}">
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" type="button" lay-submit lay-filter="*">保存</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
layui.use(['layer', 'form'], function () {
    var layer = layui.layer
        ,$ = layui.jquery
        ,form = layui.form;
    //获取验证码
    $('#get_code').click(function () {
        var that_btn = $(this);
        $.ajax({
            url: '/admin/safe/verificationcode'
            ,type: 'GET'
            ,success: function (res) {
                if (res.type == 'ok') {
                    that_btn.attr('disabled', true);
                    that_btn.toggleClass('layui-btn-disabled');
                }
                layer.msg(res.message, {
                    time: 3000
                });
            }
            ,error: function () {
                layer.msg('网络错误');
            }
        });
    });
    form.on('submit(*)', function (data) {
        $.ajax({
            url: '/admin/currency/set_out_address'
            ,type: 'POST'
            ,data: data.field
            ,success: function (res) {
                layer.msg(res.message, {
                    time: 2000
                    ,end: function () {
                        if (res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                            parent.layer.close(index); //再执行关闭
                        }
                    }
                })
            }
            ,error: function (res) {
                layer.msg('服务器或网络故障');
            }
        });
        return false;
    });
});
</script>
@endsection