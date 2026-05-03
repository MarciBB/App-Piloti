<?php

ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 30000); //300 seconds = 5 minutes
          
define('SERVER_NAME_CRM','crm.gema.it');
define('DB_NAME_CRM','crm_gema1');
define('DB_USER_CRM','crmgema');
define('DB_PASSWORD_CRM','crmgema11');

global $user_id,$team_id,$team_set_id,$datacorrente,$session_id,$url,$dataUltimaModifica,$dataStorico,$session_id,$last;   
$last=0;
$url = "https://crm.gema.it/crm/service/v4/rest.php";
$username = "admin";
$password = "Controlla2!";          
            
  $login_parameters = array(
         "user_auth"=>array(
              "user_name"=>$username,
              "password"=>md5($password),
              "version"=>"1"
         ),
         "application_name"=>"SyncTarget",
         "name_value_list"=>array(),
    );

 //   $login_result = call("login", $login_parameters, $url);
    //get session id
   // $session_id = $login_result->id;     

   


function calcolaRipartizione($arrUser,$totaleLeadAssegnati,$last )
{
    

$leadInArrivo=10;

$x=0;
$f=0;
 //usort($arrUser, 'cmp2');    
    // ordino l'array per percAssegnati desc e perc Gestibili Asc
    
   /* if ($totaleLeadAssegnati==0)
    {
        usort($arrUser, 'cmp');
        $arrUser[0]['assegnati']=1;
     
    }
    else
    {*/
        
        $last=$last+1;
        if ($last>sizeof($arrUser)-1)
        $last=0;
         echo("last".$last."<br />");
        
      
        foreach ($arrUser as $utenti)
        {
            $assegnati= $arrUser[$last]['assegnati'];
            $assegnabili=$arrUser[$last]['percGestibili']/10; 
            if ($assegnati<$assegnabili)
            {
                $arrUser[$last]['assegnati']=$arrUser[$last]['assegnati']+1;
                echo("assegno il lead all'utente ".$arrUser[$last]['utente']);
                break;
            }
            else {
                $last++;
                if ($last>sizeof($arrUser)-1)
                $last=0;
                
                  echo("last11 ".$last."<br />");
            }
            
            
                 
        }
      //}      
        
    
  //  usort($arrUser, 'cmp2');  
    return ($arrUser);
    
    
    
    
    
    
}


function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid =substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
            
        
       $uuid=  strtolower($uuid); 
        return $uuid;
    }
}



function db_connect_crm(){

	if(!($sqlconnect = mssql_connect(SERVER_NAME_CRM, DB_USER_CRM, DB_PASSWORD_CRM,true)))
		die("errore mssql connect! verifica la configurazione delle variabili PHP.");
	if(!($sqldb = mssql_select_db (DB_NAME_CRM,$sqlconnect)))
		die("errore selezione db!");
      
          echo($sqlconnect);
            
	//$sqlconnect = odbc_connect(DSN,DB_USER,DB_PASSWORD);

	return $sqlconnect;
}


function  prepare_pulisci($testo) {

                
                $testo=stripslashes($testo);
                $testo = str_replace("'","''",$testo);
                
                $testo = str_replace("\\","/",$testo);
                $testo = str_replace('"',"&quot;",$testo);
                
                         
                $testo=utf8_decode($testo);
                
  //  print ($testo."<br />");            
                
                return $testo;
                
                
                
}

function call($method, $parameters, $url)
    {
        ob_start();
        $curl_request = curl_init();

        curl_setopt($curl_request, CURLOPT_URL, $url);
        curl_setopt($curl_request, CURLOPT_POST, 1);
        curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_request, CURLOPT_HEADER, 1);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

        $jsonEncodedData = json_encode($parameters);

        $post = array(
             "method" => $method,
             "input_type" => "JSON",
             "response_type" => "JSON",
             "rest_data" => $jsonEncodedData
        );

        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($curl_request);
        curl_close($curl_request);

        $result = explode("\r\n\r\n", $result, 2);
        $response = json_decode($result[1]);
        ob_end_flush();

        return $response;
    }


$db=db_connect_crm();
die();
$sql="select campaign_id from leads where deleted=0 and campaign_id is not null and status='new' and campaign_id<>''";


// per ogni campagna devo ciclare
$base=100;
$totaleLeadAssegnati=0;// leggi dal db
$last=-1;

$arrUser[0]['assegnati']=0; // leggi dal db
$arrUser[0]['percGestibili']=50;
$arrUser[0]['utente']="utente1";



$arrUser[1]['assegnati']=0 ;// leggi dal db
$arrUser[1]['percGestibili']=30;
$arrUser[1]['utente']="utente2";

$arrUser[2]['assegnati']=0;// leggi dal db
$arrUser[2]['percGestibili']=20;
$arrUser[2]['utente']="utente3";



$x=0;
while($x<20)
{
     print("LEAD NUMERO ".($x+1)."<br />");
    $arrUser=  calcolaRipartizione($arrUser,$totaleLeadAssegnati,$last);
    $totaleLeadAssegnati++;
    $last++;
    
     if ($last>sizeof($arrUser)-1)
        $last=0;
   
    
    $c=0;
    foreach($arrUser as $utenti)
    {
        
        
        
         
        if ($totaleLeadAssegnati==10)
        {
            $arrUser[$c]['assegnati']=0;
            $last=-1;
            
        }
            
        
        $c++;
    }
    
   
    print("<br />");
    print_r($arrUser);
    print("<br />");
    print("<br />");
    print("<br />");
    
   
    
    $x++;
}






   


?>

