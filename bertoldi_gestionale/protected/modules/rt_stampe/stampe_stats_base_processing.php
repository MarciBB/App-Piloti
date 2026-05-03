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

$ModuloId=8;



function show_list()
{
global $user,$HtmlCommon, $dizionario;
$db= new Database();
$db->connect();

$gestore=new Gestore();
$gestore->conn=$db;
$ges=$user->GestoreId;
if (($user->GestoreId==1) or ($user->GestoreId==2))
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

$post_linea_id=$_POST['LineaId'];
$post_corsa_id=$_POST['CorsaId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];
$ComuneSalitaId=$_POST['ComuneSalitaId'];
$ComuneDiscesaId=$_POST['ComuneDiscesaId'];



$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");


if ($post_tipo_report==1)
	$tipo_report1=$dizionario['stampe']['per_partenze'];
elseif ($post_tipo_report==1)
	$tipo_report1=$dizionario['stampe']['per_arrivi'];
$rep=new CReporTool();
$swhere="";
if (isset($post_linea_id) and ($post_linea_id>0) )
    $swhere.="and RT_Corsa.LineaId=$post_linea_id ";
if (isset($post_corsa_id) and ($post_corsa_id>0) )
    $swhere.="and RT_Corsa.CorsaId=$post_corsa_id ";

if (isset($post_dal_format))
    $swhere.="and CorsaDataPartenza>='$post_dal_format' ";

if (isset($post_al_format))
    $swhere.="and CorsaDataPartenza<='$post_al_format' ";

if (($ComuneSalitaId!=''))
{
    $ComuneSalitaId=  addslashes($ComuneSalitaId);
    $swhere.="and ComuneSalita='$ComuneSalitaId' ";
}
    
if (($ComuneDiscesaId!=''))
   {
    $ComuneDiscesaId=  addslashes($ComuneDiscesaId);
    $swhere.="and ComuneDiscesa='$ComuneDiscesaId' ";
}

//echo($q);
//$q.="order by DataFattura,RagioneSociale,Comune asc";

$q="SELECT
RT_PrenotazionePercorso.ComuneSalita,
RT_PrenotazionePercorso.ComuneDiscesa,
sum(RT_Prenotazione.TotalePaxPrenotati-RT_PrenotazionePercorso.PasseggeriEsclusi) AS TotalePaxPrenotati,
sum(if (RT_TitoliPerPrenotazione.PrenotazioneId is null and RT_PrenotazionePercorso.Direzione='A',RT_ImportoPerPrenotazione.DaIncassare,0)) AS DaIncassare,
sum(if (RT_TitoliPerPrenotazione.PrenotazioneId is not null and RT_PrenotazionePercorso.Direzione='A',RT_ImportoPerPrenotazione.DaIncassare,0)) AS TotaleIncassato,
RT_FermataSalita.FermataNome AS FermataSalita,
RT_Fermata.FermataNome AS FermataDiscesa

FROM RT_PrenotazionePercorso INNER JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId INNER JOIN RT_ImportoPerPrenotazione ON RT_Prenotazione.PrenotazioneId = RT_ImportoPerPrenotazione.PrenotazioneId INNER JOIN RT_Corsa ON RT_PrenotazionePercorso.CorsaId = RT_Corsa.CorsaId LEFT JOIN RT_TitoliPerPrenotazione ON RT_ImportoPerPrenotazione.PrenotazioneId = RT_TitoliPerPrenotazione.PrenotazioneId INNER JOIN RT_Fermata AS RT_FermataSalita ON RT_PrenotazionePercorso.FermataSalitaId = RT_FermataSalita.FermataId INNER JOIN RT_Fermata ON RT_PrenotazionePercorso.FermataDiscesaId = RT_Fermata.FermataId

WHERE (RT_PrenotazionePercorso.PrenotazioneStato = 1 OR RT_PrenotazionePercorso.PrenotazioneStato = 3 OR RT_PrenotazionePercorso.PrenotazioneStato = 10) 
$swhere and RT_Prenotazione.GestoreIdRef IN ($InGestoreFigli)
GROUP BY
RT_PrenotazionePercorso.ComuneSalita,
RT_FermataSalita.FermataId,
RT_PrenotazionePercorso.ComuneDiscesa,
RT_Fermata.FermataId";
if ($post_tipo_report==1)
	$q.=" order by RT_PrenotazionePercorso.ComuneSalita,RT_FermataSalita.FermataNome asc";
elseif ($post_tipo_report==2)
 $q.=" order by RT_PrenotazionePercorso.ComuneDiscesa,RT_Fermata.FermataNome asc";

$rep->SetQuery($q);
//$rep->AddGroupingField('CorsaDataPartenza','CorsaDataPartenza ','DataPartenza: ','Totale DataPartenza: %name%');

//$rep->AddGroupingField('CorsaNome','CorsaNome ','Corsa: ','Totale Percorso: %name%');

if ($post_tipo_report==1)
{
       $rep->AddGroupingField('ComuneSalita','ComuneSalita ',$dizionario['stampe']['comune_salita'].': ',$dizionario['stampe']['partenze_da'].' %name%');

    $rep->AddGroupingField('FermataSalita','FermataSalita ',$dizionario['stampe']['fermata_salita'].': ',$dizionario['stampe']['partenze_da'].' %name%');
//$rep->AddGroupingField('FermataDiscesa','FermataDiscesa ','FermataDiscesa: ','Arrivi a %name%');
 
    
}
elseif ($post_tipo_report==2)
    {
        $rep->AddGroupingField('ComuneDiscesa','ComuneDiscesa ',$dizionario['stampe']['comune_discesa'].': ',$dizionario['stampe']['partenze_da'].' %name%');

    $rep->AddGroupingField('FermataDiscesa','FermataDiscesa ',$dizionario['stampe']['fermata_discesa'].': ',$dizionario['stampe']['arrivi_a'].' %name%');
    //$rep->AddGroupingField('FermataSalita','FermataSalita ','FermataSalita: ','Partenze da %name%');
 
   
}

//$rep->AddGroupingField('TotalePostiPrenotati','TotalePostiPrenotati ','TotalePostiPrenotati: ','Tot. Incassato il %name%');

//$rep->AddGroupingField('PercorsoNome','Servizio linea ','Servizio linea: ','Tot. Incassato %name%');

//$rep->AddGroupingField('TipoServizio','TipoServizio ','Tipo Servizio: ','Tot. Incassato %name%');
//if ((!isset($post_gestore_id)) or (!($post_gestore_id>=0)))
//$rep->AddGroupingField('RagioneSociale','RagioneSociale ','Gestore: ','Totale Agenzia: %name%');
//if ((!isset($post_sede_id)) or (!($post_sede_id>=0)))
//$rep->AddGroupingField('CorsaNome','CorsaNome ','CorsaNome: ','Totale Rivendita: %name%');
//$rep->AddField('CorsaDataPartenza','CorsaDataPartenza');
//$rep->AddField('CorsaNome','CorsaNome');

if ($post_tipo_report==1)
{
$rep->AddField('FermataSalita',$dizionario['stampe']['fermata_salita']);
$rep->AddField('FermataDiscesa',$dizionario['stampe']['fermata_discesa']);
    $rep->AddField('ComuneSalita',$dizionario['stampe']['comune_salita']);
$rep->AddField('ComuneDiscesa',$dizionario['stampe']['comune_discesa']);

    
}
    
    
elseif ($post_tipo_report==2)
{
    $rep->AddField('FermataDiscesa',$dizionario['stampe']['fermata_discesa']);
$rep->AddField('FermataSalita',$dizionario['stampe']['fermata_salita']);
$rep->AddField('ComuneDiscesa',$dizionario['stampe']['comune_discesa']);

    $rep->AddField('ComuneSalita',$dizionario['stampe']['comune_salita']);


    
}


//$rep->AddField('ClienteNome','ClienteNome');

$rep->AddField('TotalePaxPrenotati',$dizionario['generale']['pax'],1,'','i');
$rep->AddField('TotaleIncassato',$dizionario['stampe']['incassato'],1,'','i');
$rep->AddField('DaIncassare',$dizionario['stampe']['da_incassare'],1,'','i');
$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' <strong> ');





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


$titolo_report=$dizionario['menu_voci']['24']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
$titolo_report.="<br />".$dizionario['stampe']['tipo_report'].": ".$tipo_report1;
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
			name: "Statistiche Base",
			filename: "statistiche_base" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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