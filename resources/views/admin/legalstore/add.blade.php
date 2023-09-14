@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">商家姓名</label>
            <div class="layui-input-inline">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->name)){{$result->name}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户银行</label>
            <div class="layui-input-inline">
                <input type="text" name="bank_name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->bank_name)){{$result->bank_name}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户支行</label>
            <div class="layui-input-inline">
                <input type="text" name="bank_subname" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->bank_subname)){{$result->bank_subname}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行账号</label>
            <div class="layui-input-inline">
                <input type="text" name="bank_account" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->bank_account)){{$result->bank_account}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户人</label>
            <div class="layui-input-inline">
                <input type="text" name="bank_user" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->bank_user)){{$result->bank_user}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝账号</label>
            <div class="layui-input-inline">
                <input type="text" name="alipay_account" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->alipay_account)){{$result->alipay_account}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝二维码</label>
            <div class="layui-input-inline">
                <input type="text" name="alipay_qrcode" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->alipay_qrcode)){{$result->alipay_qrcode}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">微信账号</label>
            <div class="layui-input-inline">
                <input type="text" name="wechat_account" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->wechat_account)){{$result->wechat_account}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">微信二维码</label>
            <div class="layui-input-inline">
                <input type="text" name="wechat_qrcode" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->wechat_qrcode)){{$result->wechat_qrcode}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最小充值数量</label>
            <div class="layui-input-inline">
                <input type="text" name="min_num" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->min_num)){{$result->min_num}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大充值数量</label>
            <div class="layui-input-inline">
                <input type="text" name="max_num" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->max_num)){{$result->max_num}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最小提现数量</label>
            <div class="layui-input-inline">
                <input type="text" name="min_num_wid" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->min_num_wid)){{$result->min_num_wid}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大提现数量</label>
            <div class="layui-input-inline">
                <input type="text" name="max_num_wid" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->max_num_wid)){{$result->max_num_wid}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">充币汇率</label>
            <div class="layui-input-inline">
                <input type="text" name="rate" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->rate)){{$result->rate}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">提币汇率</label>
            <div class="layui-input-inline">
                <input type="text" name="rate_sell" lay-verify="required" autocomplete="off" placeholder="" class="layui-input"
                       value="@if(!empty($result->rate_sell)){{$result->rate_sell}}@endif">
            </div>
        </div>

        <input type="hidden" name="id" value="@if(!empty($result->id)){{$result->id}}@endif">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>


        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/legalstore/add')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection
