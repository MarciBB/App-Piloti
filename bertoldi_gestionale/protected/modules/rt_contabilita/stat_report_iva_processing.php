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

$ModuloId=1;



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
$confineId=$_POST['ConfineId'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];
$servizio=$_POST['servizio'];
$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");

$post_tipo_report=1;
if ($post_tipo_report==1)
{
$tipo_report1="Giornaliero";
$rep=new CReporTool();


$q="SELECT
date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d') AS DataIncasso,
date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%d/%m/%Y') AS DataIncassoF,
RT_PrenotazioneTitoloIva.PrenotazioneTitoloId,
RT_PrenotazioneTitoloIva.ConfineId,
sum(RT_PrenotazioneTitoloIva.KmPercorsiTotale) as TotaleKm,
sum(RT_PrenotazioneTitoloIva.KmPercorsiTerritorio) TotaleKmTerritorio,
RT_PrenotazioneTitoloIva.AliquotaIva,
sum(RT_PrenotazioneTitoloIva.ImportoTitoloPerConfine) as BaseScorporo,
sum(RT_PrenotazioneTitoloIva.ImportoIvaConfine) as Iva,
RT_PrenotazioneTitoloIva.OdcIdRef,
RT_PrenotazioneTitolo.Codice,
RT_PrenotazioneTitolo.Anno,
RT_PrenotazioneTitolo.Progressivo,
RT_PrenotazioneTitolo.TipoTitolo,
RT_PrenotazioneTitolo.ImportoTitolo,
RT_PrenotazioneTitolo.Cognome,
RT_PrenotazioneTitolo.Nome,
RT_PrenotazioneTitolo.TipologiaBiglietto
FROM
RT_PrenotazioneTitoloIva
INNER JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneTitoloIva.PrenotazioneTitoloId = RT_PrenotazioneTitolo.PrenotazioneTitoloId
where
RT_PrenotazioneTitoloIva.ConfineId=$confineId and 
RT_PrenotazioneTitoloIva.OdcIdRef=$user->OdcId and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')>='$post_dal_format' and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')<='$post_al_format'
and RT_PrenotazioneTitolo.Stato = 1 and RT_PrenotazioneTitolo.Cancella = 0
GROUP BY
RT_PrenotazioneTitoloIva.PrenotazioneTitoloId";

$rep->SetQuery($q);
$rep->AddField('DataIncassoF',$dizionario['generale']['data']);
$rep->AddField('Codice',$dizionario['generale']['biglietto']);
$rep->AddField('TipoTitolo',$dizionario['generale']['tipo']);
$rep->AddField('Cognome',$dizionario['generale']['cognome']);
$rep->AddField('Nome',$dizionario['generale']['nome']);
$rep->AddField('ImportoTitolo',$dizionario['generale']['importo'].' (&euro;)',1,'','money');
$rep->AddField('TotaleKm',$dizionario['stampe']['km_tot'],1,'','money');
$rep->AddField('TotaleKmTerritorio',$dizionario['stampe']['km_terr'],1,'','money');
$rep->AddField('BaseScorporo',$dizionario['stampe']['quota_terr'].' (&euro;)',1,'','money');
$rep->AddField('AliquotaIva',$dizionario['stampe']['iva'].' (%)',0,'','money');
$rep->AddField('Iva',$dizionario['stampe']['tot_iva'].' (&euro;)',1,'','money');



$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');


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
    
if ($post_sede_id>0) {
    $sql = "SELECT CodiceSede from ElencoSediView where SedeId=$post_sede_id";
    $row = $db->query_first($sql);
    if (!empty($row['CodiceSede']))
   	$sede=$row['CodiceSede'];   
}


$titolo_report=$dizionario['menu_voci']['1']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;

    $rep->DrawReport($titolo_report);
?>


</div>
    
<style>
.exportToExcel {
    font-size: 12px !important;
    margin-left: 10px !important;
}
</style> 
<script src= "/js/jquery.table2excel.js"> </script> 

<script>
$(document).ready(function() {
	$(".exportToExcel").click(function(e){
		$(".report_excel").table2excel({
			exclude: ".noExl",
			name: "Report IVA",
			filename: "report_iva" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
			fileext: ".xls",
			exclude_img: true,
			exclude_links: true,
			exclude_inputs: true,
			preserveColors: true
		});
	});
	
});
</script>    

    
    


           

<?php
   
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