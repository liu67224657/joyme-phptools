<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );

class indexController extends appController
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$data['title'] = $data['top_title'] = '首页';
		render( $data );
	}
        
	function test()
        {
            render( array() );
        }
	
}
	