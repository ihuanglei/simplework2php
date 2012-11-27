<?php

/**
 * description...
 * 
 * @package AbstractAction
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
abstract class AbstractAction extends Object {

	private $method = null;

	public $isPOST = false;
	
	public $isAjax = false;
	
	abstract public function execute();

	/**
	 *  
	 * @access public
	 * @param String $method
	 */
	public function doMethod($method) {
		if(!method_exists($this, $method)){
			trigger_error("no such method '$method' on $module class ".get_class($this), E_USER_ERROR);
		}
		$this->method = $method;
		return $this-> $method ();
	}

	/**
	 * 
	 * @access 
	 * @var unknown
	 */
	protected function getModel($model, $isNew = false, $noModule = false) {
		return Util :: getModel($model, $isNew, $noModule);
	}

	/**
	*/
	protected function getView() {
		return Util :: getView();
	}
	
	/**
	 * set value 
	 * 
	 * @access protected
	 * @param String $key
	 * @param unknown $value
	 * @return unknown
	 */
	protected function vmv($key,$value){
		Util :: getView()->assign($key,$value);
	}

	 /**
	  * 
	  * @access protected
	  * @param String $msg
	  */
	protected function showMessage($msg){
		trigger_error($msg,E_USER_NOTICE);
	}

 
	 /**
	  * language
	  * 
	  * @access protected
	  * @param String $s
	  * @return String
	  */
	protected function __($s){
		return Util :: __($s);
	}
	
	/**
	 * json echo
	 * @param array $a
	 */
	protected function ajaxEchoJson($a = array()){
		header('Content-type: application/json');
		echo Util :: json($a);
		exit;
	}
	
	protected function redirect($url){
	    Util :: redirect($url);
	}
	
}
?>
