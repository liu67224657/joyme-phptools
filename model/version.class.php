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

class version extends baseModel{

    //表字段
    public $fields = array(
    		'id' => 'int', //自增ID
    		'project' => 'string', //项目
    		'version' => 'string', //版本号
    		'addtime' => 'int', //创建时间
    );
    //数据表名称
    public $tableName = 'version';
    
    //获取版本
    public function getVersion($project){
    	if(empty($project)){
    		return null;
    	}
    	$fields = '*';
    	$where = array(
    			'project' => array('eq', $project)
    	);
    	$order = 'id DESC';
    	
    	$ret = $this->selectRow($fields, $where, $order);
    	
    	return $ret;
    	
    }
    //获取项目版本列表
    public function getVersionList($project){
    	if(empty($project)){
    		return null;
    	}
    	$fields = '*';
    	$where = array(
    			'project' => array('eq', $project)
    	);
    	$order = 'id DESC';
    	$limit = 20;
    	$ret = $this->select($fields, $where, $order, $limit);
    	 
    	return $ret;
    	 
    }
    //插入版本
    public function addVersion($row){
    	return $this->insert($row);
    }

    
}