<?php

include_once("main_include.php");
 ini_set('display_errors', 0);
 ini_set('error_reporting', E_ERROR);
 ini_set('max_execution_time', 36000); //300 seconds = 5 
$modulespath_ = $basepath."/protected/modules/";
$classespath_ = $basepath."/protected/classes/";
include_once ($classespath_ . "/class.Gestore.php");
include_once ($classespath_ . "/class.Sede.php");
include_once ($classespath_ . "/class.Fermata.php");
include_once ($classespath_ . "/class.Tratta.php");
include_once ($classespath_ . "/class.Prenotazione.php");
include_once ($classespath_ . "/class.Corsa.php");
include_once ($classespath_ . "/class.Linea.php");
include_once ($classespath_ . "/class.Percorso.php");
include_once ($classespath_ . "/class.Orario.php");
include_once ($classespath_ . "/class.Listino.php");
include_once ($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_."/Graph/class.LineaGraph.php");
include_once ($classespath_ . "/class.PrenotazioneMovimento.php");
include_once ($classespath_ . "/Graph/class.GrafoTratte.php");

$db = new Database ();
$db->connect ();

$CorsaAndata=1;


$sql="SELECT
RT_Fermata.FermataId,
RT_Fermata.TrattaId,
RT_Fermata.ComuneId,
RT_Corsa.CorsaId,
RT_Tratta.LineaId
FROM
RT_Fermata
INNER JOIN RT_Tratta ON RT_Fermata.TrattaId = RT_Tratta.TrattaId
INNER JOIN RT_Corsa ON RT_Tratta.LineaId = RT_Corsa.LineaId
WHERE
RT_Fermata.IsPickup = 1 AND
RT_Fermata.Stato = 1 GROUP BY
RT_Fermata.ComuneId,RT_Corsa.CorsaId order by RT_Corsa.CorsaId asc";
$fermate_pickup=$db->fetch_array($sql);

foreach($fermate_pickup as $fp)
{
          
          $ComuneAndataId=$fp['ComuneId'];
         
          //$trattaPartenza=$fp['TrattaId'];
          $CorsaAndata=$fp['CorsaId'];
          echo("Creazione grafo per corsa ".$CorsaAndata."\n");
          $lineaId=$fp['LineaId'];
          
          
          $sql="SELECT
RT_Fermata.FermataId,
RT_Fermata.TrattaId,
RT_Fermata.ComuneId,
RT_Corsa.CorsaId,
RT_Tratta.LineaId
FROM
RT_Fermata
INNER JOIN RT_Tratta ON RT_Fermata.TrattaId = RT_Tratta.TrattaId
INNER JOIN RT_Corsa ON RT_Tratta.LineaId = RT_Corsa.LineaId
WHERE
RT_Fermata.IsDropOff = 1 AND
RT_Fermata.Stato = 1 and CorsaId=$CorsaAndata GROUP BY
RT_Fermata.ComuneId";
     $fermate_drop=$db->fetch_array($sql);
    
         foreach($fermate_drop as $fd)
        {
              
          $ComuneRitornoId=$fd['ComuneId'];
         // $trattaArrivo=$fd['TrattaId'];
          $sql="select PercorsoBreveId from RT_PercorsoBreve where CorsaId=$CorsaAndata and ComunePickupId=$ComuneAndataId and ComuneDropOffId=$ComuneRitornoId";
          $r=$db->query_first($sql);
          if (empty($r['PercorsoBreveId']))
            {
              
          
           $grafo = new GrafoTratte($lineaId, $CorsaAndata, $db, $ComuneAndataId, $ComuneRitornoId);
        
             if(isset($grafo->flotta[0]))
             {
             $trattaPartenza = null;
            $trattaArrivo = null;
            $TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);    
            $Km=0;
            $kmTot=0;
           
            foreach ($TrattePercorse as $chiave => $valore)
             {
             
             $Km=$valore; 
             $kmTot+=$Km;
            }
           
             $data=null;    
             $data['ComunePickupId']=$ComuneAndataId;
             $data['ComuneDropOffId']=$ComuneRitornoId;
             $data['TrattaPickupId']=$trattaPartenza;
             $data['TrattaDropOffId']=$trattaArrivo;
             $data['KmPercorsi']=$kmTot;
              $data['DataIns']=date('Y-m-d H:i:s');
              $data['CorsaId']=$CorsaAndata;
             $lastid=$db->insert("RT_PercorsoBreve",$data);
             foreach ($TrattePercorse as $chiave => $valore)
             {
              $data=null;    
              $data['PercorsoBreveId']=$lastid;
              $data['TrattaId']=$chiave;
              $data['Km']=$valore;
              
              $db->insert("RT_PercorsoBreveTratte",$data);
            
            }
             }
             else
             {
                  $data=null;    
                    $data['ComunePickupId']=$ComuneAndataId;
                    $data['ComuneDropOffId']=$ComuneRitornoId;
                    $data['KmPercorsi']=0;
                     $data['CorsaId']=$CorsaAndata;
                      $data['DataIns']=date('Y-m-d H:i:s');
                    $lastid=$db->insert("RT_PercorsoBreve",$data);
             }
             
             
          }    
          
             
             
        } 
          
    
    
}

         
          
        
          
        
?>

