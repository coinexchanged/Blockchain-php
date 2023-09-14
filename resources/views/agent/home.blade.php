
@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
<div class="layui-fluid">
  <div class="layui-row layui-col-space15">
     
        <div class="layui-col-sm6 layui-col-md4">
          <div class="layui-card">
            <div class="layui-card-header">
              杠杆结算订单量
              <span class="layui-badge layui-bg-blue layuiadmin-badge">总计</span>
            </div>
            <div class="layui-card-body layuiadmin-card-list">
              <p class="layuiadmin-big-font">{{$settlement }}</p>
             
            </div>
          </div>
        </div>
        
        <div class="layui-col-sm6 layui-col-md4">
          <div class="layui-card">
            <div class="layui-card-header">
              下级代理商个数
              <span class="layui-badge layui-bg-cyan layuiadmin-badge">总计</span>
            </div>
            <div class="layui-card-body layuiadmin-card-list">
              <p class="layuiadmin-big-font">{{$subordinate_agent_num }}</p>
              
            </div>
          </div>
        </div>
        <div class="layui-col-sm6 layui-col-md4">
          <div class="layui-card">
            <div class="layui-card-header">
              伞下会员总数
              <span class="layui-badge layui-bg-green layuiadmin-badge">总计</span>
            </div>
            <div class="layui-card-body layuiadmin-card-list">
              <p class="layuiadmin-big-font">{{$subordinate_user_num }}</p>
             
            </div>
          </div>
        </div>
 
    <div class="layui-col-sm12">
      <div class="layui-card">
        <div class="layui-card-header">
          日订单量
        </div>
        <div class="layui-card-body">
          <div class="layui-row">
            <div class="layui-col-sm12">
              <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-pagetwo">
                <div carousel-item id="LAY-index-pagetwo">
                  <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>




  </div>
</div>
@endsection
@section('scripts')
<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/js/jquery.qrcode.min.js"></script>
<script src="/js/qrcode.js"></script>

<script>

        layui.use(['index','layer', 'laydate', 'form'], function () {
            var $ = layui.$
                , admin = layui.admin
                , laydate = layui.laydate
                , form = layui.form;

              $.ajax({
                  url: '/agent/day',
                  type: 'post',
                  dataType: "json",
                  data: {type: 'all'},
                  success: function(res) {
                    //console.log('aa');
                   // console.log(res);
                    show_table(res.data);
                  },
                  error: function() {
                      layer.msg('网络错误');
                  }
              });

        });

        function show_table(returnData) {
            //区块轮播切换
            layui.use(['carousel','admin'], function () {
                var $ = layui.$
                    , admin = layui.admin
                    , carousel = layui.carousel
                    , element = layui.element
                    , device = layui.device();

                //轮播切换
                $('.layadmin-carousel').each(function () {
                    var othis = $(this);
                    carousel.render({
                        elem: this
                        , width: '100%'
                        , arrow: 'none'
                        , interval: othis.data('interval')
                        , autoplay: othis.data('autoplay') === true
                        , trigger: (device.ios || device.android) ? 'click' : 'hover'
                        , anim: othis.data('anim')
                    });
                });

            });
            //柱状图
            layui.use(['echarts'], function () {
                var $ = layui.$
                    , echarts = layui.echarts;

                //标准柱状图
                var echnormcol = [], normcol = [
                    {

                        xAxis: {
                            type: 'category',
                            data: returnData.day
                        },
                        yAxis: {
                            type: 'value'
                        },
                        color :["#c23531"],
                        series: [{
                            data: returnData.info,
                            type: 'bar'
                        }]
                    }
                ]
                    , elemNormcol = $('#LAY-index-pagetwo').children('div')
                    , renderNormcol = function (index) {
                    echnormcol[index] = echarts.init(elemNormcol[index], layui.echartsTheme);
                    echnormcol[index].setOption(normcol[index]);
                    window.onresize = echnormcol[index].resize;
                };
                if (!elemNormcol[0]) return;
                renderNormcol(0);

            });
        }
    





</script>
@endsection