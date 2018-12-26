# Yii2 Predis Extension

### Yii2官方的redis 功能太弱，不能主从不能集群，现在用第三方Predis类库来操作redis，没有实现ActiveRecord功能

## 安装

```
composer require  mojifan/yii2-predis
```

## 配置

在配置文件添加如下配置：

单机：
```
'redis' => [
            'class' => 'mojifan\redis\Connection',
            'servers'=>[
                ['host' => '127.0.0.1','port'=> 6379],
            ],
        ],
```
redis集群：

```
'redis' => [
            'class' => 'mojifan\redis\Connection',
            'servers'=>[
                ['host' => '127.0.0.1','port'=> 6379],
                ['host' => '127.0.0.1','port'=> 6380],
                ['host' => '127.0.0.1','port'=> 6381],
            ],
            'options'=>['cluster' => 'redis'],
        ],
```

`servers`和`options`参数具体配置可以参考preids `Predis\Client($parameters, $options)`的`$parameters`和`$options`参数配置。

### session组件配置
```
        'session' => [
            'class' => 'mojifan\redis\Session',
        ],
```
### cache组件配置
```
'cache' => [
            'class' => 'mojifan\redis\Cache',
        ],
```
