<?php
/**
 * User: Pavle Lee <523260513@qq.com>
 * Date: 2017/5/17
 * Time: 13:59
 */

namespace pavle\yii\redis;

use pavle\yii\redis\Profile\RedisVersion200;
use pavle\yii\redis\Profile\RedisVersion220;
use pavle\yii\redis\Profile\RedisVersion240;
use pavle\yii\redis\Profile\RedisVersion260;
use pavle\yii\redis\Profile\RedisVersion280;
use pavle\yii\redis\Profile\RedisVersion300;
use pavle\yii\redis\Profile\RedisVersion320;
use Predis\Client;
use Predis\Profile\Factory;
use yii\db\Exception;
use yii\helpers\VarDumper;

class Connection extends \yii\redis\Connection
{
    /**
     * @var mixed Connection parameters for one or more servers.
     */
    public $parameters;

    /**
     * @var mixed Options to configure some behaviours of the client.
     */
    public $options;

    /**
     * @var Client redis connection
     */
    private $_socket = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Factory::define('2.0', RedisVersion200::class);
        Factory::define('2.2', RedisVersion220::class);
        Factory::define('2.4', RedisVersion240::class);
        Factory::define('2.6', RedisVersion260::class);
        Factory::define('2.8', RedisVersion280::class);
        Factory::define('3.0', RedisVersion300::class);
        Factory::define('3.2', RedisVersion320::class);
        Factory::define('dev', 'Predis\Profile\RedisUnstable');
        Factory::define('default', RedisVersion320::class);
    }

    /**
     * Returns a value indicating whether the DB connection is established.
     * @return bool whether the DB connection is established
     */
    public function getIsActive()
    {
        return $this->_socket !== false;
    }

    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     * @throws Exception if connection fails
     */
    public function open()
    {
        if ($this->_socket !== false) {
            return;
        }

        $trace1 = VarDumper::dumpAsString($this->parameters);
        $trace2 = VarDumper::dumpAsString($this->options);
        $trace = <<<EOL
Opening redis DB connection
-- Parameters : 
{$trace1}
-- Options :
{$trace2}
EOL;

        \Yii::trace($trace, __METHOD__);

        $this->_socket = new Client($this->parameters, $this->options);
        if (!$this->_socket) {
            \Yii::error("Failed to open redis DB connection", __CLASS__);
            $message = YII_DEBUG ? $trace : 'Failed to open redis DB connection.';
            throw new Exception($message);
        }

        $this->initConnection();
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        if ($this->_socket !== false) {
            $this->_socket->disconnect();
            $this->_socket = false;
        }
    }

    /**
     * Executes a redis command.
     * For a list of available commands and their parameters see http://redis.io/commands.
     *
     * The params array should contain the params separated by white space, e.g. to execute
     * `SET mykey somevalue NX` call the following:
     *
     * ```php
     * $redis->executeCommand('SET', ['mykey', 'somevalue', 'NX']);
     * ```
     *
     * @param string $name the name of the command
     * @param array $params list of parameters for the command
     * @return array|bool|null|string Dependent on the executed command this method
     * will return different data types:
     *
     * - `true` for commands that return "status reply" with the message `'OK'` or `'PONG'`.
     * - `string` for commands that return "status reply" that does not have the message `OK` (since version 2.0.1).
     * - `string` for commands that return "integer reply"
     *   as the value is in the range of a signed 64 bit integer.
     * - `string` or `null` for commands that return "bulk reply".
     * - `array` for commands that return "Multi-bulk replies".
     *
     * See [redis protocol description](http://redis.io/topics/protocol)
     * for details on the mentioned reply types.
     * @throws Exception for commands that return [error reply](http://redis.io/topics/protocol#error-reply).
     */
    public function executeCommand($name, $params = [])
    {
        $this->open();

        \Yii::trace("Executing Redis Command: {$name} " . join(' ', $params), __METHOD__);

        return $this->_socket->executeCommand(
            $this->_socket->createCommand($name, $params)
        );
    }

    /**
     * Get predis Client
     * @return Client
     */
    public function getClient()
    {
        $this->open();

        return $this->_socket;
    }
}
