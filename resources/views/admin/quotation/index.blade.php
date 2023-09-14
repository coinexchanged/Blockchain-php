@extends('admin._layoutNew')
@section('page-head')
    <script src="https://lib.baomitu.com/echarts/5.0.2/echarts.min.js"></script>
@endsection

@section('page-content')

    <form id="form">
        <div class="layui-row">
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">币种</label>
                    <div class="layui-input-block">
                        <select id="currency" class="layui-input" name="currency" >
                            @foreach($currencys as $currency)
                                <option value="{{$currency['currency_name']}}" {{($data?$data->base:'IEPN')==$currency['currency_name']?'selected':''}}> {{$currency['currency_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">上涨比例</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="rate1" name="rate1" lay-verify="required" placeholder="下跌比例"
                               value="70" type="text">
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">下跌比例</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="rate2" name="rate2" lay-verify="required" placeholder="下跌比例"
                               value="60" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">插入日期</label>
                    <div class="layui-input-block">
                        <input class="layui-input itime" id="dates" name="dates" lay-verify="required"
                               placeholder="请选择时间" value="{{$data?substr($data->itime,0,10):date('Y-m-d')}}"
                               type="text">
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">插入时间</label>
                    <div class="layui-input-block">
                        <select class="layui-input" name="times" id="times" lay-verify="required">
                           @for($i=0;$i<=23;$i++)
                                <option {{ $i== intval($data?substr($data->itime,11,2):date('H'))?'selected':'' }} value="{{$i}}">{{$i}}:00</option>
                            @endfor

                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">

                <div class="layui-form-item">
                    <label class="layui-form-label">增长精度</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="jingdu" name="jingdu" lay-verify="required" placeholder="增长精度"
                               value="0.00001" type="text">
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-row">

            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">起始价格</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="start" name="start" lay-verify="required" placeholder="起始价格"
                               value="{{$data?$data->close:0.035}}" type="text">
                    </div>
                </div>
            </div>
            <div class=" layui-col-md4 layui-col-xs4 layui-col-sm4">
                <div class="layui-form-item">
                    <label class="layui-form-label">上涨幅度</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="speed" name="speed" lay-verify="required" placeholder="上涨幅度"
                               value="0.05" type="text">
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">

                <div class="layui-form-item">
                    <label class="layui-form-label">涨跌浮度</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="fudu" name="fudu" lay-verify="required" placeholder="增长精度"
                               value="0.000001" type="text">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="layui-row">
        <button type="button" onclick="previewKLine()" class="layui-btn">预生成K线</button>
        <button type="button" onclick="nextKline()" class="layui-btn">保存并生成下一个时间段的数据</button>
    </div>


    <div style="width:100%; height: 768px;" id="echarts"></div>

@endsection

@section('scripts')
    <script type="text/javascript">

        let layer;
        let form;
        let element;
        layui.use(['element','laydate', 'layer','form'], () => {
            var laydate = layui.laydate;
            layer = layui.layer;
            form=layui.form;
            element =layui.element;
            laydate.render({
                elem: '#dates',
                type: 'date'
            });
        })

        let data = [];

        function previewKLine() {


            var upColor = '#ec0000';
            var upBorderColor = '#8A0000';
            var downColor = '#00da3c';
            var downBorderColor = '#008F28';

            var dataCount = 60;
            var myChart = echarts.init(document.getElementById('echarts'));


            $.getJSON('/test.php', $('#form').serialize(), res => {
                console.log(res);
                data = res;

                var option = {
                    dataset: {
                        source: data.data
                    },
                    title: {
                        text: 'Data Amount: ' + echarts.format.addCommas(dataCount)
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'line'
                        }
                    },
                    toolbox: {
                        feature: {
                            dataZoom: {
                                yAxisIndex: false
                            },
                        }
                    },
                    grid: [
                        {
                            left: '10%',
                            right: '10%',
                            bottom: 200
                        },
                        {
                            left: '10%',
                            right: '10%',
                            height: 80,
                            bottom: 80
                        }
                    ],
                    xAxis: [
                        {
                            type: 'category',
                            scale: true,
                            boundaryGap: false,
                            // inverse: true,
                            axisLine: {onZero: false},
                            splitLine: {show: false},
                            splitNumber: 20,
                            min: 'dataMin',
                            max: 'dataMax'
                        },
                        {
                            type: 'category',
                            gridIndex: 1,
                            scale: true,
                            boundaryGap: false,
                            axisLine: {onZero: false},
                            axisTick: {show: false},
                            splitLine: {show: false},
                            axisLabel: {show: false},
                            splitNumber: 20,
                            min: 'dataMin',
                            max: 'dataMax'
                        }
                    ],
                    yAxis: [
                        {
                            scale: true,
                            splitArea: {
                                show: true
                            }
                        },
                        {
                            scale: true,
                            gridIndex: 1,
                            splitNumber: 2,
                            axisLabel: {show: false},
                            axisLine: {show: false},
                            axisTick: {show: false},
                            splitLine: {show: false}
                        }
                    ],
                    dataZoom: [
                        {
                            type: 'inside',
                            xAxisIndex: [0, 1],
                            start: 10,
                            end: 100
                        },
                        {
                            show: true,
                            xAxisIndex: [0, 1],
                            type: 'slider',
                            bottom: 10,
                            start: 10,
                            end: 100
                        }
                    ],
                    visualMap: {
                        show: false,
                        seriesIndex: 1,
                        dimension: 6,
                        pieces: [{
                            value: 1,
                            color: upColor
                        }, {
                            value: -1,
                            color: downColor
                        }]
                    },
                    series: [
                        {
                            type: 'candlestick',
                            itemStyle: {
                                color: upColor,
                                color0: downColor,
                                borderColor: upBorderColor,
                                borderColor0: downBorderColor
                            },
                            encode: {
                                x: 0,
                                y: [1, 4, 3, 2]
                            }
                        },
                        {
                            name: 'Volumn',
                            type: 'bar',
                            xAxisIndex: 1,
                            yAxisIndex: 1,
                            itemStyle: {
                                color: '#7fbe9e'
                            },
                            large: true,
                            encode: {
                                x: 0,
                                y: 5
                            }
                        }
                    ]
                };

                myChart.setOption(option);
            })


        }

        function nextKline() {

            if ($('#dates').val() === '') {
                layer.msg('请选择日期');
                return;
            }
            if (data.length === 0) {
                layer.msg('请先生成k线');
                return;
            }

            let con = JSON.stringify(data.data);
            layer.load(2);
            $.post('/admin/user/quotation', {
                kline: con,
                date: ($('#dates').val() + ' ' + $('#times').val() + ":00:00"),
                currency:$('#currency').val()
            }, res => {
                console.log(res);
                layer.closeAll('loading');
                $('#start').val(data.data[data.data.length - 1][4]);
                if (parseInt($('#times').val()) < 23) {
                    $('#times').val(parseInt($('#times').val()) + 1);
                } else {
                    $('#times').val(0);
                    $('#dates').val(data.next)
                }
                previewKLine();
            });


        }

        function generateOHLC(count) {
            var data = [];

            var xValue = +new Date(2011, 0, 1);
            var minute = 60 * 1000;
            var baseValue = Math.random() * 12000;
            var boxVals = new Array(4);
            var dayRange = 12;

            for (var i = 0; i < count; i++) {
                baseValue = baseValue + Math.random() * 20 - 10;

                for (var j = 0; j < 4; j++) {
                    boxVals[j] = (Math.random() - 0.5) * dayRange + baseValue;
                }
                boxVals.sort();

                var openIdx = Math.round(Math.random() * 3);
                var closeIdx = Math.round(Math.random() * 2);
                if (closeIdx === openIdx) {
                    closeIdx++;
                }
                var volumn = boxVals[3] * (1000 + Math.random() * 500);

                // ['open', 'close', 'lowest', 'highest', 'volumn']
                // [1, 4, 3, 2]
                data[i] = [
                    echarts.format.formatTime('yyyy-MM-dd\nhh:mm:ss', xValue += minute),
                    +boxVals[openIdx].toFixed(2), // open
                    +boxVals[3].toFixed(2), // highest
                    +boxVals[0].toFixed(2), // lowest
                    +boxVals[closeIdx].toFixed(2),  // close
                    volumn.toFixed(0),
                    getSign(data, i, +boxVals[openIdx], +boxVals[closeIdx], 4) // sign
                ];
            }

            return data;

            function getSign(data, dataIndex, openVal, closeVal, closeDimIdx) {
                var sign;
                if (openVal > closeVal) {
                    sign = -1;
                } else if (openVal < closeVal) {
                    sign = 1;
                } else {
                    sign = dataIndex > 0
                        // If close === open, compare with close of last record
                        ? (data[dataIndex - 1][closeDimIdx] <= closeVal ? 1 : -1)
                        // No record of previous, set to be positive
                        : 1;
                }

                return sign;
            }
        }

    </script>
@endsection
