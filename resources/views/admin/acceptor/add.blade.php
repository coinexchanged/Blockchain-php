@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">用户名</label>
            <div class="layui-input-block">
                <input type="text" name="account_number" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account_number}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">充值额度</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="reserve_time" name="recharge_amount" value="{{$result->recharge_amount}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">充值费率</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="recharge_rate" value="{{$result->recharge_rate}}" placeholder="请填写百分比">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">提现额度</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="reserve_time" name="cash_amount" value="{{$result->cash_amount}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">提现费率</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="end_time" name="cash_rate" value="{{$result->cash_rate}}" placeholder="请填写百分比">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">微信昵称</label>
            <div class="layui-input-block">
                <input type="text" name="wechat_nickname"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->wechat_nickname}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">微信账号</label>
            <div class="layui-input-block">
                <input type="text" name="wechat_account"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->wechat_account}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝昵称</label>
            <div class="layui-input-block">
                <input type="text" name="ali_nickname"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->ali_nickname}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">支付宝账号</label>
            <div class="layui-input-block">
                <input type="text" name="ali_account"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->ali_account}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">选择银行</label>
            <div class="layui-input-block">
                <select name="bank_id" lay-filter="type">
                    @foreach($banks as $bank)
                        <option value="{{$bank->id}}" @if($result->bank_id == $bank->id) selected @endif>{{$bank->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行账号</label>
            <div class="layui-input-block">
                <input type="text" name="bank_account"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->bank_account}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户行</label>
            <div class="layui-input-block">
                <input type="text" name="bank_address"  autocomplete="off" placeholder="" class="layui-input" value="{{$result->bank_address}}">
            </div>
        </div>


        <input type="hidden" name="id" value="{{$result->id}}">
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
                    url:'{{url('admin/acceptor_add')}}'
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