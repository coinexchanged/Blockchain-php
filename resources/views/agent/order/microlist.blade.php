@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-fluid">
  <div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
      <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">ID</label>
          <div class="layui-input-block">
            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">用户账号</label>
          <div class="layui-input-block">
            <input type="text" name="account" placeholder="请输入" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">代理用户名</label>
          <div class="layui-input-block">
            <input type="text" name="agentusername" placeholder="请输入上级代理商" autocomplete="off" class="layui-input">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">开始日期：</label>
          <div class="layui-input-block" style="width:170px;">
            <input type="text" class="layui-input" id="start_time" value="" name="start_time">
          </div>
        </div>
        <div class="layui-inline">
          <label class="layui-form-label">结束日期：</label>
          <div class="layui-input-block" style="width:170px;">
            <input type="text" class="layui-input" id="end_time" value="" name="end_time">
          </div>
        </div>
      </div>
      <div class="layui-form-item">

        <div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">交易状态</label>
          <div class="layui-input-block">
            <select name="status">
              <option value="-1">不限</option>

              <option value="1">交易中</option>
              <option value="2">平仓中</option>
              <option value="3">已平仓</option>

            </select>
          </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">买卖类型</label>
          <div class="layui-input-block">
            <select name="type">
              <option value="-1">不限</option>
              <option value="1">买涨</option>
              <option value="2">买跌</option>
            </select>
          </div>
        </div>
        <!--<div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">预设</label>
          <div class="layui-input-block">
            <select name="pre_profit_result" id="pre_profit_result" class="layui-input">
              <option value="-2">所有</option>
              <option value="-1">亏</option>
              <option value="0">无</option>
              <option value="1">盈</option>
            </select>
          </div>
        </div>-->
        <div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">结果</label>
          <div class="layui-input-block">
            <select name="profit_result" id="profit_result" class="layui-input">
              <option value="-2">所有</option>
              <option value="-1">亏</option>
              <option value="0">无</option>
              <option value="1">盈</option>
            </select>
          </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">支付货币</label>
          <div class="layui-input-block">
            <select name="currency_id" id="currency_id" class="">
                <option value="-1" class="ww">全部</option>
                @foreach ($currencies as $currency)
                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                @endforeach

            </select>
          </div>
        </div>
        
        <div class="layui-inline" style="margin-left: 10px;">
          <label class="layui-form-label">交易对</label>
          <div class="layui-input-block">
            <select name="match_id" id="currency_match" class="">
                <option value="-1" class="ww">全部</option>
                @foreach ($currency_matches as $match)
                    <option value="{{$match->id}}" class="ww">{{$match->currency_name}}/{{$match->legal_name}}</option>
                @endforeach

            </select>
          </div>
        </div>

        <div class="layui-inline">
          <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
          </button>
        </div>


      </div>
    </div>

   <!-- <div class="layui-card-body">
      <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
        <ul class="layui-row layui-col-space10 layui-this">
          <li class="layui-col-xs3">
            <a href="javascript:;" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
              <h3>盈亏统计：</h3>
              <p><cite style="color:#fff" id="total_fact_profits">0.0000000</cite></p>
            </a>
          </li>

          <li class="layui-col-xs3">
            <a href="javascript:;" onclick="layer.tips('手续费统计', this, {tips: 3});" class="layadmin-backlog-body"
              style="color: #fff;background-color: #01AAED;">
              <h3>手续费统计：</h3>
              <p><cite style="color:#fff" id="total_fee">0.00000000</cite></p>
            </a>
          </li>

        </ul>
      </div>

    </div>-->

    <div class="layui-card-body">
      <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
      <script type="text/html" id="table-useradmin-webuser">
        <!-- <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event=""><i class="layui-icon layui-icon-edit"></i>查看详情</a> -->
      </script>
    </div>
  </div>
</div>

@endsection

@section('scripts')

<script type="text/html" id="status_name">
    <span class="layui-badge @{{d.status == 1 ? '' : 'layui-bg-gray'}}">@{{d.status_name}}</span>
</script>
<script type="text/html" id="symbol_name">
    <span>@{{d.symbol_name}}</span><span style="color: #848484dd; font-size: 10px;" title="收益率:@{{d.profit_ratio}}%">-@{{d.seconds}}S</span>
