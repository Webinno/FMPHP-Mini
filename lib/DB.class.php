<?php
/**
 * FMPHP-Mini (http://www.webinno.cn)
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * @package		FMPHP-Mini
 * @author		FMPHP Dev Team （QQ官方群：330488100）
 * @copyright	copyright (c) 2015 - 2016, 互联网创新实验室(http://www.webinno.cn)
 *
 * @license		
 * @link		http://www.webinno.cn/project/FMPHP-Mini
 * @link        https://github.com/Webinno/FMPHP-Mini
 * @since		Version 0.1
 * 
 * @filesource
 */

class DB {

	protected $link;
	protected $config;
	protected $dbhost, $dbuser, $dbpw, $dbcharset, $pconnect, $tablepre;

	protected $time, $goneaway = 5;
	protected $mTrxLevel = 0;
	protected $querynum = 0;
	static protected $histories;
	protected $lastQuery = '';
	protected $_params 	= array();
	protected $mDebug = DB_DEBUG;
	protected $life	= 10;

	function __construct($config = array()) {
		$this->config = $config ?  $config : $this->getDefaultConfig();
		$this->open($this->config);
	}

	protected function open($config) {

		//$dbhost, $dbuser, $dbpw, $dbname = '', $port = '', $dbcharset = '', $pconnect = 0, $tablepre='', $time = 0
		$this->dbhost = isset($config[0]) ? $config[0] : '';
		$this->dbuser = isset($config[1]) ? $config[1] : '';
		$this->dbpw   = isset($config[2]) ? $config[2] : '';
		$this->dbname = isset($config[3]) ? $config[3] : '';
		$this->dbport = isset($config[4]) ? $config[4] : '';
		$this->dbcharset = isset($config[5]) ? $config[5] : '';
		$this->pconnect  = isset($config[6]) ? $config[6] : '';
		$this->tablepre  = isset($config[7]) ? $config[7] : '';
		$this->time      = isset($config[8]) ? $config[8] : '';

		$option = array(
		PDO::ATTR_EMULATE_PREPARES   => true,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->dbcharset}'",
		);

		$dsn = !empty($config[4]) ?
		"mysql:dbname={$config[3]};host={$config[0]};port={$config[4]}" :
		"mysql:dbname={$config[3]};host={$config[0]}";

		try {
			$this->link = new PDO($dsn, $config[1], $config[2], $option);    // 可以多个连接

			if ($this->link) {
				$this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
		} catch(Exception $e ) {
			error_log('Connect failed: ' . $e->getMessage());
			//var_dump($e->getMessage());
		}
	}

	/**
	 * 析构方法
	 */
	function __destruct() {

	}

	/**
	 * 基本的SQl, 手动拼写Sql时用
	 * @param string $sql
	 * @return mysql_result $query_result
	 */
	public function query($sql, $fname='') {

		try {
			$result = $this->link->query($sql);
		} catch(Exception $e) {
			trigger_error('数据库发生错误:' . $e->getMessage());
			$result = false;
		}

		if($this->mDebug) {
			self::$histories[] = $sql;
			echo $sql . "<br />\n";
		}
		if($result) {
			return $result;
		}
		return false;
	}
	/**
	 * 获取最后插入ID
	 */
	public function lastInsertId() {
		return (int)$this->link->lastInsertId();
	}


	/**
	 * 调试用，用于打印数据查询
	 */
	public function debug($debug = true) {
		$this->mDebug = true == $debug;
	}

	function getDebug() {
		return $this->mDebug;
	}


	/**
	 * Escape string, deny injection
	 * @param string $string
	 * @return string
	 */
	static public function escapeString($string) {
		return addslashes($string);
	}
	/**
	 * get db config
	 *
	 * @return array
	 */
	function getDefaultConfig() {
		// return $GLOBALS['CONFIG_DATABASE'];
	}

