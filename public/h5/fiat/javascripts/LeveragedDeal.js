var vue = new Vue({
    el: '#app',
    data: {
        List: [],
        name: '',
        id: '',
        twoNum: '1',
        has_address_num: '',
        leftData: [],
        multList: [],
        shareList: [],
        completeList: [],
        sellOut: [],
        buyOut: [],
        currencyId: get_param('id2') || '',
        legalId: get_param('id1') || '',
        more: '加载更多',
        nodataShow: false,
        symbols: get_param('symbol'),
        token: get_user_login(),
        leftShow: false,
        type: getlocal_storage('levertype') || 'buy',
        leverDatas: {
            resMsg: '',
            legalName: '',
            currencyName: '',
            legalId: '',
            currencyId: '',
            cny: '',
            ustdPrice: '',
            shareNum: '',
            transactionFee: '',
            spread: '',
            bond: '',
            tip: '',
            setValue: '',
            selectStatus: 1,
            minShare: '',
            maxShare: '',
            marketValue: '',
            bondTotal: '',
            transactionTotal: '',
            lastPrice: '',
            useLegalNum: '',
            useCurrencyNum: '',
            share: '',
            controlPrice: '',
            muitNum: '',
            rmbRate: ''
        },
        oneHandPrice: 0,
        modalShow: false,
        positionList: [],
        positionsData: {
            balance: '',
            hazardRate: '',
            profitsTotal: '',

        },
        page: 1,
        forms: {
            prices: '',
            ylValue: '',
            prices1: '',
            ksValue: ''
        },
        htmlStatus: localStorage.getItem('htmlStatus') || '',
        testValue: '最小交易量1',
        sumitBtnStatus: false,


    },
    watch: {
        leverDatas: {
            handler(data) {
                console.log('监听:', data.lastprice);
            },
            deep: true
        }
    },
    filters: {
        toFixeds: function (value) {
            var vals = iTofixed(value, 2)
            return vals
        },
        toFixed4: function (value) {
            var vals = iTofixed(value, 4)
            return vals
        },
        toFixed5: function (value) {
            var vals = iTofixed(value, 5)
            return vals

        }
    },
    mounted: function () {
        // FastClick.attach(document.body);
        let that = this;
        var leverData = getlocal_storage('lever');
        that.leverDatas.currencyId = get_param('id2') || '';
        that.leverDatas.currencyName = get_param('name2') || '';
        that.leverDatas.legalId = get_param('id1') || '';
        that.leverDatas.legalName = get_param('name1') || '';
        if (get_param('type') && get_param('type') == 2) {
            that.type = 'sell'
        } else {
            that.type = 'buy'
        }

        that.init();
        that.testValue = '最小交易量' + that.leverDatas.minShare;
    },
    methods: {
        init() {
            var that = this;
            //法币、币种数据请求（左侧内容）
            initDataTokens({
                url: 'currency/quotation_new'
            }, function (res) {
                if (res.type == 'ok') {
                    if (res.message.length > 0) {
                        that.leftData = res.message;
                        let datas = res.message;
                        var index1 = 0;
                        var index2 = 0;
                        var leverData = getlocal_storage('lever');
                        for (var i = 0; i < datas.length; i++) {
                            if (that.legalId == datas[i].id) {
                                index1 = i;
                            }
                            for (var j = 0; j < datas[i].quotation.length; j++) {
                                if (that.currencyId == datas[i].quotation[j].currency_id) {
                                    index2 = j;
                                }
                            }
                        }
                        // 初始化法币、币种渲染
                        if (get_param('id1')) {
                            that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num, 2);
                            that.leverDatas.spread = datas[index1].quotation[index2].spread;
                            that.leverDatas.transactionFee = datas[index1].quotation[index2].lever_trade_fee;
                        } else {
                            if (leverData) {
                                that.leverDatas.currencyId = leverData.currency_id;
                                that.leverDatas.currencyName = leverData.currency_name;
                                that.leverDatas.legalId = leverData.legal_id;
                                that.leverDatas.legalName = leverData.legal_name;
                                if (!leverData.share_num) {
                                    that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num, 2);
                                } else {
                                    that.leverDatas.shareNum = leverData.share_num;
                                }
                                if (!leverData.spread) {
                                    that.leverDatas.spread = datas[index1].quotation[index2].spread;
                                    ;
                                } else {
                                    that.leverDatas.spread = leverData.spread;
                                }
                                if (!leverData.transactionFee) {
                                    that.leverDatas.transactionFee = datas[index1].quotation[index2].transactionFee;
                                } else {
                                    that.leverDatas.transactionFee = leverData.transactionFee;
                                }
                            } else {
                                that.leverDatas.currencyId = datas[index1].quotation[index2].currency_id;
                                that.leverDatas.currencyName = datas[index1].quotation[index2].currency_name;
                                that.leverDatas.legalId = datas[index1].quotation[index2].legal_id;
                                that.leverDatas.legalName = datas[index1].quotation[index2].legal_name;
                                that.leverDatas.shareNum = iTofixed(datas[index1].quotation[index2].lever_share_num, 2);
                                that.leverDatas.spread = datas[index1].quotation[index2].spread;
                                that.leverDatas.transactionFee = datas[index1].quotation[index2].transactionFee;
                            }
                        }
                        that.leverDatas.rmbRate = datas[index1].quotation[index2].rmb_relation;
                        that.testValue = '最小交易量' + that.leverDatas.minShare;
                        //交易数据请求
                        that.get_lever_data();
                        that.scoket();
                        that.upPrice();
                        that.marsketLists();

                        setTimeout(() => {
                            console.log('init_data:', that.leverDatas);
                            let bond = iTofixed(that.leverDatas.lastprice, 4);
                            console.log('init:bond', bond);
                            let share = iTofixed(that.leverDatas.share, 4);
                            // let muitNum = iTofixed(that.leverDatas.muitNum,4);
                            let spread = iTofixed(that.leverDatas.spread, 4);

                            console.log('init:spread', spread);
                            // console.log(bond,share,spread);
                            let pricesTotal = 0;
                            if (that.type == 'sell') {
                                pricesTotal = iTofixed(bond - spread, 4);
                            } else {
                                pricesTotal = iTofixed(Number(bond) + Number(spread), 4);
                            }

                            console.log('init:pricesTotal', pricesTotal);
                            // alert(bond);

                            let shareNum = 1;
                            let totalPrice = iTofixed(pricesTotal * 1, 4);
                            console.log('init:totalPrice', totalPrice);
                            that.oneHandPrice = iTofixed(totalPrice * iTofixed($('.share-num').text(), 4), 4);
                            console.log(that.oneHandPrice);
                            // alert(that.oneHandPrice);
                        }, 1000);


                    }
                }
            });
        },
        get_lever_data() {
            let that = this;
            if (that.type == 'sell') {
                setTimeout(() => {
                    $('.sell').trigger('click')
                }, 50);
                $('.num span:nth-child(2)').add('active');
            } else {
                $('.num span:nth-child(2)').add('actives');
                setTimeout(() => {
                    $('.buy').trigger('click')
                }, 50);

            }
            initDataTokens({
                url: 'lever/deal',
                data: {
                    legal_id: that.leverDatas.legalId,
                    currency_id: that.leverDatas.currencyId
                },
                type: 'post'
            }, function (res) {
                console.log(res);
                that.leverDatas.marketValue = 0.000;
                that.leverDatas.bondTotal = 0.000;
                that.leverDatas.transactionTotal = 0.000;
                if (res.type == "ok") {
                    that.leverDatas.tip = res.message.lever_burst_hazard_rate;
                    that.multList = res.message.multiple.muit;
                    that.leverDatas.minShare = res.message.lever_share_limit.min;
                    that.leverDatas.maxShare = res.message.lever_share_limit.max;
                    that.shareList = res.message.multiple.share;
                    that.completeList = res.message.my_transaction;
                    that.testValue = '最小交易量' + that.leverDatas.minShare;
                    var sellList = res.message.lever_transaction.out.reverse();
                    var arr1 = [];
                    var arr = [];
                    for (i in sellList) {
                        arr = [];
                        arr[0] = sellList[i].price;
                        arr[1] = sellList[i].number;
                        arr1.push(arr);
                    }
                    var buyList = res.message.lever_transaction.in;
                    var arr2 = [];
                    var arr3 = [];
                    for (let i in buyList) {
                        arr3 = [];
                        arr3[0] = buyList[i].price;
                        arr3[1] = buyList[i].number;
                        arr2.push(arr3);
                    }
                    that.buyOut = arr2;
                    that.sellOut = arr1;
                    that.leverDatas.cny = res.message.ExRAte - 0 || 1;
                    that.leverDatas.ustdPrice = res.message.ustd_price;
                    that.leverDatas.lastprice = res.message.last_price;
                    that.leverDatas.useLegalNum = res.message.user_lever;
                    that.leverDatas.useCurrencyNum = res.message.all_levers;
                    that.leverDatas.muitNum = that.multList[0].value;
                } else {
                    layer_msg(res.message)
                }
            });
        },
        leftShows() {
            let that = this;
            $('body').css('overflow', 'hidden');
            $('#Limited').hide();
            $('#mask1').show();
            $('#sideColumn').animate({
                left: '0px'
            }, 10);
        },
        closeLeft() {
            $('body').css('overflow', 'auto');
            $('#Limited').show();
            $('#mask1').hide();
            $('#sideColumn').css('left', '-80%');
        },
        //socket连接封装
        scoket() {
            let that = this;
            $.ajax({
                url: _API + "user/info",
                type: "GET",
                dataType: "json",
                async: true,
                beforeSend: function beforeSend(request) {
                    request.setRequestHeader("Authorization", that.token);
                },
                success: function success(data) {
                    if (data.type == 'ok') {
                        var socket = io(socket_api);
                        socket.emit('login', data.message.id);
                        // 后端推送来消息时
                        socket.on('market_depth', function (msg) {
                            if (msg.type == 'market_depth') {
                                if (that.leverDatas.legalId == msg.legal_id && that.leverDatas.currencyId == msg.currency_id) {
                                    // var buyIn = JSON.parse(msg.bids);
                                    // var out = JSON.parse(msg.asks).reverse();
                                    var buyIn = msg.bids;
                                    var out = msg.asks;
                                    that.sellOut = out;
                                    that.buyOut = buyIn;
                                    // for (var i = 0; i < out.length; i++) {
                                    // 	that.sellOut[i] = out[i];
                                    // }
                                    // for (var i = 0; i < buyIn.length; i++) {
                                    // 	that.buyOut[i] = buyIn[i];
                                    // }
                                }
                            }

                        })
                    }
                }
            });
        },
        // 买入/卖出
        submitBtn() {
            var that = this;
            var textValue = /^[1-9]*[0-9][0-9]*$/;
            if (that.leverDatas.share == '') {
                layer_msg(getlg('pthands'));
                return false;
            } else if (!textValue.test(that.leverDatas.share)) {
                layer_msg(getlg('pznum'));
                return false;
            } else if ((that.leverDatas.share - 0) > (that.leverDatas.maxShare - 0)) {
                layer_msg(getlg('pnomore') + that.leverDatas.maxShare);
                return false;
            } else if ((that.leverDatas.share - 0) < (that.leverDatas.minShare - 0)) {
                layer_msg(getlg('pnoless') + that.leverDatas.minShare);
                return false;
            }
            if (that.leverDatas.selectStatus == 0) {
                if ($('.control input').val() == '') {
                    layer_msg(getlg('ptprice'));
                    return false;
                }
            }
            if (that.leverDatas.legalId != '' && that.leverDatas.currencyId) {
                var data = {
                    share: that.leverDatas.share,
                    multiple: that.leverDatas.muitNum,
                    legal_id: that.leverDatas.legalId,
                    currency_id: that.leverDatas.currencyId,
                    type: that.type == 'buy' ? 1 : 2,
                    status: that.leverDatas.selectStatus,
                    target_price: that.leverDatas.controlPrice,
                }
            } else {
                layer_msg(getlg('pchange'))
            }
            that.sumitBtnStatus = true;
            // that.modalShow = true;
            // '<ul class="comfirm-modal"><li><p class="name"></p></li><li><p>' + getlg('ttype') + '</p><p class="type"></p></li><li><p>' + getlg('hands') + '</p><p class="share"></p></li><li><p>' + getlg('multiple') + '</p><p class="muit"></p></li><li><p>' + getlg('bond') + '</p><p class="bondPrice"></p></li></ul>'
            layer.open({
                type: 1,
                title: false,
                shadeClose: true,
                area: ['90%', 'auto'],
                skin: 'confirm-modal btn-text',
                content: $('.modal-submit'),
                btn: [getlg('ceil'), getlg('sure')],
                btn2: function (index) {
                    initDataTokens({
                        url: 'lever/submit',
                        type: 'post',
                        data: data
                    }, function (res) {
                        layer_msg(res.message);
                        if (res.type == 'ok') {
                            location.href = 'leverList.html';

                        }
                    })


                },
                success: function () {
                }
            })


        },
        // 平仓
        sellLoss(ids) {
            layer.open({
                type: 1,
                title: false,
                shadeClose: true,
                skin: 'loads-btn btn-text',
                area: ['70%', 'auto'],
                content: getlg('sureClose'),
                btn: [getlg('ceil'), getlg('sure')],
                btn2: function (index) {
                    initDataTokens({
                        url: 'lever/close',
                        type: 'post',
                        data: {
                            id: ids
                        }
                    }, function (res) {
                        layer_msg(res.message)
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)

                    })
                }
            });
        },
        // 法币切换
        legalTab(legalid) {
            let that = this;
            that.leverDatas.legalId = legalid;
            that.leverDatas.share = "";
            $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
        },
        // 币种切换
        currencyTab(legalName, currencyId, currencyName, shareNum, spread, fee, rmbRate) {
            let that = this;
            that.leverDatas.legalName = legalName;
            that.leverDatas.currencyId = currencyId;
            that.leverDatas.currencyName = currencyName;
            that.leverDatas.shareNum = shareNum;
            that.leverDatas.spread = spread;
            that.leverDatas.transactionFee = fee;
            that.leverDatas.share = "";
            that.leverDatas.rmbRate = rmbRate;
            $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            setlocal_storage('lever', {
                "currency_name": currencyName,
                "currency_id": currencyId,
                "legal_name": legalName,
                "legal_id": that.leverDatas.legalId,
                "share_num": shareNum,
                'spread': spread,
                'transactionFee': fee,
            });
            setlocal_storage('levertype', 'buy');
            that.get_lever_data();
            that.close();

        },
        // 关闭左侧
        close() {
            $('body').css('overflow', 'auto');
            $('#Limited').show();
            $('#mask1').hide();
            $('#sideColumn').animate({
                left: '-80%'
            }, 1000);
        },
        links() {
            let that = this;
            location.href = 'Entrust.html?legal_id=' + that.leverDatas.legalId + '&currency_id=' + that.leverDatas.currencyId + '&legal_name=' + that.leverDatas.legalName + '&currency_name=' + that.leverDatas.currencyName;
        },
        upPrice() {
            let that = this;
            $.ajax({
                url: _API + "user/info",
                type: "GET",
                dataType: "json",
                async: true,
                beforeSend: function beforeSend(request) {
                    request.setRequestHeader("Authorization", that.token);
                },
                success: function success(data) {
                    if (data.type == 'ok') {
                        var socket = io(socket_api);
                        socket.emit('login', data.message.id);
                        // 后端推送来消息时
                        socket.on('kline', function (msg) {
                            if (msg.type == 'kline') {
                                var symbols = $('.trade-name').text();
                                if (symbols == msg.symbol) {
                                    that.leverDatas.lastprice = msg.close;
                                }
                            }

                        })
                    }
                }
            });
        },
        marsketLists() {
            let that = this;
            $.ajax({
                url: _API + "user/info",
                type: "GET",
                dataType: "json",
                async: true,
                beforeSend: function beforeSend(request) {
                    request.setRequestHeader("Authorization", that.token);
                },
                success: function success(data) {
                    if (data.type == 'ok') {
                        var socket = io(socket_api);
                        socket.emit('login', data.message.id);
                        // 后端推送来消息时
                        socket.on('daymarket', function (msg) {
                            if (msg.type == 'daymarket') {
                                var list1 = that.leftData;
                                for (var i = 0; i < list1.length; i++) {
                                    var list2 = list1[i].quotation
                                    for (var j = 0; j < list2.length; j++) {
                                        if (list2[j].legal_id == msg.legal_id && list2[j].currency_id == msg.currency_id) {
                                            that.leftData[i].quotation[j].now_price = msg.now_price;
                                            that.leftData[i].quotation[j].change = msg.change;
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            });
        },
        // 合约市值、保证金计算
        calculation(bond, type, share, muit) {
            let that = this;
            console.log('calc_data:', that.leverDatas);
            layer.load(2);
            console.log('calc:bond', bond);
            var spread = iTofixed(that.leverDatas.spread, 4);
            console.log('calc:spread', that.leverDatas.spread);
            var pricesTotal = 0;
            if (type == 'sell') {
                pricesTotal = iTofixed(bond - spread, 4);
            } else {
                pricesTotal = iTofixed(Number(bond) + Number(spread), 4);
            }
            console.log('calc:pricesTotal', pricesTotal);
            var shareNum = iTofixed($('.share-num').text(), 4);
            var totalPrice = iTofixed(pricesTotal * share * shareNum, 4);
            var bonds = iTofixed((totalPrice - 0) / (muit - 0), 4);
            var tradeFree = iTofixed(totalPrice * that.leverDatas.transactionFee / 100, 4);
            var marketPrice = iTofixed(totalPrice, 4);
            console.log('calc:marketPrice', marketPrice);
            if (marketPrice == 'NaN') {
                $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            } else {
                console.log(marketPrice / 5);
                $('.market-value').text('≈ ' + marketPrice + " " + that.leverDatas.legalName);
            }
            if (bonds == "NaN") {
                $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            } else {
                that.leverDatas.bondTotal = bonds;
                $('.bond').text('≈ ' + bonds + " " + that.leverDatas.legalName);
            }
            if (tradeFree == "NaN") {
                $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            } else {
                that.leverDatas.transactionTotal = tradeFree;
                $('.transaction-fee').text('≈ ' + tradeFree + " " + that.leverDatas.legalName);
            }
            setTimeout(function () {
                layer_close();
            }, 500)
        },
        // 选择倍数
        selectMult(num) {
            let that = this;
            // alert(that.leverDatas.controlPrice);
            that.leverDatas.muitNum = num;
            if (that.leverDatas.selectStatus == 0) {
                if (that.leverDatas.controlPrice != '') {
                    if (that.leverDatas.share != '') {
                        var bond = iTofixed(that.leverDatas.controlPrice, 4);
                        var share = iTofixed(that.leverDatas.share, 4);
                        var muitNum = iTofixed(that.leverDatas.muitNum, 4);
                        that.calculation(bond, that.type, share, muitNum);
                    } else {
                        $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                        $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                        $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                    }
                } else {
                    $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                }
            } else {
                if (that.leverDatas.share != '') {
                    var bond = iTofixed(that.leverDatas.lastprice, 4);
                    var share = iTofixed(that.leverDatas.share, 4);
                    var muitNum = iTofixed(that.leverDatas.muitNum, 4);
                    that.calculation(bond, that.type, share, muitNum);
                } else {
                    $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                }
            }
        },
        // 选择手数
        selectShare(num) {
            let that = this;
            that.leverDatas.share = num;
            if (that.leverDatas.selectStatus == 0) {
                if (that.leverDatas.controlPrice != '') {
                    if (that.leverDatas.share != '') {
                        var bond = iTofixed(that.leverDatas.controlPrice, 4);
                        var share = iTofixed(that.leverDatas.share, 4);
                        var muitNum = iTofixed(that.leverDatas.muitNum, 4);
                        that.calculation(bond, that.type, share, muitNum);
                    } else {
                        $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                        $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                        $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                    }
                } else {
                    $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                }
            } else {
                if (that.leverDatas.share != '') {
                    var bond = iTofixed(that.leverDatas.lastprice, 4);
                    var share = iTofixed(that.leverDatas.share, 4);
                    var muitNum = iTofixed(that.leverDatas.muitNum, 4);
                    ;
                    that.calculation(bond, that.type, share, muitNum);
                } else {
                    $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                    $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
                }
            }

        },
        // 输入手数
        inputNum() {
            let that = this;
            var textValue = /^[1-9]*[0-9][0-9]*$/;
            $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            if (!textValue.test(that.leverDatas.share)) {
                layer_msg(getlg('pznum'));
                return false;
            } else if ((that.leverDatas.share - 0) < (that.leverDatas.minShare - 0)) {
                layer_msg(getlg('pnoless') + that.leverDatas.minShare);
                return false;
            } else {
                if (that.leverDatas.maxShare > 0) {
                    if ((that.leverDatas.share - 0) > (that.leverDatas.maxShare - 0)) {
                        layer_msg(getlg('pnomore') + that.leverDatas.maxShare);
                        return false;
                    }
                }
            }
            that.selectShare(that.leverDatas.share);
        },

        // 输入价格
        inputPrice() {
            let that = this;
            if (that.leverDatas.controlPrice) {
                that.selectShare(that.leverDatas.share);
            } else {
                $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
                $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
                $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            }
        },
        // 跳转k线页面
        linkLine() {
            let that = this;
            window.location.href = 'dataMap.html?legal_id=' + that.leverDatas.legalId + '&currency_id=' + that.leverDatas.currencyId + '&symbol=' + $('.trade-name').text();

        },
        recordList() {
            let that = this;
            window.location.href = 'leverList.html';
        },

        // 选择交易类型
        selectTrade(types) {
            let that = this;
            that.leverDatas.selectStatus = types;
            $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            that.leverDatas.controlPrice = '';
            that.leverDatas.share = '';
            if (types == 0) {
                $('.control').show();
                $('.select-price span').eq(1).addClass('active').siblings().removeClass('active');
            } else {
                $('.control').hide();
                $('.select-price span').eq(0).addClass('active').siblings().removeClass('active');
            }
        },
        // 选择类型
        selectType(types) {
            var that = this;
            that.type = types;
            setlocal_storage('levertype', types);
            $('.market-value').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.bond').text('≈ 0.0000' + that.leverDatas.legalName);
            $('.transaction-fee').text('≈ 0.0000' + that.leverDatas.legalName);
            that.leverDatas.share = '';
        },
        // 刷新页面
        reload() {
            location.reload();
        }


    },

});