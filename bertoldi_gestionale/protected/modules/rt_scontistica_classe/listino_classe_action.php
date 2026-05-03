<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Scontistica.php");
include_once($classespath_."class.Comune.php");


$ModuloId=35;

function aggiungiScontisticaCorsa()
{
	global $user;
	$dt=new DT();
	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn=$db;
$ListinoId=$_POST['ListinoId'];
$ScontisticaCorsaId=null;
if (isset($_POST['ScontisticaCorsaId']))
  $ScontisticaCorsaId=$_POST['ScontisticaCorsaId'];  
	// prelevo i dati del form ed aggiorno tutte le proprieta'� dell'oggetto

	$data=$_POST['RT_ScontisticaCorsa'];
	$data['ListinoId']=$ListinoId;
	$data['Dal']=$dt->format($data['Dal'], "d/m/Y", "Y-m-d");
	$data['Al']=$dt->format($data['Al'], "d/m/Y", "Y-m-d");
        
        $dal=$data['Dal'];
        $al=$data['Al'];
        
        $datas_ver=$_POST['ValiditaPromozioneCorsa'];
$errore=false;
		foreach ($datas_ver as $corsaId) {
			
		$sql="SELECT
	RT_ScontisticaCorsaDettaglio.CorsaId,
	RT_ScontisticaCorsa.Dal,
	RT_ScontisticaCorsa.Al,
	RT_Corsa.CorsaNome,
	RT_ScontisticaCorsa.ListinoId,
	RT_Scontistica.ListinoNome,
	RT_ScontisticaCorsa.ScontisticaCorsaId
FROM
	RT_ScontisticaCorsaDettaglio
INNER JOIN RT_ScontisticaCorsa ON RT_ScontisticaCorsaDettaglio.ScontisticaCorsaId = RT_ScontisticaCorsa.ScontisticaCorsaId
INNER JOIN RT_Corsa ON RT_ScontisticaCorsaDettaglio.CorsaId = RT_Corsa.CorsaId
INNER JOIN RT_Scontistica ON RT_ScontisticaCorsa.ListinoId = RT_Scontistica.ListinoId
WHERE
RT_Corsa.RitornoAperto=0 and
	RT_ScontisticaCorsa.Stato = 1
AND RT_ScontisticaCorsa.Cancella = 0
AND RT_ScontisticaCorsaDettaglio.Cancella = 0
AND RT_ScontisticaCorsaDettaglio.CorsaId = $corsaId
AND (
	(RT_ScontisticaCorsa.Dal>='$dal' AND RT_ScontisticaCorsa.Dal<='$al')
	OR
(RT_ScontisticaCorsa.Al>='$dal' AND RT_ScontisticaCorsa.Al<='$al'))";
                
                 
                $r=$db->query_first($sql);
                if (!empty($r['CorsaId']))
                {
                    
                    $listId=$r['ScontisticaCorsaId'];
                  
                    if ($ScontisticaCorsaId>0)
                    {
                          if ($listId<>$ScontisticaCorsaId)
                         {
                        $errore=true;
                        $errore_testo="Intervallo di date già presente per la corsa ".$r['CorsaNome']." nel listino ".$r['ListinoNome']."\n";
                         } 
                    }
                    else
                    {
                        $errore=true;
                        $errore_testo="Intervallo di date già presente per la corsa ".$r['CorsaNome']." nel listino ".$r['ListinoNome']."\n";
                  
                    }
                    
                  
                    
                }
                   
                    
               // echo($sql);
                    
		}
                $errore=false;
                if ($errore)
                  echo($errore_testo.",no");
                else 
                {
                    $lastid = 0;
	if($ScontisticaCorsaId == null){
		$data=$storico->operazioni_insert($data,$user);
		$lastid = $db->insert("RT_ScontisticaCorsa", $data);
	
	}else{
		$data['ScontisticaCorsaId']=$ScontisticaCorsaId;
		$data['Stato']=(isset($data['Stato']))? $data['Stato'] : 0;
		$data=$storico->operazioni_update($data,$user);
		$result=$db->update("RT_ScontisticaCorsa", $data, "ScontisticaCorsaId=".$ScontisticaCorsaId." and OdcIdRef=$user->OdcId");
		if($result){
			$result=$db->delete("RT_ScontisticaCorsaDettaglio","ScontisticaCorsaId=$ScontisticaCorsaId and OdcIdRef=$user->OdcId");
			$lastid = $ScontisticaCorsaId;
		}
	}
	
	if ($lastid > 0)
	{
		$datas1=$_POST['ValiditaPromozioneCorsa'];
		
		$datas['ScontisticaCorsaId']=$lastid;
		$datas=$storico->operazioni_insert($datas,$user);
	
		foreach ($datas1 as $tipo) {
			$datas['CorsaId'] = $tipo;
			$db->insert("RT_ScontisticaCorsaDettaglio", $datas);
		}
	
		echo("ok");
	}
	else
		echo("no");
                }
	
	
	$r=disattivaScontiSovrapprezzi();

	exit();

}

