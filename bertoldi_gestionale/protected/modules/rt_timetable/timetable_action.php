<?php
set_time_limit( 0 );
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
// include_once($classespath_."class.Mediatore.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");


include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");

$ModuloId=20;



global $tratta_wizard;
$tratta_wizard=null;

if(isset($_SESSION['TRATTA_WIZARD'])) {
$tratta_wizard=unserialize($_SESSION['TRATTA_WIZARD']);
}


function cambia_stato()
{
    global $user,$tratta_wizard,$db;
    $storico=new StoricoOperazioni();
$storico->conn=$db;
    
    $GestoreId=$tratta_wizard->GestoreId;
    
    $data['Stato']=$_POST['Stato'];
    $data=$storico->operazioni_update($data,$user);
    
    $res=$db->update('Gestore', $data, "GestoreId=".$GestoreId);
 echo("Lo stato del gestore e' stato cambiato");
 exit();
}



function create()
{
global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$post2 = getRealPOST();
// $data=$_POST['Timetable'];
// $data1=$_POST['Timetable1'];
$data=$post2['Timetable'];
$data1=$post2['Timetable1'];

$count = 0;

 foreach ($data as $chiave => $valore)
 { 
 	
 	$count++;
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $chiave);
     $CorsaId=$arr_chiave[0];
     $FermataId=$arr_chiave[1];
     $Orario=$valore;
     
     $d1['Orario']=$Orario;
     $d1['CorsaId']=$CorsaId;
     $d1['FermataId']=$FermataId;
    
     $d1['GiorniAggiuntivi']=$data1[$CorsaId."_".$FermataId];

     $lastidA=$db->delete("RT_Orario","CorsaId=$CorsaId and FermataId=$FermataId and OdcIdRef=$user->OdcId");
     
     if (!empty($Orario) and ($Orario) and ($Orario!=""))
     { 
     	$d1=$storico->operazioni_insert($d1,$user);
     	$lastidA=$db->insert("RT_Orario", $d1);
     }
 }
 $sql="truncate table RT_PercorsoBreve";
 $db->query($sql);
 
 $sql="truncate table RT_PercorsoBreveTratte";
 $db->query($sql);
 
 $sql="truncate table RT_PercorsoBreveWeb";
 $db->query($sql);
 
 $sql="truncate table RT_PercorsoBreveWebTratte";
 $db->query($sql);

    echo("ok".",".$lastidA);    

$db->close();
exit(); 

  
}

function getRealPOST() {
	$pairs = explode("&", file_get_contents("php://input"));
	$vars = array();
	foreach ($pairs as $pair) {
		$nv = explode("=", $pair);
		$name = urldecode($nv[0]);
		$value = urldecode($nv[1]);
		if (strpos($name,'Timetable') !== false) {
    		$temp = explode("[", $name);
    		$nameT = $temp[0];
    		$index = $temp[1];
    		$index = trim($index, "]");
    		$vars[$nameT][$index] = $value;
		} else {
			$vars[$name] = $value;
		}
	}
	return $vars;
}

function getRealPOSTTariffe() {
	$pairs = explode("&", file_get_contents("php://input"));
	$vars = array();
	foreach ($pairs as $pair) {
		$nv = explode("=", $pair);
		$name = urldecode($nv[0]);
		$value = urldecode($nv[1]);
		if (strpos($name,'Prezzi') !== false) {
			$temp = explode("[", $name);
			$nameT = $temp[0];
			$index = $temp[1];
			$index = trim($index, "]");
			$vars[$nameT][$index] = $value;
		} else if (strpos($name,'CorsaIdSelect') !== false) {
			$vars['CorsaIdSelect'][] = $value;
		} else {
			$vars[$name] = $value;
		}
	}
	return $vars;
}


