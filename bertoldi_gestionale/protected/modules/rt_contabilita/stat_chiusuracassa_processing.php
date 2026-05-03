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
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=10;



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



if ($post_tipo_report==1)
{
$tipo_report1="Giornaliero";
$rep=new CReporTool();

$q="SELECT * from RT_ChiusuraCassaGiornalieraGrouped where  GestoreId IN ($InGestoreFigli) and OdcIdRef=$user->OdcId and DataIncasso>='$post_dal_format' and DataIncasso<='$post_al_format'";

$q="SELECT
sum(`RT_ChiusuraCassaGiornaliera`.`Importo`) AS Incassato,
sum(`RT_ChiusuraCassaGiornaliera`.`ImportoAgenzia`) AS ProvvigioneAgenzia,
sum(`RT_ChiusuraCassaGiornaliera`.`DaBonificare`) AS DaBonificare,
sum(`RT_ChiusuraCassaGiornaliera`.`DaFatturare`) AS DaFatturare,
RT_ChiusuraCassaGiornaliera.OdcIdRef AS OdcIdRef,
RT_ChiusuraCassaGiornaliera.CodiceSede AS CodiceSede,
RT_ChiusuraCassaGiornaliera.RagioneSociale AS RagioneSociale,
RT_ChiusuraCassaGiornaliera.SedeId AS SedeId,
RT_ChiusuraCassaGiornaliera.GestoreId AS GestoreId,
RT_ChiusuraCassaGiornaliera.DataIncasso
from `RT_ChiusuraCassaGiornaliera`";
$qw=" where GestoreId IN ($InGestoreFigli) and OdcIdRef=$user->OdcId and DataIncasso>='$post_dal_format' and DataIncasso<='$post_al_format'";
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $qw.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $qw.="and SedeId=$post_sede_id ";


$qg="GROUP BY
RT_ChiusuraCassaGiornaliera.OdcIdRef,
RT_ChiusuraCassaGiornaliera.GestoreIdRef,
RT_ChiusuraCassaGiornaliera.CodiceSede,
RT_ChiusuraCassaGiornaliera.SedeId,
RT_ChiusuraCassaGiornaliera.RagioneSociale,
RT_ChiusuraCassaGiornaliera.GestoreId
ORDER BY
DataIncasso ASC,
RagioneSociale ASC,
CodiceSede ASC,
RT_ChiusuraCassaGiornaliera.LineaNome ASC";

$q=$q.$qw.$qg;

//echo($q);
/*
if (isset($post_gestore_id) and ($post_gestore_id>0) )
    $q.="and GestoreId=$post_gestore_id ";
if (isset($post_sede_id) and ($post_sede_id>0) )
    $q.="and SedeId=$post_sede_id ";
*/
//echo($q);
//$q.="order by RagioneSociale,Comune,DataFattura asc";
//$q.="order by DataFattura,RagioneSociale,Comune asc";

$rep->SetQuery($q);
//$rep->AddGroupingField('DataIncassoF','DataIncassoF ','Data Incasso: ','Totali del %name%');
//if ((!isset($post_gestore_id)) or (!($post_gestore_id>=0)))
$rep->AddGroupingField('RagioneSociale','RagioneSociale ',$dizionario['gestore']['gestore'].': ',$dizionario['stampe']['totali_agenzia'].': %name%');
//if ((!isset($post_sede_id)) or (!($post_sede_id>=0)))
$rep->AddGroupingField('CodiceSede','Rivendita ',$dizionario['conto']['rivendita'].': ',$dizionario['stampe']['totali_rivendita'].': %name%');
//$rep->AddGroupingField('PercorsoNome','Percorso ','Percorso: ','Totale Percorso: %name%');

//$rep->AddField('DataIncassoF','Data Incasso');
//if ((!isset($post_gestore_id)) or (!($post_gestore_id>=0)))
$rep->AddField('RagioneSociale',$dizionario['generale']['agenzia']);
//if ((!isset($post_sede_id)) or (!($post_sede_id>=0)))
$rep->AddField('CodiceSede',$dizionario['conto']['rivendita']);
//$rep->AddField('PercorsoNome','Percorso');
$rep->AddField('Incassato',$dizionario['stampe']['venduto'].' (&euro;)',1,'','money');
$rep->AddField('ProvvigioneAgenzia',$dizionario['stampe']['provvigione_netta'].' (&euro;)',1,'','money');
$rep->AddField('DaFatturare',$dizionario['stampe']['da_fatturare'].' (&euro;)',1,'','money');
$rep->AddField('DaBonificare',$dizionario['stampe']['da_bonificare'].' (&euro;)',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');



}
/*
elseif ($post_tipo_report==2)
{
    $tipo_report1="Mensile";
    
$rep=new CReporTool();
$q_basic="SELECT
stat_TotaleFatturatoPerGiornoTipoPag.SedeIns AS SedeIns,
stat_TotaleFatturatoPerGiornoTipoPag.OdcIdRef AS OdcIdRef,
stat_TotaleFatturatoPerGiornoTipoPag.GestoreIdRef AS GestoreIdRef,
stat_TotaleFatturatoPerGiornoTipoPag.GestoreId AS GestoreId,
stat_TotaleFatturatoPerGiornoTipoPag.Indirizzo AS Indirizzo,
stat_TotaleFatturatoPerGiornoTipoPag.Comune AS Comune,
stat_TotaleFatturatoPerGiornoTipoPag.RagioneSociale AS RagioneSociale,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotImponibile`) AS TotaleImponibile,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotNonImponibile`) AS TotaleNonImponibile,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotaleIva`) AS TotaleIva,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotaleFattura`) AS TotaleFattura,
stat_TotaleFatturatoPerGiornoTipoPag.MeseFatturato AS MeseFatturato,
stat_TotaleFatturatoPerGiornoTipoPag.AnnoFatturato AS AnnoFatturato,
stat_TotaleFatturatoPerGiornoTipoPag.Odc AS Odc,
AppMese.AppMese,
stat_TotaleFatturatoPerGiornoTipoPag.PagamentoTipo
from (`stat_TotaleFatturatoPerGiornoTipoPag` join `AppMese` on((`stat_TotaleFatturatoPerGiornoTipoPag`.`MeseFatturato` = `AppMese`.`AppMeseId`))) ";
$q_group="group by `stat_TotaleFatturatoPerGiornoTipoPag`.`SedeIns`,`stat_TotaleFatturatoPerGiornoTipoPag`.`MeseFatturato`,`stat_TotaleFatturatoPerGiornoTipoPag`.`AnnoFatturato`,`stat_TotaleFatturatoPerGiornoTipoPag`.`OdcIdRef`,`stat_TotaleFatturatoPerGiornoTipoPag`.`GestoreIdRef`,stat_TotaleFatturatoPerGiornoTipoPag.PagamentoTipo";

$q_where=" where  stat_TotaleFatturatoPerGiornoTipoPag.GestoreIdRef IN ($InGestoreFigli) and stat_TotaleFatturatoPerGiornoTipoPag.OdcIdRef=$user->OdcId and stat_TotaleFatturatoPerGiornoTipoPag.DataFattura>='$post_dal_format' and stat_TotaleFatturatoPerGiornoTipoPag.DataFattura<='$post_al_format'";



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
$rep->AddField('PagamentoTipo','Incasso');
$rep->AddField('TotaleImponibile','Imponibile',1,'','money');
$rep->AddField('TotaleIva','Iva',1,'','money');
$rep->AddField('TotaleNonImponibile','Non Imponibile',1,'','money');
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
stat_TotaleFatturatoPerGiornoTipoPag.SedeIns AS SedeIns,
stat_TotaleFatturatoPerGiornoTipoPag.OdcIdRef AS OdcIdRef,
stat_TotaleFatturatoPerGiornoTipoPag.GestoreIdRef AS GestoreIdRef,
stat_TotaleFatturatoPerGiornoTipoPag.GestoreId AS GestoreId,
stat_TotaleFatturatoPerGiornoTipoPag.Indirizzo AS Indirizzo,
stat_TotaleFatturatoPerGiornoTipoPag.Comune AS Comune,
stat_TotaleFatturatoPerGiornoTipoPag.RagioneSociale AS RagioneSociale,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotImponibile`) AS TotaleImponibile,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotNonImponibile`) AS TotaleNonImponibile,

sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotaleIva`) AS TotaleIva,
sum(`stat_TotaleFatturatoPerGiornoTipoPag`.`TotaleFattura`) AS TotaleFattura,
stat_TotaleFatturatoPerGiornoTipoPag.AnnoFatturato AS AnnoFatturato,
stat_TotaleFatturatoPerGiornoTipoPag.Odc AS Odc,
AppMese.AppMese,stat_TotaleFatturatoPerGiornoTipoPag.PagamentoTipo
from (`stat_TotaleFatturatoPerGiornoTipoPag` join `AppMese` on((`stat_TotaleFatturatoPerGiornoTipoPag`.`MeseFatturato` = `AppMese`.`AppMeseId`))) ";
$q_group="group by `stat_TotaleFatturatoPerGiornoTipoPag`.`SedeIns`,`stat_TotaleFatturatoPerGiornoTipoPag`.`AnnoFatturato`,`stat_TotaleFatturatoPerGiornoTipoPag`.`OdcIdRef`,`stat_TotaleFatturatoPerGiornoTipoPag`.GestoreIdRef,stat_TotaleFatturatoPerGiornoTipoPag.PagamentoTipo";

$q_where=" where  stat_TotaleFatturatoPerGiornoTipoPag.GestoreIdRef IN ($InGestoreFigli) and stat_TotaleFatturatoPerGiornoTipoPag.OdcIdRef=$user->OdcId and stat_TotaleFatturatoPerGiornoTipoPag.DataFattura>='$post_dal_format' and stat_TotaleFatturatoPerGiornoTipoPag.DataFattura<='$post_al_format'";



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
$rep->AddField('PagamentoTipo','Incasso');
$rep->AddField('TotaleImponibile','Imponibile',1,'','money');
$rep->AddField('TotaleNonImponibile','Non Imponibile',1,'','money');
$rep->AddField('TotaleIva','Iva',1,'','money');
$rep->AddField('TotaleFattura','Importo',1,'','money');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>TOTALE FATTURATO <strong> ');

    
    
}    
*/
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
    $sql = "SELECT CodiceSede from ElencoSediView where SedeId=$post_sede_id";
    $row = $db->query_first($sql);
    if (!empty($row['CodiceSede']))
   $sede=$row['CodiceSede'];
    
    
}


$titolo_report=$dizionario['menu_voci']['10']."<br/>";
$titolo_report.="<br />".$dizionario['stampe']['tipo_report']." ".$tipo_report1;
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
		if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }
		
		
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