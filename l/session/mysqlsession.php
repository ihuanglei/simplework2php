<?php

/**
 * description...
 * 
 * @package MysqlSession
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
class MysqlSession extends AbstractMysqlModel implements Session{

 	private $maxLifeTime = 0;

	public function open(){
		return true;
	}

	public function close(){
		return true;
	}

	public function read($sessionId){
		$r= $this->get("data","id = '$sessionId' and expires >".time());
		return isset($r["data"])?$r["data"]:"";
	}

	public function write($sessionId, $sessionData){
		$sessionData = addslashes($sessionData);
		$expiry = $this->getExpires();
		$this->query("REPLACE INTO ".$this->getTable()." SET id = '$sessionId', expires = $expiry, data = '$sessionData',ip='".Util::getonlineip()."'");
		return true;
	}

	public function destroy($sessionId){
		$this->query("DELETE FROM ".$this->getTable()." WHERE id = '$sessionId'");
		return true;
	}

	public function gc($maxLifeTime){
		$this->query("DELETE FROM ".$this->getTable()." WHERE expires < ".time());
		return true;
	}
	
	protected function getTable(){
		return "tprj_session";
	}
	
	private function getSessionId(){
		return md5(uniqid(mt_rand(), true));
	}
	
	//当前时间加上失效时间
	private function getExpires(){
		return time() + $this->maxLifeTime;
	}
	
	public function setmaxLifeTime($maxLifeTime){
		$this->maxLifeTime = $maxLifeTime;
	}
	
} 
?>
