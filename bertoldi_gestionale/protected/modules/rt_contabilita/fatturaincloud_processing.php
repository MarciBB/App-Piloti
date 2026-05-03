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

$ModuloId=12;



function show_list()
{
global $user,$HtmlCommon, $dizionario;
$db= new Database();
$db->connect();

$gestore=new Gestore();
$gestore->conn=$db;
$ges=$user->GestoreId;
if (($user->GestoreId==1))
{
    $ges=1;
}
$gestorefigli=$gestore->getGestoreFigli($ges);
$InGestoreFigli=implode(",", $gestorefigli);


?>
<div>





<?PHP
//$as_dbengine->Connect('localhost','dbadmin','AccediDb2011!','resolve_dev_odc'); 
# Your MySQL host, login, password and database name.

$post_gestore_id=$_POST['GestoreId'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");



$rep=new CReporTool();

$q="SELECT Articolo as Periodo,
        IF(ArticoloCodIva = 14, 0, ArticoloCodIva) as IVA,
        (ArticoloPrezzoLordo-ArticoloPrezzoNetto) as Imponibile,
        ArticoloPrezzoLordo as Totale,
        Nome as Agenzia,
        FatturaData as FatturaData,
        FatturaInCloudId as FatturaInCloudId,
        FatturaNumero as FatturaNumero
    FROM FatturaInCloudAgenzia";


$qw=" where date_format(FatturaData,_utf8'%Y-%m-%d')>='$post_dal_format' and date_format(FatturaData,_utf8'%Y-%m-%d')<='$post_al_format'";

if (isset($post_gestore_id) and ($post_gestore_id>0) ) {
	$qw.="and GestoreId=$post_gestore_id ";
}

$qo=" order by  FatturaData asc, FatturaInCloudId asc";
$q=$q.$qw.$qo;

// echo($q);
$rep->SetQuery($q);
$rep->AddGroupingField('Nome','Nome ',$dizionario['gestore']['gestore'].': ',$dizionario['stampe']['totali_agenzia'].': %name%');

$rep->AddField('Agenzia',$dizionario['generale']['agenzia']);
$rep->AddField('FatturaData',$dizionario['generale']['data']);
$rep->AddField('FatturaNumero', $dizionario['generale']['fattura_numero']);
$rep->AddField('Periodo', $dizionario['generale']['periodo']);
$rep->AddField('IVA',$dizionario['generale']['percentuale'].' (%)');
$rep->AddField('Imponibile',$dizionario['generale']['imponibile'].' (&euro;)',1,'','money');
$rep->AddField('Totale',$dizionario['generale']['importo'].' (&euro;)',1,'','money');



$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');




$gestore="Tutti";

if ($post_gestore_id>0)
{
    $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
    $row = $db->query_first($sql);
    if (!empty($row['RagioneSociale']))
    $gestore=$row['RagioneSociale'];
   
    
    
}
   


$titolo_report=$dizionario['menu_voci']['50']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;
$titolo_report.="<br />".$dizionario['gestore']['gestore'].": ".$gestore;





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
			name: "FattureInCloud",
			filename: "fatture_in_cloud" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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