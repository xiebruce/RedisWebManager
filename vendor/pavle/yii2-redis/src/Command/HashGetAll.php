<?php
/**
 * User: Pavle Lee <523260513@qq.com>
 * Date: 2017/8/26
 * Time: 16:11
 */

namespace pavle\yii\redis\Command;


use Predis\Command\Command;

class HashGetAll extends Command
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'HGETALL';
    }

    /**
     * {@inheritdoc}
     */
    public function parseResponse($data)
    {
        return $data;
    }
}