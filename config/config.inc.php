<?php   if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// session设置
/*
ini_set("session.save_handler", "memcache");
ini_set("session.save_path", "tcp://127.0.0.1:11211");
*/

//ini_set( 'display_errors', 1);    // or ini_set( 'display_errors', "0n" ); //正式环境下请注释掉

//系统路径常量
define('WEBROOT', ROOT . 'webroot/' );   //web服务器绝对路径
define('LIB', ROOT . 'lib/' );    //库文件
define('CLS', ROOT . 'class/' );    //类文件
define('CONFIG', ROOT . 'config/' );    //config文件

define('DB_DEBUG', false );     //db 调试开关
define('DEBUG_MODE', true );    //打开调试模式， 上线后应关闭

define('STATIC_URL', '/public/' );    // 静态资源文件：css,js,images
define('STATIC_URL_IMG','');    //定义图片服务器URL路径


//定义系统版本编号
define('STATIC_ERA_VERNSION', 'version0.0.1'); // 每改后加1
define('ASSET_COMBO', true );    //配置 资源合并事项

//日志配置
define('LOG_THRESHOLD', 1);    //1, 2, 3
define('LOG_EMAIL', '');    //1, 2, 3
define('LOG_FIlE', ROOT .'log/');    //1, 2, 3

define('HOST_NAME', 'www.webinno.cn');    


define('SITE_NAME',  '网上楼市');
define('SITE_URL',  '');

// smarty 模板
define('TPL_COMPILED_DIR', ROOT . 'compiled' );
define('TPL_TEMPLATE_DIR', ROOT . 'template' );


// 接口常量


//数字证书


define('EXT', '.do');    // 定义PHP扩展名
define('PAGE_SIZE', 10);   // Page_Size
define('INSTID', 'xxx');


/* 以下为暂不开放
//发送邮件
define('SMTPSERVER', 'mail.webinno.cn'); //SMTP服务器
define('SMTPSERVERPORT', 25); //SMTP服务器端口
define('SMTPUSERMAIL', ''); //SMTP服务器的用户邮箱
define('SMTPUSER', ''); //SMTP服务器的用户帐号
define('SMTPPASS', ''); //SMTP服务器的用户密码
define('MAILTYPE', 'HTML'); //邮件格式（HTML/TXT）,TXT为文本邮件


//上传路径


//定义SEO代码
define('GA', 'UA-19369996-1');    //统计代码 id
define('CNZZ', '30039450');    //统计代码 id
*/



//$CONFIG_DATABASE = array ('host', '用户名', '密码', '数据库',  '',  'utf8');
