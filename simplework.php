<?php
/**
 * @author huanglei
 * @date Apr 15, 2010
 */


/***************************************************
*
*      constant
* 
***************************************************/
define('SIMPLEWORK','simplework');
define('VERSION','0.10.1');
define("STDOUT","<div style=\'font-size:12px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\'><b>simplework %s :</b> %s </div>");

/**
 * argements
 *
 */
define('ARGS_ROOTPATH','rootPath');
define('ARGS_MODULEPATH','modulePath');
define('ARGS_ACTIONPATH','actionPath');
define('ARGS_MODELPATH','modelPath');
define('ARGS_INTERCEPTORPATH','interceptorPath');
define('ARGS_ASPECTPATH','aspectPath');
define('ARGS_VIEWFILE','viewFile');
define('ARGS_INCLUDES','includes');
define('ARGS_SESSION','session');
define('ARGS_EXCEPTIONHANDLE','');
define('ARGS_ERRORHANDLER','');
 
/**
 * default
 *
 */
define('DEFAULT_MODULEPATH','modules');
define('DEFAULT_ACTIONPATH','actions');
define('DEFAULT_MODELPATH','models');
define('DEFAULT_INTERCEPTORPATH','interceptors');
define('DEFAULT_ASPECTPATH','aspects');

/**
 * simplework path
 *
 */
define('SIMPLEWORKPATH',str_replace('\\','/',dirname(__FILE__)) . '/');

 /**
  * simplework4php
  * 
  * @package class
  * @author huanglei
  * @date 2011-01-15
  * @version $Id$
  */
abstract class Object {

	private $_mt = null;

	public function __construct() {
		$this->_mt = microtime();
	}

	public function hashCode() {
		$className = get_class($this);
		$hashCode = md5($className . $this->_mt);
		return $hashCode;
	}

	public function __toString() {
		return sprintf('%s@%s', get_class($this), $this->hashCode());
	}

}



/**
 * simplework4php
 * 
 *
 **/
class DoMethodErrorException extends Exception{}

 /**
  * simplework4php
  * 
  * @package Simplework
  * @author huanglei
  * @date 2011-01-15
  * @version $Id$
  */
class Simplework extends Object {

	private $logger;

	/**
	 * 
	 * @access public
	 */
	public function __construct(){
			
		ini_set('error_reporting',E_ALL^E_NOTICE);
		
		/**
		 * china
		 */
		date_default_timezone_set('PRC');
		
		/**
		 * set handler
		 */
		define('EXCEPTIONHANDLE',ARGS_EXCEPTIONHANDLE); 
		define('ERRORHANDLER',ARGS_ERRORHANDLER);
		set_error_handler(array (& $this,'errorHandler'));
		set_exception_handler(array (& $this,'exceptionHandler'));
		
	}

