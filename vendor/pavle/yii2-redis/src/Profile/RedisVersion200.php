<?php

namespace pavle\yii\redis\Profile;


class RedisVersion200 extends \Predis\Profile\RedisVersion200
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
