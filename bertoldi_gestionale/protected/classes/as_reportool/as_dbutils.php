<?php
/**
 *********************************************************************
 @package: as_dbutils.php
 @desc    SQL data accessing wrapper with backup/restore functions
 @author: Alexander Selifonov <as-works@narod.ru>
 @link http://www.selifan.ru
 last_modified (mm.dd.yyyy): 26.09.2008
 @version: 0.98.129
 *********************************************************************
**/
define('DBTYPE_MYSQL',1);
define('XML_PREFIX', 'AS_table'); # root tag in backup xml-file
define('DB_DEFAULTCHARSET','WINDOWS-1251');
$findcnt = 0;
/**
* if var $as_dbparam('server'=>'hostaddr', 'dbname'=>'mybase','username'=>'login','password'=>'psw') set,
* connection will be created inside this class, and passing these vars to constructor is not nessesary
*/
//include('../class.Config.php');

class CDbEngine { // main class def.
  var $dbtype = DBTYPE_MYSQL;
  /*var $host = 'localhost';
  var $username = 'dbadmin';
  var $password = 'AccediDb2011!';
  var $db_name = 'resolve_dev_odc';*/
  var $b_permconn = true; # use permanent connection when possible
  var $connection = false;
  var $connected = false;
  var $qrylink = 0; # link returned by  last sql_query()
  var $affectedrows = 0;
  var $lastquerytext = ''; # last executed query text
  var $tables = array(); # table list for backup
  var $outputfile = ''; # output backup filename
  var $fhan = 0; # file handle for backup file read/write
  var $bckp_emptyfields = 0; // 1 or true - backup with empty (default) field values
  var $charset = DB_DEFAULTCHARSET;
  var $rfrom = array("\\",'<','>');
  var $rto = array("\\x92","\\x60","\\x62");
  var $gzmode = false;
  var $verbose = 0;
  var $buf = '';
  var $tmpbuf = '';
  var $stoptag = '';
  var $fileeof = false;
  var $errormessage = '';
  var $errorlog_file = '';
  var $extract_ddl = true; // put 'CREATE TABLE...' operators into backup file
  var $tablename = '';
  var $createSql = '';
  var $bContents = false; // create table-list in backup XML file
  var $emulate = false; // restore,sql_query: no real INSERT, just emulating (debug or other purposes)
  var $logging = false; # logging mode (0-don't log anything)
  var $safemode = 1; # converting 'unsafe' chars in text fields method : 0:no conversion, 1:'=>", 2:mysql_real_escape_string()
  var $blobfields = array(); # these fields excluded from "str_replace" before update
  var $bckp_filter=array(); # $bckp_filter['mytable']= "datecreate='2006-12-31'" - backup records filter
  var $fakeinsertid=0;
  function CDbEngine($db_type=DBTYPE_MYSQL, $host=false,$user=false,$password=false,$dbname=false) {
    global $as_dbparam;
    $this->dbtype=$db_type;
    if(Config::$dbserver===false && isset($as_dbparam['server']))       $host = $as_dbparam['server'];
    if(Config::$dbuser===false && isset($as_dbparam['username']))     $user = $as_dbparam['username'];
    if(Config::$dbpass===false && isset($as_dbparam['password'])) $password = $as_dbparam['password'];
    if(Config::$dbname===false   && isset($as_dbparam['dbname']))   $dbname   = $as_dbparam['dbname'];
    # some providers ban persistent connections, so just define this CONST to force using mysql_connect()
    if(defined('DB_AVOID_PERSISTENT_CONNECT')) {
      $this->b_permconn = false;
    }
    if(Config::$dbserver!==false) $this->Connect(Config::$dbserver,Config::$dbuser,Config::$dbpass,Config::$dbname);
  }
  /**
  * @desc sets list of field names that are 'BLOB', so do not convert them with addslashes or str_replace
  */
  function SetBlobFields($fldarray) {
    if(is_string($fldarray)) $this->blobfields = split("[,;|]",$fldarray);
    elseif(is_array($fldarray)) $this->blobfields = $fldarray;
  }
  function SaveDDLMode($flag=true) { $this->extract_ddl = $flag; }
  function SetCharSet($charset) { $this->charset = strtoupper($charset); }

