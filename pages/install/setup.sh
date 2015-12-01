#!/bin/bash

wget -c http://soft.vpser.net/lnmp/lnmp1.2-full.tar.gz
tar zxf lnmp1.2-full.tar.gz
cd lnmp1.2-full

# Setup Secken Domain
domain="ycprivate.com"
echo "Please enter your domain for Secken-private (default: ycprivate.com)"
read -p "Enter your domain: " domain

# Setup LNMP
bash install.sh lnmp

# Download Secken Private
echo 'download yangcong private-cloud'
wget -c https://github.com/secken/secken-private/archive/master.zip -O Secken_private_cloud.zip
mkdir -p temp
unzip Secken_private_cloud.zip -d temp
rm -rf temp/__MACOSX

mkdir -p /home/wwwroot/$domain
mv temp/secken*/* /home/wwwroot/$domain/
    chown -R www:www /home/wwwroot/$domain
rm -rf temp Secken_private_cloud.zip
server="server {\n\
    \tlisten       80;\n\
    \tserver_name  $domain;\n\
    \troot /home/wwwroot/$domain;\n\
    \tlocation / {\n\
        \t\tindex  index.html index.php index.htm;\n\
    \t}\n\
    \tlocation /api/{\n\
        \t\tindex  index.php index.html index.htm;\n\
        \t\tif (\$request_filename !~ (resources|js|css|images|robots\.txt|index\.php)) {\n\
             \t\t\trewrite ^/(.+)$ /api/index.php last;\n\
        \t\t}\n\
    \t}\n\
    \tlocation ~ \.php$ {\n\
        \t\tfastcgi_pass   unix:/tmp/php-cgi.sock;\n\
        \t\tfastcgi_index  index.php;\n\
        \t\tfastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;\n\
        \t\tinclude        fastcgi_params;\n\
    \t}\n\
}\n\
"
echo -e $server >> /usr/local/nginx/conf/vhost/$domain.conf
/usr/local/nginx/sbin/nginx -s reload
#/usr/local/mysql/bin/mysql -uroot -p$MyMysqlRootPWD -e 'create database yangcong;'
#/usr/local/mysql/bin/mysql -uroot -p$MyMysqlRootPWD -e 'CREATE USER yangcong@localhost IDENTIFIED BY "yangcongabc";'
#/usr/local/mysql/bin/mysql -uroot -p$MyMysqlRootPWD -e 'grant select,insert,update,delete on yangcong.* to yangcong@"%" Identified by "yangcongabc";'
