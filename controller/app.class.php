<?php

if (!defined('IN'))
    die('bad request');
include_once( CROOT . 'controller' . DS . 'core.class.php' );

use Joyme\core\JoymeToolsUser;

class appController extends coreController {

    function __construct() {
        // 载入默认的
        parent::__construct();
    }

    // login check or something
    
    
    protected function  deepScanDir($dir) {
        $fileArr = array();
        $dirArr = array();
        $dir = rtrim($dir, '//');
        if (is_dir($dir)) {
            $dirHandle = opendir($dir);
            while (false !== ($fileName = readdir($dirHandle))) {
                if (strpos($fileName, '.') === 0) {
                    continue;
                }
                $subFile = $dir . DIRECTORY_SEPARATOR . $fileName;
                if (is_file($subFile)) {
                    $fileArr[$fileName] = $fileName;
                } elseif (is_dir($subFile) && str_replace('.', '', $fileName) != '') {
                    $dirArr[$fileName] = $fileName;
                }
            }
            ksort($fileArr);
            ksort($dirArr);
            closedir($dirHandle);
        }
        return array('fileArr' => $fileArr, 'dirArr' => $dirArr);
    }

}
