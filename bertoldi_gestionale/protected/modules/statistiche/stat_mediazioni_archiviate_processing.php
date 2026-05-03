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

$ModuloId=7;



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

$post_gestore_id=$_POST['GestoreId'];
$post_sede_id=$_POST['SedeId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "y-m-d");


if ($post_tipo_report==1)
{
$tipo_report1="Giornaliero";
$rep=new CReporTool();

$q="SELECT * from stat_MediazioniConclusePagabili_03_raggruppata where  GestoreIdRef IN ($InGestoreFigli) and OdcIdRef=$user->OdcId and DataArchiviazioneIstanza>='$post_dal_format' and DataArchiviazioneIstanza<='$post_al_format'";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q.="and SedeIns=$post_sede_id ";


//$q.="order by RagioneSociale,Comune,DataFattura asc";
$q.="order by RagioneSociale,Comune,Indirizzo,DataArchiviazioneIstanza,MediazioneId asc";

$rep->SetQuery($q);
//$rep->AddGroupingField('DataArchiviazioneIstanza_formattata','DataArchiviazioneIstanza_formattata ','Data Archiviazione: ','totale fatturato il %name%');
$rep->AddGroupingField('RagioneSociale','RagioneSociale ','Gestore: ','Totale Gestore: %name%');
$rep->AddGroupingField('Comune','Comune ','Sede: ','Totale Sede: %name% ');

//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
//$rep->AddField('DataFattura_formattata','Data Archiviazione');
$rep->AddField('RagioneSociale','Gestore');
$rep->AddField('Comune','Sede');
$rep->AddField('Codice','Codice');
$rep->AddField('DataArchiviazioneIstanza_formattata','Data Archiviazione');

$rep->AddField('TotImponibile','Imponibile',1,'','money');
$rep->AddField('TotIva','Iva',1,'','money');
$rep->AddField('TotalePagabile','Totale',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE PAGABILE <strong> ');



}

elseif ($post_tipo_report==2)
{
    $tipo_report1="Mensile";
    
$rep=new CReporTool();
$q_basic="SELECT
stat_TotaleFatturatoMediazioniConcluse.SedeIns AS SedeIns,
stat_TotaleFatturatoMediazioniConcluse.OdcIdRef AS OdcIdRef,
stat_TotaleFatturatoMediazioniConcluse.GestoreIdRef AS GestoreIdRef,
stat_TotaleFatturatoMediazioniConcluse.GestoreId AS GestoreId,
stat_TotaleFatturatoMediazioniConcluse.Indirizzo AS Indirizzo,
stat_TotaleFatturatoMediazioniConcluse.Comune AS Comune,
stat_TotaleFatturatoMediazioniConcluse.RagioneSociale AS RagioneSociale,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotImponibile`) AS TotaleImponibile,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotaleIva`) AS TotaleIva,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotaleFattura`) AS TotaleFattura,
stat_TotaleFatturatoMediazioniConcluse.MeseFatturato AS MeseFatturato,
stat_TotaleFatturatoMediazioniConcluse.AnnoFatturato AS AnnoFatturato,
stat_TotaleFatturatoMediazioniConcluse.Odc AS Odc,
AppMese.AppMese
from (`stat_TotaleFatturatoMediazioniConcluse` join `AppMese` on((`stat_TotaleFatturatoMediazioniConcluse`.`MeseFatturato` = `AppMese`.`AppMeseId`))) ";
$q_group="group by `stat_TotaleFatturatoMediazioniConcluse`.`SedeIns`,`stat_TotaleFatturatoMediazioniConcluse`.`MeseFatturato`,`stat_TotaleFatturatoMediazioniConcluse`.`AnnoFatturato`,`stat_TotaleFatturatoMediazioniConcluse`.`OdcIdRef`,`stat_TotaleFatturatoMediazioniConcluse`.`GestoreIdRef`";

$q_where=" where  stat_TotaleFatturatoMediazioniConcluse.GestoreIdRef IN ($InGestoreFigli) and stat_TotaleFatturatoMediazioniConcluse.OdcIdRef=$user->OdcId and stat_TotaleFatturatoMediazioniConcluse.DataFattura>='$post_dal_format' and stat_TotaleFatturatoMediazioniConcluse.DataFattura<='$post_al_format'";



//$q="SELECT * from stat_TotaleFatturatoPerAnnoMese where  GestoreIdRef IN ($InGestoreFigli) and OdcIdRef=$user->OdcId ";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q_where.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q_where.="and SedeIns=$post_sede_id ";


$q_order="order by AnnoFatturato,MeseFatturato,RagioneSociale,Comune asc";


$q=$q_basic." ".$q_where." ".$q_group." ".$q_order;


$rep->SetQuery($q);
//$rep->AddGroupingField('DataFattura','DataFattura ','DataFattura: ','totale fatturato il %name%');
$rep->AddGroupingField('AnnoFatturato','AnnoFatturato ','Anno: ','Totale Anno: %name% ');
$rep->AddGroupingField('AppMese','AppMese ','Mese: ','Totale Mese: %name% ');


//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
$rep->AddField('RagioneSociale','Gestore');
$rep->AddField('Comune','Sede');
$rep->AddField('AppMese','Mese');
$rep->AddField('AnnoFatturato','Anno');
$rep->AddField('TotaleImponibile','Imponibile',1,'','money');
$rep->AddField('TotaleIva','Iva',1,'','money');
$rep->AddField('TotaleFattura','Importo',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE FATTURATO <strong> ');

    
    
}    


elseif ($post_tipo_report==3)
{
    $tipo_report1="Annuale";
    
$rep=new CReporTool();



   
$rep=new CReporTool();
$q_basic="SELECT
stat_TotaleFatturatoMediazioniConcluse.SedeIns AS SedeIns,
stat_TotaleFatturatoMediazioniConcluse.OdcIdRef AS OdcIdRef,
stat_TotaleFatturatoMediazioniConcluse.GestoreIdRef AS GestoreIdRef,
stat_TotaleFatturatoMediazioniConcluse.GestoreId AS GestoreId,
stat_TotaleFatturatoMediazioniConcluse.Indirizzo AS Indirizzo,
stat_TotaleFatturatoMediazioniConcluse.Comune AS Comune,
stat_TotaleFatturatoMediazioniConcluse.RagioneSociale AS RagioneSociale,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotImponibile`) AS TotaleImponibile,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotaleIva`) AS TotaleIva,
sum(`stat_TotaleFatturatoMediazioniConcluse`.`TotaleFattura`) AS TotaleFattura,
stat_TotaleFatturatoMediazioniConcluse.AnnoFatturato AS AnnoFatturato,
stat_TotaleFatturatoMediazioniConcluse.Odc AS Odc,
AppMese.AppMese
from (`stat_TotaleFatturatoMediazioniConcluse` join `AppMese` on((`stat_TotaleFatturatoMediazioniConcluse`.`MeseFatturato` = `AppMese`.`AppMeseId`))) ";
$q_group="group by `stat_TotaleFatturatoMediazioniConcluse`.`SedeIns`,`stat_TotaleFatturatoMediazioniConcluse`.`AnnoFatturato`,`stat_TotaleFatturatoMediazioniConcluse`.`OdcIdRef`,`stat_TotaleFatturatoMediazioniConcluse`.`GestoreIdRef`";

$q_where=" where  stat_TotaleFatturatoMediazioniConcluse.GestoreIdRef IN ($InGestoreFigli) and stat_TotaleFatturatoMediazioniConcluse.OdcIdRef=$user->OdcId and stat_TotaleFatturatoMediazioniConcluse.DataFattura>='$post_dal_format' and stat_TotaleFatturatoMediazioniConcluse.DataFattura<='$post_al_format'";



//$q="SELECT * from stat_TotaleFatturatoPerAnnoMese where  GestoreIdRef IN ($InGestoreFigli) and OdcIdRef=$user->OdcId ";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q_where.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q_where.="and SedeIns=$post_sede_id ";


$q_order="order by AnnoFatturato,MeseFatturato,GiornoFatturato,RagioneSociale,Comune asc";

$q=$q_basic." ".$q_where." ".$q_group." ".$q_order;




$rep->SetQuery($q);
//$rep->AddGroupingField('DataFattura','DataFattura ','DataFattura: ','totale fatturato il %name%');
$rep->AddGroupingField('AnnoFatturato','AnnoFatturato ','Anno: ','Totale Anno: %name% ');
$rep->AddGroupingField('RagioneSociale','RagioneSociale ','Gestore: ','Totale Gestore: %name%');
//$rep->AddGroupingField('Comune','Comune ','Sede: ','Totale Sede: %name% ');

//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
$rep->AddField('AnnoFatturato','Anno');
$rep->AddField('RagioneSociale','Gestore');
$rep->AddField('Comune','Sede');

$rep->AddField('TotaleImponibile','Imponibile',1,'','money');
$rep->AddField('TotaleIva','Iva',1,'','money');
$rep->AddField('TotaleFattura','Importo',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE FATTURATO <strong> ');

    
    
}    

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


$titolo_report="Report relativo alle mediazioni concluse<br />";
$titolo_report.="<br />Tipo report: ".$tipo_report1;
$titolo_report.="<br />Periodo considerato: dal ".$post_dal." al ".$post_al;
$titolo_report.="<br />Gestore: ".$gestore;
$titolo_report.="<br />Sede: ".$sede;




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