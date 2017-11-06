<?php
header('Content-type:text/html;charset=utf-8');
$GLOBALS['config']['site_name'] = 'PHP项目发布后台';
$GLOBALS['config']['site_domain'] = 'phptools.joyme.com';

date_default_timezone_set("PRC");

$com = substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.') + 1);

$adminid = null;

$user = null;

## load joymephplib
if ($com == 'dev') {
    $pathkey = 'dev';
    $svnphppath = 'http://svn.enjoyf.com/svn/php';
}elseif ($com == 'alpha') {
    $pathkey = 'alpha';
    $svnphppath = 'http://svn.enjoyf.com/svn/php';
}elseif ($com == 'beta') {
    $pathkey = 'beta';
    $svnphppath = 'http://staffsvn.joyme.com/svn/php';
}elseif ($com == 'com') {
    $pathkey = 'prod';
    $svnphppath = 'http://staffsvn.joyme.com/svn/php';
}else{
	$com = $pathkey = 'alpha';
	$svnphppath = 'http://svn.enjoyf.com/svn/php';
}

//配置 css／js 等版本号
$static_ver = 1;

//七牛设置 - static
//不再需要项目中配置七牛key！
//$bucket = 'static';
//$accessKey = 'G8_5kjfXfaufU53Da4bnGQ3YP-dhdmqct9sR6ImI';
//$secretKey = 'KXwyeZMxYnsZMqAwojI_IEDkYj69zkwvu8jZP5_a';

//公共库设置
$joymephplib_path = '/opt/www/joymephplib/' . $pathkey . '/phplib.php';

//配置加载PHP公共库的具体路径
if (!file_exists($joymephplib_path)) {
    die('公共库加载失败，未找到入口文件');
}
include($joymephplib_path);

//tools 
use Joyme\core\JoymeToolsUser;
$redirect_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
JoymeToolsUser::init($com, $redirect_url);
//check登录和权限
$ret = JoymeToolsUser::check(TOOLS_ROLE_LOGIN);

//SVN 帐号设置
$svn_name = 'zhangpeng';
$svn_pwd = '9734adb8';

//脚本执行目录
$sbinPath = AROOT. 'data/sh';
//补丁临时目录
$tmppatchPath = AROOT. 'data/tmppatch';
//补丁目录
$patchPath = '/opt/phppatch';
// 项目列表目录
$projectPath = '/opt/phppatch'; 
//当前版本目录 +/phptools/alpha
$alphaPath = '/opt/svndata/php';  


define('TOOLS_ROLE_LOGIN', 0); // tools登录
define('TOOLS_ROLE_ADMIN', 1); // 系统管理员
define('TOOLS_ROLE_VERSION', 114); //做版本
define('TOOLS_ROLE_RELEASE', 113); //上线发布代码
define('TOOLS_ROLE_STATIC', 115); //static发布

$commonProjectList = array('joymephplib');
$newProjectList = array('ugcwiki','pgcwiki');
