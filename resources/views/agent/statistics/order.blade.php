@extends('agent.layadmin')

@section('title', '订单统计')

@section('page-head')

@endsection

@section('page-content') 

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
              <!--<div class="layui-card-header">标准柱状图</div>-->
              <div class="layui-card-body">

                <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-normcol">
                  <div carousel-item id="LAY-index-normcol">
                    <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                  </div>
                </div>

              </div>
            </div>
        </div>
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-body">

                    <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-money">
                        <div carousel-item id="LAY-index-money">
                            <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-body">

                    <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-bin">
                        <div carousel-item id="LAY-index-bin">
                            <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
   
        layui.use(['index','admin',  'layer' , 'laydate' , 'form'], function(){
            var $ = layui.$
                ,admin = layui.admin
                ,laydate = layui.laydate
                ,form = layui.form;

            //日期
            laydate.render({
                elem: '#datestart'
            });
            laydate.render({
                elem: '#dateend'
            });

            //监听搜索
            // form.on('submit(LAY-user-front-search)', function(data){
            //     var field = data.field;
            //     field.type = 'search';

            //     admin.req( {
            //         type : "POST",
            //         url : '/agent/dojie',
            //         dataType : "json",
            //         data : field,
            //         done : function(result) { //返回数据根据结果进行相应的处理
            //             layui.layer.msg(result.msg, {icon: 6 });
            //         }
            //     });
            // });

            //订单图表
            admin.req( {
                type : "POST",
                url : '/agent/order',
                dataType : "json",
                data : {type : 'all'},
                done : function(result) { //返回数据根据结果进行相应的处理
                    //提交 Ajax 成功后，关闭当前弹层并重载图表
                    show_table(result.data);
                }
            });

            //订单数量饼状图
            admin.req( {
                type : "POST",
                url : '/agent/order_num',
                dataType : "json",
                data : {type : 'all'},
                done : function(result) { //返回数据根据结果进行相应的处理
                    //提交 Ajax 成功后，关闭当前弹层并重载图表
                    console.log(result);

                    show_bin(result.data);
                }
            });

            //订单金额饼状图
            admin.req( {
                type : "POST",
                url : '/agent/order_money',
                dataType : "json",
                data : {type : 'all'},
                done : function(result) { //返回数据根据结果进行相应的处理
                    //提交 Ajax 成功后，关闭当前弹层并重载图表
                    show_money(result.data);
                }
            });

        });

        function show_table (returnData){
            //区块轮播切换
            layui.use(['admin', 'carousel'], function(){
                var $ = layui.$
                    ,admin = layui.admin
                    ,carousel = layui.carousel
                    ,element = layui.element
                    ,device = layui.device();

                //轮播切换
                $('.layadmin-carousel').each(function(){
                    var othis = $(this);
                    carousel.render({
                        elem: this
                        ,width: '100%'
                        ,arrow: 'none'
                        ,interval: othis.data('interval')
                        ,autoplay: othis.data('autoplay') === true
                        ,trigger: (device.ios || device.android) ? 'click' : 'hover'
                        ,anim: othis.data('anim')
                    });
                });

            });
            //柱状图
            layui.use(['echarts'], function(){
                var $ = layui.$
                    ,echarts = layui.echarts;

                //标准柱状图
                var echnormcol = [], normcol = [
                    {
                        title : {
                            text: '最近52周订单图表',
                            subtext: '单位：个'
                        },
                        tooltip : {
                            trigger: 'axis'
                        },
                        legend: {
                            data:['已平仓','交易中']
                        },
                        calculable : true,
                        xAxis : [
                            {
                                type : 'category',
                                data :   returnData.xAxis //['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value'
                            }
                        ],
                        series : [
                            {
                                name:'已平仓',
                                type:'bar',
                                data: returnData.series, //[2.0, 4.9, 7.0, 23.2, 245.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3],
                                markPoint : {
                                    data : [
                                        {type : 'max', name: '最大值'},
                                        {type : 'min', name: '最小值'}
                                    ]
                                },
                                markLine : {
                                    data : [{type : 'average', name: '平均值'}]
                                }
                            },

                            {
                                name:'交易中',
                                type:'bar',
                                data: returnData.selling, //[2.0, 4.9, 7.0, 23.2, 245.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3],
                                markPoint : {
                                    data : [
                                        {type : 'max', name: '最大值'},
                                        {type : 'min', name: '最小值'}
                                    ]
                                },
                                markLine : {
                                    data : [{type : 'average', name: '平均值'}]
                                }
                            }
                        ]
                    }
                ]
                    ,elemNormcol = $('#LAY-index-normcol').children('div')
                    ,renderNormcol = function(index){
                    echnormcol[index] = echarts.init(elemNormcol[index], layui.echartsTheme);
                    echnormcol[index].setOption(normcol[index]);
                    window.onresize = echnormcol[index].resize;
                };
                if(!elemNormcol[0]) return;
                renderNormcol(0);

            });
        }



        function show_bin (returnData) {
            //区块轮播切换
            layui.use(['admin', 'carousel'], function(){
                var $ = layui.$
                    ,admin = layui.admin
                    ,carousel = layui.carousel
                    ,element = layui.element
                    ,device = layui.device();

                //轮播切换
                $('.layadmin-carousel').each(function(){
                    var othis = $(this);
                    carousel.render({
                        elem: this
                        ,width: '100%'
                        ,arrow: 'none'
                        ,interval: othis.data('interval')
                        ,autoplay: othis.data('autoplay') === true
                        ,trigger: (device.ios || device.android) ? 'click' : 'hover'
                        ,anim: othis.data('anim')
                    });
                });

            });
            //柱状图
            layui.use(['echarts'], function () {
                var $ = layui.$
                    , echarts = layui.echarts;

                var echheapcol = [], heapcol = [
                    {
                        title : {
                            text: '订单统计【订单数量】',
                            subtext: '单位：个',
                            x:'center'
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        color:['#c23531', '#d48265','#6ab0b8'],
                        legend: {
                            orient: 'vertical',
                            x: 'left',
                            data: ['交易中','平仓中','已平仓']
                        },
                        series : [
                            {
                                name: '访问来源',
                                type: 'pie',
                                radius : '55%',
                                center: ['50%', '60%'],
                                data:[
                                    {value:returnData.a, name:'交易中'},
                                    {value:returnData.b, name:'平仓中'},
                                    {value:returnData.c, name:'已平仓'}
                                ],
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    }
                ]
                    , elemHeapcol = $('#LAY-index-bin').children('div')
                    , renderHeapcol = function (index) {
                    echheapcol[index] = echarts.init(elemHeapcol[index], layui.echartsTheme);
                    echheapcol[index].setOption(heapcol[index]);
                    window.onresize = echheapcol[index].resize;
                };
                if (!elemHeapcol[0]) return;
                renderHeapcol(0);
            });
        }




        function show_money (returnData) {
            //区块轮播切换
            layui.use(['admin', 'carousel'], function(){
                var $ = layui.$
                    ,admin = layui.admin
                    ,carousel = layui.carousel
                    ,element = layui.element
                    ,device = layui.device();

                //轮播切换
                $('.layadmin-carousel').each(function(){
                    var othis = $(this);
                    carousel.render({
                        elem: this
                        ,width: '100%'
                        ,arrow: 'none'
                        ,interval: othis.data('interval')
                        ,autoplay: othis.data('autoplay') === true
                        ,trigger: (device.ios || device.android) ? 'click' : 'hover'
                        ,anim: othis.data('anim')
                    });
                });

            });
            //柱状图
            layui.use(['echarts'], function () {
                var $ = layui.$
                    , echarts = layui.echarts;

                var echheapcol = [], heapcol = [
                    {
                        title : {
                            text: '团队总订单【订单金额】',
                            subtext: '单位：元',
                            x:'center'
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        color:['#c23531', '#d48265','#6ab0b8'],
                        legend: {
                            orient: 'vertical',
                            x: 'left',
                            data: ['交易中','平仓中','已平仓']
                        },
                        series : [
                            {
                                name: '访问来源',
                                type: 'pie',
                                radius : '55%',
                                center: ['50%', '60%'],
                                data:[
                                    {value:returnData.a, name:'交易中'},
                                    {value:returnData.b, name:'平仓中'},
                                    {value:returnData.c, name:'已平仓'}
                                ],
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    }
                ]
                    , elemHeapcol = $('#LAY-index-money').children('div')
                    , renderHeapcol = function (index) {
                    echheapcol[index] = echarts.init(elemHeapcol[index], layui.echartsTheme);
                    echheapcol[index].setOption(heapcol[index]);
                    window.onresize = echheapcol[index].resize;
                };
                if (!elemHeapcol[0]) return;
                renderHeapcol(0);
            });
        }


    

</script>
@endsection