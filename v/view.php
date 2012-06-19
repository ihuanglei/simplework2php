<?php

/**
 * description...
 * 
 * @package View
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
interface View {

	const REDIRECT = "redirect:";
	
	/**
	 * set value
	 * @param $key
	 * @param $value
	 */
	public function assign($key,$value);
	
	/**
	 * show template
	 * @param $d template file
	 */
	public function display($d);

}
 
?>
