<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 


$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;

include_once($classespath_."/class.Sede.php");


$ModuloId=28;



function create() {
	global $user;
	$db= new Database();
	$db->connect();
	$storico=new StoricoOperazioni();
	$storico->conn=$db;
    // prelevo i dati del form ed aggiorno tutte le proprietà dell'oggetto

	$data = $_POST['Tratta'];
	$data = $storico->operazioni_insert($data,$user);

	$lastidA = $db->insert("RT_Tratta", $data);
	if ($lastidA != false) { 
		echo("ok".",".$lastidA);    
	} else {
		echo("no");
	}
	exit();   
}

function update($id) {
	global $user;

	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$data=$_POST['Tratta'];
	$data = $storico->operazioni_update($data,$user);

	$result = $db->update("RT_Tratta", $data, "TrattaId=".$id." and OdcIdRef=$user->OdcId");

	if ($result) {
		echo("ok");
	} else {
		echo("no");
	}
	
	exit();    
}

function updateFermate() {
	global $user;

	$db = new Database();
	$db->connect();
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$fermate = $_POST['Fermata'];
	$fermataNome = $_POST['FermataNome'];
	
	foreach ($fermate as $f){
		$dataF['FermataNome'] = $fermataNome;
		$dataF = $storico->operazioni_update($dataF,$user);
		$result = $db->update("RT_Fermata", $dataF, "FermataId = ".$f);
	}
	if ($result) {
		echo("ok");
	} else {
		echo("no");
	}

	exit();
}


function getFermateComune($id)
{
	global $user, $dizionario;

	$db= new Database();
	$db->connect();

	$sql = "select l.LineaNome, t.TrattaNome, f.FermataId, f.FermataNome from RT_Fermata f
			left join Comune c on f.ComuneId = c.ComuneId
			left join RT_Tratta t on t.TrattaId = f.TrattaId
			left join RT_Linea l on l.LineaId = t.LineaId
			where c.ComuneId = $id and t.TrattaId is not null";

	$fermate = $db->fetch_array($sql);
	?>
	<table id="brain_datatables" class="display">
		<thead>
			<tr class="brain_tabellaTr">
				<th></th>
				<th><?php echo $dizionario['generale']['fermata'];?></th>
				<th><?php echo $dizionario['generale']['tratta'];?></th>
				<th><?php echo $dizionario['generale']['linea'];?></th>
			</tr>
		</thead>
		<tbody>
	<?php 
	if(count($fermate) > 0) {
		foreach ($fermate as $f){ ?>
			<tr>
				<td>
					<input type="checkbox" alt="<?php echo $f['FermataNome'];?>"
						name="Fermata[<?php echo $f['FermataId'];?>]"
						value="<?php echo $f['FermataId'];?>">	
				</td>
				<td>
					<?php echo $f['FermataNome'];?>
				</td>
				<td>
					<?php echo $f['TrattaNome'];?>
				</td>
				<td>
					<?php echo $f['LineaNome'];?>
				</td>
			</tr>
		<?php } 
	} else { ?>
		<tr>
			<td colspan = '4'>
				<i><?php echo $dizionario['tratta']['no_fermate'] ?></i>
			</td>
		</tr>
	<?php } ?>
		
		</tbody>
	</table>
	<?php
	
	exit();

}

function elimina_tratta()
{
	global $user,$tratta_wizard,$db;
	$storico=new StoricoOperazioni();
	$storico->conn=$db;

	$trattaId = $_GET['TrattaId'];

	$data['Stato'] = 0;
	$data['Cancella'] = 1;
	$data=$storico->operazioni_update($data,$user);

	$res=$db->update('RT_Tratta', $data, "TrattaId=".$trattaId);
	echo json_encode(array('result'=>true));
}

if(is_object($user)) {
$db= new Database();
$db->connect();
$user->conn=$db;
$permessi=$user->get_permessi_modulo($ModuloId);
if (sizeof($permessi)>0)
{   
	
	
		if (!empty($_POST))
		{
			switch($_POST['action']) {
				
				case "create":
                                 
                                 $FunzioneId=2;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                  if (sizeof($permesso))
                                   create();   
                                  else
                                    Errors::$ErrorePermessiModuloFunzione;
                                
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
					
					
				break;
				
				case "del":
				$FunzioneId=3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;
				
				case "update":
                               
				 $FunzioneId=4;
                                 $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                        if (sizeof($permesso))
                                           update($_POST['idpost']);    
                                        else
                                            Errors::$ErrorePermessiModuloFunzione;    
                                
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 					
				break;
				
				case "getFermateComune":
					 
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						getFermateComune($_POST['ComuneId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
				
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
				case "updateFermate":
				
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						updateFermate();
					else
						Errors::$ErrorePermessiModuloFunzione;
				
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
					
					
					
                }
              } else {
              	switch($_GET['action']) {
              	
              		case "CancellaTratta":
              			$FunzioneId=4;
              			$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
              			if (sizeof($permesso))
              				elimina_tratta();
              			else
              				echo("no");
              			break;
              	}
              }
	} // end verifica permessi
	else {
            Errors::$ErrorePermessiModulo;
            
        }

}

// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>