	 /**
	  * 
	  * @access public
	  * @param array $options
	  * 	ARGS_ROOTPATH
	  * 	ARGS_MODULEPATH
	  * 	ARGS_ACTIONPATH
	  * 	ARGS_MODELPATH
	  * 	ARGS_INTERCEPTORPATH
	  * 	ARGS_VIEWFILE
	  * 	ARGS_INCLUDES
	  * 	ARGS_SESSION 
	  *  
	  */
	public function run($options) {

		if (!is_array($options)) {
			trigger_error('Simplework2 run method args must be array', E_USER_ERROR);
		}
		
		define('ROOTPATH', $options[ARGS_ROOTPATH] . '/');
 	
		/**
		 * load simplework2 base class
		 */
		require_once (SIMPLEWORKPATH . 'a/aop.php');
		require_once (SIMPLEWORKPATH . 'u/util.php');
		require_once (SIMPLEWORKPATH . 'c/action.php');
		require_once (SIMPLEWORKPATH . 'm/model.php');
		require_once (SIMPLEWORKPATH . 'v/view.php');
		require_once (SIMPLEWORKPATH . 'i/interceptor.php');	
			
		/***************************************************
		*
		*      include ext php
		* 
		***************************************************/
		if (isset ($options[ARGS_INCLUDES])) {
			foreach ($options[ARGS_INCLUDES] as $include) {
				if (preg_match('/^'.SIMPLEWORK.':(.*)/',$include,$matches)){
					require_once (SIMPLEWORKPATH . $matches[1]);
				} else { 
					require_once (ROOTPATH . $include);
				}
			}
		}
		
		/***************************************************
		*
		*      set session handle
		* 
		***************************************************/
		if (isset ($options[ARGS_SESSION])) {
			require_once (SIMPLEWORKPATH . 'l/session/session.php');
			$sessionArr = $options[ARGS_SESSION];
			ini_set('session.save_handler', 'user');
			if (!isset ($sessionArr['class']) || !isset ($sessionArr['file'])) {
				trigger_error('session config error:must have class and file', E_USER_ERROR);
			}
			Util :: getObject($session, $sessionArr['class'], $sessionArr['file']);
			$timeOut = (int)$sessionArr['timeout'];
			$session->getObject()->setMaxLifeTime($timeOut>0?$timeOut:30*1000);
			session_set_save_handler(array ($session->getObject(), 'open'), 
									 array ($session->getObject(),'close'), 
									 array ($session->getObject(),'read'), 
									 array ($session->getObject(),'write'), 
									 array ($session->getObject(),'destroy'), 
									 array ($session->getObject(),'gc')
									 );
			register_shutdown_function('session_write_close');
		}
		session_start();
				
		/***************************************************
		*
		*     filter variable
		* 
		***************************************************/
		if (!get_magic_quotes_gpc()){
			$_POST = Util :: addslashesArray($_POST);
			$_GET = Util :: addslashesArray($_GET);
		}

		/***************************************************
		*
		*     get path from argements
		* 
		***************************************************/

		/**
		 * module dir
		 */
		$modulePath = isset($options[ARGS_MODULEPATH])?$options[ARGS_MODULEPATH]:DEFAULT_MODULEPATH;

		/**
		 * action dir
		 */
		$actionPath = isset($options[ARGS_ACTIONPATH])?$options[ARGS_ACTIONPATH]:DEFAULT_ACTIONPATH;
		
		/**
		 * model dir
		 */
		$modelPath = isset($options[ARGS_MODELPATH])?$options[ARGS_MODELPATH]:DEFAULT_MODELPATH;
		
		/**
		 * interceptor dir
		 */
		$interceptorPath = isset($options[ARGS_INTERCEPTORPATH])?$options[ARGS_INTERCEPTORPATH]:DEFAULT_INTERCEPTORPATH;
		
		/**
		 * aspect dir
		 */
		$aspectPath = isset($options[ARGS_ASPECTPATH])?$options[ARGS_ASPECTPATH]:DEFAULT_ASPECTPATH;

		/**
		 * view file
		 */
		$viewFile = $options[ARGS_VIEWFILE];

		/**
		 * module 
		 */
		$module = strtolower(trim(isset ($_REQUEST['module']) ? $_REQUEST['module'] : ''));

		/***************************************************
		*
		*     define path
		* 
		***************************************************/

		/**
		 * module path
		 */
		define('MODULEPATH',empty($module)?'':$modulePath . '/' . $module . '/' );
		
		/**
		 * action path
		 */
		define('ACTIONPATH', $actionPath);

		/**
		 * model path
		 */
		define('MODELPATH', $modelPath);

		/**
		 * interceptor path
		 */
		define('INTERCEPTORPATH',$interceptorPath);

		/**
		 * aspect path
		 */
		define('ASPECTPATH',$aspectPath);
		
		/**
		 * action
		 */
		$action = strtolower(trim(isset ($_REQUEST['action']) ? $_REQUEST['action'] : 'index'));

		/**
		 * method
		 */
		$method = trim(isset ($_REQUEST['method']) ? trim($_REQUEST['method']) : 'execute');
					
		/***************************************************
		*
		*     AOP
		* 
		***************************************************/
		AOPResolve :: load();

		/***************************************************
		*
		*     interceptor
		* 
		***************************************************/
		InterceptorResolve :: load();

		$interceptorMapPath = ROOTPATH . INTERCEPTORPATH . '/interceptormap.php';
		if (is_file($interceptorMapPath)) {
			require_once ($interceptorMapPath);
			if (!function_exists(getIntercepto)){
				trigger_error("interceptor Error [function getIntercepto was not found in $interceptorMapPath]", E_USER_ERROR);
			}
			$interceptors = getIntercepto();
			$globalInterceptors = $interceptors['globals'];
			$actionInterceptors = $interceptors['actions'];
	
			/**
			 * global interceptor
			 */
			if (isset ($globalInterceptors)) {
				foreach ($globalInterceptors as $interceptor) {
					if($this->interceptor($interceptorPath, $interceptor, $module, $action, $method) === false){
						return false;
					};
				}
			}
			
			/**
			 * action interceptor
			 */
			if (isset ($actionInterceptors)) {
				$interceptor = $actionInterceptors[$action];
				if (isset ($interceptor)) {
					if($this->interceptor($interceptorPath, $interceptor, $module, $action, $method) === false){
						return false;
					};

				}
			}
		}

		
		$actionObject = Util :: getAction($action);
		$actionObject->isPOST = strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
		$actionObject->isAjax = array_key_exists('ajax', $_REQUEST);
		
		
		//if (!method_exists($actionObject, $method)) {
		//	trigger_error("no such method '$method' on $module class $actionClassName", E_USER_ERROR);
		//}
		
		/***************************************************
		*
		*     do method
		* 
		***************************************************/
		try{
			$vm = $actionObject->doMethod($method);
			if (!empty($vm)){
				Util :: getView()->display($vm);
			}
		} catch (Exception $e){
			throw new DoMethodErrorException($e);
		}	
	}
 
