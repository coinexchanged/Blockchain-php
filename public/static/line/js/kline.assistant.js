function Kline() {}
Kline.prototype = {
    browerState: 0,
    klineWebsocket: null,
    klineTradeInit: false,
    tradeDate: new Date(),
    tradesLimit: 100,
    lastDepth: null,
    depthShowSize: 15,
    priceDecimalDigits: 6,
    amountDecimalDigits: 4,
    symbol: null,
    curPrice: null,
    title: "",
    reset: function(a) {
        this.refreshUrl(a);
        this.symbol = a;
        this.lastDepth = null;
        this.curPrice = null;
        this.klineTradeInit = false;
        $("#gasks .table").empty();
        $("#gbids .table").empty();
        $("#asks .table").empty();
        $("#bids .table").empty();
        this.websocketRedister(a);
    },
    setTitle: function() {
        document.title = (this.curPrice == null ? "": this.curPrice + " ") + this.title;
    },
    dateFormatTf: function(a) {
        return (a < 10 ? "0": "") + a
    },
    dateFormat: function(a) {
        return a.getFullYear() + "-" + this.dateFormatTf(a.getMonth() + 1) + "-" + this.dateFormatTf(a.getDate()) + " " + this.dateFormatTf(a.getHours()) + ":" + this.dateFormatTf(a.getMinutes()) + ":" + this.dateFormatTf(a.getSeconds())
    },
    dateInit: function(b) {
        var a = new Date();
        if (b) {
            a.setTime(b)
        }
        $(".m_rightbot").text(this.dateFormat(a));
        var c = this;
        setInterval(function() {
            a.setTime(a.getTime() + 1000);
            $(".m_rightbot").text(c.dateFormat(a))
        },
        1000)
    },
    websocketRedister: function (symbol) {
        this.updateDepth();
        var $this = this;
        //ÓÒ²àÉî¶ÈÍ¼
        var json = '{"asks":[[18580,0.055109],[18520,0.8982],[18500,5.802874],[18499.9,0.005422],[18499,5],[18490,0.008221],[18475.5,0.003233],[18425.3,0.002434],[18401.4,0.000136],[18399.9,0.005451],[18366.1,0.527979],[18366,0.065427],[18298,0.045],[18297.9,8.196],[18288,1.023276],[18266,0.277472],[18256.4,0.003269],[18199.9,0.005511],[18198.8,0.440183],[18198,0.165414],[18196,0.758338],[18188,0.996],[18187,1.61925],[18185,2.098884],[18120,0.000698],[18118,0.1996],[18117,1],[18115.5,0.110451],[18115,0.907724],[18114.8,0.579]],"bids":[[17905.5,0.34],[17905.3,0.11196],[17905,0.56],[17901.8,8.197],[17901.5,1.7965],[17901.2,1],[17901,0.203545],[17900,0.34264],[17881.1,0.418736],[17865,0.385071],[17862,0.1],[17857.6,0.00014],[17826.8,0.520464],[17811,0.07],[17810.1,0.035001],[17809.2,0.00332],[17808,0.299701],[17801,1],[17800,1.248358],[17730.2,1],[17729.8,0.167771],[17711,0.08],[17700,0.00565],[17679,0.15198],[17658,0.033],[17648.8,0.319029],[17611,0.09],[17600,2.310682],[17597.2,0.003351],[17580,0.309805]]}';
        var depths = JSON.parse(json);
        $this.updateDepth(depths);
    },
    pushTrades: function(l) {        
    },
    updateDepth: function(e) {
        window._set_current_depth(e);
        if (!e) {
            return
        }
        $("#gasks .table").html(this.getgview(this.getgasks(e.asks)));
        $("#gbids .table").html(this.getgview(this.getgbids(e.bids)));
        if (this.lastDepth == null) {
            this.lastDepth = {};
            this.lastDepth.asks = this.getAsks(e.asks, this.depthShowSize);
            this.depthInit(this.lastDepth.asks, $("#asks .table"));
            this.lastDepth.bids = this.getBids(e.bids, this.depthShowSize);
            this.depthInit(this.lastDepth.bids, $("#bids .table"))
        } else {
            var b = $("#asks .table");
            b.find("div.remove").remove();
            b.find("div.add").removeClass("add");
            var f = this.getAsks(e.asks, this.depthShowSize);
            var a = this.lastDepth.asks;
            this.lastDepth.asks = f;
            this.asksAndBids(f.slice(0), a, b);
            var d = $("#bids .table");
            d.find("div.remove").remove();
            d.find("div.add").removeClass("add");
            var g = this.getBids(e.bids, this.depthShowSize);
            var c = this.lastDepth.bids;
            this.lastDepth.bids = g;
            this.asksAndBids(g.slice(0), c, $("#bids .table"))
        }
    },
    depthInit: function(f, h) {
        h.empty();
        if (f && f.length > 0) {
            var g, b = "";
            for (var e = 0; e < f.length; e++) {
                var a = (f[e][0] + "").split(".");
                var d = this.getPrice(a, g);
                g = a[0];
                a = (f[e][1] + "").split(".");
                var c = this.getAmount(a);
                b += "<div class='row'><span class='price'>" + d[0] + "<g>" + d[1] + "</g></span> <span class='amount'>" + c[0] + "<g>" + c[1] + "</g></span></div>"
            }
            h.append(b);
            b = null
        }
    },
    asksAndBids: function(b, c, l) {
        for (var f = 0; f < c.length; f++) {
            var n = false;
            for (var d = 0; d < b.length; d++) {
                if (c[f][0] == b[d][0]) {
                    n = true;
                    if (c[f][1] != b[d][1]) {
                        var a = l.find("div:eq(" + f + ") .amount");
                        a.addClass(c[f][1] > b[d][1] ? "red": "green");
                        var g = this.getAmount((b[d][1] + "").split("."));
                        setTimeout((function(j, i) {
                            return function() {
                                j.html(i[0] + "<g>" + i[1] + "</g>");
                                j.removeClass("red").removeClass("green");
                                j = null;
                                i = null
                            }
                        })(a, g), 500)
                    }
                    b.splice(d, 1);
                    break
                }
            }
            if (!n) {
                l.find("div:eq(" + f + ")").addClass("remove");
                c[f][2] = -1
            }
        }
        for (var d = 0; d < c.length; d++) {
            for (var f = 0; f < b.length; f++) {
                if (b[f][0] > c[d][0]) {
                    var k = (b[f][1] + "").split(".");
                    var g = this.getAmount(k);
                    l.find("div:eq(" + d + ")").before("<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "<g>" + g[1] + "</g></span></div>");
                    c.splice(d, 0, b[f]);
                    b.splice(f, 1);
                    break
                }
            }
        }
        var h = "";
        for (var f = 0; f < b.length; f++) {
            c.push(b[f]);
            var k = (b[f][1] + "").split(".");
            var g = this.getAmount(k);
            h += "<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "<g>" + g[1] + "</g></span></div>"
        }
        if (h.length > 0) {
            l.append(h)
        }
        h = null;
        var m;
        for (var f = 0; f < c.length; f++) {
            var o = l.find("div:eq(" + f + ")");
            if (! (c[f].length >= 3 && c[f][2] == -1)) {
                var k = (c[f][0] + "").split(".");
                var e = this.getPrice(k, m);
                m = k[0];
                o.find(".price").html(e[0] + "<g>" + e[1] + "</g>")
            }
        }
        b = null;
        c = null;
        l.find("div.add").slideDown(800);
        setTimeout((function(i, j) {
            return function() {
                i.slideUp(500,
                function() {
                    $(this).remove()
                });
                j.removeClass("add")
            }
        })(l.find("div.remove"), l.find("div.add")), 1000)
    },
    getAsks: function(b, a) {
        if (b.length > a) {
            b.splice(0, b.length - a)
        }
        return b
    },
    getBids: function(b, a) {
        if (b.length > a) {
            b.splice(a, b.length - 1)
        }
        return b
    },
    getgview: function(c) {
        var d = "";
        var e;
        for (var b = 0; b < c.length; b++) {
            var a = c[b][0].split(".");
            if (a.length == 1 || a[0] != e) {
                d += "<div class='row'><span class='price'>" + c[b][0] + "</span> <span class='amount'>" + c[b][1] + "</span></div>";
                e = a[0]
            } else {
                d += "<div class='row'><span class='price'><h>" + a[0] + ".</h>" + a[1] + "</span> <span class='amount'>" + c[b][1] + "</span></div>"
            }
        }
        return d
    },
    getgasks: function(j) {
        var k = j[j.length - 1][0];
        var e = j[0][0];
        var a = e - k;
        var d = this.getBlock(a, 100);
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) { (b = 0)
        }
        k = parseInt(k / d) * d;
        e = parseInt(e / d) * d;
        var h = [];
        var g = 0;
        for (var f = j.length - 1; f >= 0; f--) {
            if (j[f][0] > k) {
                var c = parseInt(g, 10);
                if (c > 0) {
                    h.unshift([Number(k).toFixed(b), c])
                }
                if (k >= e) {
                    break
                }
                k += d
            }
            g += j[f][1]
        }
        return h
    },
    getgbids: function(j) {
        var k = j[j.length - 1][0];
        var e = j[0][0];
        var a = e - k;
        var d = this.getBlock(a, 100);
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) { (b = 0)
        }
        k = parseInt(k / d) * d;
        e = parseInt(e / d) * d;
        var h = [];
        var g = 0;
        for (var f = 0; f < j.length; f++) {
            if (j[f][0] < e) {
                var c = parseInt(g, 10);
                if (c > 0) {
                    h.push([Number(e).toFixed(b), c])
                }
                if (e <= k) {
                    break
                }
                e -= d
            }
            g += j[f][1]
        }
        return h
    },
    getBlock: function(a, c) {
        if (a > c) {
            return c
        } else {
            c = c / 10;
            return this.getBlock(a, c)
        }
    },
    getZeros: function(b) {
        var a = "";
        while (b > 0) {
            b--;
            a += "0"
        }
        return a
    },
    getPrice: function(a, d) {
        var c = a[0];
        if (d == c) {
            c = "<h>" + c + ".</h>"
        } else {
            c += "."
        }
        var b = "";
        if (a.length == 1) {
            c += "0";
            b = this.getZeros(this.priceDecimalDigits - 1)
        } else {
            c += a[1];
            b = this.getZeros(this.priceDecimalDigits - a[1].length)
        }
        return [c, b]
    },
    getAmount: function(a) {
        var c = a[0];
        var b = "";
        var d = this.amountDecimalDigits - c.length + 1;
        if (d > 0) {
            b = ".";
            if (a.length == 1) {
                b += this.getZeros(d)
            } else {
                if (d > a[1].length) {
                    b += a[1] + this.getZeros(d - a[1].length)
                } else {
                    if (d == a[1].length) {
                        b += a[1]
                    } else {
                        b += a[1].substring(0, d)
                    }
                }
            }
        }
        return [c, b]
    },
    setTopTickers: function(c) {     
    },
    setMarketShow: function(e, b, d, c) {      
    },
    refreshPage: function(a) {      
    },
    refreshUrl: function(a) {         
    } 
};