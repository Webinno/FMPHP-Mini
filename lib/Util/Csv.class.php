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
 * csv下载或输出类 
 * @author :Tom (fxs_2008@sina.com)
 * @copyright :FMPHP-mini框架
 *
 */

class Util_Csv {
	public $file = '';
	public $encode = '';



	public function __construct() {

	}

	public function set_file($file) {
		$this->file = $file;
		return $this;
	}
	public function set_encode($encode) {
		$this->encode = $encode;
		return $this;
	}

	//输出
	function output($file_name, $content)
	{
        $file_name = iconv("utf-8","gb2312",$file_name);
		if (is_array($content)) {
			$content = $this->array_to_csv($content);
		}
		//$content = "/xEF/xBB/xBF".$content; //添加BOM
		if( empty( $file_name ) )
		{
			$file_name = date("Ymd")."csv";
		}

		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header( "Content-type: text/csv" ) ;
		header( "Content-Disposition: attchment; filename={$file_name}" ) ;
		header( "Content-Length: ". strlen( $content ) );
		echo $content;
		exit;
	}
	//保存至文件
	function save_to_file($file,array $data){
		$fp = fopen($file, 'w');

		//Windows下使用BOM来标记文本文件的编码方式
		//fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));

		foreach ($data as $line) {
			fputcsv($fp, $line);
		}

		fclose($fp);
	}

	//可用
	function array_to_csv($data)
	{
		$fp = fopen('php://output', 'w');
		ob_start();
		if('utf-8' == $this->encode) {
			fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
		}
		if (!empty($data)) {
			foreach ($data as $fields) {
				fputcsv($fp, $fields);
			}
		}
		fclose($fp);
		$csv = ob_get_clean();
		return $csv;
	}

	//未验证
	function csv_to_array($data)
	{
		$instream = fopen("php://temp", 'r+');
		fwrite($instream, $data);
		rewind($instream);
		$csv = fgetcsv($instream, 9999999, ',', '"');
		fclose($instream);
		return($csv);
	}

	function fputcsv($hFile, $aRow, $sSeparator=',', $sEnclosure='"')
	{
		foreach ($aRow as $iIdx=>$sCell)
		$aRow[$iIdx] = str_replace($sEnclosure, $sEnclosure.$sEnclosure, $sCell);

		fwrite($hFile, join($aRow, $sSeparator)."\n");
	}
	//可用
	function output_stream ($data) {
		$out = fopen('php://output', 'w');
		foreach ($data as $val) {
			fputcsv($out,$val);
		}
		fclose($out);
	}

	//   fputcsv($fp, $foo, "\t");

	/*
	If you need to send a CSV file directly to the browser, without writing in an external file, you can open the output and use fputcsv on it..

	<?php
	$out = fopen('php://output', 'w');
	fputcsv($out, array('this','is some', 'csv "stuff", you know.'));
	fclose($out);
	?>
	If you need to save the output to a variable (e.g. for use within a framework) you can write to a temporary memory-wrapper and retrieve it's contents:

	<?php
	// output up to 5MB is kept in memory, if it becomes bigger it will automatically be written to a temporary file
	$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

	fputcsv($csv, array('blah','blah'));

	rewind($csv);

	// put it all in a variable
	$output = stream_get_contents($csv);
	?>
	*/
}