	/**
	 * 根据条件获取一条记录
	 * @param string $table 表名
	 * @param mix $condition 条件
	 * @param array $option 查询选项
	 * @return record
	 */
	public function getTableRow($table,  $condition, $options=array()) {
		return $this->LimitQuery($table, array(
		'condition' => $condition,
		'one' 		=> isset($options['one']) ? $options['one'] : true,
		'select' 	=> isset($options['select']) ? $options['select'] : '*',
		));
	}


	public function select($table,  $options=array()) {
		return $this->DBLimitQuery($table,$options);
	}

	/**
	 * 根据条件获取有限条数记录
	 * @param string $table 表名
	 * @param array $options 查询选项
	 $options 可以包含 cache 选单，表示记录cache时间
	 * @return array of record
	 */
	private function limitQuery($table, $options=array()) {
		return $this->DBLimitQuery($table, $options);
	}

	/**
	 * 根据条件获取有限条数记录，从库中查询，并进行缓存
	 *
	 * @param string $table 表名
	 * @param array $option 查询选项
	 * @return array of record
	 */
	private function DBLimitQuery($table, $options=array()) {
		$condition 	= isset($options['condition']) ? $options['condition'] : null;    // $options['condition']
		$one 		= isset($options['one']) ? $options['one'] : false;    //  $options['one']

		$offset 	= isset($options['offset']) ? abs(intval($options['offset'])) : 0;    //  $options['offset']

		// size
		if($one) {
			$size = 1;
		} else {
			$size = isset($options['size']) ? abs(intval($options['size'])) : 0;
		}

		$order 		= isset($options['order']) ? $options['order'] : null;    // order
		$select 	= isset($options['select']) ? $options['select'] : '*';    // select

		$condition 	= $this->buildCondition( $condition );
		$condition 	= (null == $condition) ? null : "WHERE $condition";    // condition


		if($one) {
			$limitation = " LIMIT 1 ";
		} else {
			$limitation = $size ? "LIMIT $offset,$size" : null;
		}

		$sql = "SELECT $select FROM `$table` $condition $order $limitation";
		//echo $sql."<BR>";
		return $this->getQueryResult($sql, $one, $this->_params);
	}

	/**
	 * 执行真正的数据库查询
	 * @param string $sql
	 * @param bool $one 是否单条记录
	 * @return array of $record
	 */
	function getQueryResult($sql, $one=true, array $params = array()) {
		$ret 	= array();
		$stmt 	= $this->execute($sql, $params);
		if(!is_object($stmt)) {
			error_log('Error: bad sql - ' . $sql);
			error_log('Error: bad sql - ' . var_export($params, true));
			return array();
		} else {
			return $one ? $stmt->fetch() : $stmt->fetchAll();
		}
	}


	/**
	 * 插入一条记录
	 * @param string $table 表名
	 * @param array $condition 记录
	 * @return int $id
	 */
	function insert($table, $condition, $type=0) {
		$content 	= null;
		$sql = "INSERT INTO `$table`
				(`" . join('`,`', array_keys($condition)) . '`)
				values (' . join(',', array_fill(0, count($condition), '?')) . ')';

		$stmt = $this->execute($sql, array_values($condition));
		if(1==$type) {//用于不是自增id时的判断
			return is_object($stmt);
		}
		//		return $insertId;
		if ($stmt) {
			return $this->lastInsertId();
		} else {
			return false;
		}
	}


	/**
	 * 删除一条记录
	 * @param string $table 表名
	 * @param array $condition 条件
	 * @return int $id
	 */
	function delete($table=null, $condition = array()) {
		if ( null == $table || empty($condition) ) {
			return false;
		}
		$condition = $this->buildCondition($condition);
		//var_dump(	$condition);
		//echo '<br />';
		$condition = (null == $condition) ? null : "WHERE $condition";
		$sql       = "DELETE FROM `$table` $condition";
		$flag      = $this->execute($sql, $this->_params);

		return is_object($flag) ? $flag->rowCount() : false;
	}

