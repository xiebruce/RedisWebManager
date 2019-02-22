<?php

namespace pavle\yii\redis\Profile;


class RedisVersion280 extends \Predis\Profile\RedisVersion280
{
    public function getSupportedCommands()
    {
        return array_merge(parent::getSupportedCommands(), [
            'HGETALL' => 'pavle\yii\redis\Command\HashGetAll',
            'CLIENT LIST' => 'pavle\yii\redis\Command\ClientList',
            'CLIENT SETNAME' => 'pavle\yii\redis\Command\ClientSetName',
        ]);
    }
}
