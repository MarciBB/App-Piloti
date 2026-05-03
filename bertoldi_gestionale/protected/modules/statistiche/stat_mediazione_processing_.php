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
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");


if ($post_tipo_report==1)
{
$tipo_report1="Giornaliero";
$rep=new CReporTool();

$q="SELECT * from stat_NumeroMediazioniPerGiornoFinal where  GestoreId IN ($InGestoreFigli)  and DataPre>='$post_dal_format' and DataPre<='$post_al_format'";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q.="and SedeIns=$post_sede_id ";


$q.="order by DataPre,Comune asc";



$rep->SetQuery($q);
$rep->AddGroupingField('DataPre_formattata','DataPre_formattata ','Data Presentazione: ','Totale Mediazioni in data: %name%');
$rep->AddGroupingField('Comune','Comune ','Sede: ','N. Mediazioni Sede: %name% ');
//$rep->AddGroupingField('Materia','Materia ','Materia: ','N. Mediazioni Materia: %name%');


//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
$rep->AddField('Materia','Materia');
$rep->AddField('RagioneSociale','RagioneSociale');
$rep->AddField('Comune','Sede');
$rep->AddField('DataPre_formattata','Data Presentazione');
$rep->AddField('NumeroMediazioni','Mediazioni',1,'','i');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>Totale Mediazioni <strong> ');



}

elseif ($post_tipo_report==2)
{
    $tipo_report1="Mensile";
    
$rep=new CReporTool();
$q_basic="SELECT
stat_NumeroMediazioniPerGiorno.SedeIns,
stat_NumeroMediazioniPerGiorno.GestoreId,
stat_NumeroMediazioniPerGiorno.ComuneIns,
AppMese.AppMese,
stat_NumeroMediazioniPerGiorno.RagioneSociale,
stat_NumeroMediazioniPerGiorno.IndirizzoIns,
stat_NumeroMediazioniPerGiorno.Odc,
Count(stat_NumeroMediazioniPerGiorno.SedeIns) AS NumeroMediazioni,
stat_NumeroMediazioniPerGiorno.MesePre,
stat_NumeroMediazioniPerGiorno.AnnoPre
FROM
stat_NumeroMediazioniPerGiorno
INNER JOIN AppMese ON stat_NumeroMediazioniPerGiorno.MesePre = AppMese.AppMeseId ";
$q_group="GROUP BY
stat_NumeroMediazioniPerGiorno.SedeIns,
stat_NumeroMediazioniPerGiorno.GestoreId,
stat_NumeroMediazioniPerGiorno.ComuneIns,
stat_NumeroMediazioniPerGiorno.IndirizzoIns,
stat_NumeroMediazioniPerGiorno.Odc,
AppMese.AppMese,
stat_NumeroMediazioniPerGiorno.MesePre,
stat_NumeroMediazioniPerGiorno.AnnoPre ";

$q_where=" where  stat_NumeroMediazioniPerGiorno.GestoreId IN ($InGestoreFigli)  and stat_NumeroMediazioniPerGiorno.DataPre>='$post_dal_format' and stat_NumeroMediazioniPerGiorno.DataPre<='$post_al_format'";



//$q="SELECT * from stat_TotaleFatturatoPerAnnoMese where  GestoreIdRef IN ($InGestoreFigli) and OdcIdRef=$user->OdcId ";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q_where.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q_where.="and SedeIns=$post_sede_id ";


$q_order="order by AnnoPre,MesePre,RagioneSociale,ComuneIns asc";

$q=$q_basic." ".$q_where." ".$q_group." ".$q_order;



$rep->SetQuery($q);
//$rep->AddGroupingField('DataFattura','DataFattura ','DataFattura: ','totale fatturato il %name%');
$rep->AddGroupingField('AnnoPre','AnnoPre ','Anno: ','Totale Mediazioni anno: %name% ');
$rep->AddGroupingField('AppMese','AppMese ','Mese: ','Totale Mediazioni mese: %name% ');


//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
$rep->AddField('RagioneSociale','RagioneSociale');
$rep->AddField('ComuneIns','Sede');
$rep->AddField('NumeroMediazioni','Mediazioni',1,'','i');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE MEDIAZIONI <strong> ');

    
    
}    


elseif ($post_tipo_report==3)
{
    $tipo_report1="Annuale";
    
$rep=new CReporTool();



   
$rep=new CReporTool();
$q_basic="SELECT
stat_NumeroMediazioniPerGiorno.SedeIns,
stat_NumeroMediazioniPerGiorno.GestoreId,
stat_NumeroMediazioniPerGiorno.ComuneIns,
AppMese.AppMese,
stat_NumeroMediazioniPerGiorno.RagioneSociale,
stat_NumeroMediazioniPerGiorno.IndirizzoIns,
stat_NumeroMediazioniPerGiorno.Odc,
Count(stat_NumeroMediazioniPerGiorno.SedeIns) AS NumeroMediazioni,
stat_NumeroMediazioniPerGiorno.AnnoPre
FROM
stat_NumeroMediazioniPerGiorno
INNER JOIN AppMese ON stat_NumeroMediazioniPerGiorno.MesePre = AppMese.AppMeseId ";
$q_group="GROUP BY
stat_NumeroMediazioniPerGiorno.SedeIns,
stat_NumeroMediazioniPerGiorno.GestoreId,
stat_NumeroMediazioniPerGiorno.ComuneIns,
stat_NumeroMediazioniPerGiorno.IndirizzoIns,
stat_NumeroMediazioniPerGiorno.Odc,
stat_NumeroMediazioniPerGiorno.AnnoPre ";

$q_where=" where  stat_NumeroMediazioniPerGiorno.GestoreId IN ($InGestoreFigli)  and stat_NumeroMediazioniPerGiorno.DataPre>='$post_dal_format' and stat_NumeroMediazioniPerGiorno.DataPre<='$post_al_format'";



//$q="SELECT * from stat_TotaleFatturatoPerAnnoMese where  GestoreIdRef IN ($InGestoreFigli) and OdcIdRef=$user->OdcId ";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q_where.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q_where.="and SedeIns=$post_sede_id ";


$q_order="order by AnnoPre,RagioneSociale,ComuneIns asc";

$q=$q_basic." ".$q_where." ".$q_group." ".$q_order;



$rep->SetQuery($q);
//$rep->AddGroupingField('DataFattura','DataFattura ','DataFattura: ','totale fatturato il %name%');
$rep->AddGroupingField('AnnoPre','AnnoPre ','Anno: ','Totale Mediazioni anno: %name% ');


//$rep->AddGroupingField('Materia','Materia','Materia: ','totale for materia %name%');

//$rep->AddGroupingField('animalid','GetAnymalClassName','class :','Totals for %name%');
$rep->AddField('RagioneSociale','RagioneSociale');
$rep->AddField('ComuneIns','Sede');
$rep->AddField('AnnoPre','Anno');
$rep->AddField('NumeroMediazioni','Mediazioni',1,'','i');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE MEDIAZIONI <strong> ');

    
    
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


$titolo_report="Report relativo alle Mediazioni<br </>";
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