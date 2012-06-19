<?php


/**
 * @author huanglei
 * @date Jun 20, 2010
 */

interface Session {

	public function open();

	public function close();

	public function read($sessionId);

	public function write($sessionId, $sessionData);

	public function destroy($sessionId);

	public function gc($maxlifetime);
	
	public function setMaxLifeTime($maxlifetime);
}

?>
