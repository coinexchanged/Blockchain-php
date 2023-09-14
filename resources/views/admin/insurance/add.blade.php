@extends('admin._layoutNew')

@section('page-head')
<style>
    .layui-form-label{
        width: 250px;
    }
    .layui-input-block{
        margin-left: 280px;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">险种名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="currency_id" class="layui-form-label">币种</label>
            <div class="layui-input-block">
                <select name="currency_id" lay-verify="required" lay-search>
                    @foreach ($currency as $c)
                        <option value="{{ $c->id }}" @if ((isset($result) && $result->currency_id == $c->id)) selected @endif>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label for="currency_id" class="layui-form-label">类型</label>
            <div class="layui-input-block">
                <select name="type" lay-verify="required" lay-search>
                    <option value="1" @if ((isset($result) && 1 == $result->type)) selected @endif>正向险</option>
                    <option value="2" @if ((isset($result) && 2 == $result->type)) selected @endif>反向险</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最低购买额</label>
            <div class="layui-input-block">
                <input type="text" name="min_amount" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->min_amount}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大购买额</label>
            <div class="layui-input-block">
                <input type="text" name="max_amount" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->max_amount}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">保险资产占受保资产比例（%）</label>
            <div class="layui-input-block">
                <input type="text" name="insurance_assets" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->insurance_assets}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">盈利达到比例（%），解约</label>
            <div class="layui-input-block">
                <input type="text" name="profit_termination_condition" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->profit_termination_condition}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">亏损达到比例（%），赔偿【正向】</label>
            <div class="layui-input-block">
                <input type="text" name="defective_claims_condition" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->defective_claims_condition}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">亏损达到额度，赔偿【反向】</label>
            <div class="layui-input-block">
                <input type="text" name="defective_claims_condition2" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->defective_claims_condition2}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">每日最大赔付次数【正向】</label>
            <div class="layui-input-block">
                <input type="text" name="claims_times_daily" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->claims_times_daily}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">赔付比例（%）</label>
            <div class="layui-input-block">
                <input type="text" name="claim_rate" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->claim_rate}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">赔付去向</label>
            <div class="layui-input-block">
                <select name="claim_direction" lay-verify="required" lay-search>

                    <option value="1" @if ((isset($result) && 1 == $result->claim_direction)) selected @endif>受保资产</option>
                    <option value="2" @if ((isset($result) && 2 == $result->claim_direction)) selected @endif>可用资产</option>

                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否自动赔付</label>
            <div class="layui-input-block">
                <div class="layui-input-inline">
                    <input type="radio" name="auto_claim" value="1" title="打开" @if (isset($result->auto_claim)) {{$result->auto_claim == 1 ? 'checked' : ''}} @endif >
                    <input type="radio" name="auto_claim" value="0" title="关闭" @if (isset($result->auto_claim)) {{$result->auto_claim == 0 ? 'checked' : ''}} @else checked @endif >
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否开启购买</label>
            <div class="layui-input-block">
                <div class="layui-input-inline">
                    <input type="radio" name="status" value="1" title="打开" @if (isset($result->status)) {{$result->status == 1 ? 'checked' : ''}} @endif >
                    <input type="radio" name="status" value="0" title="关闭" @if (isset($result->status)) {{$result->status == 0 ? 'checked' : ''}} @else checked @endif >
                </div>
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
                    url:'{{url('admin/insurance/add')}}'
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