<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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


//  Autodetect, convert and provide timestamps of various types
define( 'TS_UNIX', 0 );    // Unix time - the number of seconds since 1970-01-01 00:00:00 UTC
define( 'TS_MW', 1 );    // MediaWiki concatenated string timestamp (YYYYMMDDHHMMSS)
define( 'TS_DB', 2 );    // MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
define( 'TS_RFC2822', 3 );    // RFC 2822 format, for E-mail and HTTP headers
define( 'TS_ISO_8601', 4 );    // ISO 8601 format with no timezone: 1986-02-09T20:00:00Z; This is used by Special:Export.
// An Exif timestamp (YYYY:MM:DD HH:MM:SS); http://exif.org/Exif2-2.PDF The Exif 2.2 spec, see page 28 for the DateTime tag and page 36 for the DateTimeOriginal and DateTimeDigitized tags.
define( 'TS_EXIF', 5 );
define( 'TS_ORACLE', 6 );    // Oracle format time.
define( 'TS_POSTGRES', 7 );   // Postgres format time.
define( 'TS_DB2', 8 );   //DB2 format time
define( 'TS_ISO_8601_BASIC', 9 );    // ISO 8601 basic format with no timezone: 19860209T200000Z.  This is used by ResourceLoader

abstract class FM_Func {

	//判断php版本
	static function is_php($version = '5.0.0')
	{
		static $_is_php;
		$version = (string)$version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
		}