	function execute($sql, array $params = array(), $retry = 0) {

		if ( $this->mDebug ) {
			$query = $this->showQuery($sql, $params);
			//echo "/*<!--\n";
			//debug($query, 2);
			//	echo "-->*/\n";
			self::$histories[] = $query;
			echo $query . "<br />\n";
		}
		try {
			$conn	= $this->link;
			$sth 	= $conn->prepare($sql);
			if(!is_object($sth))
			throw new Exception('Error: bad sql');
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$result = empty($params) ? $sth->execute() : $sth->execute(array_values($params));
			if(($sth->errorCode() == 2006) and !$retry) {
				$this->execute($sql, $params, $retry = 1);
			}
			//print_r($sth);
			//print_r($params);
		} catch(Exception $e) {
			// print_r($e);
			trigger_error('数据库发生错误:' . $e->getMessage());
			//debug($e->getMessage(), 1);
			$result = false;
		}

		$this->_params = array();
		if ( false == $result ) {
			return false;
		}

		return $sth;
	}

	/**
	 * 更新一条记录
	 * @param string $table 表名
	 * @param mix $id 更新条件
	 * @param mix $updaterow 修改内容
	 * @param string $pkname 主键
	 * @return boolean
	 */
	function update( $table = null,  $updaterow = array(), $id = null, $pkname = 'id' ) {
		if ( null==$table || empty($updaterow) || null==$id)
		return false;

		if ( is_array($id) ) {
			$condition = $this->buildCondition($id);
		} else {
			$condition = "`$pkname`='$id'";
		}

		$sql 		= "UPDATE `$table` SET ";
		$content 	= null;
		$updates 	= array();
		$v_str		= '?';

		foreach ( $updaterow as $k => $v ) {
			if(is_array($v)) {
				$str = $v[0]; //for 'count'=>array('count+1');
				$content .= "`$k`=$str,";
			} else {
				$updates[] 	= $v;
				$content .= "`$k`=$v_str,";
			}
		}

		$content 	= trim($content, ',');
		$sql 		.= $content;
		$sql 		.= " WHERE $condition";
		$result = $this->execute($sql, array_merge($updates, $this->_params));
		//var_dump($result);
		//var_dump($result->rowCount());

		return is_object($result) ? $result->rowCount() : false;
	}

	/**
	 * 获取表的字段列表
	 * @param string $table 表名
	 * @param $select_map 对应enum字段的解释
	 * @return array
	 */
	function getField($table, $select_map = array()) {
		$fields = array();
		$stmt	= $this->Query( "DESC `$table`" );
		if ($stmt) {
			$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
			while ( $r = $stmt->fetch() ) {
				$Field = $r['Field'];
				$Type = $r['Type'];

				$type = 'varchar';
				$cate = 'other';
				$extra = null;

				if ( preg_match( '/^id$/i', $Field ) )
				$cate = 'id';
				else if ( preg_match( '/^time_/i', $Field ) )
				$cate = 'time';
				else if ( preg_match ( '/_id$/i', $Field ) )
				$cate = 'fkey';


				if ( preg_match('/text/i', $Type ) ) {
					$type = 'text';
					$cate = 'text';
				}

				if ( preg_match('/date/i', $Type ) ) {
					$type = 'date';
					$cate = 'time';
				} else if ( preg_match( '/int/i', $Type) ) {
					$type = 'int';
				} else if ( preg_match( '/(enum|set)\((.+)\)/i', $Type, $matches ) ) {
					$type = strtolower($matches[1]);
					eval("\$extra=array($matches[2]);");
					$extra = array_combine($extra, $extra);

					foreach( $extra AS $k=>$v)
					$extra[$k] = isset($select_map[$k]) ? $select_map[$k] : $v;

					$cate = 'select';
				}

				$fields[] = array(
				'name' => $Field,
				'type' => $type,
				'extra' => $extra,
				'cate' => $cate,
				);
			}
		}
		return $fields;
	}

	/**
	 * 是否存在符合条件的记录
	 * @param string $table 表名
	 * @param array $condition
	 * @param boolean $returnid 是否返回记录id
	 * @return mixed (int)id /(array)record
	 */



