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

$ModuloId=30;



function show_list()
{
global $user,$HtmlCommon;
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

$post_odc_id=$_POST['OdcId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");


if ($post_tipo_report==1)
{
$tipo_report1="Sintetico";
$rep=new CReporTool();

$q="SELECT * from BrainDirittiMaturatiPerOdc where  DataIns>='$post_dal_format' and DataIns<='$post_al_format'";
if (isset($post_odc_id) and ($post_odc_id>0) )
    $q.=" and OdcIdRef=$post_odc_id ";




//$q.="order by RagioneSociale,Comune,DataFattura asc";
$q.=" order by Odc,MediazioneId,DataIns,CognomeRagioneSociale,Nome";

$rep->SetQuery($q);
$rep->AddGroupingField('Odc','Odc ','Organismo: ','totale per organismo %name%');

$rep->AddField('DataIns','Data');
$rep->AddField('Codice','Codice');
/*
$rep->AddField('CognomeRagioneSociale','Cognome');
$rep->AddField('Nome','Nome');*/

$rep->AddField('Maturato','Maturato',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE PROVVIGIONI MATURATE <strong> ');






$titolo_report="Report relativo alle Provvigioni Maturate<br </>";
$titolo_report.="<br />Periodo considerato: dal ".$post_dal." al ".$post_al;




    $rep->DrawReport($titolo_report);
    
}

 
?>


</div>
    
    

    
    


           

<?
   
}



if ( (is_object($user)) and ($user->OdcId==1)) {
   
    
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