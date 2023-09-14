@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')


<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
            
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">开始日期</label>
                    <div class="layui-input-block">
                        <input type="text" name="start" id="datestart" placeholder="yyyy-MM-dd" autocomplete="off"
                            class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">结束日期</label>
                    <div class="layui-input-block">
                        <input type="text" name="end" id="dateend" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">订单状态</label>
                    <div class="layui-input-block">
                        <select name="status">
                            <option value="10">不限</option>
                            <option value="0">挂单中</option>
                            <option value="1">交易中</option>
                            <option value="2">平仓中</option>
                            <option value="3">已平仓</option>
                            <option value="4">已撤单</option>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">交易类型</label>
                    <div class="layui-input-block">
                        <select name="type">
                            <option value="0">不限</option>
                            <option value="1">买入</option>
                            <option value="2">卖出</option>
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

        <div class="layui-card-body">
            <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
            
        </div>
    </div>
</div>

@endsection

@section('scripts')


<script type="text/html" id="lockTpl">
  @{{#  if(d.type == 1){ }}
  <span class="layui-badge layui-bg-red">买入</span>
  @{{#  } else { }}
  <span class="layui-badge layui-bg-blue">卖出</span>
  @{{#  } }}
</script>
<script type="text/html" id="addsonTpl">
  @{{#  if(d.status == 0){ }}
  <i class="layui-icon layui-icon-rate"style="font-size: 16px; color: #1E9FFF;">挂单中</i>
  @{{#  } else if(d.status == 1) { }}
  <i class="layui-icon layui-icon-rate-half"style="font-size: 16px; color: #FFB800;">交易中</i>
  @{{#  } else if(d.status == 2) { }}

  <i class="layui-icon layui-icon-refresh-3"style="font-size: 16px; color: red;">平仓中</i>
  @{{#  } else if(d.status == 3) { }}

  <i class="layui-icon layui-icon-rate-solid"style="font-size: 16px; color: #009688;">已平仓</i>
  @{{#  } else if(d.status == 4) { }}

  <i class="layui-icon layui-icon-close-fill"style="font-size: 16px; color: #FF5722;">已撤单</i>
  @{{#  } }}
</script>

<script>
    layui.use(['index','element', 'form','table', 'layer', 'laydate'], function () {
        var $ = layui.$
            ,element = layui.element
            ,layer = layui.layer
            , table = layui.table
            , laydate = layui.laydate
            , form = layui.form
            , admin = layui.admin


        //日期
        laydate.render({
            elem: '#datestart'
        });
        laydate.render({
            elem: '#dateend'
        });

        var user_id={{$user_id}};
        // console.log(user_id);

   
        //订单管理
        table.render({
            elem: '#LAY-user-manage'
            , method: 'get'
            , url: '/agent/user/lever_order_list?user_id='+user_id
            , cols: [[
                { type: 'checkbox', fixed: 'left' }
                , { field: 'id', width: 60, title: 'ID', sort: true }
                , { field: 'user_name', title: '用户名', minWidth: 150, style: "color: #fff;background-color: #5FB878;" }
                , { field: 'parent_agent_name', title: '上级代理商', width: 120 }
                , { field: 'agent_level', title: '用户等级', width: 100 }
                , { field: 'type', title: '交易类型', width: 90, templet: '#lockTpl' }
                ,{field: 'symbol', title: '交易对', width: 100}
                , { field: 'status', title: '当前状态', sort: true, width: 170, templet: '#addsonTpl' }
                , { field: 'origin_price', title: '原始价格', width: 120 }
                , { field: 'price', title: '开仓价格', width: 120 }
                , { field: 'update_price', title: '当前价格', width: 120 }
                
                , { field: 'fact_profits', title: '最终盈亏', width: 120 }
                , { field: 'share', title: '手数', sort: true, width: 90 }
                , { field: 'multiple', title: '倍数', sort: true, width: 90 }
                , { field: 'origin_caution_money', title: '初始保证金', width: 120 }
                , { field: 'caution_money', title: '当前可用保证金', sort: true, width: 170 }
                , { field: 'create_time', title: '创建时间', width: 170 }
                , { field: 'update_time', title: '价格刷新时间', sort: true, width: 170 }
                , { field: 'handle_time', title: '平仓时间', sort: true, width: 170 }
                , { field: 'complete_time', title: '完成时间', width: 170 }
            ]]
            , page: true
            , limit: 30
            , height: 'full-320'
            ,toolbar:true
            , text: '对不起，加载出现异常！'
            , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
            
                if (res !== 0) {
                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }
                }
            }
        });


         //监听搜索
         form.on('submit(LAY-user-front-search)', function (data) {
            var field = data.field;
           
            //执行重载
            table.reload('LAY-user-manage', {
                where: field
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }

                    if (res.code === 1) {
                        layer.msg(res.msg, { icon: 5 });
                    }
                }
            });
        });

        form.render(null, 'layadmin-userfront-formlist');

 

    });

</script>
@endsection