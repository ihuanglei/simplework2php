<?php
/**
 * 
 * @package class
 * @author huanglei
 * @date 2011-01-15
 * @version $Id$
 */
abstract class Model extends Object {

	abstract protected function get($fields, $condition = ' 1=1');

	abstract protected function getArray($fields, $condition = ' 1=1', $rowFun = null);
}

abstract class DBModel extends Model {

	abstract protected function insert($fields);

	abstract protected function update($fields, $condition);

	abstract protected function getDb();

	abstract protected function getTable();

	abstract protected function getSql();
	
	abstract protected function query($sql);

}
?>
