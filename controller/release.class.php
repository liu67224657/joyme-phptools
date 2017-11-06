<?php

if (!defined('IN'))
    die('bad request');
include_once( AROOT . 'controller' . DS . 'app.class.php' );
use Joyme\core\JoymeToolsUser;
use Joyme\qiniu\Qiniu_Utils;
class releaseController extends appController {

    function __construct() {
        $ret = JoymeToolsUser::check(TOOLS_ROLE_STATIC,TOOLS_ROLE_RELEASE);
        parent::__construct();
    }
    function index(){
    	$this->project();
    }
    
    //get status
    function getstatus(){
    	$data = $this->checkstatus();
    	$status = json_encode($data);
    	
    	echo $status;
    }
    //check status
    function checkstatus(){
    	global $sbinPath;
    	$tmpfile = $sbinPath.'/status';
    	$file = file($tmpfile);
    	$data = array();
    	if(!empty($file)){
    		foreach ($file as $v){
    			$data[] = substr($v, 2);
    		}
    		$lastdata = array_pop($file);
    		$code = intval(substr($lastdata, 0,1));
    	}else{
    		$code = 1;
    	}
    	$status = array('code'=>$code,'data'=>$data);
    	return $status;
    }
    
    function checkstatic(){
    	$roles = JoymeToolsUser::getRoles();
    	if(in_array(TOOLS_ROLE_STATIC, $roles) == true && in_array(TOOLS_ROLE_RELEASE, $roles) == false){
    		return true;
    	}else{
    		return false;
    	}
    }

    //发布版本
    function project() {
    	global $user, $sbinPath, $com,$newProjectList,$commonProjectList;
    	if($this->checkstatic()){
    		info_page('您只能发补丁哦');
    		exit;
    	}
    	
    	$status = $this->checkstatus();
    	
    	if($status['code'] == 0){
    		info_page('有版本或者补丁正在发布中，请稍后');
    		exit;
    	}
    	
        $version = nc('version');
        $list = $version->getProList();

        if (!empty($_POST)) {
        	if($com == 'beta'){
        		$path = 'beta';
        	}else if($com == 'com'){
        		$path = empty($_POST['path'])?'beta':$_POST['path'];
        	}else{
        		$path = $com;
        	}
            $version = empty($_POST['version']) ? '' : $_POST['version'];
            $versionArr = explode('_', $version);
            $project = $versionArr[0];
            if (empty($path) || empty($project) || empty($version)) {
                $data['result'] = '参数不全';
            } else {
            	if(in_array($project, $commonProjectList)){
            		$cmd = "sh ".$sbinPath . "/new_release.sh $path $project $version > ".$sbinPath . "/status &";
            		@shell_exec($cmd);
            		$cmd = "sh ".$sbinPath . "/release.sh $path $project $version > ".$sbinPath . "/status &";
            		@shell_exec($cmd);
            	}else if(in_array($project, $newProjectList)){
            		$cmd = "sh ".$sbinPath . "/new_release.sh $path $project $version > ".$sbinPath . "/status &";
                 	@shell_exec($cmd);
            	}else{
            		$cmd = "sh ".$sbinPath . "/release.sh $path $project $version > ".$sbinPath . "/status &";
            		@shell_exec($cmd);
            	}
                 
                 //addlog
                 //addlog($user[2], "发布了{$project}项目，版本号为{$version}");
                 $ret = JoymeToolsUser::addlog("发布了{$project}项目到{$path}，版本号为{$version}");
                 $result = '1';
                 $data['result'] = ' 发布结果：' . $result;
            }
        }
        $data['com'] = $com;
        $data['list'] = $list;
        $data['title'] = $data['top_title'] = '版本发布';
        render($data);
    }

