Introduction
============

目录结构:
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

依赖环境:
1.服务器:建议使用类Unix系统
2.需要手动安装nginx、php、mysql下载当前的稳定版即可
3.用到的php扩展:curl.so、zip.so、gd.so

注意: 请将nginx、php、私有云项目 的用户和组所属保持一致，比如都为www:www

在nginx上如何配置项目:

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

如何安装:
例： admin.domain.com 指向了/var/hosts/com/domain/admin 目录

打开admin.domain.com 会自动跳转到安装页面、按顺序安装即可。

需要执行的脚本：

例:项目放在了:/var/hosts/com/domain/admin, php的执行目录为:/usr/local/php/bin/php

脚本需要每天0点来执行一次，用来统计验证信息
/usr/local/php/bin/php /var/hosts/com/domain/admin/api/index.php webApi Cron auth_statistics
