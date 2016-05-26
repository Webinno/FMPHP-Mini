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
 * 业务公共函数库
 * author: Tom
 */

class Common {
	
	static function getDB() {
		static $db = null;
		if (empty($db)) {
			global $CONFIG_DATABASE;
			//$dbhost, $dbuser, $dbpw, $dbname = '', $port = '', $dbcharset = '', $pconnect = 0, $tablepre='', $time = 0
			$db =  new DB ($CONFIG_DATABASE);
		}
		return $db;
	}
	
}
