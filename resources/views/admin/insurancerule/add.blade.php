@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label for="currency_id" class="layui-form-label">币种</label>
            <div class="layui-input-block">
                <select name="insurance_type_id" lay-verify="required" lay-search>
                    @foreach ($insurance_type as $insurance)
                        <option value="{{ $insurance->id }}" @if ((isset($result) && $result->insurance__type_id == $insurance->id)) selected @endif>{{ $insurance->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">金额</label>
            <div class="layui-input-block">
                <input type="text" name="amount" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->amount}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">单笔最大金额</label>
            <div class="layui-input-block">
                <input type="text" name="place_an_order_max" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->place_an_order_max}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大持仓笔数</label>
            <div class="layui-input-block">
                <input type="text" name="existing_number" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->existing_number}}">
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
                    url:'{{url('admin/insurance_rules_add')}}'
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