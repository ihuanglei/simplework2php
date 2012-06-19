<?php
/**
 * @author huanglei
 * @date Jun 20, 2010
 */
 
 
class MemcachedSession implements Session{

	public function open(){
		
	}

	public function close(){
		return true;
	}

	public function read(){
		
	}

	public function write(){
		
	}

	public function destroy(){
		return false;
	}

	public function gc(){
		return false;
	}

} 
?>
