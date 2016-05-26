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



/**
 * 该类不允许单独实例化
 *
 */
abstract class Table
{
	protected $tableName = '';
	protected $fieldName = '*';
	protected $pkName = 'id';
	protected $pkValue = null;
	//protected $fields = array();
	protected $DB = null;  //DB对象

	public function __construct( $db = null)
	{
		$this->DB = $db;
	}
	//暂不开放
	private function setPk($k = null, $v = null)
	{
		if ( $k && $v )
		{
			$this->pkName  = $k;
			$this->pkValue = $v;
		}
	}
	//暂不开放
	private function setTableName($table_name)
	{
		$this->tableName = $table_name;
	}


	//  添加
	function add($vars = array(), $type=0)
	{
		return $this->DB->insert($this->tableName, $vars, $type);
	}

	// 删除
	function delete($id = null, $pk = 'id')
	{
		$condition = array( $pk => $id );
		return $this->DB->delete( $this->tableName, $condition );
	}

	// 更新
	function update( $updaterow,$id = null, $pk = 'id') {
		return 	$this->DB->update( $this->tableName,  $updaterow, $id, $pk );
	}

	// 查找
	function find($ids = array(), $k = 'id') {
		if ( empty($ids) ) {
			return array();
		}

		if (is_array($ids)) {
			$option = array(
			'condition' => $ids,
			'one'       => true
			);
			$res = $this->DB->select($this->tableName, $option);
		}
		else
		{
			$option = array(
			'condition' => array($k => $ids),
			'one'       => true
			);
			$res = $this->DB->select($this->tableName, $option);
		}
		/*
		if ($res)
		{
		$record = new DB_Table($res);
		return $record;
		}
		*/
		return $res;
	}

	function findAll($condition, $order='ORDER BY id DESC',  $fieldName = '')
	{
		if (empty($fieldName)) {
			$fieldName = $this->fieldName;
		}
		$options = array(
		'select'    => $fieldName,
		'condition' => $condition,
		'order' 	=> $order,
		);
		$result = $this->DB->select($this->tableName, $options);

		return $result;
	}


	// 统计
	function count($condition = null,  $order = '', $db = null) {
		$row = $this->DB->select( $this->tableName, array(
		'condition' => $condition,
		'select' => 'COUNT(1) AS totalNums',
		'one' => true,
		'order' => $order
		));
		return intval($row['totalNums']);
	}

	/*
	static function find_by_sql($sql, $params =array(),$db = null) {
	$DB = $db ? $db : getDefaultDb();
	return $DB->execute($sql, $params);
	}
	*/

			// 查询
	function select($options=array(),  $db = null) {
		return  $this->DB->select( $this->tableName, $options);
	}
	//暂不开放
 function query($sql) {
		return $this->DB->query($sql);
	}

	/**
     * 调试用，用于打印数据查询
     */
	function debug($debug = true)
	{
		$this->DB->debug();
	}

	/*====================扩展方法=======================*/
	//
	function checkExist($condition=array(), $returnid = true, $order='') {
		if (!$condition)  {
			return false;
		}

		$row =  $this->DB->select($this->tableName, array(
		'select' =>  ($returnid ? 'id' : '*'),
		'condition' => $condition,
		'one' => true,
		'order' => $order));

		if($returnid) {
			return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
		} else {
			return empty($row) ? array() : $row;
		}
	}
	//
	function order($order)
	{
		foreach($order as $k=>$v){
			$condition = '';
			$condition[] = "`id`=$k";
			$data = array('order' => $v);
			$result = self::Update($condition,$data);
		}
		return true;
	}
	// count别名
	function getCount($condition, $order = null, $db = null) {
		return $this->count($condition, $order, $db);
	}
	//根据条件查询条数
	function getDBList($condition , $offset = 0, $limit = 10, $fieldName = '',$order='ORDER BY id DESC')
	{
		if (empty($fieldName)) {
			$fieldName = $this->fieldName;
		}

		$options = array(
		'select'    => $fieldName,
		'condition' => $condition,
		'order' 	=> $order,
		'size'      =>(int)$limit,
		'offset'    => (int)$offset,
		);
		$result = $this->DB->select($this->tableName, $options);

		return $result;
	}

	function getInfoById($id, $keyName = 'id') {
		$options = array(
		'condition' => array($keyName => $id),
		'one' => 1
		);
		return  $this->DB->select($this->tableName, $options);
	}

	function showFields ($print = 0) {
		$this->DB->showFields($this->tableName, $print) ;
	}

	function colFromId($id, $colName ='', $pk = 'id') {
		$options = array(
		'select'    => $colName,
		'condition' => array($pk => $id),
		'one' => 1
		);
		$res = $this->DB->select($this->tableName, $options);
		return $res;
	}
}