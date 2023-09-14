@extends('admin._layoutNew')
@section('page-head')
    <!--头部-->
    <style>
        .btn-group {
            top: -2px;
        }

        #newsAdd {
            float: left;
        }

        .cateManage {
            float: left;
        }

        .btn-search {
            left: -10px;
            position: relative;
            background: #e0e0e0;
        }

        #pull_right {
            text-align: center;
        }

        .pull-right {
            /*float: left!important;*/
        }

        .pagination {
            display: inline-block;
            padding-left: 0;
            margin: 20px 0;
            border-radius: 4px;
        }

        .pagination > li {
            display: inline;
        }

        .pagination > li > a,
        .pagination > li > span {
            position: relative;
            float: left;
            padding: 6px 12px;
            margin-left: -1px;
            line-height: 1.42857143;
            color: #428bca;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        .pagination > li:first-child > a,
        .pagination > li:first-child > span {
            margin-left: 0;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }

        .pagination > li:last-child > a,
        .pagination > li:last-child > span {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .pagination > li > a:hover,
        .pagination > li > span:hover,
        .pagination > li > a:focus,
        .pagination > li > span:focus {
            color: #2a6496;
            background-color: #eee;
            border-color: #ddd;
        }

        .pagination > .active > a,
        .pagination > .active > span,
        .pagination > .active > a:hover,
        .pagination > .active > span:hover,
        .pagination > .active > a:focus,
        .pagination > .active > span:focus {
            z-index: 2;
            color: #fff;
            cursor: default;
            background-color: #428bca;
            border-color: #428bca;
        }

        .pagination > .disabled > span,
        .pagination > .disabled > span:hover,
        .pagination > .disabled > span:focus,
        .pagination > .disabled > a,
        .pagination > .disabled > a:hover,
        .pagination > .disabled > a:focus {
            color: #777;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #ddd;
        }

        .clear {
            clear: both;
        }

    </style>
@endsection

@section('page-content')
    <div style="">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">订单类型</label>
                <div class="layui-input-inline">
                    <select name="type" class="layui-input">
                        <option value="buy" selectde>买入单</option>
                        <option value="sell">卖出单</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">订单状态</label>
                <div class="layui-input-inline">
                    <select name="status" class="layui-input">
                        <option value="1" selectde>已付款</option>
                        <option value="0">待付款</option>
                        <option value="2">已完成</option>
                        <option value="-1">已关闭</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i
                                class="layui-icon">&#xe615;</i></button>
                </div>
            </div>


        </form>
    </div>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">

        @{{((d.type=='buy' && d.status==1)||(d.type=='sell' && d.status==0)) ?'<a class="layui-btn layui-btn-xs" lay-event="confirm">立即结单</a>':''}}
        @{{d.status==0 ?'<a class="layui-btn layui-btn-xs" lay-event="cancel">关闭</a>':''}}
        @{{d.type=='sell' ?'<a class="layui-btn layui-btn-xs" lay-event="info">查看付款详情</a>':''}}
        <a class="layui-btn layui-btn-xs" lay-event="delete">删除</a>
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'已付款'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'购买完成'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-black">'+'待付款'+'</span>' : '' }}
        @{{d.status==-1 ? '<span class="layui-badge layui-bg-black">'+'已取消'+'</span>' : '' }}

    </script>
    <script type="text/html" id="nametml">
        <span>@{{  d.user_info?d.user_info.userreal_name:'-' }}</span>
    </script>
@endsection

@section('scripts')
    <script type="text/javascript">


        layui.use(['element', 'form', 'table', 'layedit', 'laypage', 'layer'], function () {
            var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit,
                laypage = layui.laypage;

            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                , url: "{{url('admin/legal/order')}}" //数据接口
                , page: true //开启分页
                , id: 'mobileSearch'
                , cols: [[ //表头
                    {field: 'id', title: 'ID', width: 80, sort: true}
                    , {field: 'type', title: '类型', minWidth: 80,templet:function(obj,row){
                            if(obj.type=='sell')
                            {
                                return `<span class="layui-badge layui-bg-blue">提现</span>`;
                            }else{
                                return `<span class="layui-badge layui-bg-orange">充值</span>`;
                            }
                        }}
                    , {field: 'account_number', title: '用户名', width: 120}
                    , {field: 'user_info', title: '真实姓名', width: 120, templet: '#nametml'}
                    , {
                        field: 'rate', title: '汇率', width: 60
                    }
                    , {field: 'usdt_amount', title: 'USDT数量', minWidth: 110,templet(obj){
                        return parseFloat(obj.usdt_amount).toFixed(2);
                        }}

                    , {field: 'amount', title: '金额￥', minWidth: 110}
                    , {field: 'status', title: '状态', minWidth: 80, templet: '#statustml'}
                    , {field: 'url', title: '付款截图', minWidth: 180,templet:function(obj,row){
                        return obj.url?`<img onclick=showBig('${obj.url}') style="width:180px;" src='${obj.url}'>`:'未上传'
                        }}
                    , {field: 'pay_way', title: '支付方式', minWidth: 180,templet:function(obj,row){
                            switch(obj.pay_way)
                            {
                                case "alipay":
                                    return '支付宝';
                                case "wechat":
                                    return '微信';
                                case "bank":
                                    return '银行转账';
                            }
                        }}
                    , {field: 'created_at', title: '购买时间', minWidth: 180}
                    , {title: '操作', minWidth: 120, toolbar: '#barDemo'}

                ]]
            });


            $(document).off('mousedown','.layui-table-grid-down').on('mousedown','.layui-table-grid-down',function(event){
                table._tableTrCurr = $(this).closest('td');
            });

            $(document).off('click','.layui-table-tips-main [lay-event]').on('click','.layui-table-tips-main [lay-event]',function(event){
                var elem = $(this);
                var tableTrCurr = table._tableTrCurr;
                if(!tableTrCurr){
                    return;
                }
                var layerIndex = elem.closest('.layui-table-tips').attr('times');
                // 关闭当前这个显示更多的tip
                layer.close(layerIndex);
                table._tableTrCurr.find('[lay-event="' + elem.attr('lay-event') + '"]').first().click();
            });


            table.on('tool(test)', function (obj) {
                var data = obj.data;
                if (obj.event === 'delete') {
                    layer.confirm('真的删除行么', function (index) {
                        $.ajax({
                            url: '{{url('admin/legal/delete')}}',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if (obj.event === 'confirm') {
                    layer.confirm('确定结单吗？', function (index) {
                        layer.load(2);
                        $.post('/admin/legal/confirm', {id: data.id}, function (res) {
                            layer.closeAll('loading');
                            res = JSON.parse(res);

                            layer.close(index);
                            if (res.code > 0) {
                                layer.msg('已结单');
                                $(".layui-laypage-btn")[0].click();
                            } else {
                                layer.msg(res.msg);
                            }
                        });
                    });
                } else if (obj.event === 'cancel') {
                    layer.confirm('确定关闭该订单吗？', function (index) {
                        layer.load(2);
                        $.post('/admin/legal/cancel', {id: data.id}, function (res) {
                            layer.closeAll('loading');
                            res = JSON.parse(res);
                            layer.close(index);
                            if (res.code > 0) {
                                layer.msg('已取消');
                                $(".layui-laypage-btn")[0].click();
                            } else {
                                layer.msg(res.msg);
                            }
                        });
                    })
                } else if(obj.event==='info')
                {
                    layer.open({
                        type:1,
                        area:['640px','480px'],
                        title:'提款银行卡详情',
                        'content':`<div style="text-align: center; font-size:26px; line-height:45px;"><h3>${ data.cash_info.bank_name+data.cash_info.bank_branch } </h3></div>
                                    <div  style="text-align: center; font-size:24px; line-height:45px;"><h4>${ data.cash_info.bank_account } </h4></div>
                                    <div  style="text-align: center; font-size:24px; line-height:45px;"><h4>${ data.cash_info.real_name } </h4></div>
`
                    })
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function (data) {
                var account_number = data.field.account_number;
                table.reload('mobileSearch', {
                    where: {account_number: account_number, status: data.field.status,type:data.field.type},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

            $('#newsAdd').click(function () {


                var index = window.layer.open({
                    title: '添加针'
                    , type: 2
                    , content: '/admin/needle/add'
                    , area: ['800px', '600px']
                    , maxmin: true
                    , anim: 3
                });
                layer.full(index);

            });

            $('.newsConfirm').on('click', function () {

                let id = $(this).attr('data-id');
                let index = layer.confirm('确认结单吗？', () => {
                    layer.close(index);
                    layer.load(2);
                    $.post('/admin/legal/confirm', {id: id}, function (res) {
                        layer.closeAll('loading');
                        res = JSON.parse(res);
                        if (res.code > 0) {
                            layer.msg('已结单');
                            window.location.reload();
                        } else {
                            layer.msg(res.msg);
                        }
                    });
                });

            })

            $('.newsCancel').on('click', function () {

                let id = $(this).attr('data-id');
                let index = layer.confirm('确认取消订单吗？', () => {
                    layer.close(index);
                    layer.load(2);
                    $.post('/admin/legal/cancel', {id: id}, function (res) {
                        layer.closeAll('loading');
                        res = JSON.parse(res);
                        if (res.code > 0) {
                            layer.msg('已取消');
                            window.location.reload();
                        } else {
                            layer.msg(res.msg);
                        }
                    });
                });

            })

            $('.newsDel').click(function () {
                let id = $(this).attr('data-id');

                layer.confirm('确定删除吗？该操作不可逆', function () {
                    layer.load(2);
                    $.post('/admin/legal/delete', {id: id}, function (res) {
                        layer.closeAll('loading');
                        res = JSON.parse(res);
                        if (res.code > 0) {
                            layer.msg('已删除');
                            window.location.reload();
                        } else {
                            layer.msg(res.msg);
                        }
                    });
                })

            });


        });

        function showBig(url)
        {
            layer.photos({photos: {"data": [{"src": url}]}});
        }
    </script>
@endsection
