# php交易所源码
## 安装
#### 环境搭建
可通过安装宝塔搭建环境，也可自行搭建
```
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh ed8484bec
```
新版宝塔会强制绑定手机号，可通过降级包解决
### 环境

|软件|版本|备注|
|----|----|----|
|Nginx| 1.18|开启静态化|
|MySQL| 5.6||
|PHP|7.2|不兼容5.6和8，会出问题|
|phpMyAdmin| 4.7|可以不安装|
|Redis| 7.0 |必须安装|
|elasticsearch|7.0|

### 删除所有禁用函数

### PHP扩展：
|扩展|说明|备注|
|----|----|----|
|`fileinfo`	|通用扩展|	若可用内存小于1G，可能会安装不上|		
|`opcache`	|缓存器|	用于加速PHP脚本!|
|`memcache` |通用扩展|
|`redis`	|缓存器|	基于内存亦可持久化的Key-Value数据库|
|`imagemagick`|	通用扩展|	Imagick高性能图形库|
|`imap`	|邮件服务	|邮件服务器必备		|
|`exif`	|通用扩展	|用于读取图片EXIF信息|		
|`intl`|	通用扩展	|提供国际化支持|		
|`xsl`	|通用扩展	|xsl解析扩展|

### 服务器要求
CentOS 7.6.1810(Py2.7.5)
4核8G 容量大于50G
## python
```
pip -V
 
pip install --upgrade pip
 
pip install websocket-client
 
pip install redis
 
pip install websocket
```
## 配置文件
启动前需要先修改.env文件中对应的各种配置，如mysql、redis、wallet等
## 清理缓存
```
php artisan config:cache
```
## 设置代理

```
  location ~/(wss|socket.io) {
    # 此处改为 socket.io 后端的 ip 和端⼝即可 
    proxy_pass http://127.0.0.1:2000; 
    proxy_set_header Upgrade $http_upgrade; proxy_set_header Connection "upgrade";
    proxy_http_version 1.1;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $host;
  }
```
## es安装
```
rpm --import https://artifacts.elastic.co/GPG-KEY-elasticsearch
vi /etc/yum.repos.d/elasticsearch.repo
---------------------------------------------
[elasticsearch-7.x]
name=Elasticsearch repository for 7.x packages
baseurl=https://artifacts.elastic.co/packages/7.x/yum
gpgcheck=1
gpgkey=https://artifacts.elastic.co/GPG-KEY-elasticsearch
enabled=1
autorefresh=1
type=rpm-md
---------------------------------------------
yum install elasticsearch -y
service elasticsearch restart
```
## 然后添加计划任务
```
cd /www/wwwroot/Site/public/vendor/webmsgsender
 
php start.php start -d
```
  
```
cd /www/wwwroot/Site
 
php artisan websocket:client restart
```
  
```
cd /www/wwwroot/Site/python
 
pkill python3
 
python3 main.py
```
## 数据库 
上传futures.sql之后 附加到数据库，然后修改.env  数据库futures 账号futures 密码futures

## 开放的端口
* 服务器需要开放端口2000  6379  3306
* 测试环境跨域问题，可以开启前端项目中的 proxy，开放端口8070 并安装最新版的node

## 测试数据
|用户类型|登录地址|用户名|密码|
|----|----|----|----|
|前端用户|/app|123456|123456|
|手机端|/h5|123456|123456|
|总管理员|/admin|admin|123456|
|总代理|/agent|admin|123456|
