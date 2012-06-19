<?php 
/**
 *  MYSQL 类
 *  
 * @author huanglei
 * @date 2009-07-13
 */

 
class Mysql {
    var $link;
    var $charset;
    
    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = "utf8", $pconnect = 0, $halt = TRUE) {
        if ($pconnect) {
            if (!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
                $halt && $this->halt('Can not connect to MySQL server');
            }
        } else {
            if (!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw,false,65536)) {
                $halt && $this->halt('Can not connect to MySQL server');
            }
        }
        
        $this->charset = $charset;
        
        if ($this->version() > '4.1') {
            if ($this->charset) {
                @mysql_query("SET character_set_connection=$this->charset, character_set_results=$this->charset, character_set_client=binary", $this->link);
            }
            if ($this->version() > '5.0.1') {
                @mysql_query("SET sql_mode=''", $this->link);
            }
        }
        if ($dbname) {
            @mysql_select_db($dbname, $this->link);
        }
    }
    
    function select_db($dbname) {
        return mysql_select_db($dbname, $this->link);
    }
    
    function fetch_array($query, $result_type = MYSQL_ASSOC ) {
        return mysql_fetch_array($query, $result_type);
    }
    
    function query($sql, $type = '') {
        if (defined('DB_DEBUG'))
            echo '<!--'.$sql.'-->';
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
        if (!($query = $func($sql, $this->link)) && $type != 'SILENT') {
            $this->halt('MySQL Query Error', $sql);
        }
        return $query;
    }

    
    function insert($table, $data) {
        $fields = array_keys($data);
		return $this->query("INSERT INTO $table (`".implode('`,`', $fields)."`) VALUES ('".implode("','", $data)."')");
    }
    
    function update($table, $data, $where) {
        $bits = $wheres = array();
        foreach ((array) array_keys($data) as $k)
            $bits[] = "`$k` = '$data[$k]'";
            
        if (is_array($where))
            foreach ($where as $c=>$v)
                $wheres[] = "$c = '".$v."'";
        else
            return false;

        return $this->query("UPDATE $table SET ".implode(', ', $bits).' WHERE '.implode(' AND ', $wheres));
    }

    
    function affected_rows() {
        return mysql_affected_rows($this->link);
    }
    
    function error() {
        return (($this->link) ? mysql_error($this->link) : mysql_error());
    }
    
    function errno() {
        return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
    }
    
    function result($query, $row) {
        $query = @mysql_result($query, $row);
        return $query;
    }
    
    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }
    
    function num_fields($query) {
        return mysql_num_fields($query);
    }
    
    function free_result($query) {
        return mysql_free_result($query);
    }
    
    function insert_id() {
        return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }
    
    function fetch_row($query) {
        return mysql_fetch_row($query);
    }
    
    function fetch_row_assoc($query) {
    	return mysql_fetch_assoc($query); 
    }
    
    function fetch_fields($query) {
        return mysql_fetch_field($query);
    }
    
    function version() {
        return mysql_get_server_info($this->link);
    }
    
    function close() {
        return mysql_close($this->link);
    }
    
    function halt($message = '', $sql = '') {
        echo "<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">
				<b>MySQL Error</b><br>
				Message: $message<br>
				SQL: $sql<br>
				Error: ".$this->error()."<br>
				Errno.: ".$this->errno()."</div>";
        exit();
    }
}

?>
