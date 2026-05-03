<?
/**
 Autore: Marco Casaburi
 Data ultima modifica: 24-09-2014
 */
$basepath=$_SERVER['DOCUMENT_ROOT'];
global $search;

include_once($basepath."/main_include.php");


$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();   


$search=unserialize($_SESSION['CURRENT_SEARCH']);
$search->conn=$db;
$arr=$search->ArrSearch;
$row = $search->getComuneNazioneById($arr['ComuneIdPickup']);
$comPickup=$row['Comune'];
$nazionePickup = $row['Nazione'];
$row = $search->getComuneNazioneById($arr['ComuneIdDropOff']);
$comDrop=$row['Comune'];
$nazioneDropoff = $row['Nazione'];
$ferPickup=$arr['FermataPickup'];
$ferDrop=$arr['FermataDropOff'];

if ((isset($_REQUEST['changedate'])) and ($_REQUEST['changedate']=="true"))
{
    if (isset($_REQUEST['andata']))
        $search->SetDataInizioA($_REQUEST['andata']);
    if (isset($_REQUEST['ritorno']))
        $search->SetDataInizioR($_REQUEST['ritorno']);
}
$viaggio=$_REQUEST['viaggio'];

if(isset($arr['TipoPercorsoId'])){
	$corsaEsiste = true;
} else {
	$corsaEsiste = false;
}

if ($viaggio=="andata"){
	include_once($basepath."/util_print_calendar_andata.php");  
}
if (($arr['TipoViaggioId']==2) and ($viaggio=="ritorno")){
    include_once($basepath."/util_print_calendar_ritorno.php");  
}

$_SESSION['CURRENT_SEARCH']=serialize($search);
?>