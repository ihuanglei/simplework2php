<?php

/**
 * @author huanglei
 * @date Apr 19, 2010
 */

require_once (SIMPLEWORKPATH . '/l/mysql/mysqli.php');

abstract class AbstractMysqlModel extends DBModel {

	private static $db = null;

	protected function insert($fields) {
		$q = $this->getDb()->insert($this->getTable(), $fields);
		$insertId = $this->getDb()->insert_id();
		return $insertId;
	}
	
	protected function update($fields, $condition) {
		if (!is_array($fields)){
			trigger_error('fields_must_array');
		}
		$q = $this->getDb()->update($this->getTable(), $fields, $condition);
		$num = $this->getDb()->affected_rows();
		return $num;
	}

	protected function getBySql($sql, $rowFun = null) {
		$result = array();
		$q = $this->query($sql);
		while ($r = $this->getDb()->fetch_array($q)) {
			$result[] = function_exists($rowFun) ? $rowFun ($r) : $r;
		}
		return $result;
	}

	protected function get($fields, $condition = ' 1=1') {
		$fieldStr = '';
		if (is_array($fields)){
			$fieldStr = implode(',',$fields);
		} else  if (is_string($fields)){
			$fieldStr = $fields;
		}

		$sql = sprintf($this->getSql(), $fieldStr, $condition);
 		$q = $this->query($sql);
		$result = $this->getDb()->fetch_row_assoc($q);
		return $result;
	}

	protected function getArray($fields, $condition = ' 1=1 ', $rowFun=null) {
		$result = array ();
		$fieldStr = '';
		if (is_array($fields)){
			$fieldStr = implode(',',$fields);
		} else  if (is_string($fields)){
			$fieldStr = $fields;
		}
		$sql = sprintf($this->getSql(), $fieldStr, $condition);
		$q = $this->query($sql);
		while ($r = $this->getDb()->fetch_array($q)) {
			
			$result[] = empty($rowFun) && !method_exists($this, $rowFun)?$r:$this->$rowFun($r);
		}
		return $result;
	}

	protected function query($sql){
		return $this->getDb()->query($sql);
	}

	protected function multi_query($sql){
		return $this->getDb()->multi_query($sql);
	}

	protected function getDb() {
		if (!isset (self::$db)) {
			self::$db = new Mysql();
			self::$db->connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_CHARSET);
		}
		return self::$db;
	}
	
	protected function free_result($q){
		$this->getDb()->free_result($q);
	}

	protected function getSequence($sequenceName){
		$q = $this->getDb()->query("select nextval('$sequenceName') as id");
		$result = $this->getDb()->fetch_row_assoc($q);
  		return $result['id'];
	}

	protected function getSql(){
		return 'select %s from '.$this->getTable().' where %s';
	}
	
}
?>
