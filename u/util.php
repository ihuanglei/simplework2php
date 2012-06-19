<?php
/**
 * description...
 * 
 * @package class
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
final class Util extends Object{
	
	private static $models = array();
	private static $view = null;
	private static $cache = null;
	
	/**
	 * halt
	 * @param $msg 
	 */
	public static function halt($msg) {
		exit (sprintf(STDOUT , VERSION . ' error',$mag));
	}
	
	/**
	 * current time
	 * @param $format
	 */
	public static function currentTime($format=""){
		return time();
	}
	
	/**
	 * shorturl
	 * @param $url long url
	 */
	public static function shortUrl($url) {
		$base32 = array (
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
			'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
			'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
			'y', 'z', '0', '1', '2', '3', '4', '5'
		);
	 
		$hex = md5($url);
		 
		$hexLen = strlen($hex);
		$subHexLen = $hexLen / 8;
		$output = array();
		 
		for ($i = 0; $i < $subHexLen; $i++) {
			$subHex = substr ($hex, $i * 8, 8);
			
			$int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
			$out = '';
			
			for ($j = 0; $j < 5; $j++) {
				$val = 0x0000001F & $int;
				$out .= $base32[$val];
				$int = $int >> 5;
			}
			$output[] = $out;
		}
		return $output;
	}
	
	/**
	 * import class
	 */
	public static function import() {
	    $c = func_get_args();
	    if (empty($c)) {
	        return;
	    }
	    array_walk($c, create_function('$item, $key', 'include_once(ROOT_PATH . $item . \'.php\');'));
	}
	
	/**
	 * format time
	 * @param $dateformat
	 * @param $timestamp
	 * @param $format
	 */
	public static function sgmdate($dateformat, $timestamp='', $format=0) {
		$result = '';
		$timestamps = explode(' ', microtime());
	    $ntimestamp = $timestamps[1];
		if($format) {
			$time = $ntimestamp - $timestamp;
			if($time > 24*3600) {
				$result = gmdate($dateformat, $timestamp);
			} elseif ($time > 3600) {
				$result = intval($time/3600).__("hour").__("before");
			} elseif ($time > 60) {
				$result = intval($time/60).__("minute").__("before");
			} elseif ($time > 0) {
				$result = $time.__("second").__("before");
			} else {
				$result = __("now");
			}
		} else {
			$result = gmdate($dateformat, $timestamp);
		}
		return $result;
	}
	
	/**
	 * ip
	 */
	public static function getOnlineIP() {
	    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $onlineip = getenv('HTTP_CLIENT_IP');
	    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $onlineip = getenv('REMOTE_ADDR');
	    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $onlineip = $_SERVER['REMOTE_ADDR'];
	    }
	    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	    return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	
	/**
	 * translate
	 * @param String $str 
	 */
	public static function __($str) {
	    global $LANG;
	    return $LANG[$str] ? $LANG[$str] : $str;
	}
	
	/**
	 * redirect
	 * @param String $url
	 */
	public static function redirect($url="") {
		$url = empty($url)?$_SERVER["HTTP_REFERER"]:$url;
		header("HTTP/1.1 301 Moved Permanently"); 
	   	header("Location:$url");
	    exit;
	}
	
	/**
	 * object
	 * @param Object $object
	 * @param String $className
	 * @param String $objectfile
	 */
	public static function getObject(&$object,$className,$classFile){
		if (!class_exists($className)){
			if (!is_file($classFile)){
				trigger_error("file $classFile was not found",E_USER_ERROR);
			}
			require_once ($classFile);
		}		
		if (!class_exists($className)){
			trigger_error("class $className was not found",E_USER_ERROR);
		}

		$object = new AOPObject(new $className);
	}

	/**
	 *
	 *
	 */
	public static function &getAction($action){
		$classFile =  ROOTPATH . MODULEPATH . ACTIONPATH . "/$action.action.php";
		$actionClassName = ucfirst($action) . 'Action';
		$actionObject = null;
		self::getObject(&$actionObject,$actionClassName,$classFile);

		if (!$actionObject->getObject() instanceof AbstractAction) {
			trigger_error("$module $actionClassName was not a subclass of AbstractAction", E_USER_ERROR);
		}
		return $actionObject;
	}

	/**
	 * 
	 * @param String $model
	 * @param boolean $isNew
	 * @param boolean $noModule
	 */
	public static function &getModel($model,$isNew = false,$noModule = false) {
		
		$modelHash = md5($model);
		 
		if ($isNew || !isset(self::$models[$modelHash])){
 	        if ($noModule === false){
 	            $classFile =  ROOTPATH . MODULEPATH . MODELPATH .  "/{$model}.model.php";
			} else {
			    $classFile =  ROOTPATH . MODELPATH .  "/{$model}.model.php";
            }
                       
			$modelClassName = ucfirst($model) . "Model";

			$modelObject = null;
			self::getObject(&$modelObject,$modelClassName,$classFile);
			
			if (!$modelObject->getObject() instanceof Model)
				trigger_error("$modelClassName was not a subclass of Model",E_USER_ERROR);
			
			if ($isNew){
				return $modelObject;
			}
				
			self::$models[$modelHash] = &$modelObject;
		}
		
		return self::$models[$modelHash];

	}
	
	/**
	 * 
	 */
	public static function &getView() {
		if (!isset(self::$view)) {
 	
			if (defined('VIEWFILE')){
				$pathParts = pathinfo(VIEWFILE);
			} else {
				$pathParts = pathinfo(SIMPLEWORKPATH . 'l/view/vview.php');
			}
			
			$view = $pathParts["filename"];
			
			$viewClassName = ucfirst($view);
			
			self::getObject(&self::$view,$viewClassName,$pathParts['dirname'] . '/' . $pathParts['basename']);
			
			if (!self::$view->getObject() instanceof View)
				trigger_error("$viewClassName was not a subclass of View",E_USER_ERROR);
		}  
		
		return self::$view;
	}
	
	/**
	*
	*/
	public static function &getCache(){
		if (!isset(self::$cache)) {
			self::getObject(&self::$cache,CACHEOBJ,"");
		}
		return self::$cache;
	}
	
	
	public static function pager($pageInfo){
	    $page = (int)$pageInfo['page'];
	    $pageCount = (int)$pageInfo['pageCount'];
 	    $record = (int)$pageInfo['record']; 
	    $link = $pageInfo['link'];
	    if (empty($link)){
	        $uri = $_SERVER['REQUEST_URI'];
		    if ($_SERVER['QUERY_STRING']){
			    if (stripos($uri, 'page=')){
				    $uri = preg_replace("/page=\d+/i", 'page=?', $uri);
 			    } else {
				    $uri = $uri . '&page=?';
			    }
            } else {
                $uri = $uri . '?page=?';
            }
            $link = $uri;
	    }
	    
	    
	    $pages = ceil($record / $pageCount);
	    
	     
	    $prePage = $page - 1;
	    
	    $nextPage = $page + 1;
	    
	    if ($prePage >= 0)
	        $prePageLink = str_replace('page=?','page='.($prePage+1), $link);
	        
	        
	    if ($nextPage < $pages)
	        $nextPageLink = str_replace('page=?','page='.($nextPage+1), $link);
	        
        $firstPageLink = str_replace('page=?',"page=1", $link);
	    $lastPageLink = str_replace('page=?',"page=$pages", $link);
	    
	    return array('record'=>$record,
	                 'page'=>$page + 1,
	                 'firstPageLink'=>$firstPageLink,
	                 'prePageLink'=>$prePageLink,
	                 'nextPageLink'=>$nextPageLink,
	                 'lastPageLink'=>$lastPageLink,
	                 'pages'=>$pages
	                 );
	    
	}
	
	public static function json($arr){
	    if (is_string($arr))
	        $arr = (array)$arr;
	    if ( function_exists('json_encode')) {
	        return json_encode($arr);
	    } else {
	        if (class_exists('Services_JSON')) {
                $json = new Services_JSON();
                return $json->encode($arr);
            }
            return implode($arr);
	    }
	    
	}

	public static function addslashesArray($a){
        if(is_array($a)){
            foreach($a as $n=>$v){
                $b[$n]=addslashes_array($v);
            }
            return $b;
        }else{
            return addslashes($a);
        }
	}

}

?>
