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

$ModuloId=1;



function show_list()
{
global $user,$HtmlCommon, $dizionario;
$HtmlCommon->html_titolo_pagina("Elenco Partenze");
$HtmlCommon->html_titolo_box("Elenco Partenze");
$db= new Database();
$db->connect();

$gestore=new Gestore();
$gestore->conn=$db;
$gestorefigli=$gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli=implode(",", $gestorefigli);


?>

<div>
        
    
<link rel='stylesheet' type='text/css' href='/css/cupertino/theme.css' />            
<link rel='stylesheet' type='text/css' href='/fullcalendar/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='/fullcalendar/fullcalendar.print.css' media='print' />
<script type='text/javascript' src='/js/jquery-ui-1.8.13.custom/js/jquery-1.5.1.min.js'></script>
<script type='text/javascript' src='/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js'></script>
<script type='text/javascript' src='/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript'>

	$(document).ready(function() {
	
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
		
		$('#calendar').fullCalendar({
			theme: true,
                        firstDay:1,
                        allDaySlot:false,
                       
                        height: 600,
                        defaultView:'basicWeek',
                      
                       monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
					   monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					   dayNames : [<?=$dizionario['generale']['nome_giorni']?>],             
 					   dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					 titleFormat : {
					    month: 'MMMM yyyy',                             // September 2009
					    week: "d[ yyyy]{ '&#8212;'[ MMM] d -  MMMM yyyy}", // Sep 7 - 13 2009
					    day: 'dddd, d MMMM  yyyy'                  // Tuesday, Sep 8, 2009
					},
 
  timeFormat: 'H:mm',
  columnFormat:{
    month: 'ddd',    // Mon
    week: 'ddd d/M', // Mon 9/7
    day: 'dddd d/M'  // Monday 9/7
},
      
 
                  header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,basicWeek,basicDay'
			},
                        
                  buttonText: {
		
		today: '<?=$dizionario['generale']['oggi']?>',
		month: '<?=$dizionario['generale']['mese']?>',
		week: '<?=$dizionario['generale']['settimana']?>',
		day: '<?=$dizionario['generale']['giorno']?>'
                    },      
                        
                      
			//editable: true,
		
			editable: false,
			
			events: "/protected/modules/agenda/json-events.php",
			
			eventDrop: function(event, delta) {
				alert(event.title + ' was moved ' + delta + ' days\n' +
					'(should probably update your database)');
			},
                        
                         eventClick: function(event) {
        if (event.id) {
            loadMainContentFromMenu('mediazione','mediazione.php?do=edit&MediazioneId='+event.id,'1');
            
            return false;
        }
    },
			
			loading: function(bool) {
				if (bool) $('#loading').show();
				else $('#loading').hide();
			}
                        
                        
                        
			
		});
		
	});

</script>
<style type='text/css'>

	body {
		
		text-align: center;
		font-size: 13px;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		}

	
		
	#loading {
		position: absolute;
		top: 5px;
		right: 5px;
		}

	#calendar {
		width: auto;
		margin: 0 auto;
		}

</style>


<div id='loading' style='display:none'>loading...</div>
<div id='calendar'></div>
    </div>
    
    

    
    


           

<?
   
}



if(is_object($user)) {
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
    if (!empty($_REQUEST)){
		$do = $_REQUEST['do'];
	} else {
		$do='';
	}
		
		
			switch($do) {
                                
                              

				default:
		                show_list();    
                		break;
			}
		

	

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>