		return $_is_php[$version];
	}


	static function substr($str, $start, $length, $encode = 'utf-8') {
		return	mb_substr($str, $start, $length, $encode );
	}

	/**
 * 字符串加密以及解密函数
 *
 * @param string $string	原文或者密文
 * @param string $operation	操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key		密钥
 * @param int $expiry		密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string		处理后的 原文或者 经过 base64_encode 处理后的密文
 *
 * @example
 *
 * 	$a = authcode('abc', 'ENCODE', 'key');
 * 	$b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 * 	$a = authcode('abc', 'ENCODE', 'key', 3600);
 * 	$b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
	static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

		$ckey_length = 4;	//note 随机密钥长度 取值 0-32;
		//note 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
		//note 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
		//note 当此值为 0 时，则不产生随机密钥

		$key = md5($key ? $key : UC_KEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	static function rand_tring($len, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		$string = '';
		for ($i = 0; $i < $len; $i++)
		{
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}
		return $string;
	}

	static function rand_number($len, $chars = '0123456789')
	{
		$string = '';
		$str_len  = strlen($chars);
		for ($i = 0; $i < $len; $i++)
		{
			$pos = rand(0, $str_len-1);
			$string .= $chars{$pos};
		}
		return $string;
	}
	/** Generate a random 32-character hexadecimal token.
 * @param mixed $salt Some sort of salt, if necessary, to add to random characters before hashing.
 */
	//MW
	static function generateToken( $salt = '' ) {
		$salt = serialize($salt);
		return md5( mt_rand( 0, 0x7fffffff ) . $salt );
	}
	//discuz
	static function getIP() {
		static $ip = '';
		if (!empty($ip)) {
			return $ip;
		}

		$cip = getenv('HTTP_CLIENT_IP');
		$xip = getenv('HTTP_X_FORWARDED_FOR');
		$rip = getenv('REMOTE_ADDR');
		$srip = $_SERVER['REMOTE_ADDR'];
		if($cip && strcasecmp($cip, 'unknown')) {
			$ip = $cip;
		} elseif($xip && strcasecmp($xip, 'unknown')) {
			$ip = $xip;
		} elseif($rip && strcasecmp($rip, 'unknown')) {
			$ip = $rip;
		} elseif($srip && strcasecmp($srip, 'unknown')) {
			$ip = $srip;
		}
		preg_match("/[\d\.]{7,15}/", $ip, $match);
		$ip = $match[0] ? $match[0] : 'unknown';
		return $ip;
	}

	static function die_backtrace() {
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		exit;
	}

	function Date($format, $time = 0, $timezone = FM_TIMEZONE) {
		$output = '';
		$time += $timezone * 3600;
		$output = gmdate($format, $time );
		return $output;
	}

	/**
 * Get a timestamp string in one of various formats
 *
 * @param $outputtype Mixed: A timestamp in one of the supported formats, the
 *                    function will autodetect which format is supplied and act
 *                    accordingly.
 * @param $ts Mixed: the timestamp to convert or 0 for the current timestamp
 * @return Mixed: String / false The same date in the format specified in $outputtype or false
 */

	static function Timestamp( $outputtype = TS_UNIX, $ts = 0 ) {
		$uts = 0;
		$da = array();
		$strtime = '';
		//  取得时间
		if ( !$ts ) { // We want to catch 0, '', null... but not date strings starting with a letter.
			$uts = time();
			$strtime = "@$uts";
		} elseif ( preg_match( '/^(\d{4})\-(\d\d)\-(\d\d) (\d\d):(\d\d):(\d\d)$/D', $ts, $da ) ) {
			# TS_DB
		} elseif ( preg_match( '/^(\d{4}):(\d\d):(\d\d) (\d\d):(\d\d):(\d\d)$/D', $ts, $da ) ) {
			# TS_EXIF
		} elseif ( preg_match( '/^(\d{4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)$/D', $ts, $da ) ) {
			# TS_MW
		} elseif ( preg_match( '/^-?\d{1,13}$/D', $ts ) ) {
			# TS_UNIX
			$uts = $ts;
			$strtime = "@$ts"; // http://php.net/manual/en/datetime.formats.compound.php
		} elseif ( preg_match( '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}.\d{6}$/', $ts ) ) {
			# TS_ORACLE // session altered to DD-MM-YYYY HH24:MI:SS.FF6
			$strtime = preg_replace( '/(\d\d)\.(\d\d)\.(\d\d)(\.(\d+))?/', "$1:$2:$3",
			str_replace( '+00:00', 'UTC', $ts ) );
		} elseif ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.*\d*)?Z$/', $ts, $da ) ) {
			# TS_ISO_8601
		} elseif ( preg_match( '/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})(?:\.*\d*)?Z$/', $ts, $da ) ) {
			#TS_ISO_8601_BASIC
		} elseif ( preg_match( '/^(\d{4})\-(\d\d)\-(\d\d) (\d\d):(\d\d):(\d\d)\.*\d*[\+\- ](\d\d)$/', $ts, $da ) ) {
			# TS_POSTGRES
		} elseif ( preg_match( '/^(\d{4})\-(\d\d)\-(\d\d) (\d\d):(\d\d):(\d\d)\.*\d* GMT$/', $ts, $da ) ) {
			# TS_POSTGRES
		} elseif (preg_match( '/^(\d{4})\-(\d\d)\-(\d\d) (\d\d):(\d\d):(\d\d)\.\d\d\d$/', $ts, $da ) ) {
			# TS_DB2
		} elseif ( preg_match( '/^[ \t\r\n]*([A-Z][a-z]{2},[ \t\r\n]*)?' . # Day of week
		'\d\d?[ \t\r\n]*[A-Z][a-z]{2}[ \t\r\n]*\d{2}(?:\d{2})?' .  # dd Mon yyyy
		'[ \t\r\n]*\d\d[ \t\r\n]*:[ \t\r\n]*\d\d[ \t\r\n]*:[ \t\r\n]*\d\d/S', $ts ) ) { # hh:mm:ss
			# TS_RFC2822, accepting a trailing comment. See http://www.squid-cache.org/mail-archive/squid-users/200307/0122.html / r77171
			# The regex is a superset of rfc2822 for readability
			$strtime = strtok( $ts, ';' );
		} elseif ( preg_match( '/^[A-Z][a-z]{5,8}, \d\d-[A-Z][a-z]{2}-\d{2} \d\d:\d\d:\d\d/', $ts ) ) {
			# TS_RFC850
			$strtime = $ts;
		} elseif ( preg_match( '/^[A-Z][a-z]{2} [A-Z][a-z]{2} +\d{1,2} \d\d:\d\d:\d\d \d{4}/', $ts ) ) {
			# asctime
			$strtime = $ts;
		} else {
			# Bogus value...
			//wfDebug("wfTimestamp() fed bogus time value: TYPE=$outputtype; VALUE=$ts\n");
			return false;
		}

		static $formats = array(
		TS_UNIX => 'U',
		TS_MW => 'YmdHis',
		TS_DB => 'Y-m-d H:i:s',
		TS_ISO_8601 => 'Y-m-d\TH:i:s\Z',
		TS_ISO_8601_BASIC => 'Ymd\THis\Z',
		TS_EXIF => 'Y:m:d H:i:s', // This shouldn't ever be used, but is included for completeness
		TS_RFC2822 => 'D, d M Y H:i:s',
		TS_ORACLE => 'd-m-Y H:i:s.000000', // Was 'd-M-y h.i.s A' . ' +00:00' before r51500
		TS_POSTGRES => 'Y-m-d H:i:s',
		TS_DB2 => 'Y-m-d H:i:s',
		);

		if ( !isset( $formats[$outputtype] ) ) {
			// throw new MWException( 'wfTimestamp() called with illegal output type.' );
		}

		if ( function_exists( "date_create" ) ) {
			if ( count( $da ) ) {
				$ds = sprintf("%04d-%02d-%02dT%02d:%02d:%02d.00+00:00",
				(int)$da[1], (int)$da[2], (int)$da[3],
				(int)$da[4], (int)$da[5], (int)$da[6]);

				$d = date_create( $ds, new DateTimeZone( 'GMT' ) );
			} elseif ( $strtime ) {
				$d = date_create( $strtime, new DateTimeZone( 'GMT' ) );
			} else {
				return false;
			}

			if ( !$d ) {
				// wfDebug("wfTimestamp() fed bogus time value: $outputtype; $ts\n");
				return false;
			}

			$output = $d->format( $formats[$outputtype] );
		} else {
			if ( count( $da ) ) {
				// Warning! gmmktime() acts oddly if the month or day is set to 0
				// We may want to handle that explicitly at some point
				$uts = gmmktime( (int)$da[4], (int)$da[5], (int)$da[6],
				(int)$da[2], (int)$da[3], (int)$da[1] );
			} elseif ( $strtime ) {
				$uts = strtotime( $strtime );
			}

			if ( $uts === false ) {
				// wfDebug("wfTimestamp() can't parse the timestamp (non 32-bit time? Update php): $outputtype; $ts\n");
				return false;
			}

			if ( TS_UNIX == $outputtype ) {
				return $uts;
			}
			$output = gmdate( $formats[$outputtype], $uts );
		}

		if ( ( $outputtype == TS_RFC2822 ) || ( $outputtype == TS_POSTGRES ) ) {
			$output .= ' GMT';
		}

		return $output;
	}

	/**
* Returns HTML escaped variable
*
* @access	public
* @param	mixed
* @return	mixed
*/
	// 停用， 考虑用filter-input
	static  function html_escape($var)
	{
		if (is_array($var))
		{
			return array_map('html_escape', $var);
		}
		else
		{
			return htmlspecialchars($var, ENT_QUOTES, config_item('charset'));
		}
	}


	static function msg($msg, $url_forward = 'goback', $time = 1000)
	{
		if (is_array($msg)) {
			$msg = implode(" ", $msg);
		}
		// $msg = htmlspecialchars($msg);
		$str = '
<table align="center" width="40%" bgcolor="#8F8FBD">
	<tr><td align="center">
		<p>&nbsp;</p>
		<h4>提示信息</h4>
		<p>'.$msg.'</p>
		<script>setTimeout("location.href=\''.$url_forward.'\'", '.$time.');</script>
		<a href="'.$url_forward.'">如果您的浏览器没有自动跳转，请点击这里</a>
		<p>&nbsp;</p>
	</td></tr>
</table>';

		if($url_forward && $url_forward != 'goback') {
			exit($str);
		}
		else
		{
			exit("<script>alert('".$msg."');history.go(-1);</script>");
		}
	}
}