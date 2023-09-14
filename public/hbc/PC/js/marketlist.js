/**
 * Created by window on 2018/9/11.
 */
currType = "usdt",
void 0 !== $.cookie("market_title") && (currType = $.cookie("market_title").toLowerCase());
var LocalStorage = function(t) {
    return {
        get: function(e, a) {
            return t ? a &&
            function(e) {
                var a = t.getItem(e);
                if (!a) return ! 0;
                var s = parseInt(a);
                return ((new Date).getTime() - s) / 1e3 / 60 / 5 > 1
            } (e + "_time") ? {
                status: -3,
                msg: "expired"
            }: t.getItem(e) ? {
                status: 0,
                msg: "get success",
                data: t.getItem(e)
            }: {
                status: -2,
                msg: "not store"
            }: {
                status: -1,
                msg: "storage is unavailable"
            }
        },
        set: function(e, a) {
            if (t) {
                try {
                    t.setItem(e, a)
                } catch(r) {
                    if ("QuotaExceededError" === r.name) {
                        console.log("storage reset!"),
                            localStorage.clear();
                        for (var s = document.cookie.split(";"), i = 0; i < s.length; i++) {
                            var n = s[i].split("=")[0].toString().trim();
                            n.indexOf("_time") > -1 && $.removeCookie(n, {
                                path: "/"
                            })
                        }
                        t.setItem(e, a)
                    }
                }
                var r = (new Date).getTime();
                return t.setItem(e + "_time", r.toString()),
                {
                    status: 0,
                    msg: "store success"
                }
            }
            return {
                status: -1,
                msg: "storage is unavailable"
            }
        }
    }
} (window.localStorage);
/*function TableSorter(t) {
    if (this.Table = this._(t), !(this.Table.rows.length <= 1)) {
        var e = [];
        if (arguments.length > 1) for (var a = 1; a < arguments.length; a++) e.push(arguments[a]);
        this.Init(e)
    }
}
function buildDom(t, e, a) {
    var s = "",
        i = 1,
        n = "",
        r = 0,
        l = 2,
        o = "",
        c = "";
    for (var d in void 0 !== $.cookie("custom") && (c = $.cookie("custom").toLowerCase()), "usdt" == e && (i = USDT_fiat_rate), "btc" == e && (i = BTC_fiat_rate), "eth" == e && (i = ETH_fiat_rate), "qtum" == e && (i = QTUM_fiat_rate), "limited" == e && (i = ETH_fiat_rate), t) {
        listNum++;
        var u = t[d].pair;
        if (! (a && c.indexOf(u) < 0 || atTop && listNum > 20 && "qtum" != e && "limited" != e && !a)) {
            var h = t[d].curr_a,
                p = t[d].curr_b,
                g = t[d].rate,
                m = t[d].name_cn,
                f = (t[d].symbol, t[d].trend),
                v = t[d].vol_b,
                b = t[d].marketcap,
                T = t[d].rate_percent,
                _ = t[d].name_en,
                C = t[d].is_limited;
            o = "USDT" == p ? "$": "BTC" == p ? "฿": "ETH" == p ? "E": p.substr(0, 1),
            "limited" == e && "USDT" == p && (i = USDT_fiat_rate),
            1 != i && (l = 2, l = (r = g * i) > 1 ? 2 : r > .1 ? 2 : r > .01 ? 3 : r > .001 ? 4 : 5, r = r.toFixed(l), s = is_cn ? "<span style='color:#999'>/￥" + r + "</span>": "<span style='color:#999'>/$" + r + "</span>");
            var y = lang_string("去交易"),
                w = "",
                x = "",
                D = "",
                k = "",
                S = "";
            if ("limited" == e || C) {
                var q = JSON.parse(daysLeftArr)[h];
                q || (q = 0),
                    w = '<div class="left-d">' + lang_string("剩余") + '<span class="red"> ' + q + " </span>" + lang_string("天") + "</div>",
                    x = "limi-row"
            }
            "qtum" != e && !a && is_cn || (k = "/<em>" + p + "</em>"),
            is_cn && (S = '<span class="cname">' + m + "</span>", _ = m),
            a && (D = "class=" + e + "_type"),
                n += "<tr id=" + u + " " + D + ">",
                n += '<td ><a class="coin-name ' + x + '" href="/trade/' + u.toUpperCase() + '" title="' + _ + '"><span class="icon-32 icon-32-' + h.toLowerCase() + '"></span><span class="name-con"><b><span class="curr_a">' + h + "</span>" + k + "</b> " + S + w + " </span></a></td>",
                is_cn ? (n += '<td class="rate_' + f + '">' + o + g + s + "</td>", n += "<td>" + o + v + "</td>", n += "<td>" + o + b + "</td>", n += '<td class="day-updn">' + (T < 0 ? "<span class=green>" + T + " %</span>": 0 == T || -0 == T ? "<span class=red>0.00 %</span>": "<span class=red>+" + T + " %</span>") + "</td>") : (n += '<td><span class="rate_' + f + '">' + o + g + s + "</td>", n += "<td>" + o + v + "</td>", n += "<td>" + t[d].supply + "</td>", n += "<td>" + o + b + "</td>", n += '<td class="day-updn">' + (T < 0 ? "<span class=red>" + T + " %</span>": 0 == T || -0 == T ? "<span class=green>0.00 %</span>": "<span class=green>+" + T + " %</span>") + "</td>"),
                n += '<td><div class="price-chart" id="' + u + '_plot"></div></td>',
                n += '<td><a class="go-trade-btn r-btn" href="/trade/' + u.toUpperCase() + '">' + y + "</a></td>",
                n += '<td class="custom-option">',
                c.indexOf(u) > -1 ? n += '<i class="add-fav custom-on" data-id=' + u + ' title="' + lang_string("取消自选") + '"></i>': n += '<i class="add-fav" data-id=' + u + ' title="' + lang_string("加自选") + '"></i>',
                n += "</td></tr>",
            a || chartFuc.push(u + "_chart();")
        }
    }
    return n
}
function loadChartData(coinType, chartFucRun) {
    $.ajax({
        type: "get",
        url: "/marketlist/" + coinType + "?u=5",
        dataType: "script",
        success: function(data) {
            if (data) {
                LocalStorage.set("chartFucData_" + coinType, data);
                for (var i = 0; i < chartFucRun.length; i++) try {
                    eval(chartFucRun[i])
                } catch(t) {
                    console.log(t)
                }
                data = null,
                    chartFucRun = null
            } else $(".price-chart").html("<div class='chart-err'>" + lang_string("加载失败！") + "</div>")
        },
        error: function() {
            $(".price-chart").html("<div class='chart-err'>" + lang_string("加载失败！") + "</div>")
        },
        complete: function() {
            $("#marketlist_controller").find(".load8").remove()
        }
    })
}*/
/*function renderChart(coinType, isCustom) {
    var lsData = LocalStorage.get("chartFucData_" + coinType, !0),
        chartFucRun = chartFuc;
    if (isCustom) {
        for (var thisTypeCharts = $("#list_tbody").find("." + coinType + "_type"), thisTypeChartFuc = [], i = 0; i < thisTypeCharts.length; i++) {
            var unchartPair = $(thisTypeCharts[i]).prop("id");
            unchartPair && thisTypeChartFuc.push(unchartPair + "_chart();")
        }
        chartFucRun = thisTypeChartFuc
    }
    if (0 != lsData.status || data_expired) $(".btn_selected").append('<div class="load8"><div class="loader">Loading</div></div>'),
        loadChartData(coinType, chartFucRun);
    else {
        eval(lsData.data);
        for (var n = 0; n < chartFucRun.length; n++) try {
            eval(chartFucRun[n])
        } catch(t) {
            console.log(t)
        }
        lsData.data = null
    }
    chartFucRun = null
}
function buildAll(t, e) {
    chartFuc = [],
    void 0 === t && (t = "usdt");
    var a = "",
        s = "",
        i = "";
    if ("qtum" != t && "custom" != t && (a = "<span class=curr-base>" + t.toUpperCase() + "</span>"), "limited" == t && (a = ""), e && (s = "show-all"), i += '<table id="listTable" class="marketlist dataTable ' + s + '"><thead><tr><th class="sortable"><b>' + lang_string("币种对"), is_cn && (i += a), i += '</b></th><th class="sortable"><b>' + lang_string("价格") + a + '</b></th><th class="sortable"><b>' + lang_string("交易量") + a + "</b></th>", is_cn || (i += '<th class="sortable"><b>' + lang_string("Supply") + "</b></th>"), i += '<th class="sortable"><b>' + lang_string("总市值") + '</b></th><th class="sortable day-updn"><b>' + lang_string("日涨跌") + '</b></th><th class="sorting_disabled price-flot"><b>' + lang_string("价格趋势(3日)") + '</b></th><th class="sorting_disabled"></th><th class="sorting_disabled"><i class="fav-icon" title=' + lang_string("自选") + '></i></th></tr></thead><tbody id="list_tbody">', "custom" == t) {
        var n = $.cookie("custom");
        void 0 === n ? i += "<tr class='m-err-tip red'><td colspan='8'>" + lang_string("暂无自选币种！") + "</td></tr>": (i += buildDom(listDataUSDT, "usdt", !0), i += buildDom(listDataBTC, "btc", !0), i += buildDom(listDataETH, "eth", !0), i += buildDom(listDataQTUM, "qtum", !0), i += buildDom(listDataLIMITED, "limited", !0))
    } else "usdt" == t ? i += buildDom(listDataUSDT, t) : "btc" == t ? i += buildDom(listDataBTC, t) : "eth" == t ? i += buildDom(listDataETH, t) : "qtum" == t ? i += buildDom(listDataQTUM, t) : "limited" == t && (i += buildDom(listDataLIMITED, t));
    i += "</tbody></table>",
        $("#mianBox").html(i),
        i = null,
        "custom" == t ? void 0 !== n && (renderChart("usdt", !0), renderChart("btc", !0), renderChart("eth", !0), renderChart("qtum", !0), renderChart("limited", !0)) : renderChart(t)
}
function searchCoin(t, e) {
    $("#listTable").hasClass("show-all") || (atTop = !1, buildAll(currType, !0));
    for (var a, s, i = t.value.toLowerCase(), n = get_element("list_tbody"), r = n.rows, l = $(".coin-name "), o = 0, c = "", d = 0, u = r.length; d < u; d++) null != (a = r[d]) && (a.children[0].className ? n.deleteRow(d) : (s = $(a).find(".curr_a").text().toLowerCase(), is_cn && (c = $(a).find(".cname").text().toLowerCase()), -1 == s.indexOf(i) && -1 == c.indexOf(i) ? a.style.display = "none": (o++, a.style.display = "table-row")));
    if (0 == o) {
        var h = n.insertRow( - 1),
            p = h.insertCell(0);
        h.className = "r-empty",
            p.colSpan = "8",
            p.className = "dataTables_empty",
            "limited" == currType ? p.innerHTML = lang_string("限时交易区") + lang_string("搜无 ") + "<b>" + i + "</b>": "custom" == currType ? p.innerHTML = lang_string("自选区") + lang_string("搜无 ") + "<b>" + i + "</b>": p.innerHTML = currType + lang_string("交易区") + lang_string("搜无 ") + "<b>" + i + "</b>"
    }
    l.removeHighlight(),
    i && l.highlight(i)
}
TableSorter.prototype = {
    _: function(t) {
        return document.getElementById(t)
    },
    Init: function(t) {
        this.Rows = [],
            this.Header = [],
            this.ViewState = [],
            this.LastSorted = null,
            this.NormalCss = "sortable",
            this.SortAscCss = "sorting_asc",
            this.SortDescCss = "sorting_desc";
        for (var e = 0; e < this.Table.rows.length; e++) this.Rows.push(this.Table.rows[e]);
        this.Header = this.Rows.shift().cells;
        for (e = 0; e < (t.length ? t.length: this.Header.length); e++) {
            var a = t.length ? t[e] : e;
            a >= this.Header.length || (this.ViewState[a] = !1, this.Header[a].style.cursor = "pointer", this.Header[a].onclick = this.GetFunction(this, "Sort", a))
        }
    },
    GetFunction: function(t, e, a) {
        return function() {
            t[e](a)
        }
    },
    Sort: function(t) {
        this.LastSorted && (this.LastSorted.className = this.NormalCss);
        for (var e = !0,
                 a = 0; a < this.Rows.length && e; a++) e = is_cn ? 1 == t || 2 == t || 3 == t || 4 == t: 1 == t || 2 == t || 3 == t || 4 == t || 5 == t;
        this.Rows.sort(function(a, s) {
            var i, n;
            return i = a.cells[t].innerHTML,
                n = s.cells[t].innerHTML,
                is_cn ? (1 == t && (i = i.split("<sp")[0].substring(1), n = n.split("<sp")[0].substring(1)), 2 != t && 3 != t && 4 != t || (i = i.replace(/[^\d\-\.]/g, ""), n = n.replace(/[^\d\-\.]/g, ""))) : 1 != t && 2 != t && 4 != t && 5 != t || (i = i.replace(/[^\d\-\.]/g, ""), n = n.replace(/[^\d\-\.]/g, "")),
                i == n ? 0 : (e ? parseFloat(i) > parseFloat(n) : i > n) ? -1 : 1
        }),
            this.ViewState[t] ? (this.Rows.reverse(), this.ViewState[t] = !1, this.Header[t].className = this.SortAscCss) : (this.ViewState[t] = !0, this.Header[t].className = this.SortDescCss),
            this.LastSorted = this.Header[t];
        var s = document.createDocumentFragment();
        for (a = 0; a < this.Rows.length; a++) s.appendChild(this.Rows[a]);
        this.Table.tBodies[0].appendChild(s),
            this.OnSorted(this.Header[t], this.ViewState[t])
    },
    OnSorted: function(t, e) {}
},
    chartFuc = [],
    listNum = 0,
    listDataUSDT = "",
    listDataBTC = "",
    listDataETH = "",
    listDataQTUM = "",
    listDataLIMITED = "",
    jQuery.fn.highlight = function(t) {
        return this.each(function() { !
            function t(e, a) {
                var s = 0;
                if (3 == e.nodeType) {
                    var i = e.data.toUpperCase().indexOf(a);
                    if (i >= 0) {
                        var n = document.createElement("span");
                        n.className = "highlight";
                        var r = e.splitText(i),
                            l = (r.splitText(a.length), r.cloneNode(!0));
                        n.appendChild(l),
                            r.parentNode.replaceChild(n, r),
                            s = 1
                    }
                } else if (1 == e.nodeType && e.childNodes && !/(script|style)/i.test(e.tagName)) for (var o = 0; o < e.childNodes.length; ++o) o += t(e.childNodes[o], a);
                return s
            } (this, t.toUpperCase())
        })
    },
    jQuery.fn.removeHighlight = function() {
        return this.find("span.highlight").each(function() {
            var t = this.parentNode;
            t.replaceChild(this.firstChild, this),
                function t(e) {
                    for (var a = 0,
                             s = e.childNodes,
                             i = s.length; a < i; a++) {
                        var n = s[a];
                        if (1 != n.nodeType) {
                            if (3 == n.nodeType) {
                                var r = n.nextSibling;
                                if (null != r && 3 == r.nodeType) {
                                    var l = n.nodeValue + r.nodeValue;
                                    new_node = e.ownerDocument.createTextNode(l),
                                        e.insertBefore(new_node, n),
                                        e.removeChild(n),
                                        e.removeChild(r),
                                        a--,
                                        i--
                                }
                            }
                        } else t(n)
                    }
                } (t)
        }).end()
    };
var searchInput = $("#fsBar"),
    cpLock = !1;
function winScroll() {
    "usdt" != currType && "btc" != currType && "eth" != currType || $(window).on("scroll",
        function() {
            $("#listTable").hasClass("show-all") ? 20 != $("#list_tbody").find("tr").length && $(this).off("scroll") : (atTop = !1, buildAll(currType, !0))
        })
}
searchInput.on("input propertychange",
    function() {
        cpLock || searchCoin(this)
    }).on("compositionstart",
    function() {
        cpLock = !0
    }).on("compositionend",
    function() { (cpLock = !1) || searchCoin(this)
    }).on("keyup",
    function(t) {
        if (13 == t.keyCode) {
            var e = $("#list_tbody").find("tr:visible")[0];
            if (e && "r-empty" == e.className) return;
            var a = $(e).find(".curr_a").text(),
                s = currType;
            if ("limited" == currType) a = a.split("/")[0];
            else if ("custom" == currType || !is_cn) {
                var i = a.split("/");
                a = i[0],
                    s = i[1]
            }
            window.location = "/trade/" + a + "_" + s.toUpperCase()
        }
    }),
    atTop = !1,
0 == $(window).scrollTop() && (atTop = !0),
    setTimeout(function() {
            0 != $(window).scrollTop() && atTop && (atTop = !1, buildAll(currType, !0))
        },
        200),
    winScroll();
var leftbar_data = LocalStorage.get("leftbar_data", !0);
if (0 != leftbar_data.status || data_expired) $(".btn_selected").append('<div class="load8"><div class="loader">Loading</div></div>'),
    $.ajax({
        type: "get",
        url: "/json_svr/get_leftbar/?u=128&c=" + Math.floor(1e6 * Math.random()),
        xhrFields: {
            withCredentials: !0
        },
        success: function(t) {
            listDataUSDT = t.USDT,
                listDataBTC = t.BTC,
                listDataETH = t.ETH,
                listDataQTUM = t.QTUM,
                listDataLIMITED = t.LIMITED,
                "usdt" == currType || "btc" == currType || "eth" == currType ? buildAll(currType) : (atTop = !1, buildAll(currType, !0)),
                LocalStorage.set("leftbar_data", JSON.stringify(t)),
                t = null
        },
        error: function() {
            $("#normal_tbody").html("<tr class='m-err-tip red'><td colspan='8'>" + lang_string("网络错误") + ": " + lang_string("请检查您的网络连接后重试。") + "</td></tr>")
        },
        complete: function() {
            $("#marketlist_controller").find(".load8").remove()
        }
    });
else {
    var data = JSON.parse(leftbar_data.data);
    listDataUSDT = data.USDT,
        listDataBTC = data.BTC,
        listDataETH = data.ETH,
        listDataQTUM = data.QTUM,
        listDataLIMITED = data.LIMITED,
        "usdt" == currType || "btc" == currType || "eth" == currType ? buildAll(currType) : (atTop = !1, buildAll(currType, !0)),
        leftbar_data = null,
        data = null
}
function addCoinCookie(t) {
    var e = t.attr("data-id");
    if (void 0 !== $.cookie("custom")) {
        var a = $.cookie("custom").split(",");
        if ( - 1 == $.inArray(e, a)) {
            var s = [a, e];
            $.cookie("custom", s, {
                expires: 365,
                path: "/",
                secure: !0
            })
        }
    } else $.cookie("custom", e, {
        expires: 365,
        path: "/"
    })
}
function cutCoinCookie(t) {
    var e = t.attr("data-id"),
        a = $.cookie("custom");
    if (void 0 !== a) {
        var s = a.split(","); !
            function(t, e) {
                for (var a = 0; a < t.length; a++) if (t[a] == e) {
                    t.splice(a, 1);
                    break
                }
            } (s, e),
            0 === s.length ? $.removeCookie("custom", {
                path: "/"
            }) : $.cookie("custom", s, {
                expires: 365,
                path: "/",
                secure: !0
            })
    }
}*/
function setTableSorter(t) {
    var e = $("#listTable").hasClass("show-all"),
        a = $("#mianBox");
    if (!e) {
        if ("custom" == currType && $("#list_tbody").find(".m-err-tip").length) return ! 1;
        listNum = 0,
            atTop = !1,
            buildAll(currType),
            $("#listTable").addClass("show-all")
    }
    is_cn ? tableSort = new TableSorter("listTable", 0, 1, 2, 3, 4) : tableSort = new TableSorter("listTable", 0, 1, 2, 3, 4, 5),
        a.off("click"),
        e ? t[0].click() : a.find("th").eq(t.index())[0].click()
}
function fixedAlertPosition(t) {
    var e = document.body.clientHeight,
        a = document.body.clientWidth;
    t.css({
        left: (a - 390) / 2,
        top: (e - 598) / 2
    })
}
$("#marketMain").on("click", ".add-fav",
    function() {
        var t = $(this);
        if (t.hasClass("custom-on")) {
            if ("custom" == currType) {
                var e = $("#list_tbody");
                t.parents("tr").remove(),
                0 == e.find("tr").length && e.html("<tr class='m-err-tip red'><td colspan='8'>" + lang_string("暂无自选币种！") + "</td></tr>")
            }
            t.removeClass("custom-on").attr("title", lang_string("加自选")),
                cutCoinCookie(t)
        } else t.addClass("custom-on").attr("title", lang_string("取消自选")),
            addCoinCookie(t)
    }),
    $("#mianBox").on("click", ".sortable",
        function() {
            setTableSorter($(this))
        }),
    $("#marketlist_controller").on("click", "button",
        function() {
            if ($(this).hasClass("btn_selected")) return ! 1;
            var t = $(this).attr("id"),
                e = t.toUpperCase() + lang_string("交易区");
            return listNum = 0,
                tableSort = null,
                currType = t,
                $(this).addClass("btn_selected").siblings().removeClass("btn_selected"),
                $(this).siblings().find(".load8").remove(),
                searchInput.val(""),
                $("#mianBox").on("click", ".sortable",
                    function() {
                        setTableSorter($(this))
                    }).prop("class", t.toUpperCase() + "-box"),
            "limited" == t && (e = lang_string("限时交易区")),
            "custom" == t && (e = lang_string("自选区")),
                searchInput.attr("placeholder", lang_string("搜索") + e),
                0 == $(window).scrollTop() ? (atTop = !0, buildAll(currType, !0), $("#listTable").removeClass("show-all"), winScroll()) : (atTop = !1, buildAll(currType, !0), $("#listTable").addClass("show-all")),
                $.cookie("market_title", t, {
                    expires: 365,
                    path: "/",
                    secure: !0
                }),
                !1
        }),
    window.downloadFile = function(t) {
        if (/(iP)/g.test(navigator.userAgent)) return alert("Your device does not support files downloading. Please try again in desktop browser."),
            !1;
        if (window.downloadFile.isChrome || window.downloadFile.isSafari) {
            var e = document.createElement("a");
            if (e.href = t, void 0 !== e.download) {
                var a = t.substring(t.lastIndexOf("/") + 1, t.length);
                e.download = a
            }
            if (document.createEvent) {
                var s = document.createEvent("MouseEvents");
                return s.initEvent("click", !0, !0),
                    e.dispatchEvent(s),
                    !0
            }
        }
        return - 1 === t.indexOf("?") && (t += "?download"),
            window.open(t, "_self"),
            !0
    },
    window.downloadFile.isChrome = navigator.userAgent.toLowerCase().indexOf("chrome") > -1,
    window.downloadFile.isSafari = navigator.userAgent.toLowerCase().indexOf("safari") > -1,
    $(".gate-logo").on("click",
        function() {
            var t = "/images/gate.io_logo_en.png",
                e = "/images/gateio_en.svg",
                a = "/images/gateio_h_en.png",
                s = "/images/gateio_h_en.svg";
            is_cn && (t = "/images/gate.io_logo.png", e = "/images/gateio.svg", a = "/images/gateio_h.png", s = "/images/gateio_h.svg"),
                $("<div class='img-modal logo-img'><div class='img-box'><img src=" + s + "><ul class=save-btn><li onclick=downloadFile('" + a + "')>" + lang_string("保存为") + " <b>png</b> " + lang_string("图片") + "</li><li onclick=downloadFile('" + s + "')>" + lang_string("保存为") + " <b>svg</b> " + lang_string("图片") + "</li></ul></div><div class='img-box'><img src=" + e + "><ul class=save-btn><li onclick=downloadFile('" + t + "')>" + lang_string("保存为") + " <b>png</b> " + lang_string("图片") + "</li><li onclick=downloadFile('" + e + "')>" + lang_string("保存为") + " <b>svg</b> " + lang_string("图片") + "</li></ul></div><span class=img-close>×</span></div>").prependTo("body");
            var i = $(".img-modal");
            fixedAlertPosition(i);
            var n = null;
            $(window).on("resize",
                function() {
                    n && clearTimeout(n),
                        n = setTimeout(function() {
                                fixedAlertPosition(i)
                            },
                            200)
                }),
                $(".img-close").on("click",
                    function() {
                        $(".img-modal").remove(),
                            $(window).off("resize")
                    });
            var r = setTimeout(function() {
                    $(".img-close").stop().animate({
                        opacity: "0"
                    })
                },
                1e3);
            i.on("mouseenter",
                function() {
                    $(".img-close").stop().animate({
                        opacity: "1"
                    }),
                        clearTimeout(r)
                }).on("mouseleave",
                function() {
                    $(".img-close").stop().animate({
                        opacity: "0"
                    })
                })
        }),
    $(function() {
        var t = $("#slides"),
            e = t.find("li"),
            a = $("#innerProg"),
            s = e.length - 1,
            i = 0,
            n = 0,
            r = 0,
            l = 5e3;
        function o(t) {
            var a = e.eq(t).find("span"),
                s = a.data("src");
            s && 0 === a.children().length && $("<img src=" + s + ' alt="Gate.io">').appendTo(a)
        }
        e.eq(0).hasClass("imgli") && o(0),
            setTimeout(function() {
                    e.eq(1).hasClass("imgli") && o(1)
                },
                3e3),
            $("#full-screen-slider").css("background-color", e.eq(0).attr("data-id")),
            e.addClass("opa-solid"),
            e.eq(0).find(".txt-banner").css("color", e.eq(0).attr("data-txt")),
            e.eq(0).siblings("li").addClass("hide");
        var c = '<div id="sCon" class="sCon"><ul id="pagination">',
            d = "",
            u = "</ul></div>"; !
            function() {
                for (var e = 0; e <= s; e++) d += '<li><a href="javascript:;">' + (e + 1) + "</a></li>";
                t.after(c + d + u)
            } ();
        var h = $("#pagination"),
            p = h.find("li"),
            g = "https://player.youku.com/embed/XMzY3NjQyMTkzMg==?autoplay=true",
            m = $(".video_button");
        function f() {
            $("iframe").remove(),
                $(".videoli").removeClass("playing"),
                m.css({
                    "z-index": "-1"
                })
        }
        function v(t) {
            var s = t,
                n = e.eq(s);
            n.hasClass("videoli") && !n.find(".video_iframe").hasClass("video-bg") && n.find(".video_iframe").addClass("video-bg"),
            n.hasClass("imgli") && o(t),
                e.eq(i).css("z-index", "900"),
                e.eq(s).css({
                    "z-index": "800"
                }).removeClass("hide"),
                p.eq(s).addClass("current").siblings("li").removeClass("current"),
                e.eq(i).addClass("hide").find(".txt-banner").css("color", e.eq(s).attr("data-txt")),
                e.eq(s).fadeIn(1e3),
                i = s,
                $("#full-screen-slider").css({
                    "background-color": e.eq(s).attr("data-id")
                }),
                e.eq(s).find(".txt-banner").css({
                    color: e.eq(s).attr("data-txt")
                }),
                a[0].className = "pro-reset"
        }
        function b() {
            setTimeout(function() {
                    a[0].className = "pro-run"
                },
                50)
        }
        function T() {
            a[0].className = "pro-reset";
            var t = i + 1;
            if (1 == n);
            else {
                var c = t + 1,
                    d = e.eq(c);
                d.hasClass("imgli") && o(c),
                d.hasClass("videoli") && !d.find(".video_iframe").hasClass("video-bg") && d.find(".video_iframe").addClass("video-bg"),
                    i < s ? (e.eq(i).css("z-index", "900"), e.eq(t).css({
                        "z-index": "800"
                    }).removeClass("hide"), p.eq(t).addClass("current").siblings("li").removeClass("current"), e.eq(i).addClass("hide").find(".txt-banner").css("color", e.eq(t).attr("data-txt")), e.eq(t).removeClass("hide"), $("#full-screen-slider").css({
                        "background-color": e.eq(t).attr("data-id")
                    }), e.eq(t).find(".txt-banner").css({
                        color: e.eq(t).attr("data-txt")
                    }), i += 1, f()) : (t = 0, e.eq(i).css("z-index", "900"), e.eq(t).stop(!0, !0).css({
                        "z-index": "800"
                    }).removeClass("hide"), e.eq(i).addClass("hide").find(".txt-banner").css("color", e.eq(t).attr("data-txt")), e.eq(0).removeClass("hide"), $("#full-screen-slider").css({
                        "background-color": e.eq(0).attr("data-id")
                    }), e.eq(0).find(".txt-banner").css({
                        color: e.eq(0).attr("data-txt")
                    }), p.eq(t).addClass("current").siblings("li").removeClass("current"), i = 0),
                    b(),
                    r = setTimeout(T, l)
            }
        }
        //is_cn || (g = "https://www.youtube.com/embed/D070ieurn04?rel=0&amp;showinfo=0;autoplay=true"),
            $(".video_iframe").on("click",
                function() {
                    $(this).html("<iframe src=" + g + " allowfullscreen=true></iframe>"),
                        m.css({
                            "z-index": "10999"
                        }),
                        $(this).parents("li").addClass("playing"),
                        n = 1,
                        clearTimeout(r),
                        a[0].className = "pro-reset",
                        setTimeout(function() {
                                $("#pagination, #slides").on("mouseleave",
                                    function() {
                                        n = 0,
                                            T(),
                                            $("#pagination, #slides").off("mouseleave")
                                    })
                            },
                            6e4)
                }),
            h.find("li:gt(0)").on("click", f),
            p.eq(0).addClass("current"),
            m.on("click",
                function() {
                    v($(this).parents("li").next().index()),
                        f()
                }),
            p.on("click",
                function() {
                    "current" != $(this).attr("class") && v($(this).index())
                }),
            $("#pagination li, #slides li").on("mouseenter",
                function() {
                    n = 1,
                        clearTimeout(r),
                        a[0].className = "pro-reset"
                }).on("mouseleave",
                function() {
                    $(this).hasClass("playing") || (n = 0, r = setTimeout(T, 5e3), b())
                }),
            b(),
            r = setTimeout(T, 5e3);
        document.getElementById("innerProg");
        function _() {
            var t = $(window).width(),
                e = (t - 1280) / 2,
                a = (t - 1024) / 2,
                s = $(".left_con"),
                i = $("#sCon");
            1280 == s.width() ? i.css("left", e) : 1024 == s.width() && i.css("left", a),
            t < 1128 && i.css("left", "30px")
        }
        _();
        var C = null;
        $(window).on("resize",
            function() {
                C && clearTimeout(C),
                    C = setTimeout(function() {
                            _()
                        },
                        200)
            }),
            $(".banner-coininfo").each(function(t) {
                $(this).text(function(t, e) {
                    if (2 * t.length <= e) return t;
                    for (var a = 0,
                             s = "",
                             i = 0; i < t.length; i++) if (s += t.charAt(i), t.charCodeAt(i) > 128) {
                        if ((a += 2) >= e) return s.substring(0, s.length - 1) + "..."
                    } else if ((a += 1) >= e) return s.substring(0, s.length - 2) + "...";
                    return s
                } ($(this).text(), 128))
            }),
        0 == $.cookie("total_fund") && ($("#showHideBtn").addClass("view-hide").removeClass("view-show"), $(".total-num").hide(), $(".hide-num").text("******").show()),
        0 == parseFloat($("#assetBTC").text()) && $(".z-hide,#zHide").hide();
        var y = get_element("showHideBtn");
        y && (y.onclick = function() {
            var t = $(".total-num"),
                e = $(".hide-num");
            "none" == t[0].style.display ? (this.className = "view-show", get_element("zHide") && "none" == get_element("zHide").style.display ? t.show() : get_element("cnyNum").style.display = "block", e.hide(), $.cookie("total_fund", 1, {
                expires: 365,
                path: "/"
            })) : (this.className = "view-hide", t.hide(), e.text("******").show(), $.cookie("total_fund", 0, {
                expires: 365,
                path: "/"
            }))
        })
    });
