<?php
/**
 * User: Pavle Lee <523260513@qq.com>
 * Date: 2017/8/26
 * Time: 16:29
 */

namespace pavle\yii\redis\Command;


class ClientList extends \Predis\Command\ServerClient
{
    /**
     * @inheritDoc
     */
    public function getArguments()
    {
        return ['LIST'];
    }


    /**
     * Parses the response to CLIENT LIST and returns a structured list.
     *
     * @param string $data Response buffer.
     *
     * @return string
     */
    protected function parseClientList($data)
    {
        return $data;
    }
}