function create_tariffe() {
    global $user,$db;
    
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    
    $dt=new DT();
	
	
    $post2 = getRealPOSTTariffe();
	
    $data = $post2['Prezzi'];
    $LineaId = $_POST['LineaId'];
    $CorsaId = $_POST['CorsaId'];
    $TrattaId = $_POST['TrattaId'];
    $PickupId = $_POST['PickupId'];
    $DropoffId = $_POST['DropoffId'];
    
    $tipoCreazione = $post2['tipoCreazione'];
    if($tipoCreazione > 1) {
        $corse = $post2['CorsaIdSelect'];
        if(!isset($corse)) {
            $corse = array();
        }
    }
    
    if(!in_array($CorsaId, $corse)){
        $corse[] = $CorsaId;
    }

    foreach ($corse as $c ) {
        if(isset($c) && $c != '') {
            $CorsaId = $c;
            if((!isset($TrattaId) || $TrattaId == 0) && (!isset($PickupId) || $PickupId == 0) && (!isset($DropoffId) || $DropoffId == 0)){
                echo "cancella corsa $CorsaId";
				$db->delete("RT_CorsaTariffa", "CorsaId=$CorsaId AND OdcIdRef=$user->OdcId");
                $delete = true;
            }
            
            //$sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 order by TipologiaBigliettoPeso asc ";
            //$ArrObjectTipoBiglietto = $db->fetch_array($sql);
            
            $sql = "Select * from RT_ListinoTipo";
            $ArrVariazioni = $db->fetch_array($sql);
            $variazioni = array();
            foreach($ArrVariazioni as $var){
                $variazioni[$var['BigliettoId']] = $var['Variazione'];
            }
            $variazioni[17] = 0;
            
            $count = 0;
            
            foreach ($data as $chiave => $tariffa) {
                
                $chiave=str_replace("'","",$chiave);
                $chiave=str_replace("\\","",$chiave);
                
                $arr_chiave = explode('_', $chiave);
                $LineaId = $arr_chiave[0];
				$TipologiaBigliettoId = $arr_chiave[1];
                $PkId = $arr_chiave[2];
                $DoffId = $arr_chiave[3];
                //$CorsaId=$arr_chiave[3];
				
				//recupero le variazioni di prezzo delle varie tipologie di prezzo
				$sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 
				AND ((TipoPrezzo = 1 AND TipoBigliettoIdRiferimento = ".$TipologiaBigliettoId.") OR TipologiaBigliettoId = ".$TipologiaBigliettoId.")
				order by TipologiaBigliettoPeso asc ";
				$ArrObjectTipoBiglietto = $db->fetch_array($sql);
                
                if(!$delete){
					echo "cancella 2 $CorsaId $PkId $DoffId";
                    $db->delete("RT_CorsaTariffa", "CorsaId=$CorsaId AND FermataPickup=$PkId AND FermataDropOff = $DoffId AND TipologiaBigliettoId = $TipologiaBigliettoId");
                }
                
                
                
				if(floatval($tariffa) > 0) {
					$qCorsa="INSERT INTO RT_CorsaTariffa
						(`TipologiaBigliettoId`, `TrattaId`, `CorsaId`, `FermataPickup`, `FermataDropOff`, `ListinoId`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
						VALUES ";
					$countCorsa = 0;
					$limite = count($ArrObjectTipoBiglietto)-1;
				
					foreach ($ArrObjectTipoBiglietto as $tipoBiglietto){
						$dc['TipologiaBigliettoId'] = $tipoBiglietto['TipologiaBigliettoId'];
						$dc['TrattaId'] = 0;
						$dc['CorsaId'] = $CorsaId;
						$dc['FermataPickup'] = $PkId;
						$dc['FermataDropOff'] = $DoffId;
						$dc['ListinoId'] = 1 ;
						if(!isset($variazioni[$tipoBiglietto['TipologiaBigliettoId']])){
							$variazioni[$tipoBiglietto['TipologiaBigliettoId']] = 0;
						}
						$dc['Tariffa'] = floatval($tariffa + ($variazioni[$tipoBiglietto['TipologiaBigliettoId']]*$tariffa)/100);
						
						$dc=$storico->operazioni_insert($dc,$user);
						
						$vc='';
						foreach($dc as $key => $val){
							if(strtolower($val)=='null') $vc.="NULL, ";
							elseif(strtolower($val)=='now()') $vc.="NOW(), ";
							else $vc.= "'".$db->escape($val)."', ";
						}
						
						$qCorsa .= " (". rtrim($vc, ', ') .")";
						if($countCorsa != $limite){
							$qCorsa .= ",";
						}
						$countCorsa++;
					}
echo "!!!!!!!!!!!!! ".$qCorsa;
					$r=$db->query($qCorsa);
	echo "********* ";				
					$q="delete from RT_CorsaTariffa where Tariffa = 0";
				
					$r = $db->query($q);   
				}

                           

                $count++;
            }
        }
    }
    
    echo("ok");
    
    exit();
}

function aumentoPrezzi() {
    global $user,$db;
    
    $storico=new StoricoOperazioni();
    $storico->conn=$db;
    
    $dt=new DT();
    $post2 = getRealPOSTTariffe();
    $CorsaId = $_POST['corsaId'];
    $aumento = $_POST['aumento'];
    
    $sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 order by TipologiaBigliettoPeso asc ";
    $ArrObjectTipoBiglietto = $db->fetch_array($sql);
    
    $sql = "Select * from RT_ListinoTipo";
    $ArrVariazioni = $db->fetch_array($sql);
    $variazioni = array();
    foreach($ArrVariazioni as $var){
        $variazioni[$var['BigliettoId']] = $var['Variazione'];
    }
    $variazioni[17] = 0;
    $aumento = floatval(str_replace(",", ".", $aumento));
    if($aumento >= 0){
        $string = '+'.$aumento;
    } else {
        $string = $aumento;
    }
    $sql = "UPDATE RT_CorsaTariffa SET Tariffa = (Tariffa $string) WHERE CorsaId=$CorsaId AND Tariffa > 0;";
    $r = $db->query($sql);
    
    echo("ok");
    
    exit();
}

function create_tariffe_servizio()
{
global $user,$db;
/*
$db= new Database();
$db->connect();*/
$storico=new StoricoOperazioni();
$storico->conn=$db;

$dt=new DT();
$post2 = getRealPOSTTariffe();
$data=$post2['Prezzi'];
// $data=$_POST['Prezzi'];



 foreach ($data as $chiave => $valore)
 { 
     $chiave=str_replace("'","",$chiave);
     $chiave=str_replace("\\","",$chiave);
     
     
     $arr_chiave=explode('_', $chiave);
     $BigliettoId=$arr_chiave[0];
     $CorsaId=$arr_chiave[1];
     
     
     $PkId=$arr_chiave[2];
     $DoffId=$arr_chiave[3];
     $Tariffa=$valore;
     $Tariffa=  str_replace(",",".", $Tariffa);
    
     $d1['Tariffa']=$Tariffa;
     $d1['CorsaId']=$CorsaId;
     $d1['FermataPickup']=$PkId;
     $d1['FermataDropOff']=$DoffId;
     $d1['TipologiaServizioId']=$BigliettoId;
     
    $db->delete("RT_CorsaTariffaServizio","CorsaId=$CorsaId and FermataPickup=$PkId and TipologiaServizioId=$BigliettoId and  FermataDropOff=$DoffId and OdcIdRef=$user->OdcId");
   
    
   
     
     if (!empty($Tariffa) and ($Tariffa) and ($Tariffa>0))
         { 
     $d1=$storico->operazioni_insert($d1,$user);
//     print_r($d1);
     $lastidA=$db->insert("RT_CorsaTariffaServizio", $d1);
      }
 }

    echo("ok".",".$lastidA);    

$db->close();
exit(); 

  
}

function update()
{
global $user,$tratta_wizard,$db;
$Id=$tratta_wizard->Id;

$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();

$step_corrente=$_POST['step_corrente'];
$data=$_POST['Tratta']; 
$data=$storico->operazioni_update($data,$user);
$result=$db->update("RT_Tratta", $data, "TrattaId=".$Id);

if ($result)
{
    echo("ok");
    $db->close();
    exit();
}
else
{
    echo("no");
    $db->close();
    exit();   
}    

    
  
}



  

if(is_object($user)) {
    
$db= new Database();
$db->connect();
$user->conn=$db;
$permessi=$user->get_permessi_modulo($ModuloId);
if (sizeof($permessi)>0)
{   
	
	
		if (!empty($_POST))
		{
			switch($_POST['action']) {
			    case "aumentoPrezzi":
			        $FunzioneId=2;
			        $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
			        if (sizeof($permesso))
			            aumentoPrezzi();
			            else
			                Errors::$ErrorePermessiModuloFunzione;
	                break;
				case "create":  
                     $FunzioneId=2;
                     $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                      if (sizeof($permesso))
                       create();   
                      else
                        Errors::$ErrorePermessiModuloFunzione;
                      
                      break;
                      
                case "create_tariffe":
                     $FunzioneId=2;
                     $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                      if (sizeof($permesso))
                       create_tariffe();   
                      else
                        Errors::$ErrorePermessiModuloFunzione;
                      
                      break;
                      
                case "create_tariffe_servizio":
                     $FunzioneId=2;
                     $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                      if (sizeof($permesso))
                       create_tariffe_servizio();   
                      else
                        Errors::$ErrorePermessiModuloFunzione;
                      
                      break;
                                  
                    case "update":   
                         $FunzioneId=4;
                         $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                          if (sizeof($permesso))
                           update();   
                          else
                            echo("no");
                          
                          break;

                    case "cambia_stato": 
			            $FunzioneId=4;
                         $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                if (sizeof($permesso))
                                   cambia_stato();    
                                else
                                    echo("no");
                                
					       // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
				        break;           
                }
              }
	} // end verifica permessi
	else {
           echo("no");
            
        }

}

// se l'utente non Ã¨ loggato
else {
header("Location: /logout.php");
}
?>