</script>
<script type="text/html" id="type_name">
    @{{# if (d.type == 1) { }}
        <div class="order_type order_type_rise">
            <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span style="font-size: 18px; font-weight: bold;">↑</span> 
        </div>
    @{{# } else { }}
        <div class="order_type order_type_fall">
            <span style="font-size: 13px; font-weight: bold;">@{{d.type_name}}</span><span style="font-size: 18px; font-weight: bold;">↓</span>
        </div>
    @{{# } }}
</script>

<script type="text/html" id="pre_profit_result_name">
    @{{# if (d.pre_profit_result == 1) { }}
        <span style="color:#89deb3;">@{{d.pre_profit_result_name}}</span>
    @{{# } else if (d.pre_profit_result == -1) { }}
        <span style="color:#d67a7a;">@{{d.pre_profit_result_name}}</span>
    @{{# } else { }}
        <span class="layui-badge-rim">@{{d.pre_profit_result_name}}</span>
    @{{# } }}
</script>

<script type="text/html" id="profit_result_name">
  <span class="layui-badge @{{d.profit_result == 1 ? 'layui-bg-green' : ''}}">@{{d.profit_result_name}}</span>
</script>

<script type="text/html" id="fact_profits">
  <div style="text-align: right; margin-right: 10px;">
  @{{# if (d.profit_result == 1) { }}
      <span style="color: #f00; font-weight: bolder;">@{{Number(d.fact_profits).toFixed(2)}}</span>
  @{{# } else { }}
      <span>@{{Number(d.fact_profits).toFixed(2)}}</span>
  @{{# } }}
  </div>
</script>

<script>

  layui.use(['index','table', 'layer', 'laydate', 'form'], function () {
    var $ = layui.$
      , admin = layui.admin
      , view = layui.view
      , table = layui.table
      , laydate = layui.laydate
      , form = layui.form;


    laydate.render({
      elem: '#start_time',
      type: 'datetime'
    });
    laydate.render({
      elem: '#end_time',
      type: 'datetime'
    });


    //秒合约订单管理
    var data_table = table.render({
      elem: '#LAY-user-manage'
      , method: 'post'
      , url: '/agent/micro/list'
      , cols: [[
        { field: '', type: 'checkbox', width: 60 }
        , { field: '', title: '序号', type: "numbers", width: 90 }
        , { field: 'id', title: 'ID', width: 100 }
        , { field: 'account', title: '用户账号', width: 130, sort: true, totalRowText: '小计' }
        , { field: 'real_name', title: '真实姓名', width: 100 }
        , { field: 'parent_agent_name', title: '所属代理商', width: 120 }
        , { field: 'symbol_name', title: '合约', width: 140, sort: true, templet: '#symbol_name' }
        , { field: 'currency_name', title: '币种', width: 80, sort: true }
        , { field: 'type_name', title: '类型', width: 80, templet: '#type_name', sort: true }
        , { field: 'seconds', title: '秒数', width: 80, templet: '#seconds', sort: true, hide: true }
        , { field: 'status_name', title: '交易状态', width: 100, sort: true, templet: '#status_name' }
        , { field: 'number', title: '数量', width: 90, templet: '<div><div style="text-align: right;">@{{Number.parseInt(d.number)}}</div></div>', totalRow: true }
        , { field: 'fee', title: '手续费', width: 100, totalRow: true, templet: '<div><div style="text-align: right;"><span>@{{Number(d.fee).toFixed(2)}}</span></div></div>' }
//        , { field: 'pre_profit_result_name', title: '预设', width: 90, sort: true, templet: '#pre_profit_result_name', hide: false }
        , { field: 'profit_result_name', title: '结果', width: 90, sort: true, templet: '#profit_result_name', hide: false }
        , { field: 'fact_profits', title: '盈利', width: 100, sort: true, totalRow: true, templet: '#fact_profits' }
        , { field: 'open_price', title: '开仓价', width: 100, templet: '<div><div style="text-align: right;"><span>@{{Number(d.open_price).toFixed(4)}}</span></div></div>' }
        , { field: 'end_price', title: '平仓价', width: 100, templet: '<div><div style="text-align: right;"><span>@{{Number(d.end_price).toFixed(4)}}</span></div></div>' }
        , { field: 'created_at', title: '下单时间', width: 170, sort: true }
        , { field: 'updated_at', title: '更新日期', width: 170, sort: true, hide: true }
        , { field: 'handled_at', title: '平仓时间', width: 170, sort: true, hide: true }
        , { field: 'complete_at', title: '完成时间', width: 170, sort: true, hide: true }
        //,{fixed: 'right', title: '操作', width: 100, align: 'center', toolbar: '#barDemo'}
      ]]
      , page: true
      , limit: 20
      , limits: [20, 50, 100, 500, 1000]
      , toolbar: true
      , height: 'full-320'
      , totalRow: true
      , text: '对不起，加载出现异常！'
      , headers: { //通过 request 头传递
        access_token: layui.data('layuiAdmin').access_token
      }
      , where: { //通过参数传递
        access_token: layui.data('layuiAdmin').access_token
      }
      , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
        if (res !== 0) {

          if (res.code === 1001) {
            //清空本地记录的 token，并跳转到登入页
            admin.exit();
          }
          var total = res.extra_data.total;
          $('#total_fee').text(total.total_fee);
          $('#total_fact_profits').text(total.total_fact_profits);

        }
      }
    });

    //监听搜索
    form.on('submit(LAY-user-front-search)', function (data) {
      var a = layui.data('layuiAdmin').access_token;
      data.field.access_token = a;

      var option = {
        where: data.field,
        page: { curr: 1 }
      }
      data_table.reload(option);
      return false;
    });




  });

</script>
@endsection
