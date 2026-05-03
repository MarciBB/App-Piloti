<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
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
include_once($classespath_."class.AnagraficaEst.php");
include_once($classespath_."class.AnagraficaTipo.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.MediazioneStato.php");
include_once($classespath_."class.MediazioneTipoIstanza.php");
include_once($classespath_."class.MediazioneTipoRichiesta.php");
include_once($classespath_."class.Materia.php");
include_once($classespath_."class.MediazioneModPre.php");
include_once($classespath_."class.Mediazione.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Lingua.php");
include_once($classespath_."class.Aula.php");
include_once($classespath_."class.MediazioneEsitoNegativo.php");

include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=5;



function show_list()
{
global $user,$HtmlCommon, $dizionario;
$db= new Database();
$db->connect();

$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);


?>

<div>





<?PHP
//$as_dbengine->Connect('localhost','dbadmin','AccediDb2011!','resolve_dev_odc'); 
# Your MySQL host, login, password and database name.

$post_gestore_id=$_POST['GestoreId'];
$post_sede_id=$_POST['SedeId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");


$tipo_report1=$dizionario['conto']['giornaliero'];
$rep=new CReporTool();

$q_basic="SELECT * from OperatoreAccessoView ";
$q_where=" where  OperatoreAccessoView.GestoreId IN ($InGestoreFigli)  and OperatoreAccessoView.DataOra>='$post_dal_format' and OperatoreAccessoView.DataOra<='$post_al_format'";

if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q_where.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q_where.="and SedeId=$post_sede_id ";


$q_order="order by DataOra asc";

$q=$q_basic." ".$q_where." ".$q_group." ".$q_order;



$rep->SetQuery($q);


$rep->AddField('DataOra',$dizionario['stampe']['data_ora']);
$rep->AddField('Cognome',$dizionario['generale']['cognome']);
$rep->AddField('Nome',$dizionario['generale']['nome']);
$rep->AddField('Username',$dizionario['autista']['username']);
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

//$rep->SetSummary('<strong>TOTALE Accessi <strong> ');

    
    
    

$gestore="Tutti";
$sede="Tutte";

if ($post_gestore_id>0)
{
     $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
    $row = $db->query_first($sql);
    if (!empty($row['RagioneSociale']))
   $gestore=$row['RagioneSociale'];
   
    
    
}
    
if ($post_sede_id>0)
{
    $sql = "SELECT Comune from ElencoSediView where SedeId=$post_sede_id";
    $row = $db->query_first($sql);
    if (!empty($row['Comune']))
   $sede=$row['Comune'];
    
    
}


$titolo_report=$dizionario['stampe']['report_log']."<br/>";
$titolo_report.="<br />".$dizionario['stampe']['tipo_report'].": ".$tipo_report1;
$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;
$titolo_report.="<br />".$dizionario['gestore']['gestore'].": ".$gestore;
$titolo_report.="<br />".$dizionario['generale']['sede'].": ".$sede;




    $rep->DrawReport($titolo_report);
?>


</div>
    
    

    
    


           

<?
   
}



if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
			$do=$_REQUEST['do'];
			if(!isset($do)) 
			$do='';
		
		
			switch($do) {
                                
                              

				default:
                                    $FunzioneId=1;
                                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                   
                                        if (sizeof($permesso))
                                          show_list();    
                                    
		               
                		break;
			}
		

	

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>