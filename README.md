
RedisWebManager
===============
<p align="center"><img src="https://img.xiebruce.top/2018/12/14/a46a7759709cc3ee33f407e4cf1fa8c1.jpg" title="Xnip2018-12-14_00-22-03.jpg" alt="Xnip2018-12-14_00-22-03.jpg"></p>

## 中文版本/Chinese Version
[中文版本](https://www.xiebruce.top/664.html)

## Features
- View the redis key list
- Preview value of certain key(popup or in new page)
- Search by key prefix or suffix
- Delete one key & batch delete
- Select to show which db
- Flush current db or flush all db
- Redis server info

## Redis Config
redis config is located at(auto read redis_local.php at local while redis.php online):
```
RedisWebManager/config/redis.php
RedisWebManager/config/redis_local.php
```

## Flush password & quickSearch Key
```
RedisWebManager/config/params.php
RedisWebManager/config/params_local.php
```

## Login Account Config
```
RedisWebManager/models/User.php
```
<p align="center"><img src="https://img.xiebruce.top/2018/12/14/c1269f612b28ea8c2bbad37dd272741e.jpg" title="Xnip2018-12-14_14-56-49.jpg" alt="Xnip2018-12-14_14-56-49.jpg"></p>

## Nginx Config
```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    # deny accessing php files for the /assets directory
    location ~ ^/assets/.*\.php$ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~* /\. {
        deny all;
    }
}
```

## Update Log
### 2018-12-14 v0.1
- A tool that allows you to search, delete, batch delete redis key, preview value of key, flush current db or flush or db.

