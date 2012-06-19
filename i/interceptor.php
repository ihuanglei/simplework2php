<?php

/**
 * @author huanglei
 * @date Apr 19, 2010
 */
interface Interceptor {

	const INTERCEPT = "INTERCEPT";
	const SKIP = "SKIP";
	
	/**
	 * interceptor
	 * @param $action
	 * @param $method
	 */
	public function doInterceptor(&$module, &$action, &$method);

}

abstract class AbstractInterceptor extends Object implements Interceptor {
 

}

class InterceptorResolve extends Object{
	
	public static function load(){

	}

}


?>
