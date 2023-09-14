<div class="layui-inline">
    <button id="addLeverTradeOption" class="layui-btn layui-btn-sm layui-btn-primary" type="button"><i class="layui-icon layui-icon-add-1"></i>添加</button>
</div>
<div class="layui-inline">
    <div class="layui-word-aux">会员进行杠杆交易时,手续费按推荐关系进行比例结算</div>
</div>
<table class="layui-table" lay-even lay-skin="nob">
    <colgroup>
        <col width="150">
        <col width="180">
        <col width="150">
        <col>
    </colgroup>
    <thead>
        <tr>
            <th>推荐代数</th>
            <th>手续费结算比例</th>
            <th>自身交易笔数</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody id="leverTradeFeeOption">
        @isset($setting['lever_fee_options'])
            @foreach (unserialize($setting['lever_fee_options']) as $key => $options)
                <tr>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="generation[]" value="{{$options['generation']}}" required lay-verify="required">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="reward_ratio[]" value="{{$options['reward_ratio']}}" required lay-verify="required">
                            </div>
                            <div class="layui-form-mid"><span>%</span></div>
                        </div>
                    </td>
                    <td>
                        <div class="layui-input-inline" style="width: 90px;">
                            <input class="layui-input" name="need_has_trades[]" value="{{$options['need_has_trades']}}" required lay-verify="required">
                        </div>
                    </td>
                    <td>
                        <div class="layui-input-inline">
                            <button class="layui-btn layui-btn-sm layui-btn-danger" type="button" lay-event="del">删除</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>
