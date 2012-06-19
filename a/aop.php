<?php

/**
 * description...
 * 
 * @package Apect
 * @author huanglei
 * @date 2012-03-13
 * @version $Id$
 */


/**
 * 
 *
 *
 *
 */
interface Aspect{
	
	public function before($argument);

	public function after($argument);

	public function	pointCut();

}

/**
 * description...
 * 
 * @package AOPResolve
 * @author huanglei
 * @date 2012-03-13
 * @version $Id$
 */
class AOPResolve extends Object {
	
	const BEFORE = "BEFORE";
	const AFTER = "AFTER";

	private static $aspects = array();

	public static function load() {
		$aspectMapPath = ROOTPATH . ASPECTPATH . '/aspectMap.php';
		if (is_file($aspectMapPath)) {
			require_once ($aspectMapPath);
			if (!function_exists(getAspectMap)) {
				trigger_error("AOP Error [function getAspectMap was not found in $aspectMapPath]", E_USER_ERROR);
			}
			$aspects = getAspectMap();

			foreach($aspects as $aspect) {
				$aspectfile = ROOTPATH . ASPECTPATH . "/$aspect.aspect.php";			
				$aspectClassName = ucfirst($aspect) . 'Aspect';
				$aspectObject = null;
				Util :: getObject($aspectObject,$aspectClassName,$aspectfile);
				if (!$aspectObject->getObject() instanceof Aspect) {
					trigger_error("$aspectClassName was not a subclass of Aspect", E_USER_ERROR);
				}
	
				$pointCut = $aspectObject->pointCut();
				list($c,$m) = explode('->',$pointCut);
				
				self :: $aspects[] = array('c'=>'/'.$c.'/','m'=>'/'.$m.'/','o'=>$aspectObject->getObject()); 
			}
		}

	}


	public static function execute($stage,$c,$m,$a){
		foreach(self :: $aspects as $aspect) {
			if (preg_match($aspect['c'],$c)){
				if (preg_match($aspect['m'],$m)){
					if ($stage === self :: BEFORE){
						$aspect['o']->before(array('c'=>$c,'m'=>$m,'a'=>$a));
					} else if ($stage === self :: AFTER){
						$aspect['o']->after(array('c'=>$c,'m'=>$m,'a'=>$a));	
					}
				}
			}
		}
	}


}

/**
 * description...
 * 
 * @package AOPObject
 * @author huanglei
 * @date 2012-03-13
 * @version $Id$
 */

class AOPObject extends Object{

	private $_instance = null;

	protected $_values = array();

	public function __construct($instance){
		$this->_instance = $instance;
	}

	public function __call($method,$argument){
		if(!method_exists($this->_instance, $method)){
            trigger_error("no such method '$method' on $module class $this->_instance", E_USER_ERROR);
		} 
        $callBack = array($this->_instance, $method); 
		$c = get_class($this->_instance);
		AOPResolve :: execute(AOPResolve :: BEFORE,$c,$method,$argument);
        $return = call_user_func_array($callBack, $argument);
		AOPResolve :: execute(AOPResolve :: AFTER,$c,$method,$argument);
		return $return;
	}
	
    public function __get($key){
		$this->_instance->$key;
		return $this->_values[$key];
    }

    public function __set($key, $value){
		$this->_instance->$key = $value;
        $this->_values[$key] = $value;
    }

	public function getObject(){
		return $this->_instance;
	}


}


?>
