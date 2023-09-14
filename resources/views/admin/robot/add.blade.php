@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">基于币种</label>
            <div class="layui-input-inline">
                <input type="text" name="huobi_currency" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->huobi_currency)){{$result->huobi_currency}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">变换倍数</label>
            <div class="layui-input-inline">
                <input type="number" name="mult" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->mult)){{$result->mult}}@endif">
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
            <label class="layui-form-label">卖</label>
            <div class="layui-input-inline">
                <select name="sell" lay-filter="">
                    <option value="1" @if($result->sell == '1') selected @endif>开启</option>
                    <option value="0" @if($result->sell == '0') selected @endif>关闭</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">买</label>
            <div class="layui-input-inline">
                <select name="buy" lay-filter="">
                    <option value="1" @if($result->buy == '1') selected @endif>开启</option>
                    <option value="0" @if($result->buy == '0') selected @endif>关闭</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机下限</label>
            <div class="layui-input-inline">
                <input type="text" name="number_min" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->number_min)){{$result->number_min}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">数量随机上限</label>
            <div class="layui-input-inline">
                <input type="text" name="number_max" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->number_max)){{$result->number_max}}@endif">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">价格浮动下限</label>
            <div class="layui-input-inline">
                <input type="text" name="float_number_down" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->float_number_down)){{$result->float_number_down}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格浮动上限</label>
            <div class="layui-input-inline">
                <input type="text" name="float_number_up" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->float_number_up)){{$result->float_number_up}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">下单频率</label>
            <div class="layui-input-inline">
                <input type="text" name="second" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->second)){{$result->second}}@endif">
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
                    url:'{{url('admin/robot/add')}}'
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
