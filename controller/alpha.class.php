<?php

/**
 * Description of alpha
 * 
 * 
 * @author clarkzhao
 * @date 2015-06-19 10:35:42
 * @copyright joyme.com
 */
if (!defined('IN'))
    die('bad request');
include_once( AROOT . 'controller' . DS . 'app.class.php' );

use \Joyme\utils\PHPSvn as svn;

class alphaController extends appController {

    function index() {
    	global $com;
    	if($com == 'beta' || $com == 'com'){
    		info_page('此功能只能在alpha或dev中使用哦');exit;
    	}
        $version = nc('version');
        $list = $version->getProList();
        
        $data['list'] = $list;
        $data['title'] = $data['top_title'] = 'alpha同步工具';
        render($data);
    }

    function ajaxUpdate() {
        
        global $svn_name,$svn_pwd,$alphaPath,$svnphppath,$com,$newProjectList,$commonProjectList;
        global $user, $sbinPath, $com;
        
        if($com == 'beta' || $com == 'com'){
        	return ajax_echo(-1, 'please at alpha');
        }
        $project = empty($_REQUEST['project']) ? '' : $_REQUEST['project'];
        $path = empty($_REQUEST['path']) ? 'alpha' : $_REQUEST['path'];
        
        if (empty($project)) {
            return ajax_echo(-1, 'project empty');
        }
        
        $phpsvn = new svn();
        $phpsvn->setoptions(array(
        		'username' => $svn_name,
        		'password' => $svn_pwd
        ));
        $svnpath = $svnphppath.'/' . $project . '/trunk';
        
        $localpath = $alphaPath ;
        if (!file_exists($localpath . '/' . $project)) {
            shell_exec('mkdir '.$localpath . '/' . $project);
        }
        if (!file_exists($localpath . '/' . $project . '/'.$path)) {
            $phpsvn->setpath($svnpath. ' '.$project . '/'.$path, $localpath);
            $r = $phpsvn->checkout();
            $msg = '检出成功';
        }else{
        	$phpsvn->setpath($svnpath, $localpath.'/'.$project.'/'.$path);
        	$r = $phpsvn->update();
        	$msg = $path.'同步成功';
        }
        
        if(in_array($project, $newProjectList) || in_array($project, $commonProjectList)){
        	$cmd = "sh ".$sbinPath . "/alpha.sh $path $project";
        	@shell_exec($cmd);
        }
        return ajax_echo(0, $msg);
    }
}
