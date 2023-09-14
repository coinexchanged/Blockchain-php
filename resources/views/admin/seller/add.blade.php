@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">交易账号</label>
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
            <label class="layui-form-label">商家电话</label>
            <div class="layui-input-block">
                <input type="text" name="mobile" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->mobile}}">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">选择法币</label>
            <div class="layui-input-block">
                <select name="currency_id" lay-filter="type">
                    @foreach($currencies as $currency)
                        <option value="{{$currency->id}}" @if($result->currency_id == $currency->id) selected @endif>{{$currency->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{--<div class="layui-form-item">--}}
            {{--<label class="layui-form-label">商家余额</label>--}}
            {{--<div class="layui-input-block">--}}
                {{--<input type="number" class="layui-input" id="reserve_time" name="seller_balance" value="{{$result->seller_balance}}">--}}
            {{--</div>--}}
        {{--</div>--}}


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
            <label class="layui-form-label">开户支行</label>
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
                    url:'{{url('admin/seller_add')}}'
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