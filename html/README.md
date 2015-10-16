Introduction
============

目录结构:
yangcong_private
  |
  |--api   私有云接口目录，php接口所在目录
  |--html  私有云前端目录，所有的html和js都在这里

依赖环境:
1.服务器:建议使用类Unix系统
2.需要手动安装nginx、php、mysql下载当前的稳定版即可
3.用到的php扩展:curl.so、zip.so、gd.so

需要用到两个域名，分别指向yangcong_private/api 和yangcong_private/html
打开yangcong_private/html/controller/secken.js 在最顶端添加指向yangcong_private/api目录的域名地址

如何安装:
例： html.yc.com 指向了yangcong_private/html目录

打开html.yc.com 会自动跳转到安装页面、按顺序安装即可。

需要执行的脚本：

例:项目放在了:/usr/local/nginx/html/yangcong_private_git, php的执行目录为:/usr/local/php/bin/php

脚本需要每天0点来执行一次，用来统计验证信息
/usr/local/php/bin/php /usr/local/nginx/html/yangcong_private_git/api/index.php webApi Cron auth_statistics
