<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.Sede.php");
include_once($classespath_."/class.Gestore.php");

function GetSediByGestore()
{
global $db,$user;    
$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);

$GestoreId=$_REQUEST['GestoreId'];

print("<option value=\"\">- seleziona -</option>");

if ($GestoreId>0)
$sql = "SELECT SedeId,Comune,Indirizzo from ElencoSediView where GestoreId=$GestoreId and GestoreId IN ($InGestoreFigli) and Stato=1";
else
$sql = "SELECT SedeId,Comune,Indirizzo from ElencoSediView where GestoreId IN ($InGestoreFigli) and Stato=1";   

if (($user->GestoreId==1) or ($user->GestoreId==2))
{
    $sql = "SELECT SedeId,Comune,Indirizzo from ElencoSediView where GestoreId=$GestoreId and Stato=1";
    
}




$ArrObject = $db->fetch_array($sql);
$ArrObjectSize=count($ArrObject);



                        
$i=0;
                        while ($i<$ArrObjectSize)
                        {
                           
                            $value=$ArrObject[$i]['SedeId'];
                            $label=$ArrObject[$i]['Comune']." ".$ArrObject[$i]['Indirizzo'];
                           
                            ?>  
                            <option value="<?=$value?>"><?=$label?></option>
                            <?

                            $i++;

   
                        }
    
    $db->close();
exit(); 
}



if(is_object($user)) {
$db= new Database();
$db->connect();
$user->conn=$db;


	
	
		if (!empty($_REQUEST))
		{
			switch($_REQUEST['do']) {

				case "getSediByGestore":
                                 
                                 
                                   getSediByGestore();   
                                 
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					
					
				break;
				
				
                }
              }

	else {
            Errors::$ErrorePermessiModulo;
            
        }

}

// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>