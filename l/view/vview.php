<?php

/**
 * description...
 * 
 * @package VView
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
class VView extends Object implements View{
	
	private $template = null;
	
	public function __construct(){
		include_once(SIMPLEWORKPATH . "l/view/vemplator.php");
 
	    $this->template = new vemplator();
    	$this->template->setCompileDirectory(ROOTPATH . 'runtime/tmpdir/');
    	$this->template->setSuffix(".html");  
 		define("ENTRY", "entry");
		define(ENTRY, "true");
	}
		
	/**
	 * 设置变量
	 */
	public function assign($key,$value){
		$this->template->assign($key, $value);
	}
	
	/**
	 * 显示模板
	 */
	public function display($d){
		$dir = '';
		$file = $d;
		$pos = strrpos($d,'/');
		if ($pos !== false){
			$dir = substr($d,0,$pos + 1);
			$file = substr($d,$pos + 1);
		}
		echo $this->template->output(ROOTPATH . MODULEPATH . "template/$dir",$file);
	}
	
} 
?>