function disattivaScontiSovrapprezzi()
{
global $user,$db;
$db=new Database();
$db->connect();

$currentDate = date('Y-m-d H:i:s');


$sql = "update RT_ScontisticaCorsa as t INNER JOIN
(
SELECT
RT_ScontisticaCorsa.ScontisticaCorsaId
FROM
RT_ScontisticaCorsa
WHERE
RT_ScontisticaCorsa.Al <= NOW()
ORDER BY
RT_ScontisticaCorsa.Dal ASC) as t1
on t.ScontisticaCorsaId=t1.ScontisticaCorsaId
set t.Stato=0";
$db->query($sql);

$sql="update RT_Scontistica set NumeroCorseAttive=0";
$r=$db->query($sql);

$sql="update RT_Scontistica as t INNER JOIN (SELECT
RT_ScontisticaCorsa.ListinoId,
Count(RT_ScontisticaCorsa.ScontisticaCorsaId) AS N,
RT_Scontistica.ListinoNome
FROM
RT_ScontisticaCorsa
INNER JOIN RT_Scontistica ON RT_ScontisticaCorsa.ListinoId = RT_Scontistica.ListinoId
WHERE
RT_ScontisticaCorsa.Stato = 1
GROUP BY
RT_ScontisticaCorsa.ListinoId) as t1
on t.ListinoId=t1.ListinoId
set t.NumeroCorseAttive=t1.N";
$r=$db->query($sql);


return true;
}



function create()
{
global $user;
$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();


    
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

$data=$_POST['Listino'];
$data['AttivaDal']=$dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
$data['AttivaAl']=$dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
$data=$storico->operazioni_insert($data,$user);


$lastidA=$db->insert("RT_Scontistica", $data);
if ($lastidA!=false)
{
 
echo("ok".",".$lastidA);    
}
else
echo("no");
exit();   
  
}


function update($id)
{
global $user;

$db= new Database();
$db->connect();
$storico=new StoricoOperazioni();
$storico->conn=$db;
$dt=new DT();

    
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

$data=$_POST['Listino'];
$data['AttivaDal']=$dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
$data['AttivaAl']=$dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
$data=$storico->operazioni_update($data,$user);


$result=$db->update("RT_Scontistica", $data, "ListinoId=".$id." and OdcIdRef=$user->OdcId");


if ($result)
echo("ok");
else
echo("no");
exit();   
  
}

function getElencoCorseByLineaId($lineaId) {
    // Connessione al database
    $db = new Database();
    $db->connect();

    // Costruzione della query
    $sql = "SELECT CorsaId, CorsaNome 
            FROM RT_Corsa 
            WHERE LineaId = $lineaId 
              AND Stato = 1 
              AND Cancella = 0 
            ORDER BY CorsaPeso ASC";

    // Esecuzione della query e recupero dei risultati
    $elenco_corse = $db->fetch_array($sql);

	// Imposta l'intestazione della risposta come JSON
    header('Content-Type: application/json');

    // Restituisce i dati come JSON
    echo json_encode($elenco_corse);

    // Opzionalmente interrompe l'esecuzione ulteriore
    exit;
}



if (is_object($user)) {
    $db = new Database();
    $db->connect();
    $user->conn = $db;

    $permessi = $user->get_permessi_modulo($ModuloId);

    if (sizeof($permessi) > 0) {
        if (!empty($_POST)) {
            switch ($_POST['action']) {
                case "create":
                    $FunzioneId = 2;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                    if (sizeof($permesso)) {
                        create();
                    } else {
                        Errors::$ErrorePermessiModuloFunzione;
                    }
                    break;

                case "AggiungiPromozioneCorsa":
                    $FunzioneId = 2;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                    if (sizeof($permesso)) {
                        aggiungiScontisticaCorsa();
                    } else {
                        Errors::$ErrorePermessiModuloFunzione;
                    }
                    break;

                case "del":
                    $FunzioneId = 3;
                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
                    break;

                case "update":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                    if (sizeof($permesso)) {
                        update($_POST['idpost']);
                    } else {
                        Errors::$ErrorePermessiModuloFunzione;
                    }
                    break;
					
				case "getCorse":
                    $FunzioneId = 2;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

                    if (sizeof($permesso)) {
                        getElencoCorseByLineaId($_POST['lineaId']);
                    } else {
                        Errors::$ErrorePermessiModuloFunzione;
                    }
                    break;

                default:
                    // Azione non riconosciuta
                    break;
            }
        }
    } else {
        Errors::$ErrorePermessiModulo;
    }
} else {
    // Se l'utente non è loggato
    header("Location: /logout.php");
}

?>