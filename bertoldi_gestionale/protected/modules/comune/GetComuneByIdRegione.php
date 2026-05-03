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

$RegioneId_=$_REQUEST['id_regione'];

global $dizionario;

$db=New Database();
$db->connect();

print("<option value=\"\">- ".$dizionario['generale']['seleziona']." -</option>");

if ($RegioneId_!="")
{
    
$sql = "SELECT ComuneId,Comune From ComuneByRegioneId where RegioneId=$RegioneId_";
$ArrObject = $db->fetch_array($sql);
$ArrObjectSize=count($ArrObject);
                        
$i=0;
                        while ($i< $ArrObjectSize)
                        {
                            $value=$ArrObject[$i]['ComuneId'];
                            $label=$ArrObject[$i]['Comune'];
                           
                            ?>  
                            <option value="<?=$value?>"><?=$label?></option>
                            <?

                            $i++;
                        }
}
?>
