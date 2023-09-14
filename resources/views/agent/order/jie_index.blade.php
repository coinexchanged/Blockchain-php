@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto"
                 lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">代理商</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="belong_agent" >
                                <option value="" >全部</option>
                                @foreach ($son_agents as $son)
                                    <option value="{{$son->username}}">{{$son->username}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结算类型</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="type">
                                <option value="-1" class="ww">全部</option>
                                
                                <option value="1" class="ww">头寸</option>
                                <option value="2" class="ww">手续费</option>
                               
                            </select>
                        </div>
                    </div>
                    
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">法币</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="legal_id" id="type_type">
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="start_time" value="" name="start">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="end_time" value="" name="end">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>

                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="export">导出表格</button>


                        @if($is_admin== 1 )
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="dojie">结算(对账)</button>
                        @endif

                    </div>

                </div>



            </div>
            <div class="layui-card-body">

                <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
                <script type="text/html" id="table-useradmin-webuser">
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="info">查看关联订单</a>
                    @{{# if (d.status == 0) { }}
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="money_out">提现到账</a>
                    @{{# } }}
                </script>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script type="text/html" id="lockTpl">
        @{{#  if(d.type == 1){ }}
        <span class="layui-badge layui-bg-red">头寸收益</span>
        @{{#  } else { }}
        <span class="layui-badge layui-bg-blue">手续费收益</span>
        @{{#  } }}
    </script>

    <script type="text/html" id="statusTpl">
        @{{#  if(d.status == 1){ }}
        <span class="layui-badge layui-bg-red">已提现</span>
        @{{#  } else { }}
        <span class="layui-badge layui-bg-blue">未提现</span>
        @{{#  } }}
    </script>
   

    <script>
        layui.use(['index','admin',  'table' , 'layer', 'laydate'], function(){
            var $ = layui.$
                ,admin = layui.admin
                ,view = layui.view
                ,table = layui.table
                , laydate = layui.laydate
                ,form = layui.form;
            //日期
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });

            //结算管理
            table.render({
                elem: '#LAY-user-manage'
                ,method : 'post'
                ,url: '/agent/jie/list'
                ,cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    ,{field: 'id', width: 60, title: 'ID', sort: true }
                    ,{field: 'jie_agent_name', title: '代理商', minWidth: 100}
                    ,{field: 'jie_agent_level', title: '代理商等级', minWidth: 100}
                    ,{field: 'user_name', title: '用户名', minWidth: 100}
                    //,{field: 'agent_level', title: '用户等级', width: 150}

                    ,{field: 'relate_id', title: '杠杆订单id', width: 100}
                    ,{field: 'type', title: '结算类型', width: 90, templet: '#lockTpl'}
                    ,{field: 'status', title: '是否到账', width: 90, templet: '#statusTpl'}
                    ,{field: 'legal_name', title: '结算币种', width: 100}
                    //,{field: 'before', title: '初始账户金额',sort: true, width: 170,style:"color: #fff;background-color: #01AAED;"}
                    ,{field: 'change', title: '结算收益', sort: true, width: 170,style:"color: #fff;background-color: #FF5722;"}
                    //,{field: 'after', title: '最终账户金额', sort: true, width: 170,style:"color: #fff;background-color: #01AAED;"}
                    ,{field: 'memo', title: '备注', width:150}

                    ,{title: '操作', width: 220, align:'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
                ]]
                ,page: true
                ,limit: 30
                ,height: 'full-320'
                ,text: '对不起，加载出现异常！'
                
                ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    if (res !== 0 ){
                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }
                    }
                }
            });


            form.render(null, 'layadmin-userfront-formlist');

            //监听搜索
            form.on('submit(LAY-user-front-search)', function(data){
                var field = data.field;

                //执行重载
                table.reload('LAY-user-manage', {
                    where: field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                    ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }

                        if (res.code === 1){
                            layer.msg(res.msg ,{icon : 5});
                        }
                    }
                });
            });

            //结算
            form.on('submit(dojie)', function(data){
                var field = data.field;
                //console.log(field);

                admin.req( {
                    type : "POST",
                    url : '/agent/dojie',
                    dataType : "json",
                    data : field,
                    done : function(result) {
                        //console.log(result);
                         //返回数据根据结果进行相应的处理
                        layer.msg(result.msg, {icon: 6 });
                    }
                });
            });

            form.on('submit(export)', function(data){

                var field = data.field;
               let param = Object.keys(field).map(function (key) {
                    // body...
                    return encodeURIComponent(key) + "=" + encodeURIComponent(field[key]);
                }).join("&");
                window.open('/agent/jie/export?'+param);

                // //执行重载
                // table.reload('LAY-user-manage', {
                //     where: field
                //     ,page: {
                //         curr: 1 //重新从第 1 页开始
                //     }
                //     ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                //
                //         if (res.code === 1001) {
                //             //清空本地记录的 token，并跳转到登入页
                //             admin.exit();
                //         }
                //
                //         if (res.code === 1){
                //             layer.msg(res.msg ,{icon : 5});
                //         }
                //     }
                // });
            });
                //console.log(field);

                // admin.req( {
                //     type : "POST",
                //     url : '/agent/dojie',
                //     dataType : "json",
                //     data : field,
                //     done : function(result) {
                //         //console.log(result);
                //         //返回数据根据结果进行相应的处理
                //         layer.msg(result.msg, {icon: 6 });
                //     }
                // });
            // });


            //监听工具条
            table.on('tool(LAY-user-manage)', function(obj) {
                    var data = obj.data;
                    if (obj.event === 'info') {
                            layer.open({
                                title: '查看订单详情'
                                , type: 2
                                , content: '{{url('/agent/order/info')}}?order_id=' + data.relate_id
                                // , maxmin: true
                                ,area: ['800px', '600px']
                            });

                    }else if(obj.event === 'money_out'){

                            //结算  提现到账  到用户的钱包
                            layer.confirm('确定代理商收益划转到账?', function(index) {
                            
                            layer.close(index);
                            $.ajax({
                                url: '/agent/wallet_out/done'
                                ,type: 'post'
                                ,dataType: 'json'
                                ,data:{id:data.id}
                                ,success: function(res) {
                                    console.log(res);
                                    layer.msg(res.msg ,{icon : 5});

                                    if (res.code ===0){
                                        layer.close(index);
                                        window.location.reload();
                                    }
                                    
                                }
                                
                            });
                        });
                        return false;

                    }
            });




        });
    </script>



@endsection