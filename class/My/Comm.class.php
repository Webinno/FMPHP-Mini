<?php
/**
 * 
 */

class My_Comm {
	
	/**
	 * @name   sms
	 * @author Chiwm
	 * @time   2015-03-30
	 * @功能：    短信接口
	 * @return array
	 */
	static function sms($phone,$content) {
		
	}
	
	///设置
	static function createFlowNo($prefix = '') {
		$sn = date('YmdHis').substr(microtime(),2,4).sprintf('%02d',rand(0,99)) ;
		return $prefix . $sn;
	}
	///设置
	static function createApplicationNo() {
		return self::createFlowNo('LFAN');
	}



}