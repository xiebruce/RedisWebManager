<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-24
 * Time: 00:57
 */

namespace app\models;

use yii\base\BaseObject;

class RedisRawCmd extends BaseObject {
	private $sock;
	private $hostname;
	private $port;
	private $timeout;
	
	private $command;
	private $key;
	private $methodList;
	
	public $commands = [
		'APPEND' => 'APPEND key value',
		'AUTH' => 'AUTH password',
		'BGREWRITEAOF' => 'BGREWRITEAOF ',
		'BGSAVE' => 'BGSAVE ',
		'BITCOUNT' => 'BITCOUNT key [start end]',
		'BITFIELD' => 'BITFIELD key [GET type offset] [SET type offset value] [INCRBY type offset increment] [OVERFLOW WRAP|SAT|FAIL]',
		'BITOP' => 'BITOP operation destkey key [key ...]',
		'BITPOS' => 'BITPOS key bit [start] [end]',
		'BLPOP' => 'BLPOP key [key ...] timeout',
		'BRPOP' => 'BRPOP key [key ...] timeout',
		'BRPOPLPUSH' => 'BRPOPLPUSH source destination timeout',
		'BZPOPMIN' => 'BZPOPMIN key [key ...] timeout',
		'BZPOPMAX' => 'BZPOPMAX key [key ...] timeout',
		'CLIENT ID' => 'CLIENT ID',
	    'CLIENT KILL' => 'CLIENT KILL [ip:port] [ID client-id] [TYPE normal|master|slave|pubsub] [ADDR ip:port] [SKIPME yes/no]',
	    'CLIENT LIST' => 'CLIENT LIST [TYPE normal|master|replica|pubsub]',
	    'CLIENT GETNAME' => 'CLIENT GETNAME ',
	    'CLIENT PAUSE' => 'CLIENT PAUSE timeout',
	    'CLIENT REPLY' => 'CLIENT REPLY ON|OFF|SKIP',
	    'CLIENT SETNAME' => 'CLIENT SETNAME connection-name',
	    'CLIENT UNBLOCK' => 'CLIENT UNBLOCK client-id [TIMEOUT|ERROR]',
	    'CLUSTER ADDSLOTS' => 'CLUSTER ADDSLOTS slot [slot ...]',
	    'CLUSTER COUNT-FAILURE-REPORTS' => 'CLUSTER COUNT-FAILURE-REPORTS node-id',
	    'CLUSTER COUNTKEYSINSLOT' => 'CLUSTER COUNTKEYSINSLOT slot',
	    'CLUSTER DELSLOTS' => 'CLUSTER DELSLOTS slot [slot ...]',
	    'CLUSTER FAILOVER' => 'CLUSTER FAILOVER [FORCE|TAKEOVER]',
	    'CLUSTER FORGET' => 'CLUSTER FORGET node-id',
	    'CLUSTER GETKEYSINSLOT' => 'CLUSTER GETKEYSINSLOT slot count',
	    'CLUSTER INFO' => 'CLUSTER INFO ',
	    'CLUSTER KEYSLOT' => 'CLUSTER KEYSLOT key',
	    'CLUSTER MEET' => 'CLUSTER MEET ip port',
	    'CLUSTER NODES' => 'CLUSTER NODES ',
	    'CLUSTER REPLICATE' => 'CLUSTER REPLICATE node-id',
	    'CLUSTER RESET' => 'CLUSTER RESET [HARD|SOFT]',
	    'CLUSTER SAVECONFIG' => 'CLUSTER SAVECONFIG ',
	    'CLUSTER SET-CONFIG-EPOCH' => 'CLUSTER SET-CONFIG-EPOCH config-epoch',
	    'CLUSTER SETSLOT' => 'CLUSTER SETSLOT slot IMPORTING|MIGRATING|STABLE|NODE [node-id]',
	    'CLUSTER SLAVES' => 'CLUSTER SLAVES node-id',
	    'CLUSTER REPLICAS' => 'CLUSTER REPLICAS node-id',
	    'CLUSTER SLOTS' => 'CLUSTER SLOTS ',
	    'COMMAND' => 'COMMAND ',
	    'COMMAND COUNT' => 'COMMAND COUNT ',
	    'COMMAND GETKEYS' => 'COMMAND GETKEYS ',
	    'COMMAND INFO' => 'COMMAND INFO command-name [command-name ...]',
	    'CONFIG GET' => 'CONFIG GET parameter',
	    'CONFIG REWRITE' => 'CONFIG REWRITE ',
	    'CONFIG SET' => 'CONFIG SET parameter value',
	    'CONFIG RESETSTAT' => 'CONFIG RESETSTAT ',
	    'DBSIZE' => 'DBSIZE ',
	    'DEBUG OBJECT' => 'DEBUG OBJECT key',
	    'DEBUG SEGFAULT' => 'DEBUG SEGFAULT ',
	    'DECR' => 'DECR key',
	    'DECRBY' => 'DECRBY key decrement',
	    'DEL' => 'DEL key [key ...]',
	    'DISCARD' => 'DISCARD ',
	    'DUMP' => 'DUMP key',
	    'ECHO' => 'ECHO message',
	    'EVAL' => 'EVAL script numkeys key [key ...] arg [arg ...]',
	    'EVALSHA' => 'EVALSHA sha1 numkeys key [key ...] arg [arg ...]',
	    'EXEC' => 'EXEC ',
	    'EXISTS' => 'EXISTS key [key ...]',
	    'EXPIRE' => 'EXPIRE key seconds',
	    'EXPIREAT' => 'EXPIREAT key timestamp',
	    'FLUSHALL' => 'FLUSHALL [ASYNC]',
	    'FLUSHDB' => 'FLUSHDB [ASYNC]',
	    'GEOADD' => 'GEOADD key longitude latitude member [longitude latitude member ...]',
	    'GEOHASH' => 'GEOHASH key member [member ...]',
	    'GEOPOS' => 'GEOPOS key member [member ...]',
	    'GEODIST' => 'GEODIST key member1 member2 [unit]',
	    'GEORADIUS' => 'GEORADIUS key longitude latitude radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] [ASC|DESC] [STORE key] [STOREDIST key]',
	    'GEORADIUSBYMEMBER' => 'GEORADIUSBYMEMBER key member radius m|km|ft|mi [WITHCOORD] [WITHDIST] [WITHHASH] [COUNT count] [ASC|DESC] [STORE key] [STOREDIST key]',
	    'GET' => 'GET key',
	    'GETBIT' => 'GETBIT key offset',
	    'GETRANGE' => 'GETRANGE key start end',
	    'GETSET' => 'GETSET key value',
	    'HDEL' => 'HDEL key field [field ...]',
	    'HEXISTS' => 'HEXISTS key field',
	    'HGET' => 'HGET key field',
	    'HGETALL' => 'HGETALL key',
	    'HINCRBY' => 'HINCRBY key field increment',
	    'HINCRBYFLOAT' => 'HINCRBYFLOAT key field increment',
	    'HKEYS' => 'HKEYS key',
	    'HLEN' => 'HLEN key',
	    'HMGET' => 'HMGET key field [field ...]',
	    'HMSET' => 'HMSET key field value [field value ...]',
	    'HSET' => 'HSET key field value',
	    'HSETNX' => 'HSETNX key field value',
	    'HSTRLEN' => 'HSTRLEN key field',
	    'HVALS' => 'HVALS key',
	    'INCR' => 'INCR key',
	    'INCRBY' => 'INCRBY key increment',
	    'INCRBYFLOAT' => 'INCRBYFLOAT key increment',
	    'INFO' => 'INFO [section]',
	    'KEYS' => 'KEYS pattern',
	    'LASTSAVE' => 'LASTSAVE ',
	    'LINDEX' => 'LINDEX key index',
	    'LINSERT' => 'LINSERT key BEFORE|AFTER pivot value',
	    'LLEN' => 'LLEN key',
	    'LPOP' => 'LPOP key',
	    'LPUSH' => 'LPUSH key value [value ...]',
	    'LPUSHX' => 'LPUSHX key value',
	    'LRANGE' => 'LRANGE key start stop',
	    'LREM' => 'LREM key count value',
	    'LSET' => 'LSET key index value',
	    'LTRIM' => 'LTRIM key start stop',
	    'MEMORY DOCTOR' => 'MEMORY DOCTOR ',
	    'MEMORY HELP' => 'MEMORY HELP ',
	    'MEMORY MALLOC-STATS' => 'MEMORY MALLOC-STATS ',
	    'MEMORY PURGE' => 'MEMORY PURGE ',
	    'MEMORY STATS' => 'MEMORY STATS ',
	    'MEMORY USAGE' => 'MEMORY USAGE key [SAMPLES count]',
	    'MGET' => 'MGET key [key ...]',
	    'MIGRATE' => 'MIGRATE host port key|"" destination-db timeout [COPY] [REPLACE] [KEYS key [key ...]]',
	    'MONITOR' => 'MONITOR ',
	    'MOVE' => 'MOVE key db',
	    'MSET' => 'MSET key value [key value ...]',
	    'MSETNX' => 'MSETNX key value [key value ...]',
	    'MULTI' => 'MULTI ',
	    'OBJECT' => 'OBJECT subcommand [arguments [arguments ...]]',
	    'PERSIST' => 'PERSIST key',
	    'PEXPIRE' => 'PEXPIRE key milliseconds',
	    'PEXPIREAT' => 'PEXPIREAT key milliseconds-timestamp',
	    'PFADD' => 'PFADD key element [element ...]',
	    'PFCOUNT' => 'PFCOUNT key [key ...]',
	    'PFMERGE' => 'PFMERGE destkey sourcekey [sourcekey ...]',
	    'PING' => 'PING [message]',
	    'PSETEX' => 'PSETEX key milliseconds value',
	    'PSUBSCRIBE' => 'PSUBSCRIBE pattern [pattern ...]',
	    'PUBSUB' => 'PUBSUB subcommand [argument [argument ...]]',
	    'PTTL' => 'PTTL key',
	    'PUBLISH' => 'PUBLISH channel message',
	    'PUNSUBSCRIBE' => 'PUNSUBSCRIBE [pattern [pattern ...]]',
	    'QUIT' => 'QUIT ',
	    'RANDOMKEY' => 'RANDOMKEY ',
	    'READONLY' => 'READONLY ',
	    'READWRITE' => 'READWRITE ',
	    'RENAME' => 'RENAME key newkey',
	    'RENAMENX' => 'RENAMENX key newkey',
	    'RESTORE' => 'RESTORE key ttl serialized-value [REPLACE]',
	    'ROLE' => 'ROLE ',
	    'RPOP' => 'RPOP key',
	    'RPOPLPUSH' => 'RPOPLPUSH source destination',
	    'RPUSH' => 'RPUSH key value [value ...]',
	    'RPUSHX' => 'RPUSHX key value',
	    'SADD' => 'SADD key member [member ...]',
	    'SAVE' => 'SAVE ',
	    'SCARD' => 'SCARD key',
	    'SCRIPT DEBUG' => 'SCRIPT DEBUG YES|SYNC|NO',
	    'SCRIPT EXISTS' => 'SCRIPT EXISTS sha1 [sha1 ...]',
	    'SCRIPT FLUSH' => 'SCRIPT FLUSH ',
	    'SCRIPT KILL' => 'SCRIPT KILL ',
	    'SCRIPT LOAD' => 'SCRIPT LOAD script',
	    'SDIFF' => 'SDIFF key [key ...]',
	    'SDIFFSTORE' => 'SDIFFSTORE destination key [key ...]',
	    'SELECT' => 'SELECT index',
	    'SET' => 'SET key value [expiration EX seconds|PX milliseconds] [NX|XX]',
	    'SETBIT' => 'SETBIT key offset value',
	    'SETEX' => 'SETEX key seconds value',
	    'SETNX' => 'SETNX key value',
	    'SETRANGE' => 'SETRANGE key offset value',
	    'SHUTDOWN' => 'SHUTDOWN [NOSAVE|SAVE]',
	    'SINTER' => 'SINTER key [key ...]',
	    'SINTERSTORE' => 'SINTERSTORE destination key [key ...]',
	    'SISMEMBER' => 'SISMEMBER key member',
	    'SLAVEOF' => 'SLAVEOF host port',
	    'REPLICAOF' => 'REPLICAOF host port',
	    'SLOWLOG' => 'SLOWLOG subcommand [argument]',
	    'SMEMBERS' => 'SMEMBERS key',
	    'SMOVE' => 'SMOVE source destination member',
	    'SORT' => 'SORT key [BY pattern] [LIMIT offset count] [GET pattern [GET pattern ...]] [ASC|DESC] [ALPHA] [STORE destination]',
	    'SPOP' => 'SPOP key [count]',
	    'SRANDMEMBER' => 'SRANDMEMBER key [count]',
	    'SREM' => 'SREM key member [member ...]',
	    'STRLEN' => 'STRLEN key',
	    'SUBSCRIBE' => 'SUBSCRIBE channel [channel ...]',
	    'SUNION' => 'SUNION key [key ...]',
	    'SUNIONSTORE' => 'SUNIONSTORE destination key [key ...]',
	    'SWAPDB' => 'SWAPDB index index',
	    'SYNC' => 'SYNC ',
	    'TIME' => 'TIME ',
	    'TOUCH' => 'TOUCH key [key ...]',
	    'TTL' => 'TTL key',
	    'TYPE' => 'TYPE key',
	    'UNSUBSCRIBE' => 'UNSUBSCRIBE [channel [channel ...]]',
	    'UNLINK' => 'UNLINK key [key ...]',
	    'UNWATCH' => 'UNWATCH ',
	    'WAIT' => 'WAIT numreplicas timeout',
	    'WATCH' => 'WATCH key [key ...]',
	    'ZADD' => 'ZADD key [NX|XX] [CH] [INCR] score member [score member ...]',
	    'ZCARD' => 'ZCARD key',
	    'ZCOUNT' => 'ZCOUNT key min max',
	    'ZINCRBY' => 'ZINCRBY key increment member',
	    'ZINTERSTORE' => 'ZINTERSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX]',
	    'ZLEXCOUNT' => 'ZLEXCOUNT key min max',
	    'ZPOPMAX' => 'ZPOPMAX key [count]',
	    'ZPOPMIN' => 'ZPOPMIN key [count]',
	    'ZRANGE' => 'ZRANGE key start stop [WITHSCORES]',
	    'ZRANGEBYLEX' => 'ZRANGEBYLEX key min max [LIMIT offset count]',
	    'ZREVRANGEBYLEX' => 'ZREVRANGEBYLEX key max min [LIMIT offset count]',
	    'ZRANGEBYSCORE' => 'ZRANGEBYSCORE key min max [WITHSCORES] [LIMIT offset count]',
	    'ZRANK' => 'ZRANK key member',
	    'ZREM' => 'ZREM key member [member ...]',
	    'ZREMRANGEBYLEX' => 'ZREMRANGEBYLEX key min max',
	    'ZREMRANGEBYRANK' => 'ZREMRANGEBYRANK key start stop',
	    'ZREMRANGEBYSCORE' => 'ZREMRANGEBYSCORE key min max',
	    'ZREVRANGE' => 'ZREVRANGE key start stop [WITHSCORES]',
	    'ZREVRANGEBYSCORE' => 'ZREVRANGEBYSCORE key max min [WITHSCORES] [LIMIT offset count]',
	    'ZREVRANK' => 'ZREVRANK key member',
	    'ZSCORE' => 'ZSCORE key member',
	    'ZUNIONSTORE' => 'ZUNIONSTORE destination numkeys key [key ...] [WEIGHTS weight [weight ...]] [AGGREGATE SUM|MIN|MAX]',
	    'SCAN' => 'SCAN cursor [MATCH pattern] [COUNT count]',
	    'SSCAN' => 'SSCAN key cursor [MATCH pattern] [COUNT count]',
	    'HSCAN' => 'HSCAN key cursor [MATCH pattern] [COUNT count]',
	    'ZSCAN' => 'ZSCAN key cursor [MATCH pattern] [COUNT count]',
	    'XINFO' => 'XINFO [CONSUMERS key groupname] [GROUPS key] [STREAM key] [HELP]',
	    'XADD' => 'XADD key ID field string [field string ...]',
	    'XTRIM' => 'XTRIM key MAXLEN [~] count',
	    'XDEL' => 'XDEL key ID [ID ...]',
	    'XRANGE' => 'XRANGE key start end [COUNT count]',
	    'XREVRANGE' => 'XREVRANGE key end start [COUNT count]',
	    'XLEN' => 'XLEN key',
	    'XREAD' => 'XREAD [COUNT count] [BLOCK milliseconds] STREAMS key [key ...] ID [ID ...]',
	    'XGROUP' => 'XGROUP [CREATE key groupname id-or-$] [SETID key groupname id-or-$] [DESTROY key groupname] [DELCONSUMER key groupname consumername]',
	    'XREADGROUP' => 'XREADGROUP GROUP group consumer [COUNT count] [BLOCK milliseconds] [NOACK] STREAMS key [key ...] ID [ID ...]',
	    'XACK' => 'XACK key group ID [ID ...]',
	    'XCLAIM' => 'XCLAIM key group consumer min-idle-time ID [ID ...] [IDLE ms] [TIME ms-unix-time] [RETRYCOUNT count] [FORCE] [JUSTID]',
	    'XPENDING' => 'XPENDING key group [start end count] [consumer]',
	];
	
