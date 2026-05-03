<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 

$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$auth_salt=Config::$auth_salt;

include_once($classespath_."/class.Ruolo.php");
$ModuloId=14;

function cambia_stato_ruolo()
{
    global $user,$mediazione_wizard,$db;
    $storico=new StoricoOperazioni();
$storico->conn=$db;
    
    
    $id=$_POST['idpost'];
    $data['Stato']=$_POST['Ruolo']['Stato'];
    $data=$storico->operazioni_update($data,$user);
    
   
    
    $result=$db->update("Ruolo", $data, "RuoloId=".$id);
 echo("Lo stato del ruolo è stato cambiato correttamente");
 exit();
}

function create()
{
    global $user,$auth_salt,$db;
    /*$db= new Database();
    $db->connect();*/
    $storico=new StoricoOperazioni();
    $storico->conn=$db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto
    $data=$_POST['Ruolo'];
    $data['OdcId']=$user->OdcId;
    $data=$storico->operazioni_insert($data,$user);

    $lastidA=$db->insert("Ruolo", $data);
    if ($lastidA!=false)
    {
        echo("ok".",".$lastidA);    
    }
    else
        echo("no");
    $db->close();
    exit();
}


function update($id)
{
    global $user,$auth_salt,$db;
    /*
    $db= new Database();
    $db->connect();*/
    $storico=new StoricoOperazioni();
    $storico->conn=$db;

    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

    $data=$_POST['Ruolo'];
    $data['OdcId']=$user->OdcId;
    $data=$storico->operazioni_update($data,$user);    
    $result=$db->update("Ruolo", $data, "RuoloId=".$id);
    if ($result)
        echo("ok");
    else
        echo("no");
    $db->close();
    exit();
}

function update_ruoli($id)
{
    global $user,$auth_salt,$db;
    /*
    $db= new Database();
    $db->connect();*/
    $storico=new StoricoOperazioni();
    $storico->conn=$db;

    $sql="SELECT * FROM RuoloPermesso WHERE RuoloId=$id";
    $row = $db->query_first($sql);
     if(!empty($row['RuoloId'])){
         $action="update";
     }else{
         $action="create";
     }
    $matrix_permessi=$_POST['Permesso'];   
    $chiavi=array_keys($matrix_permessi);
    foreach ( $chiavi as &$modulo) {              
       echo("modulo:".$modulo);        
       $chiavi_mod=array_keys($matrix_permessi[$modulo]);
       foreach ( $chiavi_mod as &$funzione) {  
            //echo(" funzione:".$funzione." permesso:".$matrix_permessi[$modulo][$funzione]);           
            $data['AppModuloId']=$modulo;
            $data['AppModuloFunzioneId']=$funzione;
            $data['AppPermessoId']=$matrix_permessi[$modulo][$funzione];
            if($action=="create"){
                $data['RuoloId']=$id;
                $data=$storico->operazioni_insert($data,$user);
                $result=$db->insert("RuoloPermesso", $data);
            }else{
                $data=$storico->operazioni_update($data,$user); 
                $result=$db->update("RuoloPermesso", $data, "RuoloId=".$id." and AppModuloId=".$modulo." and AppModuloFunzioneId=".$funzione);
            }
            print_r($data);
       }                   
    }    
    $db->close();
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
                
                case "cambia_stato_ruolo":
                               
                 $FunzioneId=5;
                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                        if (sizeof($permesso))
                           cambia_stato_ruolo();    
                        else
                            echo("no");

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
                case "update_ruoli":
                 $FunzioneId=4;
                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                        if (sizeof($permesso))
                           update_ruoli($_POST['idpost']);    
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