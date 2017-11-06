<?php

/**
 * Description of patch
 * 
 * 
 * @author clarkzhao
 * @date 2015-06-19 01:38:28
 * @copyright joyme.com
 */
if (!defined('IN'))
    die('bad request');
include_once( AROOT . 'controller' . DS . 'app.class.php' );

use \Joyme\utils\PHPSvn as svn;
use \Joyme\utils\PHPZip as zip;

class patchController extends appController {

    function index() {
    	global $com;
    	if($com == 'beta' || $com == 'com'){
    		info_page('此功能只能在alpha中使用哦');exit;
    	}
        $version = nc('version');
        $list = $version->getProList();
        
        $data['list'] = $list;
        $data['patch_code'] = $_REQUEST['id'];
        $data['title'] = $data['top_title'] = '补丁制作';
        render($data);
    }

    //ajax 展开目录
    function dirname() {
        global $alphaPath;
        $project = empty($_REQUEST['project']) ? '/' : $_REQUEST['project'];
        $dirname = empty($_REQUEST['dir']) ? '/' : $_REQUEST['dir'];

        if (empty($project)) {
        	return ajax_echo(-1, 'project empty');
        }
        
        $dir = $alphaPath . '/' . $project . '/alpha/' . $dirname;
        if (!file_exists($dir)) {
            return ajax_echo(-2, $dir . ' not file_exists ');
        }
        $info = $this->deepScanDir($dir);
        return ajax_echo(0, $info);
    }
	
    //补丁生成
    function make() {
        global $com,$tmppatchPath,$alphaPath,$patchPath,$svnphppath,$svn_name,$svn_pwd;
        if($com == 'beta' || $com == 'com'){
        	info_page('此功能只能在alpha中使用哦');exit;
        }
        $project = empty($_REQUEST['project']) ? '' : $_REQUEST['project'];
        if (empty($_REQUEST['dir'])) {
            echo '<script>alert("请选择补丁文件");history.go(-1);</script>';
            exit;
        } else if (empty($_REQUEST['project'])) {
            echo '<script>alert("请输入项目");history.go(-1);</script>';
            exit;
        } else if (empty($_REQUEST['id'])) {
            echo '<script>alert("请输入补丁编号");history.go(-1);</script>';
            exit;
        } else if (!is_numeric($_REQUEST['id'])) {
            echo '<script>alert("请输入数字补丁编号");history.go(-1);</script>';
            exit;
        } else {
            $dstPath = $tmppatchPath . '/' .$project . '/' . intval($_REQUEST['id']);
            
            foreach ($_REQUEST['dir'] as $v) {
                $rs = $this->recurse_copy($alphaPath . '/' . $project . '/alpha/' . $v, $dstPath . $v);
            	
            	if($rs === false){
            		echo '<script>alert("补丁创建失败");history.go(-1);</script>';
            		exit;
            	}
            }
            if($project == 'static'){
            	@file_put_contents($dstPath.'/version', time());
            }
            $archive = new zip();
            $zipfilename = $patchPath.'/'. $project . '/' . intval($_REQUEST['id']) . '.zip';
            $archive->Zip($dstPath, $zipfilename );
            $phpsvn = new svn();
            $phpsvn->setoptions(array(
	            'username' => $svn_name,
	            'password' => $svn_pwd
	        ));
            $svnpath = $svnphppath.'/phppatch';
            $localpath = $patchPath.'/'. $project;
            
            $phpsvn->setpath($svnpath, $localpath);
            $phpsvn->add($zipfilename);

            $r = $phpsvn->commit();

            shell_exec('rm -rf ' . $dstPath);

            $alert_msg = '<textarea cols="50" rows="10" id="biao1" style="width:500px;">';
            foreach ($_REQUEST['dir'] as $v) {
                $alert_msg.=trim($v) . "\n";
            }
            $alert_msg.='</textarea>';
            $tourl = 'http://mantis.enjoyf.com/view.php?id=' . intval($_REQUEST['id']);
            $data['alert_msg'] = $alert_msg;
            $data['tourl'] = $tourl;
            $data['title'] = $data['top_title'] = '补丁制作';
            render($data);
        }
    }

    private function recurse_copy($src, $dst) {  // 原目录，复制到的目录
        $this->mkdirs(substr($dst, 0, strrpos($dst, '/')));
        $rs = copy($src, $dst);
        return $rs;
    }

    private function mkdirs($dir) {
    	shell_exec('mkdir -p '.$dir);
    	return true;
    	
        if (!is_dir($dir)) {
            if (!$this->mkdirs(dirname($dir))) {
                return false;
            }
            
            if (!mkdir($dir, 0644)) {
                return false;
            }
        }
        return true;
    }

}
