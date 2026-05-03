<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Mediatore.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");

$ModuloId = 16;

global $gestore_wizard;
$gestore_wizard = null;

if (isset($_SESSION['GESTORE_WIZARD'])) {
    $gestore_wizard = unserialize($_SESSION['GESTORE_WIZARD']);
}

function cambia_stato_gestore()
{
    global $user, $gestore_wizard, $db, $dizionario;
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    $GestoreId = $gestore_wizard->GestoreId;

    $data['Stato'] = $_POST['Stato'];
    $data = $storico->operazioni_update($data, $user);

    $res = $db->update('Gestore', $data, "GestoreId=" . $GestoreId);
    echo($dizionario['gestore']['stato_cambiato']);
    exit();
}

function create()
{
    global $user, $db;

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    $dt = new DT();
    $data1 = $_POST['Gestore'];
    $data1['GestorePadre'] = $_POST['GestorePadreId'];
    $nomegestore = $data1['RagioneSociale'];
    $gestore_padre = $_POST['GestorePadreId'];

    $SQL = "CALL Gestore_proc ('I', 0, $user->OdcId, 0, '$nomegestore')";
    if ($gestore_padre > 0) {
        $SQL = "CALL Gestore_proc ('I', $gestore_padre, $user->OdcId, 0, '$nomegestore')";
    }

    //echo($SQL);

    $mysqli = new mysqli(Config::$dbserver, Config::$dbuser, Config::$dbpass, Config::$dbname);

    $query = $mysqli->multi_query($SQL);

    if ($query) {
        $result = $mysqli->use_result();
        $data = $result->fetch_assoc();
        $result->free();

        while ($mysqli->next_result()) {
            $result = $mysqli->use_result();

            if ($result instanceof mysqli_result) {
                while ($myrow = $result->fetch_array(MYSQLI_ASSOC)) {
                    if ($myrow['LAST_INSERT_ID()'] > 0) {
                        $GestoreId = $myrow['LAST_INSERT_ID()'];

                        $data1['RagioneSociale'] = ucfirst($data1['RagioneSociale']);
                        $data1['PartitaIva'] = strtoupper($data1['PartitaIva']);
                        $data1['CodiceFiscale'] = strtoupper($data1['CodiceFiscale']);
                        $data1['CodiceAzienda'] = generaCodiceAzienda();

                        $data1 = $storico->operazioni_insert($data1, $user);

                        $result2 = $db->update("Gestore", $data1, "GestoreId=" . $GestoreId);

                        if ($result2) {
                            $gestore_wizard = new Gestore($GestoreId);
                            $gestore_wizard->GestoreDatiGenerali = $data1;
                            $_SESSION['GESTORE_WIZARD'] = serialize($gestore_wizard);

                            echo("ok");
                            $db->close();
                            exit();
                        } else {
                            echo("no");
                            $db->close();
                            exit();
                        }
                    }
                }
                $result->free();
            }
        }
    }

    echo("no");
    $db->close();
    exit();
}

function update()
{
    global $user, $gestore_wizard, $db;
    $GestoreId = $gestore_wizard->GestoreId;

    $storico = new StoricoOperazioni();
    $storico->conn = $db;
    $dt = new DT();

    $step_corrente = $_POST['step_corrente'];
    $data = $_POST['Gestore'];
    $data['GestorePadre'] = $_POST['GestorePadreId'];

    $data['RagioneSociale'] = ucfirst($data['RagioneSociale']);
    $data['PartitaIva'] = strtoupper($data['PartitaIva']);
    $data['CodiceFiscale'] = strtoupper($data['CodiceFiscale']);
    $data = $storico->operazioni_update($data, $user);
    $result = $db->update("Gestore", $data, "GestoreId=" . $GestoreId);

    $gestore_padre = $_POST['GestorePadreId'];
    if ($gestore_padre > 0) {
        $SQL = "CALL Gestore_proc ('U', $gestore_padre, $user->OdcId, $gestore_wizard->GestoreId, '')";
    }

    $mysqli = new mysqli(Config::$dbserver, Config::$dbuser, Config::$dbpass, Config::$dbname);
    $query = $mysqli->multi_query($SQL);

    if ($result) {
        echo("ok");
        $db->close();
        exit();
    } else {
        echo("no");
        $db->close();
        exit();
    }
}

function generaCodiceAzienda($length = 6) {
    $caratteri = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codice = '';
    $maxIndex = strlen($caratteri) - 1;

    for ($i = 0; $i < $length; $i++) {
        $codice .= $caratteri[mt_rand(0, $maxIndex)];
    }

    return $codice;
}

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

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

                case "update":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso)) {
                        update();
                    } else {
                        echo("no");
                    }
                    break;

                case "cambia_stato_gestore":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso)) {
                        cambia_stato_gestore();
                    } else {
                        echo("no");
                    }
                    break;
            }
        }
    } else {
        echo("no");
    }
} else {
    header("Location: /logout.php");
}
?>
