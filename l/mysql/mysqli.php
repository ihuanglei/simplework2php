<?php 
/**
 *  MYSQLI 类
 *  
 * @author huanglei
 * @date 2009-07-13
 */

 
class Mysql {
    var $link;
    var $charset;
    
	public function getMysql(){
		return $this->link;
	}

    public function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $halt = TRUE) {

        $this->link = mysqli_connect($dbhost, $dbuser, $dbpw);
		
		if (mysqli_connect_error()){
             $halt && $this->halt('Can not connect to MySQL server');
        }
        
        $this->charset = $charset;
		
		mysqli_set_charset($this->link,$this->charset);
		
		if ($dbname) {
            $this->select_db($dbname);
        }
    }
    
    public function select_db($dbname) {
        return mysqli_select_db($this->link,$dbname);
    }
    
    public function fetch_array($query, $result_type = MYSQL_ASSOC ) {
        return mysqli_fetch_array($query, $result_type);
    }
    
	public function store_result(){
		return mysqli_store_result($this->link);
	}

	public function next_result(){
		return mysqli_next_result($this->link);
	}

    public function query($sql) {
        if (defined('DB_DEBUG')) echo '<!--'.$sql.'-->';
        if (($query = mysqli_query($this->link, $sql)) === false) {
            $this->halt('MySQL Query Error', $sql);
        }
        return $query;
    }

	public function multi_query($sql){
		if (defined('DB_DEBUG')) echo '<!--'.$sql.'-->';
        if (($query = mysqli_multi_query($this->link, $sql)) === false) {
            $this->halt('MySQL Query Error', $sql);
        }
        return $query;
	}

    public function insert($table, $data) {
        $fields = array_keys($data);
		return $this->query("INSERT INTO $table (`".implode('`,`', $fields)."`) VALUES ('".implode("','", $data)."')");
    }
    
    public function update($table, $data, $where) {
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

    
    public function affected_rows() {
        return mysqli_affected_rows($this->link);
    }
    
    public function error() {
        return mysqli_error($this->link);
    }
    
    public function errno() {
        return mysqli_errno($this->link);
    }
    
    public function result($query, $row) {
        $query = @mysqli_result($query, $row);
        return $query;
    }
    
    public function num_rows($query) {
        $query = mysqli_num_rows($query);
        return $query;
    }
    
    public function num_fields($query) {
        return mysqli_num_fields($query);
    }
    
    public function free_result($query) {
        return mysqli_free_result($query);
    }
    
    public function insert_id() {
        return ($id = mysqli_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }
    
    public function fetch_row($query) {
        return mysqli_fetch_row($query);
    }
    
    public function fetch_row_assoc($query) {
    	return mysqli_fetch_assoc($query); 
    }
    
    public function fetch_fields($query) {
        return mysqli_fetch_field($query);
    }
    
    public function version() {
        return mysqli_get_server_info($this->link);
    }
    
    public function close() {
        return mysqli_close($this->link);
    }
    
    public function halt($message = '', $sql = '') {
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
