@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">卖方账号</label>
            <div class="layui-input-inline">
                <input type="text" name="sell_account" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->sell_account)){{$result->sell_account}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">买方账号</label>
            <div class="layui-input-inline">
                <input type="text" name="buy_account" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->buy_account)){{$result->buy_account}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易币</label>
            <div class="layui-input-inline">
                <select name="currency_id" lay-filter="" lay-search>
                    <option value=""></option>
                    @if(!empty($currencies))
                    @foreach($currencies as $currency)
                    <option value="{{$currency->id}}" @if($currency->id == $result->currency_id) selected @endif>{{$currency->name}}</option>
                    @endforeach
                        @endif
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">法币</label>
            <div class="layui-input-inline">
                <select name="legal_id" lay-filter="">
                    <option value=""></option>
                    @if(!empty($currencies))
                    @foreach($legals as $legal)
                        <option value="{{$legal->id}}" @if($legal->id == $result->legal_id) selected @endif>{{$legal->name}}</option>
                    @endforeach
                        @endif
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格区间下限</label>
            <div class="layui-input-inline">
                <input type="text" name="min_price" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->min_price)){{$result->min_price}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格区间上限</label>
            <div class="layui-input-inline">
                <input type="text" name="max_price" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->max_price)){{$result->max_price}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机下限</label>
            <div class="layui-input-inline">
                <input type="text" name="min_number" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->min_number)){{$result->min_number}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机上限</label>
            <div class="layui-input-inline">
                <input type="text" name="max_number" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->max_number)){{$result->max_number}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">下单频率</label>
            <div class="layui-input-inline">
                <input type="text" name="need_second" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->need_second)){{$result->need_second}}@endif">
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
                    url:'{{url('admin/auto_add')}}'
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