Introduction
============

一、目录结构:
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

二、依赖环境:
1.服务器:建议使用类Unix系统
2.需要手动安装nginx、php、mysql下载当前的稳定版即可
3.用到的php扩展:curl.so、zip.so、gd.so

注意: 请将nginx、php、私有云项目 的用户和组所属保持一致，比如都为www:www


三、二级目录配置


若项目安装在某个域名的二级目录下，可采用以下配置方式：


如主目录为：/var/hosts/com/domain/admin
二级目录为：/var/hosts/com/domain/admin/private

修需改/var/hosts/com/domain/admin/private/controller/secken.js文件中的第三行，将
base_dir: '', 配置改为     base_dir: '/private', 


四、如何安装:
例： admin.domain.com 指向了/var/hosts/com/domain/admin 目录

打开admin.domain.com 会自动跳转到安装页面、按顺序安装即可。


五、需要执行的脚本：

例:项目放在了:/var/hosts/com/domain/admin, php的执行目录为:/usr/local/php/bin/php

脚本需要每天0点来执行一次，用来统计验证信息
/usr/local/php/bin/php /var/hosts/com/domain/admin/api/index.php webApi Cron auth_statistics