  function AddBackupFilter($prm1,$prm2) { // add an array or one filter
    if(is_array($prm1)) $this->bckp_filter = array_merge($this->bckp_filter,$prm1);
    else $this->bckp_filter[$prm1]= $prm2;
  }

  function SetVerbose($flag=true) { $this->verbose = $flag; }
  function GetErrorMessage() { return $this->errormessage; }
  function Connect($host=false,$user=false,$password=false,$dbname=false) {
    $b_reconnect= false;
    if(!$this->connected) {
      if($host!==false) $this->host = $host;
      if($user!==false) $this->username = $user;
      if($password!==false) $this->password = $password;
      $b_reconnect = true;
    }
    $ret = false;
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $hostaddr = $this->host;
      if (isset(Config::$dbport) && Config::$dbport) {
        if (strpos($hostaddr, ':') === false) $hostaddr .= ':' . Config::$dbport;
      }
      $ret = ($b_reconnect? (($this->b_permconn)? @mysql_pconnect($hostaddr,$this->username,$this->password) :
      @mysql_connect($hostaddr,$this->username,$this->password)) : true);
      if($this->logging) {
        $flog = @fopen('as_dbutils.log','a');
        if($flog) {
          fwrite($flog, "\n".date('Y-m-d H:i:s')."|opening DB, server=[{$this->host}], db=[$dbname/$this->db_name] : message=".mysql_error());
          fclose($flog);
        }
      }

      if($ret) {
        if($b_reconnect) $this->connection = $ret;
        if(!empty($dbname)) $this->db_name = $dbname;
        $ret = @mysql_select_db($this->db_name);
        if(!is_resource($ret)) $this->errormessage = mysql_error();
      }
      else {
        $ret = false;
        if(function_exists('WriteDebugInfo')) WriteDebugInfo("as_dbutils- ERROR connecting, host={$this->host}, user={$this->username}, pwd={$this->password}",mysql_error());
      }
      $this->errormessage = mysql_error();
      break;
      // case other DBTYPE_...
    }
    $this->connected = $ret;
    #echo "debug: CDEngine::Connect done: $ret<br>"; #debug
    return $ret;
  }
  function select_db($dbname) {
    $ret = false;
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $ret = mysql_select_db($dbname);
      if($ret) $this->db_name = $dbname;
      $this->errormessage = mysql_error();
      break;
      // case other DBTYPE_...
    }
    return $ret;
  }
  function GetDbVersion() {
    # works for MySQL only for the moment
    $lnk = $this->sql_query('SELECT VERSION()');
    $ret = '';
    if(is_resource($lnk) && ($r=$this->fetch_row($lnk))) {
      $ret = $r[0];
      $this->free_result($lnk);
    }
    return $ret;
  }

  function Disconnect() {
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      if(empty($this->b_permconn)) mysql_close($this->connection);
      $connected = false;
      return true;
#   case other DBTYPE_...
    }
    return false;
  }
  # GetTableList() - returns array with all table names
  function GetTableList() {
    $ret = array();
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $tlnk = mysql_query('show tables');
      while($tlnk && ($trow = mysql_fetch_row($tlnk))) { $ret[] = $trow[0]; }
      break;
    # case DBTYPE_...
    }
    return $ret;
  }
  function GetFieldList($tablename) {
    $lnk = $this->sql_query("DESCRIBE $tablename");
    $ret = array();
    while(is_resource($lnk) && ($r=$this->fetch_row($lnk))) $ret[] = $r;
    if(is_resource($lnk)) $this->free_result($lnk);
    return $ret;
  }
  function GetPrimaryKeyField($tablename) {
    $ret = '';
    $flds = $this->GetFieldList($tablename);
    foreach($flds as $no=>$f) { if($f[3]=='PRI') {$ret=$f[0]; break;} }
    return $ret;
  }
  function affected_rows() {
#    switch($this->dbtype) {
#    case DBTYPE_MYSQL:
     if(func_num_args()>0) return mysql_affected_rows(func_get_arg(0));
     $ret = mysql_affected_rows();
#     break;
    # case DBTYPE_...
#    }
    return $ret;
  }
  function insert_id() {
    if($this->emulate) return $this->fakeinsertid;
#    switch($this->dbtype) {
#    case DBTYPE_MYSQL:
      if(func_num_args()>0) return mysql_insert_id(func_get_arg(0));
      return mysql_insert_id();
#    }
    return 0;
  }
  function sql_errno() {
#    switch($this->dbtype) {
    if(func_num_args()>0) return mysql_errno(func_get_arg(0));
#    case DBTYPE_MYSQL:
    return mysql_errno();
    # case DBTYPE_...
#    }
    return 0;
  }
  function sql_error() {
    switch($this->dbtype) {
    case DBTYPE_MYSQL: return mysql_error();
    # case DBTYPE_...
    }
    return 0;
  }
  function IsTableExist($table) {
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $ret = (mysql_query("SELECT COUNT(1) FROM $table"))? true:false;
      break;
    }
    return $ret;
  }
  function sql_query($query,$getresult=false, $assoc=false, $multirow=false) { // universal query execute
    $this->lastquerytext = $query;
    $this->affectedrows = 0;
    if($this->emulate) {
      echo "emulate query: $query\r\n<br />";
      $this->fakeinsertid = rand(1000,99999);
      $ret = $this->affectedrows = 0;
      return $ret;
    }
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $ret = $this->qrylink = @mysql_query($query);
      $this->affectedrows = ($ret? @mysql_affected_rows() : false);
      $this->errormessage = mysql_error();
      break;
    # case DBTYPE_...
    }
    if($this->affectedrows>0 && !empty($getresult)) {
      if(!$multirow) $ret = ($assoc)? $this->fetch_assoc($this->qrylink) : $this->fetch_row($this->qrylink);
      else while(true) {
        $r = ($assoc)? $this->fetch_assoc($this->qrylink) : $this->fetch_row($this->qrylink);
        if(!$r) break;
        $ret[] = $r;
      }
    }
    if(is_resource($this->qrylink) && !empty($getresult)) { $this->free_result($this->qrylink); $this->qrylink=false; }
    if($this->logging) {
      $flog = fopen('as_dbutils.log','a');
      if($flog) {
        fwrite($flog, "\n".date('Y-m-d H:i:s')."|exec query|$query|{$this->qrylink}|err:{$this->errormessage}|rows: {$this->affectedrows}");
        fclose($flog);
      }
    }
    if(strlen($this->errormessage) && strlen($this->errorlog_file)) {
       $flog = fopen($this->errorlog_file,'a');
       if($flog) {
         fwrite($flog,date('Y-m-d H:i:s')." : SQL: $query; Error: {$this->errormessage}\n");
         fclose($flog);
       }
    }
    return $ret;
  }

  function sql_explain($query) { // 'explain plan'
    $this->lastquerytext = $query;
    $this->affectedrows = 0;
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $ret = $this->qrylink = mysql_query("EXPLAIN $query");
      $this->affectedrows = ($ret? mysql_affected_rows() : false);
      $this->errormessage = mysql_error();
      break;
    # case DBTYPE_...
    }
    return $ret;
  }

  function fetch_row($link) {
    if(!is_resource($link)) return false;
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $ret = mysql_fetch_row($link);
      if(mysql_error()) echo "as_dbutils:fetch_row: wrong link $link !";
      return $ret;
      break;
    # case DBTYPE_...
    }
    return false;
  }
  function fetch_assoc($link) {
    if(!is_resource($link)) return false;
    switch($this->dbtype) {
    case DBTYPE_MYSQL: return mysql_fetch_assoc($link);
    # case DBTYPE_...
    }
    return false;
  }
  function fetch_object($link) {
    switch($this->dbtype) {
    case DBTYPE_MYSQL: return mysql_fetch_object($link);
    # case DBTYPE_...
    }
    return false;
  }

  function SQLBuildAndExecute($table,$sqltype,$p1='',$p2='',$p3=false,$p4=false) {
  # builds SQL query and execute it. Returns cursor link or false, $this->affectedrows holds affected rows count
    $ret = false;
    $qry = '';
    $sqltype = strtoupper($sqltype);
    switch($sqltype) { #<3>
      case 'S': case 'SELECT': # $table-table(s), p2-field(s) to select, $p3- WHERE conditions
        $qry = "SELECT $p1 FROM $table";
        if(is_string($p2) && !empty($p2) ) {
          if($p3===false) // WHERE condition in p3, so don't build filed=value
               $cond = $p2;
          else $cond = "$p2='$p3'";
          $qry .= " $cond";
        }
        break;
      case 'I': case 'INSERT': # $table-table, $p1-'field'=>value assoc.array
        $flist = $vlist = '';
        foreach($p1 as $fld=>$value) {
          $flist .= ($flist==''?'':',').$fld;
          if($value==='now') $oneval = 'SYSDATE()';
          else {
            $oneval = "'";
            if($this->safemode==0 || in_array($fld,$this->blobfields)) $oneval .= $value;
            elseif($this->safemode==1) $oneval .= str_replace("'",'"',$value);
            else $oneval .= mysql_real_escape_string($value); # safemode>1 make real_escaped value
            $oneval .= "'";
          }
          $vlist .= ($vlist==''?'':',').$oneval;
        }
        $qry = "INSERT INTO $table ($flist) VALUES ($vlist)";
        break;
      case 'U': case 'UPDATE': # $table-table, $p1-'field'=>value assoc.array, $p2,$p3= PK field name and it's value
        $flist='';
        foreach($p1 as $fld=>$value) {
          if($value==='now') $oneval = 'SYSDATE()';
          else {
            $oneval = "'";
            if($this->safemode==0 || in_array($fld,$this->blobfields)) $oneval .= $value;
            elseif($this->safemode==1) $oneval .= str_replace("'",'"',$value);
            else $oneval .= mysql_real_escape_string($value); # safemode>1 make real_escaped value
            $oneval .= "'";
          }
          $flist .= ($flist==''?'':',')."$fld=$oneval";
        }

        $qry = "UPDATE $table SET $flist";
        $cond = '';
        if(is_string($p2) && !empty($p2) ) {
          if($p3===false) // WHERE condition in p3, so don't build filed=value
               $cond = $p2;
          else $cond = "$p2='$p3'";
        }
        if($cond!='') $qry .= " WHERE $cond";
        else $qry = ''; # protect from accident WHOLE TABLE update
        break;
      case 'D': case 'DELETE': # p1 - WHERE condition ("field=value" or what else...
        if(!empty($p1)) $qry = "DELETE FROM $table WHERE $p1";
        break;
    } #<3> switch end
    if($qry !='')  $ret = $this->sql_query($qry);
#    echo "SQLBuildAndExecute qry: $qry, result: $ret, error:<br>".$this->sql_error(); #debug
    return $ret;
  }

  function free_result($link) {
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      if(is_resource($link)) mysql_free_result($link);
      break;
    # case DBTYPE_...
    }
  }
  /**
  * @desc returns record count for desired table (with optional WHERE condition, if passed)
  */
  function GetRecordCount($tblname,$filter='') {
#    $flt = '(1)'.(empty($filter)? '':" AND $filter");
    $result = $this->GetQueryResult($tblname,'COUNT(1)',$filter);
    return $result;
  }
  function GetQueryResult($table,$fieldlist,$cond='',$multirow=false, $assoc=false,$safe=false) {
    $qry="SELECT $fieldlist FROM $table". ($cond==''? '': " WHERE $cond");
    $lnk = $this->sql_query($qry);
#    echo "$qry ".mysql_error(); #debug
    if(empty($lnk)) return false;
    $reta = false;
    while(($row=($assoc ? $this->fetch_assoc($lnk): $this->fetch_row($lnk) ))) {
      if(($safe) && !get_magic_quotes_runtime()) {
        foreach($row as $key=>$val) $row[$key] = addslashes($val);
      }
      if(($assoc)) $retvalue = $row;
      else $retvalue = (count($row)==1) ? $row[0] : $row;
      if(empty($multirow)) return $retvalue;
      if(!is_array($reta)) $reta=array();
      $reta[] = $retvalue;
    }
    $this->free_result($lnk);
    return $reta;
  }
  function SqlAffectedRows() { return $this->affectedrows; }
  function FileWrite($strg) {
    return (($this->gzmode)? gzwrite($this->fhan,$strg):fwrite($this->fhan,$strg));
  }
  /**
  * @desc CloneRecords() duplicates record(s) in the table
  * @param $atblename - table name
  * @param $pk_name - primary key field name
  * @param $pk_value - one value or value array of records to be cloned
  */
  function CloneRecords($tablename,$pk_name,$pk_value,$desttable='') {
    $ret = 0;
    $totable = ($desttable=='')? $tablename:$desttable;
    if(is_array($pk_value)) {
      $ret = array();
      foreach($pk_value as $val) {
        $dta = $this->GetQueryResult($tablename,'*',"$pk_name='$val'",false,true,true);
        if($totable==$tablename) unset($dta[$pk_name]);
        $this->SQLBuildAndExecute($totable,'I',$dta);
        if($this->affected_rows())  $ret[] = $this->insert_id();
      }
    }
    else {
      $dta = $this->GetQueryResult($tablename,'*',"$pk_name='$pk_value'",false,true);
      if($totable==$tablename) unset($dta[$pk_name]);
      $this->SQLBuildAndExecute($totable,'I',$dta);
      if($this->affected_rows()) $ret = $this->insert_id();
    }
    return $ret;
  }
  function GetTableStructure($table) {
    $qry = 'DESC '.$table;
    $rsrc = $this->sql_query($qry);
    $ret = array();
    switch($this->dbtype) {
    case DBTYPE_MYSQL:;
      while(($row=$this->fetch_row($rsrc))) {
       //  $ret[field_name] =[ type,  Null ,   Key(MUL|PRI) Default Extra (auto-increment)
       $ret[$row[0]] = array($row[1], $row[2],$row[3],$row[4],$row[5]);
      }
      break;
    # case DBTYPE_...
    }
    return $ret;
  }

  // backup/restore data function
  function TryOpenBackupFile($fname) {
     if($this->fhan>0) $this->fileClose();
     $flen = strlen($fname);
     if($flen<1) return false;
     $this->gzmode = ($flen>3 && strtolower(substr($fname,$flen-3))=='.gz')? 1:0;
     $this->fileeof = false;
     $this->fhan = ($this->gzmode? @gzopen($fname,'r') : @fopen($fname,'r'));
     if(empty($this->fhan)) {
       $this->errormessage = 'Cannot open backup file '.$fname;
       return false;
     }
     $this->buf = ''; $this->tempbuf = '';
     $result = $this->FindStartTag(XML_PREFIX);
     return true;
  }
  function FileClose() {
    $closed = ($this->gzmode? gzclose($this->fhan): fclose($this->fhan));
    $this->fhan = 0;
  }
  function FileRewind() {
    if($this->gzmode) gzrewind($this->fhan);
    else  rewind($this->fhan);
  }
  function CreateContents($var=true) { $this->bContents = $var; }
  function BackupOneTable($tablename) {
    $defval = array();
    $ret = 0;
    if(empty($this->bckp_emptyfields)) { # get default fields values into $defval
      $defval = $this->GetTableStructure($tablename);
    }
    if(is_array($defval)) { #<2>
     $this->FileWrite(" <as_dbutils_table><name>$tablename</name>\n");
     if($this->extract_ddl) { #<3>
       $lnk = mysql_query("SHOW CREATE TABLE $tablename");
       if(is_resource($lnk) && ($r=mysql_fetch_row($lnk))) { #<4>
          $ddl = $r[1];
          $this->FileWrite(" <CreateSQL>$ddl</CreateSQL>\n");
       } #<4>
     } #<3>
    } #<2>
    $qry = "SELECT * FROM $tablename".(empty($this->bckp_filter[$tablename])?'':' WHERE '.$this->bckp_filter[$tablename]);
    $lnk = $this->sql_query($qry);
    if(is_resource($lnk)) {
      $rcnt = 0;
      while(($lnk) && ($r=$this->fetch_assoc($lnk))) {
        $this->FileWrite("  <as_dbutils_record>\n");
        while (list($key, $val) = each($r)) { //<3>
         if($this->bckp_emptyfields || $val !=$defval[$key][3] ) {
           $val = str_replace($this->rfrom, $this->rto, $val);
           $this->FileWrite("   <$key>$val</$key>\n");
         }
        } //<3>
        $rcnt++;
        $this->FileWrite("  </as_dbutils_record>\n");
      }
      $this->FileWrite(" </as_dbutils_table>\n");
      if($this->verbose) echo date('Y.m.d H:i:s')." &nbsp;$tablename, records saved: $rcnt<br>\n";
      $ret = 1;
    }
    else {
      if($this->verbose) echo date('Y.m.d H:i:s')." &nbsp;table $tablename does not exist !<br>\n";
    }
    return $ret;
  }

  function BckpBackupTables($tlist, $fname='', $pack=0) {
    $this->tables = $tlist;
    if(!is_array($this->tables) || count($this->tables)<1)
      $this->tables = $this->GetAllTablesList();
    if(count($this->tables)<1) { $this->errormessage = "Empty table list or no connection"; return 0; }
    $this->gzmode = (($pack) && function_exists('gzopen'));
    if($fname==='') $fname = 'backup-'.date('Y-m-d').'.xml';
    if(!is_array($this->tables) || count($this->tables)<1) return false;

    $this->outputfile = $fname .($this->gzmode ? '.gz':'');
    $this->fhan = ($this->gzmode)? @gzopen($this->outputfile, 'w9') : @fopen($this->outputfile,'w');
    if(empty($this->fhan)) { $this->errormessage='Cannot open output file for writing'; return 0; }
    $this->FileWrite("<?xml version=\"1.0\" encoding=\"$this->charset\"?>\n");
    $this->FileWrite('<'.XML_PREFIX.">\n");
    $retcode = 0;
    if($this->bContents) {
        $stlist = '';
        foreach($this->tables as $tname) $stlist .= ($stlist=='' ? '':',').$tname;
        $this->FileWrite("<TableList>$stlist</TableList>\n");
    }
    reset($this->tables);
    foreach($this->tables as $tname) {
       $retcode += $this->BackupOneTable($tname);
    }
    $this->FileWrite('</'.XML_PREFIX.">\n");
    $this->FileClose();
    return $retcode;
  } // BackupTables() end

  function GetAllTablesList() {
    $ret = array();
    switch($this->dbtype) {
    case DBTYPE_MYSQL:
      $lnk=$this->sql_query('SHOW TABLES');
      if($this->affected_rows()<1) { $this->errormessage = "no tables in DB or no DB connection"; return 0; }
      while(($tbl=mysql_fetch_row($lnk))) { $ret[] = $tbl[0]; }
      break;
#   case DBTYPE_xxx ...
    }
  }
  function BackupDatabase($fname='',$pack=0) {
    $tlist = $this->GetAllTablesList();
    if(is_array($tlist) && count($tlist))
      $this->BackupTables($tlist, $fname, $pack);
  }

  function ReadFilePortion($bytes=4096) {
    if(!$this->fileeof && !empty($this->fhan)) {
      $this->buf .= $this->gzmode ? gzread($this->fhan,$bytes): fread($this->fhan,$bytes);
      $this->fileeof = $this->gzmode ? gzeof($this->fhan) : feof($this->fhan);
#      echo "<br>read file portion $bytes ...<br>"; // debug
    }
    return $this->fileeof;
  }

  function FindStartTag($tag, $dropoldbuf=false,$maxbytes=0) {
    global $findcnt;
    $ftag = "<$tag>";
    $readcount=0;
    while(1) {
#      $findcnt++; if($findcnt>20) break;
      if(($npos = strpos($this->buf,$ftag))!==false) {
        if($this->stoptag !=='') { //<4>
           $endpos = strpos($this->buf,'<'.$this->stoptag.'>');
           if($endpos !==false && $endpos < $npos) return -1;
        } //<4>
        if($dropoldbuf) { $this->buf = substr($this->buf, $npos); $npos = 0; }
        return $npos;
      }
      if($this->fileeof || (!empty($maxbytes) && $readcount>=$maxbytes)) break;
      $this->ReadFilePortion(); $readcount += 4096;
#      echo "debug FindStartTag($tag): read 4096<br>"; # debug
    }
    return -1; // no more tags in stream!
  }

  function FindEndTag($tag, $stoptag='', $dropoldbuf=false ) {
    $ftag = "</$tag>";
    while(1) {
      if(($npos = strpos($this->buf,$ftag))!==false) {
        if($dropoldbuf) { $this->buf = substr($this->buf, $npos); $npos = 0; }
        return $npos;
      }
      if(!$this->fileeof) $this->ReadFilePortion();
      else break;
    }
    return -1; // no more tags in stream!
  }

  function FindXmlValue($tag,$maxbytes=0) { // read from <tag> to </tag> into result
     $ret = false;
     $taglen = strlen($tag);
     $pos2 = $this->FindStartTag($tag,1,$maxbytes);
     if($pos2>=0) {
        $pos3 = $this->FindEndTag($tag);
        if($pos3>0) {
             $ret = substr($this->buf,$pos2+$taglen+2,$pos3-$pos2-$taglen-2);
             $this->buf = substr($this->buf,$pos3+$taglen+3);
        }
     }
     return $ret;
  }

  function GetNextTable() { // finds <table> beginning, read CREATE TABLE DDL
     $strt = $this->FindStartTag('as_dbutils_table',1);
     $ret = 0;
     if($strt>=0) {
        $this->tablename = $this->FindXmlValue('name');
        $this->createSql = '';
        if(strpos($this->buf, '<CreateSQL>')!==false) {
           $this->createSql = $this->FindXmlValue('CreateSQL');
        }
        $ret = 1;
     }
     return $ret;
  }
  function BuildInsertSql($xmlrecord) {
    $ret = '';
    $flds = array();
    while(1) {
       $spos1 = strpos($xmlrecord,'<');
       if($spos1!==false) {
          $spos2 = strpos($xmlrecord,'>',$spos1+1);
          if($spos2!==false) {
            $fldname = substr($xmlrecord,$spos1+1, $spos2-$spos1-1);
            $spos3 = strpos($xmlrecord,"</$fldname>",$spos2+1);
            if($spos3> $spos2) { //<6>
              $fvalue = substr($xmlrecord,$spos2+1,$spos3-$spos2-1);
              $flds[$fldname]=addslashes($fvalue); // escape special chars!
              $xmlrecord = substr($xmlrecord, $spos3+strlen($fldname)+2);
            } //<6>
          }
          else break;
       }
       else break;
    }
    if(count($flds)) {
      // building SQL INSERT into...
      $fnames = ''; $fvals = '';
      reset($flds);
      foreach ($flds as $fldname => $fvalue) {
        $fnames .= ($fnames==''?'':',').$fldname;
        $fvalue = str_replace($this->rto, $this->rfrom, $fvalue); // decode spec chars
        $fvals .= ($fvals==''?'':',')."'$fvalue'";
      }

      $ret = "INSERT INTO {$this->tablename} ($fnames) VALUES ($fvals)";
    }
    return $ret;
  }
  function BckpGetContents($fname) { // gets table list from XML backup file
     if(empty($fname)) return '';
     $this->TryOpenBackupFile($fname);
     if(empty($this->fhan)) return 0;
     $ret = array();
     $slist = $this->FindXmlValue('TableList',4096);
     if(strlen($slist)>0) { // contents (table list) exist, so get it!
       $ret = explode(',',$slist);
#       echo "debug:GetContents short way!";
     }
     else { // long way - get all table names by GetNextTable()
#       echo "debug:GetContents LONG way!<br>";
       $this->FileRewind();
#       $ideb = 0;
       while(($this->GetNextTable())) {
         $ret[] = $this->tablename;
#         $ideb++; if($ideb>=100) break;
       }
     }
     $this->FileClose();
     return $ret;
  }
  /**
  * @desc BckpRestoreTables() restores SQL data from xml[.gz] backup file.
  * @param $fname - backup filename to restore from
  * @param $verbose - if not empty, function echoes log
  * @param $tlist - can be table names array  that must be restored (the rest will be skipped)
  */
  function BckpRestoreTables($fname, $verbose=0, $tlist='') {
     $flen = strlen($fname);
     $this->verbose = $verbose;
     $this->TryOpenBackupFile($fname);
     if(empty($this->fhan)) return false;

     if($this->verbose && $this->emulate) echo "Emulated Restore, no real data changing...<br>\n";
     if($this->verbose) echo date('Y.m.d H:i:s')." Restore from $fname begin <hr>\n";
     $ret = 0;
     while(1) { //<3>
        $result = $this->GetNextTable();
        if($result) { //<4>
          $inscnt = $errcnt = 0;
          $ret++;
          $skiptable = (is_array($tlist) && !in_array($this->tablename,$tlist));
          if($skiptable) {
            if($this->verbose) echo $this->tablename." - skipped<br />\n";
            continue;
          }
          $this->stoptag = '/as_dbutils_table'; // don't miss table end!
          if($this->createSql !=='') { //<5>
            $qry = 'DROP TABLE '.$this->tablename;
            if($this->verbose) echo date('Y.m.d H:i:s')." {$this->tablename} : Re-creating table...<br>";
            if(empty($this->emulate)) { //<6>
              mysql_query($qry);
              $created = mysql_query($this->createSql);
              if(empty($created)) { //<7>
                $this->errormessage = "{$this->tablename}: Re-creating table error: ".mysql_error();
                if($this->verbose) echo "{$this->errormessage}<br>\n";
                return 0;
              } //<7>
            } //<6>

          } //<5>
          else { // no CREATE DDL, so just truncate table before adding records
            if($this->verbose) echo date('Y.m.d H:i:s')."$this->tablename : truncating before adding data...<br>";
            if(empty($this->emulate)) $this->sql_query('TRUNCATE TABLE '.$this->tablename);
          }

          // start parse records and inserting thrm into the table
          while(($record = $this->FindXmlValue('as_dbutils_record'))) {
            $sql = $this->BuildInsertSql($record);
//            if($this->verbose) echo "  &nbsp; inserting record: $sql<br>";
            $this->sql_query($sql);
            if($this->errormessage) $errcnt++;
            else $inscnt++;
          }
          if($this->verbose) echo date('Y.m.d H:i:s')." $this->tablename, inserted records: $inscnt, failed inserts: $errcnt<br>";
          $epos = $this->FindEndTag('as_dbutils_table');
          if($epos !== false) $this->buf = substr($this->buf, $epos+18);
        } //<4>
        else { // no more tables in backup file
          break;
        }
     } //<3> while read loop
     if($this->verbose) echo date('Y.m.d H:i:s')."<hr> Restore from $fname finished<br>";
     $this->FileClose();
     return $ret;
  } // BackupTables() end

} // CDbEngine definition end

/**
* @desc cleanup OnExit - closes db connection
*/
function As_dbutilsCleanUp() {
  @mysql_close();
#  if(function_exists('WriteDebugInfo')) WriteDebugInfo("as_dbutils cleanup code done (closing Mysql connection)");
}
if(defined('DB_AVOID_PERSISTENT_CONNECT')) register_shutdown_function('As_dbutilsCleanUp');

$as_dbengine = new CDbEngine(); // ready-to-use class instance
?>
