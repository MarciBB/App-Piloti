<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.AnagraficaParte.php");
include_once($classespath_."class.AnagraficaTipo.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");

$NazioneId_=$_REQUEST['id_nazione'];
global $dizionario;
$db=New Database();
$db->connect();
print("<option value=\"\">- ".$dizionario['generale']['seleziona']." -</option>");

if ($NazioneId_!="")
{
    

$sql = "SELECT RegioneId,Regione From Regione where NazioneId=$NazioneId_";
$ArrObject = $db->fetch_array($sql);
 $ArrObjectSize=count($ArrObject);

 $i=0;
                        while ($i< $ArrObjectSize)
                        {
                            $value=$ArrObject[$i]['RegioneId'];
                            $label=$ArrObject[$i]['Regione'];
                           
                            ?>  
                            <option value="<?=$value?>"><?=$label?></option>
                            <?

                            $i++;
                        }
}
?>
