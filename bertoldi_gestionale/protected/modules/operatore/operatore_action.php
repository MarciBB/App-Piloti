<?php

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$auth_salt = Config::$auth_salt;

include_once($classespath_ . "/class.Operatore.php");
include_once($classespath_ . "/class.Ruolo.php");
$ModuloId = 13;

function merge_ruoli()
{
    global $user, $mediazione_wizard, $db;

    $ruoli = $_POST['ruoli'];
    $ruolo = new Ruolo();
    $ruolo->conn = $db;
    $contenuto_merge = $ruolo->getMergeRuoli($ruoli);
    echo ($contenuto_merge);
    exit();
}

function elimina_operatore()
{
    global $user, $mediazione_wizard, $db;
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    $MediazioneId = $mediazione_wizard->MediazioneId;
    $id = $_POST['idpost'];
    if ($id == "") $id = $_REQUEST['operatoreId'];
    $data['Cancella'] = "1";
    $data = $storico->operazioni_update($data, $user);

    $result = $db->update("Operatore", $data, "OperatoreId=" . $id);
    echo ("ok");
    exit();
}

function cambia_stato_operatore()
{
    global $user, $mediazione_wizard, $db, $dizionario;
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    $MediazioneId = $mediazione_wizard->MediazioneId;

    $id = $_POST['idpost'];
    $data['Stato'] = $_POST['StatoOperatore'];
    $data = $storico->operazioni_update($data, $user);

    $result = $db->update("Operatore", $data, "OperatoreId=" . $id);
    echo ($dizionario['operatore']['cambio_stato']);
    exit();
}

function create()
{
    global $user, $db;

    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Operatore'];

    $data = $storico->operazioni_insert($data, $user);
    if ($data['Password']) $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

    $lastidA = $db->insert("Operatore", $data);
    if ($lastidA != false) {
        echo ("ok" . "," . $lastidA);
    } else
        echo ("no");
    $db->close();
    exit();
}


function update($id) {
    global $user, $db;
    
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data = $_POST['Operatore'];

    $data = $storico->operazioni_update($data, $user);
    if ($data['Password']) {
        $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);  
    }
    $password_updated = $db->update("Operatore", $data, "OperatoreId=" . $id . " and OdcIdRef=$user->OdcId");
    if ($password_updated) {
        echo ("ok");
    } else {
        echo ("no");
        $db->close();
        exit();
    }
}

function modifica_password($id)
{
    global $user, $db, $dizionario;

    $storico = new StoricoOperazioni();
    $storico->conn = $db;
    $data = $_POST['Operatore'];

    $data = $storico->operazioni_update($data, $user);
    $data['Password'] = password_hash($data['Password'], PASSWORD_DEFAULT);

    if (password_verify($_POST['v_password'], $user->Password)) {
        $result = $db->update("Operatore", $data, "OperatoreId=" . $user->OperatoreId);
        if ($result) {
            echo ("ok");
        } else {
            echo ("no");
            $db->close();
            exit();
        }
    } else {
        echo 'password errata';
        exit();
    }
}

function  update_ruoli($id)
{
    global $user, $auth_salt, $db;

    $OperatoreRuolo = array();
    $storico = new StoricoOperazioni();
    $storico->conn = $db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $result = $db->delete("OperatoreRuolo", 'OperatoreId=' . $id);
    $check = $_POST['RuoliAttivi'];
    foreach ($check as &$value) {
        $OperatoreRuolo['RuoloId'] = $value;
        $OperatoreRuolo['OperatoreId'] = $id;
        $data = $OperatoreRuolo;
        $data = $storico->operazioni_insert($data, $user);
        $result = $db->insert("OperatoreRuolo", $data);
    }
    if ($result)
        echo ("ok");
    else
        echo ("no");

    $db->close();
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
                    if (sizeof($permesso))
                        create();
                    else
                        Errors::$ErrorePermessiModuloFunzione;
                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                    break;

                case "del":
                    $FunzioneId = 3;
                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
                    break;

                case "mod_password":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (true)
                        modifica_password($_POST['idpost']);
                    else
                        Errors::$ErrorePermessiModuloFunzione;

                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;

                case "update":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        update($_POST['idpost']);
                    else
                        Errors::$ErrorePermessiModuloFunzione;

                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;

                case "update_ruoli":
                    $FunzioneId = 4;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        update_ruoli($_POST['idpost']);
                    else
                        Errors::$ErrorePermessiModuloFunzione;

                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;

                case "usernameExist":
                    $FunzioneId = 5;
                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni                                         
                    if ($user->UsernameExist($_REQUEST['new_user'], $_POST['ope_id']))
                        echo ("trovato");
                    else
                        echo ("ok");
                    break;

                case "cambia_stato_operatore":

                    $FunzioneId = 5;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        cambia_stato_operatore();
                    else
                        echo ("no");

                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;

                case "elimina_operatore":
                    $FunzioneId = 5;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        elimina_operatore();
                    else
                        echo ("no");

                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;


                case "merge_ruoli":
                    $FunzioneId = 5;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        merge_ruoli();
                    else
                        echo ("no");
                    // verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
                    break;
            }
        } else {
            switch ($_REQUEST['do']) {
                case "elimina_operatore":
                    $FunzioneId = 5;
                    $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
                    if (sizeof($permesso))
                        elimina_operatore();
                    else
                        echo ("no");

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
