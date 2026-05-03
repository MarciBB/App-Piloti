<?php
class Database
{

	// debug flag for showing error messages
	public	$debug = false;

	// Store the single instance of Database
	private static $instance;

	private	$server=""; //database server
	private	$user=""; //database login name
	private	$pass="";  //database login password
	private	$database=""; //database name
	private $port = 3306; //database port
	


	private	$error = "";

	#######################
	//number of rows affected by SQL query
	public	$affected_rows = 0;

	private	$link_id = 0;
	private	$query_id = 0;


#-#############################################
# desc: constructor
public function __construct()
{
	// error catching if not passed in
	
	$this->server=Config::$dbserver;
	$this->user=Config::$dbuser;
	$this->pass=Config::$dbpass;
	$this->database=Config::$dbname;
	$this->port=isset(Config::$dbport) ? Config::$dbport : 3306;
	
 
	
	


	/*$this->server=$server;
	$this->user=$user;
	$this->pass=$pass;
	$this->database=$database;*/
}#-#constructor()


#-#############################################
# desc: singleton declaration
public static function obtain($server=null, $user=null, $pass=null, $database=null)
{
	if (!self::$instance){ 
		self::$instance = new Database($server, $user, $pass, $database); 
	} 

	return self::$instance; 
}#-#obtain()


#-#############################################
# desc: connect and select database using vars above
# Param: $new_link can force connect() to open a new link, even if mysql_connect() was called before with the same parameters
public function connect($new_link=false)
{

	// MySQLi connection with explicit init to force charset before handshake (PHP 5.4 compatibility)
	$this->link_id = mysqli_init();
	if(!$this->link_id){
		$this->oops("Could not initialize MySQLi.");
		return false;
	}
	@mysqli_options($this->link_id, MYSQLI_INIT_COMMAND, "SET NAMES utf8");
	if(!@mysqli_real_connect($this->link_id, $this->server, $this->user, $this->pass, $this->database, $this->port)){
		$this->oops("Could not connect to server: <b>$this->server</b>. Connect Error (".mysqli_connect_errno().") ".mysqli_connect_error());
		return false;
	}

	if (!$this->link_id){//open failed
		$this->oops("Could not connect to server: <b>$this->server</b>.");
		return false;
	}
        
	// Set charset to utf8 for compatibility with PHP 5.6.4
	if(!@mysqli_set_charset($this->link_id, 'utf8')){
		$this->oops("Could not set charset to utf8.");
	}
              

	// unset the data so it can't be dumped
	$this->server='';
	$this->user='';
	$this->pass='';
	$this->database='';
	$this->port=0;
}#-#connect()



#-#############################################
# desc: close the connection
public function close()
{
	if($this->link_id && !@mysqli_close($this->link_id)){
		$this->oops("Connection close failed.");
	}
}#-#close()


#-#############################################
# Desc: escapes characters to be mysql ready
# Param: string
# returns: string
public function escape($string)
{
	if(get_magic_quotes_runtime()) $string = stripslashes($string);
	if($this->link_id) {
		return @mysqli_real_escape_string($this->link_id, $string);
	}
	return $this->pulisci($string);
}#-#escape()

public function pulisci($testo)
{

   // print ($testo." mod ");
    $testo=stripslashes($testo);
                $testo = str_replace("'","''",$testo);
                //$testo = str_replace('"','""',$testo);
                $testo = str_replace("\\","/",$testo);
                $testo = str_replace('"',"&quot;",$testo);
                   //             $testo=utf8_encode($testo);
                         
                //$testo=utf8_decode($testo);
                
  //  print ($testo."<br />");            
                
                return $testo;
                
                
                
}



public function call_procedure($sql)
{
	// do query
	$result = @mysqli_query($this->link_id, $sql);
        if (!$result){
		$this->oops("<b>MySQLi Query fail:</b> $sql");
		
	}

	
}#-#query()


#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc
public function query($sql)
{
	// do query
	$this->query_id = @mysqli_query($this->link_id, $sql);

	if (!$this->query_id){
		$this->oops("<b>MySQLi Query fail:</b> $sql");
		return 0;
	}
	
	$this->affected_rows = @mysqli_affected_rows($this->link_id);

	return $this->query_id;
}#-#query()


#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results
public function query_first($query_string)
{
	$query_id = $this->query($query_string);
	$out = $this->fetch($query_id);
	$this->free_result($query_id);
	return $out;
}#-#query_first()


#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
public function fetch($query_id=-1)
{
	// retrieve row
	if ($query_id !== -1 && $query_id !== null){
		$this->query_id=$query_id;
	}

	if (isset($this->query_id) && $this->query_id){
		$record = @mysqli_fetch_assoc($this->query_id);
	}else{
		$this->oops("Invalid query_id. Records could not be fetched.");
		return false;
	}

	return $record;
}#-#fetch()


#-#############################################
# desc: returns all the results (not one row) OR single fetch if result object
# param: (MySQL query) the query to run on server OR mysqli_result object  
# returns: assoc array of ALL fetched results OR single row array
public function fetch_array($sql_or_result)
{
	// If it's a mysqli_result object, return single row as numeric array (like mysql_fetch_array)
	if (is_object($sql_or_result)) {
		return @mysqli_fetch_array($sql_or_result, MYSQLI_BOTH);
	}
	
	// Original behavior: execute SQL and return all results
	$query_id = $this->query($sql_or_result);
	$out = array();

	while ($row = $this->fetch($query_id)){
		$out[] = $row;
	}

	$this->free_result($query_id);
	return $out;
}#-#fetch_array()


#-#############################################
# desc: does an update query with an array
# param: table, assoc array with data (not escaped), where condition (optional. if none given, all records updated)
# returns: (query_id) for fetching results etc
public function update($table, $data, $where='1')
{
	
	$q="UPDATE `$table` SET ";

	foreach($data as $key=>$val){
		if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
		elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
        elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], "; 
		else $q.= "`$key`='".$this->escape($val)."', ";
	}

