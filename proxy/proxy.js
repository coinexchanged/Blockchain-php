const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');

const app = express();
const proxy = createProxyMiddleware({
    target: 'https://bakk.bizzan.com.cn', changeOrigin: true, onProxyRes: (proxyRes, req, res) => {
        console.log(proxyRes.headers);
        delete proxyRes.headers['access-control-allow-origin'];
        delete proxyRes.headers['access-control-allow-headers'];

        proxyRes.headers['access-control-allow-origin'] = '*';
        proxyRes.headers['Access-Control-Allow-Headers'] = 'Origin, X-Requested-With, content-Type, Accept, Authorization,lang';
    }
});
// console.log(proxy);
app.use('/', proxy);
app.listen(8070);