	function exist($table, $condition=array(), $returnid = true, $order='') {
		if (!$condition)  {
			return false;
		}
		$row = $this->select($table, array(
		'select' =>  ($returnid ? 'id' : '1'),
		'condition' => $condition,
		'one' => true,
		'order' => $order));

		if($returnid) {
			return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
		} else {
			return empty($row) ? array() : $row;
		}
	}


	/**
	 * 组建QueryCondition
	 *
	 * @param mix $condition;
	 * @param string $logic, optional
	 * @return string $condition
	 */

	function buildCondition($condition=array(), $logic='AND') {
		if (!is_array( $condition ) || is_null($condition) ) {
			return $condition;
		}

		$logic = strtoupper( $logic );    // 逻辑符
		$content = null;

		foreach ( $condition as $k => $v ) {
			$v_str = ' ? ';    //
			$v_connect = '=';    // 连接符

			if ( is_numeric($k) ) {
				// 如果是数字键, 递归连接, and 连接
				$content .= ' ' . $logic . ' (' . $this->buildCondition( $v ) . ')';
				//  "() AND (Tanggq) AND (222222) AND ((a) AND (b))"
				continue;
			}

			// 如果是关联数组
			$maybe_logic = strtoupper($k);    // 逻辑符 = key
			if ( in_array($maybe_logic, array('AND','OR')) ) {
				// 如果键是 "AND" or "OR" 仅在下一级中使用
				$content .= $logic . ' (' . $this->buildCondition( $v, $maybe_logic ) . ')';
				continue;
			}

			if ( is_numeric($v) ) {
				$this->_params[] = $v;    //如果值是数字
			} else if ( is_null($v) ) {
				//如果值是null
				$v_connect = ' IS ';
				$v_str = 'NULL';
			} else if ( is_array($v) && ($c = count($v))) {
				// 如果值是数组且大于一个
				if (1<$c) {
					//多个值
					$this->_params = array_merge($this->_params, $v);    //添加参数
					$v_connect 	= 'IN(' . join(',', array_fill(0, $c, '?')) . ')';
					$v_str		= '';
				} else if ( empty($v) ) {
					//不可能出现, 因为是数组,且大于一
					$v_str = $k;
					$v_connect = '<>';
				} else {
					//
					$tmp_keys = array_keys($v);    // 取得键数组
					$v_connect = array_shift($tmp_keys);    // 移出开头的第一个

					if( is_numeric($v_connect) ) {// 如果是数字,返回"="
						$v_connect = '=';
					}

					$tmp_values = array_values($v);
					$v_s = array_shift($tmp_values);    // 取第一值

					if(is_array($v_s)) {
						// 如果是数组, 用in
						$v_str = 'IN (' . join(',', array_fill(0, count($v_s), '?')) . ')';
						$this->_params = array_merge($this->_params, $v_s);
					} else {
						// 如果不是, 设置 参数值
						$this->_params[] = $v_s;
					}
				}
			} else {
				// 其他情况，直接是值
				$this->_params[] = $v;
			}
			$content .= " $logic `$k` $v_connect $v_str ";
		}

		$content = preg_replace( '/^\s*'.$logic.'\s*/', '', $content );    // 删除空格开头的逻辑符
		$content = preg_replace( '/\s*'.$logic.'\s*$/', '', $content );    // 删除空格结束的逻辑符
		$content = trim($content);    // 删笔空格

		return $content;
	}

	/**
	 * 检查是否DB用于的Int
	 * @param mix $id
	 * @return int $id
	 */
	function checkInt(&$id, $is_abs=false) {
		if ( is_array($id) ) {
			foreach( $id AS $k => $o ) $id[$k] = $this->CheckInt($o);
			return $id;
		}

		if(!is_int($id))
		$id = intval($id);

		if(0>$id && $is_abs)
		return abs($id);
		else
		return $id;
	}

