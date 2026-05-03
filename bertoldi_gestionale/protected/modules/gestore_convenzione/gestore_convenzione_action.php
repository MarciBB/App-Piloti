<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$config = new Config();
$run = $config->load();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

include_once($classespath_ . "/class.Sede.php");

$ModuloId = 16;

function create()
{
    global $user;

    $db = new Database();
    $db->connect();

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    $dt = new DT();

    // Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Convenzione'];
    $data['ValidaDal'] = $dt->format($data['ValidaDal'], "d/m/Y", "Y-m-d");
    $data['ValidaAl'] = $dt->format($data['ValidaAl'], "d/m/Y", "Y-m-d");

	$lineaArray = array();
	if(is_array($_POST['LineaId'])) {
		$lineaArray = $_POST['LineaId'];
	} else {
		$lineaArray[] = $_POST['LineaId'];
	}

    $data = $storico->operazioni_insert($data, $user);
	
	foreach($lineaArray as $linea){
		if(isset($linea)) {
			$data['LineaId'] = $linea;
			$sql = "UPDATE GestoreConvenzione SET Stato = 0, Cancella = 1 WHERE LineaId = $linea AND GestoreId = ".$data['GestoreId'];
			$db->query($sql);
			$lastidA = $db->insert("GestoreConvenzione", $data);
		}
	}

    
    if ($lastidA !== false) {
        echo "ok," . $lastidA;
    } else {
        echo "no";
    }
    exit();
}

function update($id)
{
    global $user;

    $dt = new DT();
    $db = new Database();
    $db->connect();

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Convenzione'];
    $data['ValidaDal'] = $dt->format($data['ValidaDal'], "d/m/Y", "Y-m-d");
    $data['ValidaAl'] = $dt->format($data['ValidaAl'], "d/m/Y", "Y-m-d");
    $data = $storico->operazioni_update($data, $user);

    $result = $db->update("GestoreConvenzione", $data, "GestoreConvenzioneId=" . $id);

    echo $result ? "ok" : "no";
    exit();
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

                case "del":
                    $FunzioneId = 3;
                    // Verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
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
            }
        }
    } else {
        Errors::$ErrorePermessiModulo;
    }
} else {
    header("Location: /logout.php");
}
?>