	/**
	 *
	 *
	 * @param String $interceptor
	 *
	 */
	private function interceptor($interceptorPath, $interceptor, &$module, &$action, &$method){
			if (preg_match('/^'.SIMPLEWORK.':(.*)/',$interceptor,$matches)){
				$p = explode('/',$interceptor);
				$interceptorfile = SIMPLEWORKPATH . $matches[1] . ".intercepto.php";
				$interceptor =  $p[sizeof($p) - 1];
			} else {
				$interceptorfile = ROOTPATH . $interceptorPath . "/$interceptor.intercepto.php";			
			}
			$interceptorClassName = ucfirst($interceptor) . 'Intercepto';
			$interceptorObject = null;
			Util :: getObject($interceptorObject, $interceptorClassName, $interceptorfile);
			if (!$interceptorObject->getObject() instanceof Interceptor) {
				trigger_error("$interceptorClassName was not a subclass of Interceptor", E_USER_ERROR);
			}
			if ($interceptorObject->getObject()->doInterceptor($module, $action, $method) === Interceptor :: INTERCEPT) {
				return false;
			}
			return true;
	}

	/**
	 * error handle
	 * 
	 * @access public
	 * @param int $errno
	 * @param String $errstr
	 * @param String $errfile
	 * @param int $errline
	 * @param String $errcontext
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		switch ($errno) {
			case E_NOTICE :
				break;
			case E_USER_NOTICE :
				Util :: getView()->assign('message',$errstr);
				Util :: getView()->display('error');
				exit(-1);
			case E_USER_ERROR :
				die(sprintf(STDOUT , VERSION . ' error',str_replace(getcwd(), '', $errstr)));
			default :
				break;	
		}
		
		return true;
	}
 
	/**
	 * exception handle
	 * 
	 * @access public
	 * @param object $exception
	 */
	public function exceptionHandler($exception){
		//TODO:change exception handle
		$file = $exception->getFile();
		
		trigger_error('exception:'.$file.' [line:'.$exception->getLine().'] '.$exception->getMessage(),E_USER_ERROR);
	}
}
?>