	/**
     * 检查是否DB用于的Array
     * @param mix $arr
     * @return int $arr
     */
	function checkArray(&$arr) {
		if ( !is_array($arr) ) {
			if(false===$arr)
			$arr = array();
			else
			settype($arr, 'array');
		}
		return $arr;
	}

	//Tools
	public function showQuery($query, $params)
	{
		$keys = $values = array();
		# build a regular expression for each parameter
		foreach ($params as $key=>$value) {
			if (is_string($key)) {
				$keys[] = '/:'.$key.'/';
			} else {
				$keys[] = '/[?]/';
			}

			//			if(is_numeric($value)) {
			//				$values[] = intval($value);
			//			} else {
			$values[] = '\''.$value .'\'';
			//			}
		}

		$query = preg_replace($keys, $values, $query, 1, $count);
		return $query;
	}

	function tableName( $name) {
		if ( $name[0] == '"' && substr( $name, -1, 1 ) == '"') {
			return $name;
		}
		if ( preg_match( '/(^|\s)(DISTINCT|JOIN|ON|AS|,)(\s|$)/i', $name ) !== 0 ) {
			return $name;
		}
		$dbDetails = array_reverse( explode( '.', $name, 2 ) );
		if ( isset( $dbDetails[1] ) ) {
			list( $table, $database ) = $dbDetails;
		} else {
			list( $table ) = $dbDetails;
		}

		$prefix = $this->tablepre; # Default prefix
		if ( isset( $database ) ) {
			$prefix = '';
		}

		$table = "{$prefix}{$table}";
		# Merge our database and table into our final table name.
		$tableName = ( isset( $database ) ? "{$database}.{$table}" : "{$table}" );
		return $tableName;
	}

	function showFields($table, $print =0) {
		$fileds = $this->getField($table);
		$arr = array();
		if ($fileds) {
			foreach ($fileds as $item) {
				$arr[] = $item['name'];
			}
		}
		$rtn = '';
		if ($arr) {
			$rtn = implode (', ', $arr);
		} else {
			$rtn = $arr;
		}
		if ($print) {
			print_r($rtn);
		} else {
			return $rtn;
		}
	}

	static function history() {
		return self::$histories;
	}


	public function count($n=null, $condition=null, $order = null,  $db=null) {
		//var_dump($condition);

		if(is_object($db)) {
			$row = $db->select( $n, array(
			'condition' => $condition,
			'select' => 'COUNT(1) AS count',
			'one' => true,
			'order' =>  $order,
			));

		} else {

			$row = $this->select( $n, array(
			'condition' => $condition,
			'select' => 'COUNT(1) AS count',
			'one' => true,
			'order' =>  $order
			));
		}
		return intval($row['count']);
	}

	/**
	 * Begin a transaction, committing any previously open transaction
	 */
	function begin( $fname = 'Database::begin' ) {
		$this->query( 'BEGIN', $fname );
		$this->mTrxLevel = 1;
	}

	/**
	 * End a transaction
	 */
	function commit( $fname = 'Database::commit' ) {
		$this->query( 'COMMIT', $fname );
		$this->mTrxLevel = 0;
	}

	/**
	 * Rollback a transaction.
	 * No-op on non-transactional databases.
	 */
	function rollback( $fname = 'Database::rollback' ) {
		$this->query( 'ROLLBACK', $fname, true );
		$this->mTrxLevel = 0;
	}
}



/*

if ( isset( $a[0] ) && is_array( $a[0] ) ) {
$multi = true;
$keys = array_keys( $a[0] );
} else {
$multi = false;
$keys = array_keys( $a );
}

$sql = 'INSERT ' . implode( ' ', $options ) .
" INTO $table (" . implode( ',', $keys ) . ') VALUES ';

if ( $multi ) {
$first = true;
foreach ( $a as $row ) {
if ( $first ) {
$first = false;
} else {
$sql .= ',';
}
$sql .= '(' . $this->makeList( $row ) . ')';
}
} else {
$sql .= '(' . $this->makeList( $a ) . ')';
}
return (bool)$this->query( $sql, $fname );
*/