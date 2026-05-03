<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$config = new Config();
$run = $config->load();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

include_once($classespath_ . "/class.Sede.php");

$ModuloId = 28;

function create()
{
    global $user;

    $db = new Database();
    $db->connect();

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Linea'];
    $data['GestoreGruppoId'] = 2;
    $data = $storico->operazioni_insert($data, $user);

    $lastidA = $db->insert("RT_Linea", $data);

    if ($lastidA != false) {
        echo "ok," . $lastidA;
    } else {
        echo "no";
    }
    exit();
}

function update($id)
{
    global $user;

    $db = new Database();
    $db->connect();

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // Prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Linea'];
    $data['GestoreGruppoId'] = 2;
    $data = $storico->operazioni_update($data, $user);

    $result = $db->update("RT_Linea", $data, "LineaId=" . $id . " AND OdcIdRef=$user->OdcId");

    if ($result) {
        echo "ok";
    } else {
        echo "no";
    }
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
    // Se l'utente non è loggato
    header("Location: /logout.php");
}
?>
