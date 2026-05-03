<?php

               


ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
$basepath=$_SERVER['DOCUMENT_ROOT'];
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$date_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($date_include);
$config=new Config();
$run=$config->load();
global $db;
$db=new Database();
$db->connect();

$CorsaId=1;

$sql="SELECT
RT_Fermata.FermataId,
RT_Fermata.ComuneId,
Comune.Comune,
Regione.idnazione
FROM
RT_Tratta
INNER JOIN RT_Fermata ON RT_Tratta.TrattaId = RT_Fermata.TrattaId
INNER JOIN Comune ON RT_Fermata.ComuneId = Comune.ComuneId
INNER JOIN Provincia ON Comune.provincia = Provincia.ProvinciaId
INNER JOIN Regione ON Provincia.RegioneId = Regione.RegioneId
WHERE
RT_Tratta.LineaId =2 AND
Regione.idnazione = 1";


$ArrObject = $db->fetch_array($sql);
$n_com=sizeof($ArrObject); 

$nc=0;
while ($nc<$n_com)
{
    $FermataId=$ArrObject[$nc]['FermataId'];
    $data['IsDropOff']=0;
    $db->update("RT_Fermata",$data,"FermataId=$FermataId");
    $nc++;
 }
?>
