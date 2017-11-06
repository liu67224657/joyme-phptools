<?php

if (!defined('IN'))
    die('bad request');
include_once( AROOT . 'controller' . DS . 'app.class.php' );
use Joyme\core\JoymeToolsUser;
class versionController extends appController {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $this->create();
    }

    function create() {
        global $user, $sbinPath, $com;
        $ret = JoymeToolsUser::check(TOOLS_ROLE_VERSION);
        $list = $this->getProList();


        // 版本制作
        if (!empty($_POST)) {

            $version = empty($_POST['version']) ? '' : $_POST['version'];

            $versionArr = explode('_', $version);

            $project = $versionArr[0];
            
            $path = $com=='com'?'prod':$com;

            if (empty($project) || empty($version)) {
                $data['error'] = '参数不全';
            } else {
                $result = @shell_exec("sh ".$sbinPath . "/creatbranch.sh $path $project $version");
                $data['result'] = '制作结果：' . $result;

                //DB记录版本
                $versionModel = M('version');
                $row = array('project' => $project, 'version' => $versionArr[1], 'addtime' => time());
                $rs = $versionModel->addVersion($row);

                if (empty($rs)) {
                    $data['error'] = '版本存数据库失败';
                }

                //addlog
                //addlog($user[2], "制作{$project}项目版本，版本号为{$version}");
                $ret = JoymeToolsUser::addlog("制作{$project}项目版本，版本号为{$version}");
            }
        }

        $data['list'] = $list;
        $data['title'] = $data['top_title'] = '版本制作';
        render($data);
    }

    //ajax获取版本号
    function getNewVersion() {

        $project = !empty($_POST['project']) ? $_POST['project'] : '';
        $isnew = !empty($_POST['isnew']) ? 1 : 0;

        if (empty($project)) {
            ajax_echo(0, 'no project');
        }

        $versionModel = M('version');
        $versionRs = $versionModel->getVersion($project);

        $versionYm = (date('Y') - 2010) . '.' . intval(date('m')); //版本固定格式年月

        if (empty($versionRs)) {
            $version = $versionYm . '.1.1';
        } else {
            $versionArr = explode('.', $versionRs['version']);
            
            if( intval(date('m')) != $versionArr[1]){
            	$version = $versionYm . '.1.1';
            }else if ($isnew == 1) {
                $version = $versionYm . '.' . intval(1 + $versionArr[2]) . '.1';
            } else {
                $version = $versionYm . '.' . ($versionArr[2]) . '.' . intval(1 + $versionArr[3]);
            }
        }

        ajax_echo(1, $project . '_' . $version);
    }

    //获取版本列表
    function getVersion() {
        $project = !empty($_POST['project']) ? $_POST['project'] : '';

        if (empty($project)) {
            ajax_echo(0, 'no project');
        }

        $versionModel = M('version');
        $versionRs = $versionModel->getVersionList($project);

        if (empty($versionRs)) {
            ajax_echo(-1, 'no this project version');
        } else {
            ajax_echo(1, array('project'=>$project,'version'=>$versionRs));
        }
    }

    //获取项目列表
    function getProList() {
        global $projectPath;
        $list = scandir($projectPath);
        if ($list == false) {
            return '';
        }
        foreach ($list as $k => $v) {
            if (substr($v, 0, 1) == '.') {
                unset($list[$k]);
            }
        }
        return $list;
    }

}