	private $excludeCmd = [
		'AUTH' => 'You don\'t need to auth by yourself',
		'MULTI' => '',
		'SUBSCRIBE' => '',
		'UNSUBSCRIBE' => '',
		'PSUBSCRIBE' => '',
		'PUNSUBSCRIBE' => '',
		'PUBLISH' => '',
		'WATCH' => '',
		'UNWATCH' => '',
		'MONITOR' => '',
		'SLAVEOF' => '',
		'SELECT' => 'Please select db on select form',
		'exec' => '',
		'discard' => '',
		'sync' => '',
		'bgsave' => '',
		'bgrewriteaof' => '',
		'shutdown' => '',
		'quit' => '',
	];
	
	public function __get($propertyName){
		if(in_array($propertyName, ['commands', 'excludeCmd'])){
			return $this->$propertyName;
		}
	}
	
	/**
	 * RedisRawCmd constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		if(PHP_OS == 'Darwin'){
			ini_set("auto_detect_line_endings", true);
		}
		if(!isset($config['hostname'])){
			trigger_error("hostname needed, format\n\narray(\n'hostname' => 'your_redis_hostname',\n'port' => 'your_redis_port',\n//timeout is optional, unit is sec, default 3\n'timeout' => 3\n)", E_USER_ERROR);
		}
		if(!isset($config['port'])){
			trigger_error("Port needed, format\n\narray(\n'hostname' => 'your_redis_hostname',\n'port' => 'your_redis_port',\n//timeout is optional, unit is sec, default 3\n'timeout' => 3\n)", E_USER_ERROR);
		}
		$this->hostname = $config['hostname'];
		$this->port = $config['port'];
		$this->timeout = $config['timeout'] ?? 3;
		$this->connect();
		$this->methodList = get_class_methods(__CLASS__);
	}
	
	/**
	 * RedisRawCmd destructor.
	 */
	public function __destruct()
	{
		$this->disconnect();
	}
	