	$q = rtrim($q, ', ') . ' WHERE '.$where.';';
	
      //echo($q."<br />");

	return $this->query($q);
}#-#update()


public function delete($table, $where='1')
{
	$q="DELETE FROM `$table` ";
	$q = $q. ' WHERE '.$where.';';
	return $this->query($q);
}#-#update()


#-#############################################
# desc: does an insert query with an array
# param: table, assoc array with data (not escaped)
# returns: id of inserted record, false if error
public function insert($table, $data)
{
	$q="INSERT INTO `$table` ";
	$v=''; $n='';

	foreach($data as $key=>$val){
		$n.="`$key`, ";
		if(strtolower($val)=='null' || !isset($val)) $v.="NULL, ";
		elseif(strtolower($val)=='now()') $v.="NOW(), ";
		else $v.= "'".$this->escape($val)."', ";
	}

	$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

	if($this->query($q)){
		return mysqli_insert_id($this->link_id);
	}
	else return false;

}#-#insert()


#-#############################################
# desc: get number of rows in result set
# param: query_id for mysql run. if none specified, last used
public function num_rows($query_id=-1)
{
	if ($query_id !== -1 && $query_id !== null){
		$this->query_id=$query_id;
	}
	if($this->query_id && is_object($this->query_id)){
		return mysqli_num_rows($this->query_id);
	}
	return 0;
}#-#num_rows()


#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used
private function free_result($query_id=-1)
{
	if ($query_id !== -1 && $query_id !== null){
		$this->query_id=$query_id;
	}
	if($this->query_id && is_object($this->query_id) && !@mysqli_free_result($this->query_id)){
		$this->oops("Result ID could not be freed.");
	}
}#-#free_result()


#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display




private function oops($msg='')
{
	if(!empty($this->link_id)){
		$this->error = mysqli_error($this->link_id);
	}
	else{
		$this->error = mysqli_connect_error();
		$msg="<b>WARNING:</b> No link_id found. Likely not be connected to database.<br />$msg";
	}

	// if no debug, done here
	if(!$this->debug) return;
	?>
		<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
		<tr><th colspan=2>Database Error</th></tr>
		<tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
		<?php if(!empty($this->error)) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>'.$this->error.'</td></tr>'; ?>
		<tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
		<?php if(!empty($_SERVER['REQUEST_URI'])) echo '<tr><td align="right">Script:</td><td><a href="'.$_SERVER['REQUEST_URI'].'">'.$_SERVER['REQUEST_URI'].'</a></td></tr>'; ?>
		<?php if(!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td align="right">Referer:</td><td><a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
		</table>
	<?php
}#-#oops()

public function random_string($length)
{
	$string = "";

	// genera una stringa casuale che ha lunghezza
	// uguale al multiplo di 32 successivo a $length
	for ($i = 0; $i <= ($length/32); $i++)
		$string .= md5(time()+rand(0,99));

	// indice di partenza limite
	$max_start_index = (32*$i)-$length;

	// seleziona la stringa, utilizzando come indice iniziale
	// un valore tra 0 e $max_start_point
	$random_string = substr($string, rand(0, $max_start_index), $length);

	return $random_string;
}



}?>
