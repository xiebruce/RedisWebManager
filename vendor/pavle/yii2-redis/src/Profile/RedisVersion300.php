<?php

namespace pavle\yii\redis\Profile;


class RedisVersion300 extends \Predis\Profile\RedisVersion300
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
