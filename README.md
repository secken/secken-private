洋葱企业内网验证系统安装指南
============

# 快速安装脚本

此脚本在以下环境和机器测试通过:

- Microsoft Azure: Ubuntu 14.04 x64 (Trusty)
- Microsoft Azure: CentOS 7.0 (Trusty)

**此脚本是方便小白用户快速安装体验用，需要一个全新的Linux系统环境，我们不确保其适用所有情况。**

## 安装使用脚本

### For Ubuntu and Debian

中国版：
```
wget https://coding.net/u/secken/p/secken-private/git/raw/master/pages/install/setup-cn.sh
sudo sh setup-cn.sh
```
国际版：
```
wget https://github.com/secken/secken-private/raw/master/pages/install/setup.sh
sudo sh setup.sh
```
### For CentOS

中国版：
```
sudo yum install wget
wget https://coding.net/u/secken/p/secken-private/git/raw/master/pages/install/setup-cn.sh
sudo sh setup-cn.sh
```
国际版：
```
sudo yum install wget
wget https://github.com/secken/secken-private/raw/master/pages/install/setup.sh
sudo sh setup.sh
```

# 手动安装资源

## 国内外安装源：
你可以根据自己的安装环境，使用以下指令下载最新的程序:
* 国际版安装源：
```
wget -c https://github.com/secken/secken-private/archive/master.zip -O secken_private_cloud.zip --no-check-certificate
```
* 中国版安装源：
```
wget -c https://coding.net/u/secken/p/secken-private/git/archive/master.zip -O secken_private_cloud.zip --no-check-certificate
```

## 国内外镜像源
* 国际版镜像源：
https://github.com/secken/secken-private
* 中国版镜像源：
https://coding.net/u/secken/p/secken-private

# 手动安装指南

## 目录结构:
```
rootdir
  |
  |--api        私有云接口目录，php接口所在目录
  |--bootstrap  bootstrap 相关js、css库文件
  |--controller 路由控制js
  |--dist       界面模块相关js、css、img
  |--pages      html 界面模板
  |--plugins    前端开发相关插件
  |--index.html 首页
  |--login.html 登录
```

## 依赖环境:
1.服务器:建议使用类Unix系统
2.需要手动安装nginx、php、mysql下载当前的稳定版即可
3.用到的php扩展:curl.so、zip.so、gd.so

注意: 请将nginx、php、私有云项目 的用户和组所属保持一致，比如都为www:www

## Rewrite配置

因为受制于php CI框架限制，为了统一API的调用格式，在配置webserver时，必须针对/api的请求启用rewrite规则，以nginx为例


server {
    listen       80;
    server_name  admin.domain.com;

    access_log  logs/wp-secken.access.log  main;
    root /var/hosts/com/domain/admin;

    location / {
        index  index.html index.htm;
    }

    location /api/{
        index  index.php index.html index.htm;

        if ($request_filename !~ (resources|js|css|images|robots\.txt|index\.php)) {
             rewrite ^/(.+)$ /api/index.php last;
        }
    }

    location ~ \.php$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}



## 二级目录配置


若项目安装在某个域名的二级目录下，可采用以下配置方式：


如主目录为：/var/hosts/com/domain/admin
二级目录为：/var/hosts/com/domain/admin/private

1. 修需改/var/hosts/com/domain/admin/private/controller/secken.js文件中的第三行，将
base_dir: '', 配置改为     base_dir: '/private',
2. 并修改相应的rewrite配置


    location /private/api/{
        index  index.php index.html index.htm;

        if ($request_filename !~ (resources|js|css|images|robots\.txt|index\.php)) {
             rewrite ^/(.+)$ /private/api/index.php last;
        }
    }


## 如何安装:
例： admin.domain.com 指向了/var/hosts/com/domain/admin 目录

打开admin.domain.com 会自动跳转到安装页面、按顺序安装即可。


## 需要执行的脚本：

例:项目放在了:/var/hosts/com/domain/admin, php的执行目录为:/usr/local/php/bin/php

脚本需要每天0点来执行一次，用来统计验证信息
/usr/local/php/bin/php /var/hosts/com/domain/admin/api/index.php webApi Cron auth_statistics


## 安装成功之后，API地址即： http://admin.domain.com/api/access/

在Raduis和LDAP代理模块的响应配置文件中的API链接改为该地址前缀。

## 联系我们

技术支持：<support@secken.com><br>
洋葱官网：https://www.yangcong.com<br>
Copyright (C) 2014-2015 Secken, Inc. 