	/**
	 *  Open socket
	 */
	private function connect(){
		if(!$this->sock){
			$this->sock = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);
			if ($errno || $errstr){
				$msg = ($errno ? " error {$errno}" : "") . ($errstr ? ", $errstr" : "");
				trigger_error($msg, E_USER_ERROR);
			}
			// set to unblocking mode
			$unblock = stream_set_blocking($this->sock, 0);
		}
	}
	
	/**
	 *  Disconnect socket
	 */
	private function disconnect() {
		if ($this->sock){
			@fclose($this->sock);
		}
		$this->sock = null;
	}
	
	/**
	 * Create command
	 * @param $command
	 *
	 * @return $this
	 */
	public function createCommand($command){
		$arr = explode(" ", $command);
		if(isset($arr[0]) && array_key_exists($arr[0], $this->commands)){
			$this->key = strtolower($arr[0]);
		}
		$this->command = strtolower($command) . "\n";
		return $this;
	}
	
	/**
	 * Execute command
	 * @return $this
	 */
	public function execute(){
		if(!$cmdLength = fwrite($this->sock, $this->command)){
			trigger_error('Execute command error', E_USER_ERROR);
		}
		return $this;
	}
	
	/**
	 * Get response from socket
	 * @return array
	 */
	public function getResponse(){
		$contents = $this->read();
		$data = [];
		if($contents){
			$method = 'format' . ucfirst($this->key);
			if(in_array($method, $this->methodList)){
				$data = $this->$method($contents);
			}else{
				$results = explode("\n", $contents);
				// $i = 0;
				foreach($results as $key=>$result){
					$match = [];
					/*if(preg_match('/\*(\d+)/', $result, $match)){
						$i++;
					}*/
					if($result!=''){
						if(!preg_match('/[\$|\*][\d+|-]/', $result, $match)){
							// $data[$i][] = $result;
							$result = preg_replace('/^\+(.*?)\s+/', '$1', $result);
							$result = preg_replace('/^:(.*?)\s+/', '(integer) $1', $result);
							$result = preg_replace('/(.*?)\s+/', '$1', $result);
							$data[] = $result;
						}
					}
				}
			}
		}
		return $data;
	}
	
	public function formatInfo($data){
		$newArr = [];
		if($data!=''){
			$results = explode("\n", $data);
			if(!empty($results)){
				$i = 0;
				foreach($results as $key=>$result){
					$match = [];
					if(preg_match('/\*(\d+)/', $result, $match)){
						$i++;
					}
					if(!preg_match('/[\$|\*](\d+)/', $result, $match) && $result!=''){
						$newArr[$i][] = $result;
					}
				}
			}
		}
		return $newArr;
	}
	
	public function formatScan($data){
		return $this->formatInfo($data);
	}
	public function formatHscan($data){
		return $this->formatInfo($data);
	}
	public function formatZscan($data){
		return $this->formatInfo($data);
	}
	
	/**
	 * Read return from socket
	 * @param int $length
	 *
	 * @return string
	 */
	public function read($length=100){
		$timeout = 5;
		$read = [$this->sock];
		$write = null;
		$e = null;
		// I/O多路复用模型允许我们同时等待多个套接字描述符是否就绪。Linux系统为实现I/O多路复用提供的最常见的一个函数是select函数，该函数允许进程指示内核等待多个事件中的任何一个发生，并只有在一个或多个事件发生或经历一段指定的时间后才唤醒它。
		// $read 参数用于指定被监控的socket的字符是否已经准备好(即是否已可以读取)
		// $write 参数用于指定对被监控的socket的写操作是否会被阻塞(由于这里只是读数据，所以写的话传空就行)
		// $e 参数用于指定对被监控的socket是否有高优先级异常数据(如果有说明发生了异常)，这里我们不做处理，所以传null
		// $timeout 超时时间，这个没什么好说的，超时了如果还没有数据可以读取，那就返回0
		$n = stream_select($read, $write, $e, $timeout);
		$content = '';
		if($n>0){
			// fgets() 从指定的文件指针中获取一行数据(可指定要获取的长度)，这里的文件指针就是socket文件
			while (($buffer = fgets($read[0], $length)) !== false) {
				$content .= $buffer;
			}
		}
		return $content;
	}
	
	/**
	 * Redis auth
	 * @param $password
	 *
	 * @return bool
	 */
	public function auth($password){
		$str = $this->createCommand('AUTH '.$password)->execute()->read();
		if($str!='' && substr($str,1,-2) == 'OK'){
			return true;
		}
		return false;
	}
	
	/**
	 * Select a db
	 * @param $db
	 *
	 * @return bool
	 */
	public function select($db){
		$str = $this->createCommand('SELECT '.$db)->execute()->read();
		if($str!='' && substr($str,1,-2) == 'OK'){
			return true;
		}
		return false;
	}
	
	/**
	 * Redis ping
	 * @return bool|string
	 */
	public function ping(){
		$str = $this->createCommand('PING')->execute()->read();
		if($str!=''){
			if($str!=''){
				$str = substr($str,1,-2);
				if($str == 'PONG'){
					return $str;
				}
			}
			return false;
		}
	}
}