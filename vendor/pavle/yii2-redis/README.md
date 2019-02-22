# yii2-redis
Using (https://github.com/nrk/predis)[Predis] and yii2 combination
This extension is fully compatible with the yii2-redis extension, and you can use predis only if you replace the configuration

```
'redis' => [
    'class' => pavle\yii\redis\Connection::class,
    'parameters' => ['tcp://xx.xx.x.xx:30001', 'tcp://xx.xx.x.xx:30002', 'tcp://xx.xx.x.xx:30003'],
    //'parameters' => 'tcp://192.168.2.240:6379',
    'options' => ['cluster' => 'redis'],
],
```
Get Predis client

```
Yii::$app->redis->getClient();
```
