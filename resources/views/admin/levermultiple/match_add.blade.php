@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-form">
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">币种</label>
        <div class="layui-input-block">
            <select name="currency_id" lay-verify="required" lay-search>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->id }}" @if ((isset($currency_match) && $currency_match->currency_id == $currency->id)) selected @endif>{{ $currency->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">显示</label>
        <div class="layui-input-block">
            <input type="radio" name="is_display" value="1" title="是" @if (isset($currency_match)) {{ $currency_match->is_display == 1 ? 'checked' : '' }} @else checked @endif>
            <input type="radio" name="is_display" value="0" title="否" @if (isset($currency_match) && $currency_match->is_display == 0) checked @endif>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="market_from" class="layui-form-label">行情</label>
        <div class="layui-input-block">
            <select name="market_from" lay-verify="required">
                @foreach ($market_from_names as $key => $market_from_name)
                    <option value="{{ $key }}" @if ((isset($currency_match) && $currency_match->market_from == $key)) selected @endif>{{ $market_from_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">交易功能</label>
        <div class="layui-input-block">
            <input type="checkbox" name="open_transaction" value="1" title="撮合交易" @if (isset($currency_match)) {{ $currency_match->open_transaction == 1 ? 'checked' : '' }} @else checked @endif>
            <input type="checkbox" name="open_lever" value="1" title="杠杆开启" @if (isset($currency_match)) {{ $currency_match->open_lever == 1 ? 'checked' : '' }} @else checked @endif>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">每手数量</label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" name="lever_share_num" placeholder="杠杆交易每手数量" value="{{ $currency_match->lever_share_num ?? 1.00 }}" >
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">杠杆手续费</label>
        <div class="layui-input-block">
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="lever_trade_fee" placeholder="杠杆交易手费续" value="{{ $currency_match->lever_trade_fee ?? 0.00 }}" >
            </div>
            <div class="layui-form-mid">%</div>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">点差</label>
        <div class="layui-input-block">
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="spread" placeholder="交易点差" value="{{ $currency_match->spread ?? 0.00 }}" >
            </div>
            <div class="layui-form-mid">%</div>
        </div>
    </div>
    <div class="layui-form-item">
        <label for="currency_id" class="layui-form-label">隔夜费</label>
        <div class="layui-input-block">
            <div class="layui-input-inline">
                <input type="text" class="layui-input" name="overnight" placeholder="隔夜费" value="{{ $currency_match->overnight ?? 0.00 }}" >
            </div>
            <div class="layui-form-mid">%</div>
        </div>
    </div>

    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit lay-filter="form">提交</button>
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
</script>
@endsection