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
global $user, $HtmlCommon, $dizionario;
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

$post_linea_id=$_POST['LineaId'];
$post_sede_id=$_POST['SedeId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");


if ($post_tipo_report==1)
$tipo_report1=$dizionario['stampe']['per_partenze'];
elseif ($post_tipo_report==1)
$tipo_report1=$dizionario['stampe']['per_arrivi'];
$rep=new CReporTool();

$q="SELECT * from RT_ViewElencoPrenotazione where OdcIdRef=$user->OdcId and CorsaDataPartenza='$post_dal_format' and (PrenStatoId=1 or PrenStatoId=3)";
if (isset($post_linea_id) and ($post_linea_id>0) )
    $q.="and LineaId=$post_linea_id ";

//echo($q);
if ($post_tipo_report==1)
$q.="order by LineaNome,CorsaNome,ComuneSalita,FermataSalita,ComuneDiscesa,FermataDiscesa,ClienteNome asc";
elseif ($post_tipo_report==2)
 $q.="order by LineaNome,CorsaNome,ComuneDiscesa,FermataDiscesa,ComuneSalita,FermataDiscesa,ClienteNome asc";   
//$q.="order by DataFattura,RagioneSociale,Comune asc";

$rep->SetQuery($q);
$rep->AddGroupingField('CorsaNome','CorsaNome ','Corsa: ',$dizionario['stampe']['tot_percorso'].' %name%');

if ($post_tipo_report==1)
{
    $rep->AddGroupingField('ComuneSalita','ComuneSalita ','ComuneSalita: ',$dizionario['stampe']['partenze_da'].' %name%');
$rep->AddGroupingField('FermataSalita','FermataSalita ','FermataSalita: ',$dizionario['stampe']['partenze_da'].' %name%');

    
}
elseif ($post_tipo_report==2)
    {
   $rep->AddGroupingField('ComuneDiscesa','ComuneDiscesa ','ComuneDiscesa: ',$dizionario['stampe']['arrivi'].' %name%');
   $rep->AddGroupingField('FermataDiscesa','FermataDiscesa ','FermataDiscesa: ',$dizionario['stampe']['arrivi'].' %name%');
 
}

//$rep->AddGroupingField('TotalePostiPrenotati','TotalePostiPrenotati ','TotalePostiPrenotati: ','Tot. Incassato il %name%');

//$rep->AddGroupingField('PercorsoNome','Servizio linea ','Servizio linea: ','Tot. Incassato %name%');

//$rep->AddGroupingField('TipoServizio','TipoServizio ','Tipo Servizio: ','Tot. Incassato %name%');
//if ((!isset($post_gestore_id)) or (!($post_gestore_id>=0)))
//$rep->AddGroupingField('RagioneSociale','RagioneSociale ','Gestore: ','Totale Agenzia: %name%');
//if ((!isset($post_sede_id)) or (!($post_sede_id>=0)))
//$rep->AddGroupingField('CorsaNome','CorsaNome ','CorsaNome: ','Totale Rivendita: %name%');

$rep->AddField('CorsaNome','CorsaNome');
if ($post_tipo_report==1)
{
$rep->AddField('ComuneSalita','ComuneSalita');
$rep->AddField('ComuneDiscesa','ComuneDiscesa');
    
}
    
    
elseif ($post_tipo_report==2)
{
    $rep->AddField('ComuneDiscesa','ComuneDiscesa');
    $rep->AddField('FermataDiscesa','FermataDiscesa');
    $rep->AddField('ComuneSalita','ComuneSalita');
    $rep->AddField('FermataSalita','FermataSalita');

    
}


$rep->AddField('ClienteNome','ClienteNome');
$rep->AddField('TotalePostiPrenotati','Pax');
$rep->AddField('TotalePostiPrenotati','TotalePostiPrenotati',1,'','i');
//if ((!isset($post_gestore_id)) or (!$rep->AddField('ClienteNome','ClienteNome');($post_gestore_id>=0)))
//$rep->AddField('RagioneSociale','Agenzia');
//if ((!isset($post_sede_id)) or (!($post_sede_id>=0)))
//$rep->AddField('CodiceSede','Rivendita');
//$rep->AddField('PercorsoNome','Percorso');
//$rep->AddField('Incassato','Incassato (&euro;)',1,'','money');
//$rep->AddField('ProvvigioneAgenzia','ProvvigioneNetta (&euro;)',1,'','money');

$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');

$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter

$rep->SetSummary('<strong>'.$dizionario['stampe']['num_passeggeri'].' <strong> ');





$gestore=$dizionario['stampe']['tutti'];
$sede=$dizionario['stampe']['tutte'];

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


$titolo_report=$dizionario['stampe']['stampe_servizio']."<br </>";
$titolo_report.="<br />".$dizionario['stampe']['tipo_report']." ".$tipo_report1;
$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;





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