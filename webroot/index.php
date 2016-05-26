<?php 
require('../init.inc.php');

$header_title = $header_desc = $header_keywords = '';

$getjump = isset($_GET['return']) ? $_GET['return'] : '' ;
Template::Assign('getjump',$getjump);
Template::Assign('head_title', $header_title);
Template::Assign('head_desc', $header_desc);
Template::Assign('head_desc', $header_keywords);
//Template::AddCss(array());

if(!empty($_SESSION['username'])){
	$username    = $_SESSION['username'];
}else{
	$username    = '            请输入手机号/邮箱';
}
Template::Assign('username', $username);
Template::Display('index/index.tpl');