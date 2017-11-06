<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-5-23
 * Time: 下午3:30
 * To change this template use File | Settings | File Templates.
 */
if( !defined('IN') ) die('bad request');


use Joyme\db\JoymeModel;
use Joyme\core\Log;

class baseModel extends JoymeModel{
	
	public $db = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db_config = array(
            'hostname' => $GLOBALS['config']['db']['db_host'],
            'username' => $GLOBALS['config']['db']['db_user'],
            'password' => $GLOBALS['config']['db']['db_password'],
            'database' => $GLOBALS['config']['db']['db_name']
        );
        parent::__construct();
    }
    
}