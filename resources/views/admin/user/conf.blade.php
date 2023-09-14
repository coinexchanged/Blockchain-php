@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">用户账号</label>
                <div class="layui-input-block">
                    <input type="text" name="account" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['account'] }}" placeholder="" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">钱包币种</label>
                <div class="layui-input-block">
                    <input type="text" name="currency" autocomplete="off" class="layui-input layui-disabled" value="{{ $results['currency_name'] }}" placeholder="" disabled>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">调节账户</label>
                <div class="layui-input-block">
                    <select name="type" lay-verify="required">
                        <option value=""></option>
                        <optgroup label="★★秒合约★★">
                            <option value="7">余额</option>
                            <option value="8">锁定余额</option>
                        </optgroup>
                        <hr class="layui-bg-gray">
                        <optgroup label="法币交易">
                            <option value="1">余额</option>
                            <option value="2">锁定余额</option>
                        </optgroup>
                        <hr class="layui-bg-gray">
                        <optgroup label="币币闪兑">
                            <option value="3">余额</option>
                            <option value="4">锁定余额</option>
                        </optgroup>
                        <hr class="layui-bg-gray">
                        <optgroup label="杠杆交易">
                            <option value="5">余额</option>
                            <option value="6">锁定余额</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">调节方式</label>
                <div class="layui-input-block">
                    <input type="radio" name="way" value="increment" title="增加"  checked>
                    <input type="radio" name="way" value="decrement" title="减少">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">调节值</label>
                <div class="layui-input-block">
                    <input type="text" name="conf_value" required  lay-verify="required" placeholder="请输入需要调节的数值" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">调节备注</label>
                <div class="layui-input-block">
                    <textarea name="info" placeholder="请输入内容" class="layui-textarea" lay-verify="required"></textarea>
                </div>
            </div>




            <input type="hidden" name="id" value="{{$results['id']}}">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="user_submit">立即提交</button>
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

            form.on('submit(user_submit)', function (data) {
                var data = data.field;
                console.log(data)
                $.ajax({
                    url:'{{url('admin/user/conf')}}',
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