@extends('agent.layadmin')

@section('page-head')
    <style>
        .layui-form-label {
            width: 120px;
        }

        .layui-form-mid {
            float: none;
            margin-left: 150px;
        }

        .layui-input-block {
            margin-left: 150px;
        }
    </style>
@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto"
                 lay-filter="layadmin-userfront-formlist">
            </div>
            <div class="layui-card-body">
                <div class="layui-form" lay-filter="layuiadmin-form-useradmin" style="padding: 20px 0 0 0;">
                    <div class="layui-form-item">
                        <label class="layui-form-label">用户名</label>
                        <div class="layui-input-block">

                            @if(isset($d['username']))
                                <input type="text" name="username" readonly value="{{$d['username']}}"
                                       lay-verify="required" placeholder="请输入用户名1" autocomplete="off"
                                       class="layui-input"
                                       readonly>
                            @else
                                <input type="text" name="username" readonly value=""
                                       lay-verify="required" placeholder="请输入用户名2" autocomplete="off"
                                       class="layui-input">
                            @endif

                            <input type="hidden" name="user_id" value="{{ isset($d['user_id'])?$d['user_id']:0 }}">
                            <input type="hidden" name="agent_id" value="{{ isset($d['agent_id'])?$d['agent_id']:0 }}">
                            <input type="hidden" name="id" value="{{ isset($d['id'])?$d['id']:0 }}">

                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">BTC钱包充值地址</label>
                        <div class="layui-input-block">
                            <script type="text/html" template>
                                <input type="text" name="btc_address" value="{{ isset($d['btc_address']) ? $d['btc_address'] : '' }}"
                                       lay-verify="btc_address" placeholder="BTC钱包充值地址" autocomplete="off"
                                       class="layui-input">
                            </script>
                        </div>
                        <script type="text/html" template>
                            <div class="layui-form-mid layui-word-aux">链上BTC钱包地址。
                            </div>
                        </script>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">USDT钱包充值地址</label>
                        <div class="layui-input-block">
                            <script type="text/html" template>
                                <input type="text" name="usdt_address" value="{{isset($d['usdt_address']) ? $d['usdt_address'] : ''}}"
                                       lay-verify="usdt_address" placeholder="USDT钱包充值地址" autocomplete="off" class="layui-input">
                            </script>
                        </div>
                        <script type="text/html" template>
                            <div class="layui-form-mid layui-word-aux">支持链上USDT_ERC20代币钱包。
                            </div>
                        </script>
                    </div>

                    @if(!isset($d['id']))
                    <!-- <div class="layui-form-item">
                        <label class="layui-form-label">授权码</label>
                        <div class="layui-input-block">
                            <script type="text/html" template>
                                <input type="text" name="authorization_code" value=""
                                       lay-verify="authorization_code" placeholder="请输入授权码" autocomplete="off" class="layui-input">
                            </script>
                        </div>
                        <script type="text/html" template>
                            <div class="layui-form-mid layui-word-aux">添加用户为代理商时需要填写用户的授权码(安全中心的授权码)
                            </div>
                        </script>
                    </div> -->
                    @endif

                    <div class="layui-form-item">
                        <label class="layui-form-label"></label>
                        <div class="layui-input-inline">
                            <input type="button" lay-submit lay-filter="LAY-user-front-submit" value="确认"
                                   class="layui-btn">
                        </div>
                    </div>



            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        layui.use(['index', 'form', 'upload', 'layer'], function () {
            var $ = layui.$
                , form = layui.form
                , upload = layui.upload
                , admin = layui.admin
                , view = layui.view
            var index = parent.layer.getFrameIndex(window.name)//当前ifarm索引
            //自定义验证
            form.verify({
                nickname: function (value, item) { //value：表单的值、item：表单的DOM对象
                    if (!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)) {
                        return '用户名不能有特殊字符';
                    }
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '用户名首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '用户名不能全为数字';
                    }
                }

                //我们既支持上述函数式的方式，也支持下述数组的形式
                //数组的两个值分别代表：[正则匹配、匹配不符时的提示文字]
                , pass: [
                    /^[\S]{6,12}$/
                    , '密码必须6到12位，且不能出现空格'
                ]
            });

            /*var jsObject = @json($d);
             form.val("layuiadmin-form-useradmin", jsObject)*/

            form.on('submit(LAY-user-front-submit)', function (data) {
                var field = data.field; //获取提交的字段
                console.log(field);
                var post_url = '/agent/salesmen/saveaddress';
                admin.req({
                    type: "POST",
                    url: post_url,
                    dataType: "json",
                    data: field,
                    done: function (result) { //返回数据根据结果进行相应的处理
                        layer.msg(result.msg, {
                                icon: 1,
                                time: 2000 //2秒关闭（如果不配置，默认是3秒）
                            }, function () {
                                parent.layer.close(index);
                                //parent.window.location.reload();
                                parent.layui.table.reload('LAY-user-manage' , {
                                    done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                                        if (res !== 0 ){
                                            if (res.code === 1001) {
                                                //清空本地记录的 token，并跳转到登入页
                                                admin.exit();
                                            }
                                        }
                                    }
                                }); //重载表格
                            }
                        );
                    }
                });
            });

        })
    </script>
@endsection

<div id="this_all_sons">
    <table id="LAY-user-sons" lay-filter="LAY-user-sons"></table>
</div>