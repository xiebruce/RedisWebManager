
RedisWebManager
===============
## Preview
**GIF Demo**: [https://imgur.com/kiYTz0V](https://imgur.com/kiYTz0V)

Home page:
![Xnip2019-02-22_17-01-28.png](https://img.xiebruce.top/2019/02/22/3a427db22d0603e50e9e0b9ec9b86020.png)

Check the value by popup(config option "valDisplayType" to "popup" at RedisWebManager/config/params.php):
<p align="center"><img src="https://img.xiebruce.top/2018/12/19/06b077de016906082fbd5018c2f0a831.jpg" title="Xnip2018-12-19_00-47-26.jpg" alt="Xnip2018-12-19_00-47-26.jpg"></p>

Check the value inline(config option "valDisplayType" to "inline" at RedisWebManager/config/params.php):
<p align="center"><img src="https://img.xiebruce.top/2018/12/19/bf7d905a82004282352a1768a293e489.jpg" title="Xnip2018-12-19_01-28-54.jpg" alt="Xnip2018-12-19_01-28-54.jpg"></p>

Check the value in new page(only when the data is too long, check it in new page will be more clear and comfortable):
![Xnip2019-02-22_17-03-26.png](https://img.xiebruce.top/2019/02/22/e43e2abbea4f76f41328c1cb8b8eaa09.png)

Overview server info:
![Xnip2019-02-22_16-56-56.png](https://img.xiebruce.top/2019/02/22/b7050c86f62f49389b7c99357f0a5125.png)

Web client:
![Xnip2019-02-22_16-57-24.png](https://img.xiebruce.top/2019/02/22/e14e9c7807699916ee03ebeb342a5b32.png)

As you can see above, RedisWebManager Show the origin value(maybe array or object), not just simply a json string or a serialized string, this is the advantage of this tool(unserializing string only support PHP for the present).

## 中文版本/Chinese Version
[中文版本](https://www.xiebruce.top/664.html)

## Features
- View the redis key list
- Preview value of a key(popup / inline / new page)
- Preview json as array, serialized object as an object(the most important reason I wrote this tool)
- Search by key prefix or suffix
- Delete key & batch delete keys
- Select to show which db
- Flush current db or flush all db
- View redis server info
- Use client directives on Web just like in Terminal

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
<p align="center"><img src="https://img.xiebruce.top/2018/12/19/8bddf1ceeb279d233e76af9d3e37cd2d.jpg" title="Xnip2018-12-19_01-20-59.jpg" alt="Xnip2018-12-19_01-20-59.jpg"></p>

## Set permission
```bash
sudo chmod -R 777 /path/to/RedisWebManager/runtime
sudo chmod -R 777 /path/to/RedisWebManager/web/assets
```

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
### 2019-02-22 v0.3.1
- Fix bug when click checkbox to select a key
- Fix bug when using not default port 6379
- Update README.md

### 2018-12-27 v0.3
- Add Overview server info
- Add Web redis-cli
- change redis client to predis(phpredis before)
- Use ajax search other than refreshing page
- Add load more button(remove pagination)

### 2018-12-19 v0.2
- Add inline preview manner.
- Add showing ttl of a key.
- Modified the popup window to bootstrap modal, not simply alert by js.
- Modified the new preview page, more beautiful now.
- Showing the array or object colorfully.
- fix many bugs.

### 2018-12-14 v0.1
- A tool that allows you to search, delete, batch delete redis key, preview value of key, flush current db or flush or db.

