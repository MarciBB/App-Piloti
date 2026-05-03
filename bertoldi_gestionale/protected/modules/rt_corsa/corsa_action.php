<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.Sede.php");


$ModuloId=28;



function create()
{
    global $user;
    $db = new Database();
    $db->connect();
    $storico = new StoricoOperazioni();
    $storico->conn = $db;
    $dt = new DT();

    // Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

    $data = $_POST['Corsa'];
    $data['AttivaDal'] = $dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
    $data['AttivaAl'] = $dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
    $data['VendibileDal'] = $dt->format($data['VendibileDal'], "d/m/Y", "Y-m-d");
    $data['VendibileAl'] = $dt->format($data['VendibileAl'], "d/m/Y", "Y-m-d");

    /*$tipologiaBusDefaultId = $data['TipologiaBusDefaultId'];
    if (!is_array($data['TipologiaBusDefaultId'])) {
        $tipologiaBusDefaultId = [$data['TipologiaBusDefaultId']];
    } else {
		$tipologiaBusDefaultId = $data['TipologiaBusDefaultId'];
	}*/
	
	$flottaDefaultId = $data['FlottaDefaultId'];
    if (!is_array($data['FlottaDefaultId'])) {
        $flottaDefaultId = [$data['FlottaDefaultId']];
    } else {
		$flottaDefaultId = $data['FlottaDefaultId'];
	}
	
    if (!isset($data['IncludiFeriale']) || $data['IncludiFeriale'] == '')
        $data['IncludiFeriale'] = 0;
    else
        $data['IncludiFeriale'] = 1;

    if (!isset($data['IncludiFestivo']) || $data['IncludiFestivo'] == '')
        $data['IncludiFestivo'] = 0;
    else
        $data['IncludiFestivo'] = 1;

    if (!isset($data['IncludiPrefestivo']) || $data['IncludiPrefestivo'] == '')
        $data['IncludiPrefestivo'] = 0;
    else
        $data['IncludiPrefestivo'] = 1;

    $data = $storico->operazioni_insert($data, $user);

    foreach ($flottaDefaultId as $busId) {
		$data['FlottaDefaultId'] = $busId;
		
		//recupero tipologia bus dalla flotta
		$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $busId";
		$flotta = $db->query_first($sql);
        $data['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
		
        $lastidA = $db->insert("RT_Corsa", $data);
        $corsaId = $lastidA;
		
        if ($lastidA != false) {

            $datas1 = $_POST['Settimana'];
            $datas['CorsaId'] = $lastidA;
            $datas = $storico->operazioni_insert($datas, $user);
            $gg_set = 1;
            while ($gg_set < 8) {
                if (isset($datas1['Settimana' . $gg_set])) {
                    $datas['SettimanaId'] = $gg_set;
                    $lastidA = $db->insert("RT_CorsaSettimana", $datas);
                }

                $gg_set++;
            }

            // Inserisco le tariffe
            $sql = "Select * from RT_LineaTariffa where LineaId = " . $data['LineaId'];
            $ArrTariffe = $db->fetch_array($sql);

            $sql = "Select * from RT_TipologiaBiglietto where Stato = 1 and Cancella = 0 AND OccupaPosto = 1 order by TipologiaBigliettoPeso asc ";
            $ArrObjectTipoBiglietto = $db->fetch_array($sql);

            $sql = "Select * from RT_ListinoTipo";
            $ArrVariazioni = $db->fetch_array($sql);
            $variazioni = array();
            foreach ($ArrVariazioni as $var) {
                $variazioni[$var['BigliettoId']] = $var['Variazione'];
            }
            $variazioni[17] = 0;
            $qCorsa = "INSERT INTO RT_CorsaTariffa
                    (`TipologiaBigliettoId`, `TrattaId`, `CorsaId`, `FermataPickup`, `FermataDropOff`, `ListinoId`, `Tariffa`, `OpeIns`, `SedeIns`, `DataIns`, `IpIns`, `OdcIdRef`, `GestoreIdRef`, `Cancella`, `Stato`)
                    VALUES ";
            $countCorsa = 0;
            $limite = (count($ArrTariffe) * count($ArrObjectTipoBiglietto)) - 1;
            foreach ($ArrTariffe as $dataTemp) {
                foreach ($ArrObjectTipoBiglietto as $tipoBiglietto) {
                    $dc['TipologiaBigliettoId'] = $tipoBiglietto['TipologiaBigliettoId'];
                    $dc['TrattaId'] = 0;
                    $dc['CorsaId'] = $corsaId;
                    $dc['FermataPickup'] = $dataTemp['FermataPickup'];
                    $dc['FermataDropOff'] = $dataTemp['FermataDropOff'];
                    $dc['ListinoId'] = 1;
                    $dc['Tariffa'] = floatval($dataTemp['Tariffa'] + ($variazioni[$tipoBiglietto['TipologiaBigliettoId']] * $dataTemp['Tariffa']) / 100);

                    $dc = $storico->operazioni_insert($dc, $user);

                    $vc = '';
                    foreach ($dc as $key => $val) {
                        if (strtolower($val) == 'null') $vc .= "NULL, ";
                        elseif (strtolower($val) == 'now()') $vc .= "NOW(), ";
                        else $vc .= "'" . $db->escape($val) . "', ";
                    }

                    $qCorsa .= " (" . rtrim($vc, ', ') . ")";
                    if ($countCorsa != $limite) {
                        $qCorsa .= ",";
                    }
                    $countCorsa++;
                }
            }
            $db->query($qCorsa);

        }
    }

    if ($lastidA != false) {
        // Fine inserimento corse
        echo ("ok" . "," . $lastidA);
    } else {
        echo ("no");
    }
    exit();
}



function update($idUpdate) {
	global $user;
	$dt=new DT();
	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	//recupero i dati per l'aggiornamento di massa
	$tipoCreazione = $_POST['tipoCreazione'];
	$corsaIdSelect = $_POST['CorsaIdSelect'];

	//recupero la corsa da modificare
	$sql = "SELECT c.*, l.PercorsoId FROM RT_Corsa c
				LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
				WHERE CorsaId = $idUpdate";
	$corsa = $db->query_first($sql);

	$corsaArray = array();
	if ($tipoCreazione == 1) {
		$corsaArray[] = $idUpdate;
	} else if($tipoCreazione == 2){
		$sql = "SELECT CorsaId FROM RT_Corsa WHERE LineaId = ".$corsa['LineaId']." AND Stato = 1 AND Cancella = 0";
		$corsaArrayTemp = $db->fetch_array($sql);
		foreach ($corsaArrayTemp as $corsaTemp) {
			$corsaArray[] = $corsaTemp['CorsaId'];
		}
	} else if($tipoCreazione == 3){
		$sql = "SELECT c.CorsaId FROM RT_Corsa c
					LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
					WHERE l.PercorsoId = ".$corsa['PercorsoId']."
						AND c.Stato = 1 AND c.Cancella = 0 
						AND l.Stato = 1 AND l.Cancella = 0";
		$corsaArrayTemp = $db->fetch_array($sql);
		foreach ($corsaArrayTemp as $corsaTemp) {
			$corsaArray[] = $corsaTemp['CorsaId'];
		}
	} else if($tipoCreazione == 4){
		$corsaArray = $corsaIdSelect;
	}

	$postDataCorsa = $_POST['Corsa'];

	foreach ($corsaArray as $corsaId) {
		$id = $corsaId;
	// prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
		$data = $postDataCorsa;
		$data['AttivaDal']=$dt->format($data['AttivaDal'], "d/m/Y", "Y-m-d");
		$data['AttivaAl']=$dt->format($data['AttivaAl'], "d/m/Y", "Y-m-d");
		$data['VendibileDal']=$dt->format($data['VendibileDal'], "d/m/Y", "Y-m-d");
		$data['VendibileAl']=$dt->format($data['VendibileAl'], "d/m/Y", "Y-m-d");
		if (!isset($data['IncludiFeriale']))
			$data['IncludiFeriale'] = 0;
		else
			$data['IncludiFeriale'] = 1;
		
		if (!isset($data['IncludiFestivo']))
			$data['IncludiFestivo'] = 0;
		else
			$data['IncludiFestivo'] = 1;
		
		if (!isset($data['IncludiPrefestivo']))
			$data['IncludiPrefestivo'] = 0;
		else
			$data['IncludiPrefestivo'] = 1;

		if($idUpdate != $id) {
			$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = $id";
			$corsaCheck = $db->query_first($sql);
			$data['CorsaId'] = $corsaCheck['CorsaId'];
			$data['CorsaNome'] = $corsaCheck['CorsaNome'];
			$data['FlottaDefaultId'] = $corsaCheck['FlottaDefaultId'];
			$data['CorsaPeso'] = $corsaCheck['CorsaPeso'];
			$data['OrarioPartenza'] = $corsaCheck['OrarioPartenza'];
			$data['OrarioArrivo'] = $corsaCheck['OrarioArrivo'];
		}
		
		//recupero tipologia bus dalla flotta
		$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = ".$data['FlottaDefaultId'];
		$flotta = $db->query_first($sql);
		$data['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
		
		$data=$storico->operazioni_update($data,$user);

		$result=$db->update("RT_Corsa", $data, "CorsaId=".$id." and OdcIdRef=$user->OdcId");

		if ($result) {
			$datas1=$_POST['Settimana'];
			$datas['CorsaId']=$id;
			$datas=$storico->operazioni_insert($datas,$user);
			$gg_set=1;
			$lastidA=$db->delete("RT_CorsaSettimana","CorsaId=$id and OdcIdRef=$user->OdcId");
			while ($gg_set<8) {
				if (isset($datas1['Settimana'.$gg_set]))
				{
					$datas['SettimanaId']=$gg_set;
					
					$lastidA=$db->insert("RT_CorsaSettimana", $datas);
				}

				$gg_set++;
			}
		}
	}
	
	echo("ok");
	exit();

}

function aggiungiValiditaBiglietto($corsaId, $validitaBigliettoId = null)
{ 
	global $user;
	$dt = new DT();
	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;


	//recupero i dati per l'aggiornamento di massa
	$tipoCreazione = $_POST['tipoCreazione'];
	$corsaIdSelect = $_POST['CorsaIdSelect'];

	//recupero la corsa da modificare
	$sql = "SELECT c.*, l.PercorsoId FROM RT_Corsa c
				LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
				WHERE CorsaId = $corsaId";
	$corsa = $db->query_first($sql);

	$corsaArray = array();
	$validitaArray = array();
	if ($tipoCreazione == 1) {
		$corsaArray[] = $corsaId;
	} else if($tipoCreazione == 2){
		$sql = "SELECT CorsaId FROM RT_Corsa WHERE LineaId = ".$corsa['LineaId']." AND Stato = 1 AND Cancella = 0";
		$corsaArrayTemp = $db->fetch_array($sql);
		foreach ($corsaArrayTemp as $corsaTemp) {
			$corsaArray[] = $corsaTemp['CorsaId'];
		}
	} else if($tipoCreazione == 3){
		$sql = "SELECT c.CorsaId FROM RT_Corsa c
					LEFT JOIN RT_Linea l ON l.LineaId = c.LineaId
					WHERE l.PercorsoId = ".$corsa['PercorsoId']."
						AND c.Stato = 1 AND c.Cancella = 0 
						AND l.Stato = 1 AND l.Cancella = 0";
		$corsaArrayTemp = $db->fetch_array($sql);
		foreach ($corsaArrayTemp as $corsaTemp) {
			$corsaArray[] = $corsaTemp['CorsaId'];
		}
	} else if($tipoCreazione == 4){
		$corsaArray = $corsaIdSelect;
	}

	// Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
	$dataPost = $_POST['ValiditaBiglietto'];

	//inizializzo i data da aggiornare
	$data = $dataPost;
	$data['CorsaId'] = $corsaId;
	$data['Dal'] = $dt->format($data['Dal'], "d/m/Y", "Y-m-d");
	$data['Al'] = $dt->format($data['Al'], "d/m/Y", "Y-m-d");

	$lastid = 0;
	$lastIdArray = array();
	if ($validitaBigliettoId == null) {
		$data = $storico->operazioni_insert($data, $user);
		$lastid = $db->insert("RT_ValiditaBiglietto", $data);
		$lastIdArray[] = $lastid;
	} else {
		foreach ($corsaArray as $corsaIdTemp) {
			$id = $corsaIdTemp;
			$validitaArray = array();
			if ($tipoCreazione == 1) {
				$validitaArray[] = $validitaBigliettoId;
			} else {
				$sql = "SELECT * FROM RT_ValiditaBiglietto WHERE CorsaId = $corsaIdTemp";
				$validitaArrayTemp = $db->fetch_array($sql);
				if(count($validitaArrayTemp) > 0) {
					foreach ($validitaArrayTemp as $validitaTemp) {
						$validitaArray[] = $validitaTemp['ValiditaBigliettoId'];
					}
				}
			}

			$data['CorsaId'] = $corsaIdTemp;
			if(count($validitaArray) > 0) {
				foreach ($validitaArray as $validitaId) {
					$data['ValiditaBigliettoId'] = $validitaId;
					$data['Stato'] = (isset($data['Stato'])) ? $data['Stato'] : 0;
					$data = $storico->operazioni_update($data, $user);
					$result = $db->update("RT_ValiditaBiglietto", $data, "ValiditaBigliettoId=" . $validitaId . " and OdcIdRef=$user->OdcId");
					if ($result) {
						$result = $db->delete("RT_ValiditaBigliettoDettaglio", "ValiditaBigliettoId=$validitaId and OdcIdRef=$user->OdcId");
						$lastid = $validitaId;
						$lastIdArray[] = $lastid;
					}
				}
			} else { 
				unset($data['ValiditaBigliettoId']);
				$data['Stato'] = (isset($data['Stato'])) ? $data['Stato'] : 0;
				$data = $storico->operazioni_insert($data, $user);
				$lastid = $db->insert("RT_ValiditaBiglietto", $data);
				$lastIdArray[] = $lastid;
			}

		}
	}

	if (count($lastIdArray) > 0) {
		$datas1 = $_POST['ValiditaBigliettoTipo'];
		foreach ($lastIdArray as $lastid) {
			$datas = array();
			$datas['ValiditaBigliettoId'] = $lastid;
			$datas = $storico->operazioni_insert($datas, $user);
		
			$datas['ValiditaBigliettoId'] = $lastid;
			$datas = $storico->operazioni_insert($datas, $user);

			foreach ($datas1 as $tipo) {
				$datas['BigliettoId'] = $tipo;
				$db->insert("RT_ValiditaBigliettoDettaglio", $datas);
			}
		}
	}

	echo("ok" . "," . $lastid);
	exit();
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

				case "create":
					 
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						create();
					else
						Errors::$ErrorePermessiModuloFunzione;

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni						
						
					break;

				case "del":
					$FunzioneId=3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

				case "update":
					 
				 $FunzioneId=4;
				 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				 if (sizeof($permesso))
				 	update($_POST['idpost']);
				 else
				 	Errors::$ErrorePermessiModuloFunzione;

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				 break;

				case "AggiungiValiditaBiglietto":
					 
				 $FunzioneId=4;
				 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				 if (sizeof($permesso))
				 	aggiungiValiditaBiglietto($_POST['corsaId']);
				 else
				 	Errors::$ErrorePermessiModuloFunzione;

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				 break;
				 
				 case "ModificaValiditaBiglietto":
				 
				 	$FunzioneId=4;
				 	$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				 	if (sizeof($permesso))
				 		aggiungiValiditaBiglietto($_POST['corsaId'],$_POST['validitaBigliettoId']);
				 	else
				 		Errors::$ErrorePermessiModuloFunzione;
				 
				 	// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				 	break;
			}
		}
	} // end verifica permessi
	else {
		Errors::$ErrorePermessiModulo;

	}

}

// se l'utente non è loggato
else {
	header("Location: /logout.php");
}
?>