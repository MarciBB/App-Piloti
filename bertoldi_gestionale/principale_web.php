<?php

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$config=new Config();
$run=$config->load();
$page=new Html_Page();
$classespath_=Config::$classespath;
include_once($classespath_."class.Sede.php");

function load_content()
{
    global $user,$db;
       
    $sede=new Sede();
    $sede->conn=$db;
    $sede->inizializza($user->SedeId);
    
    
    
                /*$mysqli=new mysqli("localhost","dbadmin","dbadmin" ,"concilialex_odc");
		$SQL = "call Gestore_proc('I',1,1,1,'Società Cooperativa Concilia Lex a.r.l.')";  
              
               $SQL = "call Gestore_proc('S',92,0,0,'')";  
                // 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore  

		if (($result = $mysqli->query($SQL))===false) {
		printf("Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL);
		exit();
		} 
                while ($myrow=$result->fetch_array(MYSQLI_ASSOC))
                {
                    echo($myrow["GestoreId"]."-".$myrow['RagioneSociale']."<br />");
                    
                }
                $result->close();
                $mysqli->close
    */
    
    
    
    
    
    
    
    
    ?>
<link rel="stylesheet" type="text/css" href="/css/printreport.css" media="print" />
<div class="brain_row brain_top">
        <div class="brain_titolo">
            <h1><?=Config::$application_name?> <!--?=Config::$application_version?--></h1>
        </div>
       <!-- <div class="brain_utente">
			<span class="brain_infoSede">Operatore: <strong><?=$user->Cognome." ".$user->Nome;?> (<?=$user->Username;?>)</strong> [ <a href="javascript:void(0);" onclick="javascript:ExternalLoad('operatore','operatore.php?do=mod_password');">cambia password</a> ] <a title="esci" href="/logout.php">Logout</a>  <br />
                            Sede: <strong><?=$sede->Comune?> - <?=$sede->Indirizzo?></strong><br />
                         <br />
                        </span>
       
        </div>  -->
        
        <?
        $filename = Config::$odcfile."/".$user->OdcId."/images/logo.jpg";

        if (file_exists($filename)) {
           ?>
        <div class="brain_logo"><img src="/odcfile/<?=$user->OdcId?>/images/logo.jpg"></div> 
        <?
        } 
        ?>
        
               
    <!--<div class="brain_sede">
            <span class="brain_operatore">Operatore: <strong><?=$user->Cognome." ".$user->Nome;?> (<?=$user->Username;?>)</strong> [ <a href="javascript:void(0);" onclick="javascript:ExternalLoad('operatore','operatore.php?do=mod_password');">cambia password</a> ]</span>           
            <span class="brain_login"><a title="esci" href="/logout.php">Logout</a></span>
    </div>		-->
	

    <div id="menubar">
	<ul id="topnav">
            <li class="brain_menu_el" id="brain_menu_home">
                <span id="span100" class="brain_bgMenu brain_sel">
                    <a id="a100" href="/web.php"  title="Nuovo Organismo" class="noPadding">
                        <span class="brain_home brain_sel"></span>
                    </a>
                    <span class="brain_vociMenu">
                        <a href="/web.php" title="Nuovo Organismo">
                            <span class="link_menu">Nuovo Organismo</span>
                        </a>					
                    </span>
                </span>
            </li>
	</ul>
    </div>

    <br style="clear:both;" />
    </div>
    <div id="brain_main-content">
        <? include("protected/modules/creazione_organismo/crea_organismo.php"); ?>
    </div>	
<?    
}
function browser_detection( $which_test ) 
{
	// initialize variables
	$browser_name = '';
	$browser_number = '';
	// get userAgent string
	$browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	//pack browser array
	// values [0]= user agent identifier, lowercase, [1] = dom browser, [2] = shorthand for browser,
	$a_browser_types[] = array('opera', true, 'op' );
	$a_browser_types[] = array('msie', true, 'ie' );
	$a_browser_types[] = array('konqueror', true, 'konq' );
	$a_browser_types[] = array('safari', true, 'saf' );
	$a_browser_types[] = array('gecko', true, 'moz' );
	$a_browser_types[] = array('mozilla/4', false, 'ns4' );
        $a_browser_types[] = array('Chrome', false, 'ch' );

	for ($i = 0; $i < count($a_browser_types); $i++)
	{
		$s_browser = $a_browser_types[$i][0];
		$b_dom = $a_browser_types[$i][1];
		$browser_name = $a_browser_types[$i][2];
		// if the string identifier is found in the string
		if (stristr($browser_user_agent, $s_browser)) 
		{
			// we are in this case actually searching for the 'rv' string, not the gecko string
			// this test will fail on Galeon, since it has no rv number. You can change this to 
			// searching for 'gecko' if you want, that will return the release date of the browser
			if ( $browser_name == 'moz' )
			{
				$s_browser = 'rv';
			}
			$browser_number = browser_version( $browser_user_agent, $s_browser );
			break;
		}
	}
	// which variable to return
	if ( $which_test == 'browser' )
	{
		return $browser_name;
	}
	elseif ( $which_test == 'number' )
	{
		return $browser_number;
	}

	/* this returns both values, then you only have to call the function once, and get
	 the information from the variable you have put it into when you called the function */
	elseif ( $which_test == 'full' )
	{
		$a_browser_info = array( $browser_name, $browser_number );
		return $a_browser_info;
	}
}

// function returns browser number or gecko rv number
// this function is called by above function, no need to mess with it unless you want to add more features
function browser_version( $browser_user_agent, $search_string )
{
	$string_length = 8;// this is the maximum  length to search for a version number
	//initialize browser number, will return '' if not found
	$browser_number = '';

	// which parameter is calling it determines what is returned
	$start_pos = strpos( $browser_user_agent, $search_string );
	
	// start the substring slice 1 space after the search string
	$start_pos += strlen( $search_string ) + 1;
	
	// slice out the largest piece that is numeric, going down to zero, if zero, function returns ''.
	for ( $i = $string_length; $i > 0 ; $i-- )
	{
		// is numeric makes sure that the whole substring is a number
		if ( is_numeric( substr( $browser_user_agent, $start_pos, $i ) ) )
		{
			$browser_number = substr( $browser_user_agent, $start_pos, $i );
			break;
		}
	}
	return $browser_number;
}

if(is_object($user)) 
{    
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $page->html_header();
    load_content();
    $page->html_footer();
}else{
    header("location: index.php");
    exit();
}
?>