    //发布补丁
    function patch() {

    	global $user, $sbinPath, $com,$newProjectList,$commonProjectList;
    	
    	$status = $this->checkstatus();
    	 
    	if($status['code'] == 0){
    		info_page('有版本或者补丁正在发布中，请稍后');
    		exit;
    	}
        
        if($this->checkstatic()){
        	$list = array('static');
        }else{
        	$version = nc('version');
        	$list = $version->getProList();
        }
        

        if (!empty($_POST)) {
       		if($com == 'beta'){
        		$path = 'beta';
        	}else if($com == 'com'){
        		$path = empty($_POST['path'])?'beta':$_POST['path'];
        	}else{
        		$path = $com;
        	}
            $project = $_POST['project'];
            $id = intval($_POST['id']);
            
            if($this->checkstatic() && $project !='static'){
            	info_page('您只能发static的补丁哦');
            	exit;
            }
            if (empty($path) || empty($project) || empty($id)) {
                $data['result'] = '参数不全';
            } else {
            	
            	if(in_array($project, $commonProjectList)){
            		@shell_exec("sh ".$sbinPath . "/new_phppatch.sh $path $project $id > ".$sbinPath . "/status &");
            		@shell_exec("sh ".$sbinPath . "/phppatch.sh $path $project $id > ".$sbinPath . "/status &");
            	}else if(in_array($project, $newProjectList)){
            		@shell_exec("sh ".$sbinPath . "/new_phppatch.sh $path $project $id > ".$sbinPath . "/status &");
            	}else{
            		@shell_exec("sh ".$sbinPath . "/phppatch.sh $path $project $id > ".$sbinPath . "/status &");
            	}
                //addlog
                $ret = JoymeToolsUser::addlog("发布了{$project}项目的补丁到{$path}，补丁编号号为{$id}");
                $result = 1;
                $data['result'] = '发布结果：' . $result;
            }
        }
        $data['com'] = $com;
        $data['list'] = $list;
        $data['title'] = $data['top_title'] = '补丁发布';
        render($data);
    }
    
    //版本回滚
	function back() {

		global $user, $sbinPath, $com,$newProjectList,$commonProjectList;
		
		if($this->checkstatic()){
			info_page('您只能发补丁哦');
			exit;
		}
		
        $version = nc('version');
        $list = $version->getProList();

        if (!empty($_POST)) {
        	if($com == 'beta'){
        		$path = 'beta';
        	}else if($com == 'com'){
        		$path = empty($_POST['path'])?'beta':$_POST['path'];
        	}else{
        		$path = $com;
        	}
            $version = empty($_POST['version']) ? '' : $_POST['version'];
            $versionArr = explode('_', $version);
            $project = $versionArr[0];
            if (empty($path) || empty($project) || empty($version)) {
                $data['result'] = '参数不全';
            } else {
                
                if(in_array($project, $commonProjectList)){
                	@shell_exec("sh ".$sbinPath . "/new_back.sh $path $project $version");
                	@shell_exec("sh ".$sbinPath . "/back.sh $path $project $version");
                }else if(in_array($project, $newProjectList)){
                	@shell_exec("sh ".$sbinPath . "/new_back.sh $path $project $version");
                }else{
                	@shell_exec("sh ".$sbinPath . "/back.sh $path $project $version");
                }
                
                //addlog
                //addlog($user[2], "发布了{$project}项目，版本号为{$version}");
                $ret = JoymeToolsUser::addlog("回滚了{$project}项目到{$path}，回滚到版本{$version}");
                $result = '1';
                $data['result'] = ' 发布结果：' . $result;
            }   
        }
        $data['com'] = $com;
        $data['list'] = $list;
        $data['title'] = $data['top_title'] = '版本回滚';
        render($data);
    }
    //static刷新cdn
    function refreshcdn(){
    	
    	global $user, $com;
    	
    	if($com != 'com'){
    		//info_page('此功能只能在prod中使用哦');exit;
    	}
    	
    	if (!empty($_POST)) {
    		$urls = empty($_POST['url']) ? '' : $_POST['url'];

    		if(empty($urls)){
    			$data['result'] = '请输入要刷新的url';
    		}else{
    			foreach ($urls as $k=>$v){
    				if(empty($v)){
    					unset($urls[$k]);
    				}elseif(strpos($v, 'http://static.joyme.') === false){
    					$data['result'] = '只能刷新static的哦';
    					break;
    				}elseif(strpos($v, 'http://static.joyme.beta') !== false){
    					$urls[$k] = str_replace('http://static.joyme.beta', 'http://7xjwg1.com2.z0.glb.qiniucdn.com', $v);
    				}
    			}
    			if(empty($urls)){
    				$data['result'] = '请输入要刷新的url';
    			}elseif(empty($data['result'])){
	    			$rs = Qiniu_Utils::Qiniu_Refresh($urls,'static');
	    			if($rs && $rs['code']==200){
	    				$result='刷新成功';
	    			}else if($rs){
	    				$result=$rs['error'];
	    			}else{
	    				$result='刷新失败';
	    			}
	    			$ret = JoymeToolsUser::addlog("刷新了七牛cdn，url为".implode(',', $urls));

	    			$data['result'] = $result;
    			}
    		}
    		
    		
    	}
    	$data['title'] = $data['top_title'] = '刷新static的cdn';
    	render($data);
    }

}
