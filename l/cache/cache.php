<?php

/**
 * description...
 * 
 * @package Cache
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
interface Cache {
	public function get($key);
	public function put($key, $value, $expire = 60);
	public function set($key, $value, $expire = 60);
	public function del($key);
	public function containsKey($key);
	public function flush();
}

/**
 * description...
 * 
 * @package FileCache
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
class FileCache extends Object implements Cache {

	public $cacheSubDirNum = 100;

	public $cacheDir = "./caches";

	public function get($key) {
		$cacheFile = $this->getFilepath($key);

		if (!is_file($cacheFile)) {
			return false;
		}

		$data = include_once ($cacheFile);

		return $data;
	}

	public function put($key, $value, $expire = 60) {
		$cacheFile = $this->getFilepath($key);
		if (is_file($cacheFile)) {
			return;
		}
		$this->set($key, $value, $expire);
	}

	public function set($key, $value, $expire = 60) {
		if (empty($key)) {
			return false;
		}
		$cacheFile = $this->getFilepath($key);
		
		$data = "<?php \r\n /**\r\n *  @Created By FileCache\r\n *  @Time:" . date('Y-m-d H:i:s') . "\r\n *  key $key \r\n*/\r\n\r\n";
		$data .= 'if(filemtime(__FILE__) + ' . $expire . ' < time())return false;' . " \r\n";
		$data .= "\r\nreturn " . var_export($value, true);
		$data .= "\r\n\r\n?>";

		return file_put_contents($cacheFile, $data, LOCK_EX);
	}

	public function del($key) {
		$cacheFile = $this->getFilepath($key);
		@ unlink($cacheFile);
	}
	
	public function containsKey($key){
		$cacheFile = $this->getFilepath($key);
		return file_exists($cacheFile);
	}
	
	public function flush() {

	}

	private function getFilepath($key) {

		$dir = $this->cacheDir . "/" . str_pad(abs(crc32($key)) % $this->cacheSubDirNum, 3, "0", STR_PAD_LEFT);
		if (!is_dir($dir)) {
			mkdir($dir, 777, true);
		}
		return $dir . "/" . md5($key) . ".cache.php";
	}
}

/**
 * description...
 * 
 * @package MemCache1
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
class MemCache extends Object implements Cache {
	public function get($key){
		
	}
	public function put($key, $value, $expire = 60){
		
	}
	public function set($key, $value, $expire = 60){
		
	}
	public function del($key){
		
	}
	public function containsKey($key){
		
	}
	public function flush(){
		
	}
}

?>
