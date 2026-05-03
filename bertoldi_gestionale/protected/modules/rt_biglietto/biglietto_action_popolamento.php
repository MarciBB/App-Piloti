<?php
$basepath = $_SERVER ['DOCUMENT_ROOT'];
include_once ($basepath . "/main_include.php");
$config = new Config ();
$run = $config->load ();

$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
include_once ($classespath_ . "class.Gestore.php");
include_once ($classespath_ . "class.Form.php");
include_once ($classespath_ . "/class.Sede.php");
include_once ($classespath_ . "/class.Fermata.php");
include_once ($classespath_ . "/class.Tratta.php");
include_once ($classespath_ . "/class.Prenotazione.php");
include_once ($classespath_ . "/class.Corsa.php");
include_once ($classespath_ . "/class.Linea.php");
include_once ($classespath_ . "/class.Percorso.php");
include_once ($classespath_ . "/class.Orario.php");
include_once ($classespath_ . "/class.Listino.php");
include_once ($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_."class.DT.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "/class.PrenotazioneMovimento.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");


$ModuloId = 2;

global $prenotazione_wizard;
$funzione_edit = false;
$prenotazione_wizard = null;

if (isset ( $_SESSION ['PRENOTAZIONE_WIZARD'] )) {
	$prenotazione_wizard = unserialize ( $_SESSION ['PRENOTAZIONE_WIZARD'] );
}


function RimborsoParziale()
{
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);
    
    global $user, $prenotazione_wizard;
    $db = new Database();
    $db->connect();
    
    $storico = new StoricoOperazioni();
    $storico->conn=$db;
    
    $dt = new DT();
    
    $PrenotazioneNumeroId = $_POST['PrenotazioneNumeroId'];
	$Tragitto = $_POST['Tragitto'];
	$ImportoRimborsabile = floatval(str_replace(',', '.', str_replace('.', '', $_POST['ValoreRimborso'])));
	$ImportoRimborsabile = $ImportoRimborsabile*(-1);
	$TipoMovimento = $_POST['TipoMovimento'];
	$PagamentoTipoId = $_POST['PagamentoTipoId'];
	$DataRimborso = $_POST['DataRimborso'] . " 0:0:0";
	$DataRimborso = $dt->format($DataRimborso, "d/m/Y H:i:s", "Y-m-d");
	
	$dup = null;
	$dup['Escludi'] = 1;
 	$dup = $storico->operazioni_update($dup,$user);

	if ($Tragitto == '0')
		$db->update("RT_PrenotazioneDettaglio", $dup,"PrenotazioneNumero=$PrenotazioneNumeroId");
	else
		$db->update("RT_PrenotazioneDettaglio", $dup,"PrenotazioneNumero=$PrenotazioneNumeroId and Tragitto='$Tragitto'");

	$sql = "select PrenotazioneId, TipologiaBigliettoId, PasseggeroId from RT_PrenotazioneNumero where PrenotazioneNumeroId=$PrenotazioneNumeroId";
	$row1 = $db->query_first($sql);
  
	$PrenotazioneId = $row1['PrenotazioneId'];
    if ($PrenotazioneId > 0)
    {
        $TipologiaBigliettoId = $row1['TipologiaBigliettoId'];
        $PasseggeroId = $row1['PasseggeroId'];
        
        $dup = null;
        $dup['PrenotazioneId'] = $PrenotazioneId;
        $dup['TipologiaBigliettoId'] = $TipologiaBigliettoId;
        $dup['PasseggeroId'] = $PasseggeroId;
        $dup = $storico->operazioni_insert($dup,$user);
        $NuovaPrenotazioneNumero = $db->insert("RT_PrenotazioneNumero", $dup);
        
        $sql = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneNumeroId = $PrenotazioneNumeroId";
        $titolo = $db->query_first($sql);
        
        $movimento = array();
        $movimento['PrenotazioneId'] = $PrenotazioneId;
        $movimento['TipoMovimento'] = $TipoMovimento;
        $movimento['PagamentoTipoId'] = $PagamentoTipoId;
        $CausaleTragitto = ($Tragitto == '0')? 'Andata e Ritorno' : $Tragitto;
        $movimento['Causale'] = "Rimborso titolo ". $titolo['Codice'] . " tragitto " . $CausaleTragitto;
        $movimento['Data'] = $DataRimborso;
        $movimento['Importo'] = $ImportoRimborsabile;
        $movimento['Supplemento'] = 0;
        $movimento['DataPagamento'] = $DataRimborso;
        $movimento['ImportoPagato'] = $ImportoRimborsabile;
        $movimento['Scadenza'] = 'NULL';
        $movimento['CodicePagamento'] = 'NULL';
        $movimento['CanalePagamentoId'] = 'NULL';
        $movimento = $storico->operazioni_insert($movimento, $user);
        $db->insert("RT_PrenotazioneMovimento", $movimento);
    }

	$sql="select * from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId ";
	if ($Tragitto != '0')
    	$sql.=" and Tragitto='$Tragitto'";

	$sql.=" order by TipoServizio desc";

	$ArrObject = $db->fetch_array($sql);
	$x=sizeof($ArrObject); 
	$y=0;

	$ImportoRimborsabilePerTratta=$ImportoRimborsabile;
	$ImportoResiduo=$ImportoRimborsabile;
	$arrTragitto[$Tragitto]=$ImportoResiduo;
	if ($Tragitto=='0')
	{
	    $arrTragitto['Andata']=$ImportoResiduo/2;
	    $arrTragitto['Ritorno']=$ImportoResiduo/2;
	    $ImportoRimborsabilePerTratta=$ImportoRimborsabile/2;
	}

	while($y < $x)
	{
	    $row=null;
	    $row=$ArrObject[$y];
	    $TipoServizio=$row['TipoServizio'];
	    $CorsaId=$row['CorsaInizioItinerario'];
	    if ($TipoServizio=='Bus')
    	{
	        //$CorsaId=$row['CorsaInizioItinerario'];
	        $DataInizio=$row['DataInizioItinerario'];
	        $PrenotazioneId=$row['PrenotazioneId'];
	        
	        $sql="Select PrenotazionePercorsoId,PasseggeriEsclusi from RT_PrenotazionePercorso where PrenotazioneId=$PrenotazioneId and CorsaDataPartenza='$DataInizio' and CorsaId=$CorsaId";
	        $row1=$db->query_first($sql);
	        $esclusi=$row1['PasseggeriEsclusi']+1;
	        $PrenotazionePercorsoId=$row1['PrenotazionePercorsoId'];
	        
	        $dup=null;
	        $dup['PasseggeriEsclusi']=$esclusi;
	        $dup=$storico->operazioni_update($dup, $user);
	        $db->update("RT_PrenotazionePercorso",$dup,"PrenotazionePercorsoId=$PrenotazionePercorsoId");
    	}
    
	    $row['PrenotazioneDettaglioId']=null;
	    unset($row['PrenotazioneDettaglioId']);
	    $row['PrenotazioneNumero']=$NuovaPrenotazioneNumero;
	    $ImportoOldPrenotazione=$row['Importo'];
	    $TragittoCorrente=$row['Tragitto'];
	    $row['Importo']=$ImportoRimborsabilePerTratta;
	    $Venduto=$row['Importo'];
	    $s="select LineaId from RT_Corsa where CorsaId=$CorsaId";
	    $rows=$db->query_first($s);
	  
	    $LineaId=$rows['LineaId'];
	    $sql33="select * from RT_GestoreProvvigioneDettaglio where GestoreId=$user->GestoreId and LineaId=$LineaId";
                
                  $row33=$db->query_first($sql33);
                  $PercAge=0;
                  $FissoAge=0;
                  if ($row33['GestoreConvenzioneId']>0)
                  {
                      $PercAge=$row33['Percentuale'];
                      $FissoAge=$row33['Fisso'];   
                  }
    
	    $ImportoBase = $Venduto*(-1);
	    $ImportoAgenziaNetto=number_format($ImportoBase*($PercAge/100)+$FissoAge,4);
	    
	    /*$row['ImportoAgenzia']=$ImportoAgenziaNetto;
	    $row['PercentualeAgenzia']=$PercAge;
	    $row['FissoAgenzia']=$FissoAge;
	    $row['AliquotaBiglietto']=$aliquota_b;
	    $row['AliquotaProvvigione']=$aliquota_p;  
	    $row['DaBonificare']=$DaBonificare; 
	    $row['DaFatturare']=$DaFatturare; */
	     
	    if ($user->GestoreId==1)
	    {
	        $row['DaBonificare']=0; 
	        $row['DaFatturare']=0; 
	    }
	    
	    $row['Rimborso']=1;
	    
	   	$row=$storico->operazioni_insert($row,$user);
	    $db->insert("RT_PrenotazioneDettaglio", $row);
	    $del=$db->delete("RT_PrenotazionePosto","PrenotazioneNumeroId=$PrenotazioneNumeroId and CorsaId=$CorsaId");
	    
	    $y++;
	}

	$PrenotazioneObj = new Prenotazione($PrenotazioneId);
	$PrenotazioneObj->conn = $db;
	$PrenotazioneObj->EmettiBigliettiRimborso($NuovaPrenotazioneNumero,$ImportoRimborsabile);
  
    $modifica = 0;
    echo ("ok" . '_' . $modifica . '_' . $PrenotazioneId . '_' . 3 . '_' . $CorsaId);
}

function GetImportoMassimoRimborsabile() {
	global $user,$prenotazione_wizard;
	
	$db= new Database();
	$db->connect();
	
	$Tragitto = $_POST['Tragitto'];
	$PrenotazioneNumeroId = $_POST['PrenotazioneNumeroId'];
	
	$sql = "SELECT SUM(Importo) Importo, PrenotazioneId FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero = $PrenotazioneNumeroId";
	if ($Tragitto != '0') {
		$sql .= " AND Tragitto = '$Tragitto'";
	}

	$result = $db->query_first($sql);
	$Importo = $result['Importo'];
	
	$sql = "SELECT TotaleResiduo, TotalePaxPrenotati FROM RT_Prenotazione WHERE PrenotazioneId = " . $result['PrenotazioneId'];
	
	$result = $db->query_first($sql);
	$Residuo = $result['TotaleResiduo'] / $result['TotalePaxPrenotati'];
	
	$MaxRimborsabile = $Importo - $Residuo;
	$MaxRimborsabile = number_format($MaxRimborsabile, 2, ",", ".");
	echo json_encode($MaxRimborsabile);
}

function CalcolaPrezzo() {
	global $user, $HtmlCommon;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();
	$prenotazione = new Prenotazione ();
	$prenotazione->conn = $db;

	$prezzo_finale = $prenotazione->CalcolaPrezzo ( null, null, null );

	$db->close();
}

function MostraSchemaBus() {
	global $user, $HtmlCommon, $prenotazione_wizard;
	;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();

	$CorsaId = $_REQUEST ['CorsaId'];
	$Data = $_REQUEST ['Data'];
	$CorsaIdA = $_REQUEST ['CorsaAndataId'];
	$CorsaIdR = $_REQUEST ['CorsaRitornoId'];
	$DataCorsaAndata = $_REQUEST ['DataAndata'];
	$DataCorsaRitorno = $_REQUEST ['DataRitorno'];
	$tipoviaggio = $_REQUEST ['TipoViaggio'];
	$PrenotazioneId = 0;
	if (is_object ( ($prenotazione_wizard) )) {
		$PrenotazioneId = $prenotazione_wizard->Id;
	}

	?>




<script>
	$(function() {
		$( "#tabs_posti" ).tabs();

});
</script>



<div id="tabs_posti">
	<ul>
		<li class="tab_chiudi"><a href="#tabs-100">Chiudi</a></li>
		<li><a href="#tabs-101">Preferenza posto andata</a></li>
		<li><a href="#tabs-102">Preferenza posto ritorno</a></li>
	</ul>
	<div id="tabs-100"></div>

 <?
	$f = 0;
	$riservati_a = 0;
	$riservati_r = 0;
	while ( $f < $tipoviaggio ) {

		$d = $f + 1;
		$postodiar = "Andata";
		if ($f == 1)
			$postodiar = "Ritorno";

		?>

<div id="tabs-10<?=$d?>">
		<div class="disposizionepiani">
			<!-- LEGENDA -->
			<table class="legenda">
				<tbody>
					<tr>
						<td colspan="2"><h2>Legenda</h2></td>
					</tr>
					<tr>
						<td class="legenda_simbolo"><img src="../images/empty_seat.png"
							alt="Posto Libero" /></td>
						<td class="legenda_testo">
							<p>Posto Libero</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo"><img src="../images/taken_seat.png"
							alt="Posto Prenotato" /></td>
						<td class="legenda_testo">
							<p>Posto Prenotato</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo"><img src="../images/male_seat.png"
							alt="Posto Uomo" /></td>
						<td class="legenda_testo">
							<p>Posto Occupato Maschile</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo"><img src="../images/girl_seat.png"
							alt="Posto Donna" /></td>
						<td class="legenda_testo">
							<p>Posto Occupato Femminile</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo"><img src="../images/not_seat.png"
							alt="Posto Occupato" /></td>
						<td class="legenda_testo">
							<p>Posto Occupato Non Specificato</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo">n.p.</td>
						<td class="legenda_testo">
							<p>Nessuna Preferenza</p>
						</td>
					</tr>
					<tr>
						<td class="legenda_simbolo">p.i.</td>
						<td class="legenda_testo">
							<p>Piano Inferiore</p>
						</td>
					</tr>
				</tbody>
			</table>
			<!-- FINE LEGENDA -->
			<h2>
				<span class="brain_colorh2">disposizione dei posti a sedere per la corsa di <?=$postodiar?></span>
			</h2>
            <?
		$cor = new Corsa ( $CorsaIdA );
		$cor->conn = $db;
		$cor->inizializzaDatiGenerali ();
		$arr_cor = $cor->DatiGenerali;
		$TipologiaBusId = $arr_cor ['TipologiaBusDefaultId'];

		$tb = new TipologiaBus ( $TipologiaBusId );
		$tb->conn = $db;
		$tb->inizializzaDatiGenerali ();
		$arr_tb = $tb->DatiGenerali;

		$npiani = 1;
		$NumeroPiani = $arr_tb ['NumeroPiani'];
		$NumeroColonne = $arr_tb ['Colonne'];
		$NumeroRighe = $arr_tb ['Righe'];
		// $TipologiaBusId=3;

		while ( $npiani <= $NumeroPiani ) {
			?>
                <div style="width: 20%; float: both;"
				class="disposizionepiani">
				<h2>Disposizione posti piano <?=$npiani?></h2>
				<table style="width: 97%;" cellspacing="0" cellpadding="0"
					border="0" width="100%" id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td></td>
                             <?
			$i = 0;
			$alphabet = array (
					'A',
					'B',
					'C',
					'D',
					'E',
					'F',
					'G',
					'H',
					'I',
					'J',
					'K',
					'L',
					'M',
					'N',
					'O',
					'P',
					'Q',
					'R',
					'S',
					'T',
					'U',
					'V',
					'W',
					'X',
					'Y',
					'Z'
			);
			while ( $i < $NumeroColonne ) {
				?>
                             <td><?=$alphabet[$i]?> </td>
                            <?
				$i ++;
			}
			?>
                                    </tr>
                       <?

			$i = 0;

			while ( $i < $NumeroRighe ) {

				?>
                             <tr>
							<td><?=$i+1?></td>
                             <?
				$n = 0;
				while ( $n < $NumeroColonne ) {
					// $BigliettoId=$ArrObjectTB[$n]['TipologiaBigliettoId'];
					$fisso = "";
					$percentuale = "";
					$rigacorrente = $i + 1;
					$colonnacorrente = $n + 1;
					$sql = "Select * from RT_TipologiaBusDettaglioPosto where NumeroPiano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and TipologiaBusId=$TipologiaBusId and OdcIdRef=$user->OdcId";
					// echo($sql);
					$row1 = $db->query_first ( $sql );
					$NumeroPosto = "";
					$DescrizionePosto = "";
					if (! empty ( $row1 ['TipologiaBusId'] )) {
						$NumeroPosto = $row1 ['NumeroPosto'];
						$DescrizionePosto = $row1 ['DescrizionePosto'];
					}

					// echo($sql);
					$CorsaId = $CorsaIdA;
					$Data = $DataCorsaAndata;
					$st = "A";
					if ($f == 1) {
						$st = "R";
						$CorsaId = $CorsaIdR;
						$Data = $DataCorsaRitorno;
					}

					?>
                              <td>

                              <?

					$sql = "Select * from RT_ViewCorsaDataElencoPostiPrenotati where TipoPrenotazione=1 and Piano=$npiani and Riga=$rigacorrente and Colonna=$colonnacorrente and CorsaId=$CorsaId and CorsaDataPartenza='$Data' and OdcIdRef=$user->OdcId";
					// echo($sql);

					$row2 = $db->query_first ( $sql );
					$isReserved = 0;
					$Pid = 0;
					$Preferenza = 0;
					$TipoPrenotazione = 0;
					$ClienteSessoId = 0;
					$clienteNome = "";
					if (! empty ( $row2 ['OdcIdRef'] )) {

						// print_r($row2);
						$isReserved = 1;
						$Preferenza = $row2 ['PreferenzaPiano'];
						$Pid = $row2 ['PrenotazioneId'];
						$TipoPrenotazione = $row2 ['TipoPrenotazione'];
						$ClienteSessoId = $row2 ['ClienteSessoId'];
						$clienteNome = $row2 ['ClienteNome'];
					}

					if (! $isReserved) {

						if ($NumeroPosto > 0) {

							// echo($DescrizionePosto);
							?>
                                  	  <center>
									<input class="stile_sedile"
										onclick="javascript:ScegliPosto(this,'<?=strtolower($postodiar)?>');"
										type="checkbox" alt="<?=$DescrizionePosto?>"
										name="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"
										value="<?=$NumeroPosto?>"><label
										for="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"></label>
									<br /> <br />
                                         <? if (($rigacorrente>0) and ($rigacorrente<=5)) { ?>
                                          <select
										name="PostoScelto<?=$st?>I[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]">
										<option value="0">n.p.</option>
										<option value="1">p.i.</option>
									</select>
                                          <?

} else {
								?>
                                          <input type="hidden"
										name="PostoSceltoI[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"
										value="0" />
                                            <?
							}
							?>

                                           </center>



                               <?

}
					}

					else {

						if ($PrenotazioneId > 0) {

							if ($PrenotazioneId == $Pid) 							// prenotazione corrente
							{
								if ($f == 0)
									$riservati_a ++;
								else
									$riservati_r ++;

								?>
                                          <center>
									<input checked class="stile_sedile"
										onclick="javascript:ScegliPosto(this,'<?=strtolower($postodiar)?>');"
										type="checkbox" alt="<?=$DescrizionePosto?>"
										name="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"
										value="<?=$NumeroPosto?>"><label
										for="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"></label>
									<br /> <br /> <select
										name="PostoScelto<?=$st?>I[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]">
										<option <? if ($Preferenza==0) echo ("selected")?> value="0">n.p.</option>
										<option <? if ($Preferenza==1) echo ("selected")?> value="1">p.i.</option>
									</select>
								</center> <span class="nome_cliente"><?=$clienteNome?></span>
                                 <?
							} else {
								if ($TipoPrenotazione == 1) {
									if ($ClienteSessoId == 1)
										print ("<center><strong><img src='../images/male_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
									elseif ($ClienteSessoId == 2)
										print ("<center><strong><img src='../images/girl_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
									else
										print ("<center><strong><img src='../images/not_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
								}

								/*
								 * else { ?> <center> <input checked class="stile_sedile" onclick="javascript:ScegliPosto(this,'<?=strtolower($postodiar)?>');" type="checkbox" alt="<?=$DescrizionePosto?>" name="PostoScelto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]" value="<?=$NumeroPosto?>"><label for="PostoScelto[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"></label> <br /> <br /> <select name="PostoSceltoI[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"> <option <? if ($Preferenza==0) echo ("selected")?> value="0">n.p.</option> <option <? if ($Preferenza==1) echo ("selected")?> value="1">p.i.</option> </select> </center> <span class="nome_cliente">1111<?=$clienteNome?></span> <? }
								 */
							}
						} else {
							if ($TipoPrenotazione == 1) {
								if ($ClienteSessoId == 1)
									print ("<center><strong><img src='../images/male_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
								elseif ($ClienteSessoId == 2)
									print ("<center><strong><img src='../images/girl_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
								else
									print ("<center><strong><img src='../images/not_seat.png' alt='Posto Occupato'/></strong><span class='nome_cliente'>" . $clienteNome . "</span></center>") ;
							} else {
								?>
                                          <center>
									<input checked class="stile_sedile"
										onclick="javascript:ScegliPosto(this,'<?=strtolower($postodiar)?>');"
										type="checkbox" alt="<?=$DescrizionePosto?>"
										name="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"
										value="<?=$NumeroPosto?>"><label
										for="PostoScelto<?=$st?>[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]"></label>
									<br /> <br /> <select
										name="PostoScelto<?=$st?>I[<?=$npiani."_".$rigacorrente."_".$colonnacorrente."_".$CorsaId."_".$TipologiaBusId?>]">
										<option <? if ($Preferenza==0) echo ("selected")?> value="0">n.p.</option>
										<option <? if ($Preferenza==1) echo ("selected")?> value="1">p.i.</option>
									</select>
								</center> <span class="nome_cliente">Fabio Della Selva</span>
                                              <?
							}
						}
					}

					?>
                              </td>


                              <?

					$n ++;
				}
				?>



                             </tr>
                            <?
				$i ++;
			}

			?>

                                    </tbody>
				</table>
			</div>
			<input id="posti_riservati_a" name="posti_riservati_a"
				value="<?=$riservati_a?>" type="hidden"> <input
				id="posti_riservati_r" name="posti_riservati_r"
				value="<?=$riservati_r?>" type="hidden">


             <?
			$npiani ++;
		}
		?>

</div>
	</div>
     <?
		$f ++;
	}
	?>
        </div>

<?
	exit ();
}

function GetNotePerTratta() {
	global $user, $HtmlCommon, $prenotazione_wizard;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();

	$CorsaIdA = $_REQUEST ['CorsaAndataId'];
	$CorsaIdR = $_REQUEST ['CorsaRitornoId'];

	$Tp = $_REQUEST ['TV'];

	$Nota1A = "";
	$Nota2A = "";
	$Nota3A = "";
	$Nota4A = "";
	$Nota5A = "";
	$Nota1R = "";
	$Nota2R = "";
	$Nota3R = "";
	$Nota4R = "";
	$Nota5R = "";

	if (is_object ( ($prenotazione_wizard) )) {
		$prenotazione_wizard->conn = $db;
		$PrenotazioneId = $prenotazione_wizard->Id;
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$NomeCliente = $DatiGeneraliArr ['ClienteNome'];
		$CellulareCliente = $DatiGeneraliArr ['ClienteCellulare'];
		$SessoIdCliente = $DatiGeneraliArr ['ClienteSessoId'];
		$TipoViaggioId = $DatiGeneraliArr ['TipoViaggioId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( 'A' );
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;

		$PrenotazionePercorsoA = $DatiGeneraliPercorsoArr ['PrenotazionePercorsoId'];

		$TipoNota = 'S';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";

		$row1 = $db->query_first ( $sql );
		$Nota1A = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota1A = $row1 ['Nota'];

		$TipoNota = 'D';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota2A = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota2A = $row1 ['Nota'];

		$TipoNota = 'B';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota3A = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota3A = $row1 ['Nota'];

		$TipoNota = 'P';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota4A = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota4A = $row1 ['Nota'];

		$TipoNota = 'G';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota5A = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota5A = $row1 ['Nota'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( 'R' );
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$PrenotazionePercorsoA = $DatiGeneraliPercorsoArr ['PrenotazionePercorsoId'];

		$TipoNota = 'S';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";

		$row1 = $db->query_first ( $sql );
		$Nota1R = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota1R = $row1 ['Nota'];

		$TipoNota = 'D';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota2R = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota2R = $row1 ['Nota'];

		$TipoNota = 'B';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota3R = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota3R = $row1 ['Nota'];

		$TipoNota = 'P';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota4R = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota4R = $row1 ['Nota'];

		$TipoNota = 'G';
		$sql = "select Nota from RT_PrenotazionePercorsoNote where PrenotazioneId=$PrenotazioneId and PrenotazionePercorsoId=$PrenotazionePercorsoA and TipoNota='$TipoNota'";
		$row1 = $db->query_first ( $sql );
		$Nota5R = "";
		if (! empty ( $row1 ['Nota'] ))
			$Nota5R = $row1 ['Nota'];
	}

	?>

<script>
	$(function() {
		$( "#tabs" ).tabs();
                $( "#tabs1" ).tabs();
	});
	</script>
<div id="BoxNoteAndata">


	<h3>Note per la corsa di Andata</h3>

	<div id="tabs">
		<ul>
			<li><a href="#tabs-10">Salita</a></li>
			<li><a href="#tabs-11">Discesa</a></li>
			<li><a href="#tabs-12">Biglietto</a></li>
			<li><a href="#tabs-13">Posti</a></li>
			<li><a href="#tabs-14">Generiche</a></li>
		</ul>
		<div id="tabs-10">
			<p class="txt_on_txtarea">Note relative alla fermata di salita</p>
			<textarea rows="3" cols="140" id=""
				name="PrenotazioneNote[<?=$CorsaIdA?>_1]"><?=$Nota1A?></textarea>
		</div>
		<div id="tabs-11">
			<p class="txt_on_txtarea">Note relative alla fermata di discesa</p>
			<textarea rows="3" cols="140" id=""
				name="PrenotazioneNote[<?=$CorsaIdA?>_2]"><?=$Nota2A?></textarea>
		</div>
		<div id="tabs-12">
			<p class="txt_on_txtarea">Note relative al biglietto</p>
			<textarea rows="3" cols="140" id=""
				name="PrenotazioneNote[<?=$CorsaIdA?>_3]"><?=$Nota3A?></textarea>
		</div>
		<div id="tabs-13">
			<p class="txt_on_txtarea">Note relative al posto a sedere</p>
			<textarea rows="3" cols="140" id=""
				name="PrenotazioneNote[<?=$CorsaIdA?>_4]"><?=$Nota4A?></textarea>
		</div>
		<div id="tabs-14">
			<p class="txt_on_txtarea">Note generiche</p>
			<textarea rows="3" cols="140" id=""
				name="PrenotazioneNote[<?=$CorsaIdA?>_5]"><?=$Nota5A?></textarea>
		</div>
	</div>

</div>
<!-- End demo -->

	<? if ($Tp==2) { ?>
	<div id="BoxNoteRitorno">
		<h3>Note per la corsa di ritorno</h3>

		<div id="tabs1">
			<ul>
				<li><a href="#tabs-20">Salita</a></li>
				<li><a href="#tabs-21">Discesa</a></li>
				<li><a href="#tabs-22">Biglietto</a></li>
				<li><a href="#tabs-23">Posti</a></li>
				<li><a href="#tabs-24">Generiche</a></li>
			</ul>

			<div id="tabs-20">
				<p class="txt_on_txtarea">Note relative alla fermata di salita</p>
				<textarea rows="3" cols="140" id=""
					name="PrenotazioneNote[<?=$CorsaIdR?>_1]"><?=$Nota1R?></textarea>
			</div>
			<div id="tabs-21">
				<p class="txt_on_txtarea">Note relative alla fermata di discesa</p>
				<textarea rows="3" cols="140" id=""
					name="PrenotazioneNote[<?=$CorsaIdR?>_2]"><?=$Nota2R?></textarea>
			</div>
			<div id="tabs-22">
				<p class="txt_on_txtarea">Note relative al biglietto</p>
				<textarea rows="3" cols="140" id=""
					name="PrenotazioneNote[<?=$CorsaIdR?>_3]"><?=$Nota3R?></textarea>
			</div>
			<div id="tabs-23">
				<p class="txt_on_txtarea">Note relative al posto a sedere</p>
				<textarea rows="3" cols="140" id=""
					name="PrenotazioneNote[<?=$CorsaIdR?>_4]"><?=$Nota4R?></textarea>
			</div>
			<div id="tabs-24">
				<p class="txt_on_txtarea">Note generiche</p>
				<textarea rows="3" cols="140" id=""
					name="PrenotazioneNote[<?=$CorsaIdR?>_5]"><?=$Nota5R?></textarea>
			</div>
		</div>

	</div>

	<?

	}
}

function GetPasseggeri() {
	global $user, $HtmlCommon, $prenotazione_wizard;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();

	$arr_sesso[]= array("SessoId" => '1',"Sesso" => 'Maschio');
	$arr_sesso[]= array("SessoId" => '2',"Sesso" => 'Femmina');
	$arr_sesso[]= array("SessoId" => '3',"Sesso" => 'N.D.');

	$passeggeri = null;

	if (isset($_POST['Passeggeri']) || isset($_POST['PasseggeriInseriti'])) {
		
		foreach ($_POST['PasseggeriInseriti'] as $passeggero) {
			$passeggeri[] = array(
				'TipoBigliettoId' => $passeggero['TipoBigliettoId'],
				'TipologiaBiglietto' => $passeggero['TipologiaBiglietto'],
				'Cognome' => $passeggero['Cognome'],
				'Nome' => $passeggero['Nome'],
				'SessoId' => $passeggero['SessoId'],
				'Eta' => $passeggero['Eta']
			);
		}
		
		$passeggeriGet = $_POST['Passeggeri'];

		foreach ($passeggeriGet as $passeggeroGet) {

			$id_descr = explode("_", $passeggeroGet['TipoId']);

			$count = 0;
			if (!empty($passeggeri)) {
				foreach ($passeggeri as $passeggero) {
					if ($passeggero['TipoBigliettoId'] == $id_descr[0]) $count++;
				}
			}

			for ($i = $count; $i < $passeggeroGet['Qnt']; $i++) {

				$passeggeri[] = array(
					'TipoBigliettoId' => $id_descr[0],
					'TipologiaBiglietto' => $id_descr[1],
					'Cognome' => '',
					'Nome' => '',
					'SessoId' => '',
					'Eta' => ''
				);
			}
		}
	} else {
		if (is_object($prenotazione_wizard)) {
			
			$mod = 1;

			$prenotazione_wizard->conn = $db;
			$prenotazione_wizard->inizializzaDatiGenerali();
			$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

			$PrenotazioneId = $prenotazione_wizard->Id;
			$Stato = $DatiGeneraliArr['PrenotazioneStato'];
			
			$passeggeri = $prenotazione_wizard->GetPasseggeri();
		} else if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];
			
			$prenotazione_wizard = new Prenotazione ();
			$prenotazione_wizard->Id = $prenotazione_multi[0]['PrenotazioneId'];
			$prenotazione_wizard->conn = $db;
			$prenotazione_wizard->inizializzaDatiGenerali();
			$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

			$PrenotazioneId = $prenotazione_wizard->Id;
			$Stato = $DatiGeneraliArr['PrenotazioneStato'];
			
			$passeggeri = $prenotazione_wizard->GetPasseggeri();
		}
	}
	
	if(!isset($Stato) && isset($_POST['StatoPassaggio'])){
		$Stato = $_POST['StatoPassaggio'];
	}

	if (isset($passeggeri)) {
	?>
		<script type="text/javascript">
			var totalePasseggeri = <?=count($passeggeri)?>;
		</script>

		<table width="100%" cellspacing="0" cellpadding="0" border="0" id="pagamentiTabella">
	        <tr class="rowIntestazione">
	           	<td>Biglietto</td>
	            <td>Cognome</td>
	            <td>Nome</td>
	            <td>Sesso</td>
	            <td>Et&agrave;</td>
	            <td>Rimuovi</td>
	   		</tr>
	   		
	   		<?
	   		echo "<input type='hidden' name='StatoPassaggio' id='StatoPassaggio' value='$Stato'>";
	   		foreach ($passeggeri as $index => $passeggero) {
	   		?>
	   			<tr id="riga<?=$index?>" class="rowBianca passeggero">
	           		<td>
	           			<span class="passeggeroBiglietto"><?=$passeggero['TipologiaBiglietto']?></span>
	           			<input class="passeggeroId" type="hidden" value="<?=$passeggero['TipoBigliettoId']?>" id="<?="Passeggeri[$index][TipoBigliettoId]"?>" name="<?="Passeggeri[$index][TipoBigliettoId]"?>">
	           		</td>
		            <th scope="row">
		             	<input class="passeggeroCognome required" type="text" name="Passeggeri[<?=$index?>][Cognome]" id="PasseggeroCognome<?=$index?>" value="<?=$passeggero['Cognome']?>" <?=(isset($Stato) && $Stato == 3)? "readonly" : "";?>>
		            </th>
		            <th scope="row">
		            	<input class="passeggeroNome required" type="text" name="Passeggeri[<?=$index?>][Nome]" id="PasseggeroNome<?=$index?>" value="<?=$passeggero['Nome']?>" <?=(isset($Stato) && $Stato == 3)? "readonly" : "";?>>
		            </th>
		            <th scope="row">
		           	 	<select class="passeggeroSesso required" name="Passeggeri[<?=$index?>][SessoId]" id="PasseggeroSesso<?=$index?>" <?=(isset($Stato) && $Stato == 3)? "readonly" : "";?>>
                           	<option value="">- seleziona -</option>
                            <option value="1" <?=($passeggero['SessoId'] == 1)? 'selected' : ''?>>Maschio</option>
							<option value="2" <?=($passeggero['SessoId'] == 2)? 'selected' : ''?>>Femmina</option>
							<option value="3" <?=($passeggero['SessoId'] == 3)? 'selected' : ''?>>N.D.</option>
                        </select>
		            </th>
		            <th scope="row">
		            	<input class="passeggeroEta required" type="text" name="Passeggeri[<?=$index?>][Eta]" id="PasseggeroEta<?=$index?>" value="<?=$passeggero['Eta']?>" <?=(isset($Stato) && $Stato == 3)? "readonly" : "";?>>
		            	<script type="text/javascript">
		            		$("#PasseggeroEta<?=$index?>").change(function(){
								<?php 
									$sql = "SELECT EtaDa, EtaA FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId=".$passeggero['TipoBigliettoId'];
									$rowEta = $db->query_first($sql);
								?>
								var etaInserita = $("#PasseggeroEta<?=$index?>").val();
								if(etaInserita < <?=$rowEta['EtaDa']?> || etaInserita > <?=$rowEta['EtaA']?>){
									alert("L'et\u00E1 deve essere compresa tra <?=$rowEta['EtaDa']?> e <?=$rowEta['EtaA']?> anni");
									$("#PasseggeroEta<?=$index?>").val("");
								}
								
			            	});
		            	</script>
		            </th>
		            <th scope="row">
		            	<?
		            		if (!isset($Stato) || $Stato != 3) {
								
		            		?>
		            			<a href="javascript:rimuoviBiglietto('<?=$passeggero['TipoBigliettoId'] . '_' . $passeggero['TipologiaBiglietto']?>', 'riga<?=$index?>')">rimuovi</a>
		            		<?
		            		}
		            	?>
		            </th>
	    		</tr>
	   		<?
	   		}
	   		?>
	 	</table>
	<?
	} else {
	?>
		<script type="text/javascript">
			var totalePasseggeri = 0;
		</script>
	<?
	}
	?>
<?
}

function AbilitaTipoPrenotazione() {
	global $user, $HtmlCommon;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();
	$CorsaId = $_REQUEST ['CorsaId'];
	$FermataIdAP = $_REQUEST ['FermataIdAP'];

	$prenotazione = new Prenotazione ();
	$prenotazione->conn = $db;
	$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaId, $FermataIdAP );

	echo ($abilitazioneprenotazione);

	exit ();
}

function GetTipologiaBiglietti() {
	global $user, $HtmlCommon, $prenotazione_wizard;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();

	$CorsaId = $_REQUEST ['CorsaId'];
	$FermataIdAP = $_REQUEST ['FermataIdAP'];
	$FermataIdAD = $_REQUEST ['FermataIdAD'];

	$CorsaRitornoId = $_REQUEST ['CorsaRitornoId'];
	$FermataIdRP = $_REQUEST ['FermataIdRP'];
	$FermataIdRD = $_REQUEST ['FermataIdRD'];

	$Data = $_REQUEST ['Data'];
	$TV = $_REQUEST ['TV'];

	$ar = 1;
	$ar1 = 1;
	if ($TV == 1) {
		$ar1 = 0;
		$ar = 0;
	}
	
	$prenotazione = new Prenotazione ();
	$prenotazione->conn = $db;
	$PrenotazioneId = 0;
	
	if (is_object ( ($prenotazione_wizard) )) {
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		
		$PrenotazioneId = $prenotazione_wizard->Id;
		$Stato = $DatiGeneraliArr['PrenotazioneStato'];
		
		$mod = 1;
	} else if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
		$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];
		
		$prenotazione_wizard = null;
		$PrenotazioneId = $prenotazione_multi[0]['PrenotazioneId'];
		$Stato = $DatiGeneraliArr['PrenotazioneStato'];
		
	}
	
	if($mod==1){
		if($Stato==3){
			$readOnlyReduction = "readonly = 'readonly'";
			//$readOnlyReduction = $readonly;
		}else{
			$readOnlyReduction = $readonly;
		}
	}
	
	$arrPrezziBiglietti = $prenotazione->GetTipologiaBigliettiPrezzi(0, $PrenotazioneId, $prenotazione_wizard, $Data, $CorsaId, $FermataIdAP, $FermataIdAD, $CorsaRitornoId, $FermataIdRP, $FermataIdRD, $TV, null, null, null, $PrenotazioneId);
	/*
	 * $PrenotazioneId=0; $mod=0; $DataPartenzaOriginale=null; $DataRitornoOriginale=null; if (is_object(($prenotazione_wizard))) { $prenotazione_wizard->conn=$db; $PrenotazioneId=$prenotazione_wizard->Id; $mod=1; $prenotazione_wizard->inizializzaDatiGenerali(); $DatiGeneraliArr=$prenotazione_wizard->DatiGenerali; $TipoViaggioOriginale=$DatiGeneraliArr['TipoViaggioId']; $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A'); $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso; $DataPartenzaOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza']; if ($TipoViaggioOriginale==2) { $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R'); $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso; $DataRitornoOriginale=$DatiGeneraliPercorsoArr['CorsaDataPartenza']; } } $prenotazione=new Prenotazione(); $prenotazione->conn=$db; $arr_tratte=$prenotazione->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD); if ($CorsaRitornoId>0) $arr_tratter=$prenotazione->GetTratte($CorsaRitornoId, $FermataIdRP, $FermataIdRD); $arr_listini=$prenotazione->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId); if ($CorsaRitornoId>0) $arr_listinir=$prenotazione->GetListini($arr_tratter,$FermataIdRP,$FermataIdRD,$CorsaRitornoId); $readonly=""; if ($user->SedeLegale!=1) $readonly="readonly"; $at=0; while ($at<sizeof($arr_tratte)) { if ($at>0) $tratteid.=",".$arr_tratte[$at]['TrattaId']; else $tratteid=$arr_tratte[$at]['TrattaId']; $at++; } $ar=0; $sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar"; // echo($sql); //if ($user->SedeLegale!=1) //$sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar and (TipologiaBigliettoId<>11 and TipologiaBigliettoId<>12 and TipologiaBigliettoId<>13 and TipologiaBigliettoId<>14)"; //echo($sql); $ArrObject = $db->fetch_array($sql); $i=0; $numerobiglietti=-1;
	 */
        $isAdmin=$user->IsAdmin;
	?>
<div class="brain_rowAll">
	<table width="100%" cellspacing="0" cellpadding="0" border="0"
		id="pagamentiTabella">
		<tbody>
			<tr class="rowIntestazione">
				<th scope="row">n. pax</th>
				<td>biglietto</td>
                                <?php if ($isAdmin) { ?>    
                                <td>Andata</td>
				<td>Ritorno</td>
				<td>Totale</td>
				<td>Sconto A/R (%)</td>
				<td>Var. (%)</td>
                                <?php } ?>
                                <td>importo per pax (&euro;)</td>
				<td>importo proposto (&euro;)</td>
				<td>riduzione (&euro;)</td>
				<td>aumento (&euro;)</td>
				<td>TOT (&euro;)</td>
			</tr>
			
<?
	$x = 0;

	while ( $x < sizeof ( $arrPrezziBiglietti ) ) {

		$BigliettoId = $arrPrezziBiglietti[$x]['BigliettoId'];
		$DescrizioneBiglietto = $arrPrezziBiglietti[$x]['DescrizioneBiglietto'];
		$pax = $arrPrezziBiglietti[$x]['Pax'];
		$PrezzoPax = $arrPrezziBiglietti[$x]['PrezzoPax'];
		$base = $arrPrezziBiglietti[$x]['PrezzoBasePax'];
		$PrezzoAndata = $arrPrezziBiglietti[$x]['PrezzoAndata'];
		$PrezzoRitorno = $arrPrezziBiglietti[$x]['PrezzoRitorno'];
		$riduzione = $arrPrezziBiglietti[$x]['Riduzione'];
		$aumento = $arrPrezziBiglietti[$x]['Aumento'];
		$riduzioneNote = $arrPrezziBiglietti[$x]['RiduzioneNote'];
		$aumentoNote = $arrPrezziBiglietti[$x]['AumentoNote'];
		$PrezzoAndata = $arrPrezziBiglietti[$x]['PrezzoAndata'];
		$ScontoAR = $arrPrezziBiglietti[$x]['ScontoAR'];
		$finale = $arrPrezziBiglietti[$x]['Totale'];
		$PercSconto1 = $arrPrezziBiglietti[$x]['PromozioneSconto'];

		if ($PrezzoPax >= 0) {
			$PrezzoPax_f = number_format( $PrezzoPax, 2, ",", "." );
			$PercSconto_f = number_format( $PercSconto1, 2, ",", "." );
			$finale_f = number_format( $finale, 2, ",", "." );
			$base_f = number_format( $base, 2, ",", "." );
			$riduzione_f = number_format( $riduzione, 2, ",", "." );
			$aumento_f = number_format( $aumento, 2, ",", "." );
			$TotaleAR = $PrezzoAndata + $PrezzoRitorno;
			$PrezzoAndata = number_format( $PrezzoAndata, 2, ",", "." );
			$PrezzoRitorno = number_format( $PrezzoRitorno, 2, ",", "." );
			$TotaleAR = number_format( $TotaleAR, 2, ",", "." );

			$BigliettoPrezzo .= " ( " . $PrezzoPax_f . " &euro; )";

			?>
			
        	<tr class="rowBianca">

				<th scope="row">
					<input id="Pax<?=$x?>"
					onchange="javascript:CalcolaPrezzoTipoBiglietto(this);" 
					onfocus="javascript:SalvaNumeroBiglietti(this);"
					type="text"
					maxlength="3" size="3" value="<?=$pax?>"
					name="BigliettoTipologiaPax[<?=$BigliettoId?>_<?=$DescrizioneBiglietto?>]"
					class="required digits"
					<?=(isset($Stato) && $Stato == 3)? "readonly" : "";?>>
               	<?

					
               		$page->create_textbox_hidden("TipoBigliettoId" . $x, $BigliettoId . "_" . $DescrizioneBiglietto);
					$page->create_textbox_hidden("Prezzo" . $x, $PrezzoPax_f);
					$page->create_textbox_hidden("Totale" . $x, 0);
				?>
	            </th>
				<td id="<?="Biglietto" . $x?>"><?=$DescrizioneBiglietto?></td>
                                <?php if ($isAdmin) { ?>    
				<td><?=$PrezzoAndata?>&euro;</td>
				<td><?=$PrezzoRitorno?>&euro;</td>
				<td><?=$TotaleAR?>&euro;</td>

				<td><?=$ScontoAR?>%</td>
				<td><?=$PercSconto_f?>%</td>
                                <?php  } ?>
				<td><?=$PrezzoPax_f?></td>
				<td id="PrezzoParziale<?=$x?>"><?=$base_f?> &euro; </td>

				<td><input <?=$readOnlyReduction?> id="PrezzoRiduzione<?=$x?>"
					onchange="javascript:CalcolaPrezzoTipoBiglietto(); ShowRiduzioneNote(<?=$x?>, <?=$BigliettoId?>);" type="text"
					maxlength="8" size="8" value="<?=$riduzione_f?>"
					name="BigliettoTipologiaPaxRid[<?=$BigliettoId?>][Valore]"
					class="required numberDE"></td>

				<td><input <?=$readonly?> id="PrezzoAumento<?=$x?>"
					onchange="javascript:CalcolaPrezzoTipoBiglietto(); ShowAumentoNote(<?=$x?>, <?=$BigliettoId?>);" type="text"
					maxlength="8" size="8" value="<?=$aumento_f?>"
					name="BigliettoTipologiaPaxAum[<?=$BigliettoId?>][Valore]"
					class="required numberDE"></td>

				<td id="PrezzoFinale<?=$x?>" class="prezzo_grande"><?=$finale_f?> &euro; </td>



			</tr>
			
			<?
			if ($aumentoNote != '') {
			?>
				<tr id=NoteAumento<?=$x?>>
					<td colspan="12">
						<div class="brain_campiform">
							<label for="BigliettoTipologiaPaxAum[<?=$BigliettoId?>][Note]">Note di aumento:</label>
							<input <?=$readonly?> id="AumentoNote<?=$x?>"
						 	type="text"
							value="<?=$aumentoNote?>"
							name="BigliettoTipologiaPaxAum[<?=$BigliettoId?>][Note]"
							class="">
						</div>
					</td>
				</tr>
			<?
			} else {
			?>
				<tr id=NoteAumento<?=$x?>></tr>
			<?	
			}
			?>
			
			<?
			if ($riduzioneNote != '') {
			?>
				<tr id=NoteRiduzione<?=$x?>>
					<td colspan="12">
						<div class="brain_campiform">
							<label for="BigliettoTipologiaPaxRid[<?=$BigliettoId?>][Note]">Note di riduzione:</label>
							<input <?=$readonly?> id="RiduzioneNote<?=$x?>"
						 	type="text"
							value="<?=$riduzioneNote?>"
							name="BigliettoTipologiaPaxRid[<?=$BigliettoId?>][Note]"
							class="">
						</div>
					</td>
				</tr>
			<?
			} else {
			?>
				<tr id=NoteRiduzione<?=$x?>></tr>
			<?	
			}
			?>
			
		<?

			// $page->create_textbox("Prezzo",$BigliettoPrezzo,"BigliettoTipologiaPax[$stringa]",$PrezzoPax,1,"brain_campiform",array(
			// "class"=>"'required'"));

			$totpax += $pax;
			$totalissimo += $finale;
		}
		$x ++;
	}

	/*
	 * $i++; }
	 */

	$totalissimo_f = number_format ( $totalissimo, 2, ",", "." );
	$page->create_textbox_hidden ( "NumeroBiglietti", $x );
	$page->create_textbox_hidden ( "TotalePax", $totpaxGetTipologiaBigliettiPrezzi );
	$page->create_textbox_hidden ( "TotalePaxScelti", 0 );
	?>

<tr class="rowIntestazione">
				<th scope="row">&nbsp;</th>
				<td></td>
				<?php if ($isAdmin) { ?>    
                                <td><strong></strong></td>
				<td><strong></strong></td>
				<td><strong></strong></td>
				<td><strong></strong></td>
                                <td><strong></strong></td>
                                <?php } ?>
				
				<td><strong></strong></td>
				<td><strong></strong></td>
				<td><strong></strong></td>
				<td><strong></strong></td>
				<td id="PrezzoTotalePax" class="prezzo_grande"><strong><?=$totalissimo_f?> &euro; </strong></td>

			</tr>
		</tbody>
	</table>
</div>
<?
}

function GetFermate() {
	global $user, $HtmlCommon, $prenotazione_wizard;
	$page = new Form ();
	$db = new Database ();
	$db->connect ();

	$CorsaId = $_REQUEST ['CorsaId'];
        $cor=new Corsa($CorsaId);
        $cor->conn=$db;
        $cor->inizializzaDatiGenerali();
        $lineaId=$cor->DatiGenerali['LineaId'];
	$ComuneAndataId = $_REQUEST ['ComuneAndata'];
	$ComuneRitornoId = $_REQUEST ['ComuneRitorno'];
	$DataSelezionataA = $_REQUEST ['DataSelezionataA'];
	
	$Tp = $_REQUEST ['Tp'];
	$TipoFermata = $_REQUEST ['Tf'];

	$fermata = new Fermata ();
	$fermata->conn = $db;
	// get id tratta in base a comune
	
        
        /*$fermateSalita = $fermata->getAllByCorsaComune ( $CorsaId, $ComuneAndataId, "P" );
	$fermateDiscesa = $fermata->getAllByCorsaComune ( $CorsaId, $ComuneRitornoId, "D" );*/
        
        /*$fermateSalita=$arr_fermate;
        $fermateDiscesa=$arr_fermate_d;*/
        $arr_fermate= array();
        $arr_fermate_d= array();
        $prenotazione1=new Prenotazione();
        $prenotazione1->conn=$db;
        $nFermateSalita=0;
        $trattaPartenza = null;
        $trattaArrivo = null;
        
 
//verificare se occorre ricostruire il grafo
        $ComuneSalitaId=0;
        $ComuneDiscesaId=0;
        $CorsaPrenotata=0;
	if (is_object ( ($prenotazione_wizard) )) {
    		$prenotazione_wizard->conn = $db;
                $prenotazione_wizard->inizializzaDatiGeneraliPercorso ( $Tp );
	       $DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
		$ComuneSalitaId=$DatiGeneraliPercorsoArr['ComuneSalitaId'];
		$ComuneDiscesaId=$DatiGeneraliPercorsoArr['ComuneDiscesaId'];
		$CorsaPrenotata=$DatiGeneraliPercorsoArr['CorsaId'];
		
    
	}

	if($Tp == 'A'){
		$TV = 1;
	}else{
		$TV = 2;
	}

	$change = true;
	if(is_object($prenotazione_wizard)){
		$change = $prenotazione_wizard->checkIfChanged($ComuneAndataId, $ComuneRitornoId, $TV, $DataSelezionataA, $CorsaId);
	}
	if (!$change){

		$FermataSalitaId=$DatiGeneraliPercorsoArr['FermataSalitaId'];
		$FermataDiscesaId=$DatiGeneraliPercorsoArr['FermataDiscesaId'];
		
		
				
		$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK where FermataId=$FermataSalitaId ";      
		
		$arr_fermate=$db->fetch_array($sql);
		
		$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE FermataIdDrop=$FermataDiscesaId";      
		
		$arr_fermate_d=$db->fetch_array($sql);
	}else{

		$sql="select * from RT_PercorsoBreve where ComunePickupId=$ComuneAndataId and ComuneDropOffId=$ComuneRitornoId and CorsaId=$CorsaId";     
		$r=$db->query_first($sql);
		if (!empty($r['PercorsoBreveId'])){
		   $trattaPartenza=$r['TrattaPickupId']; 
		   $trattaArrivo=$r['TrattaDropOffId']; 
		}else{
  			$grafo = new GrafoTratte($lineaId, $CorsaId, $db, $ComuneAndataId, $ComuneRitornoId);

			$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);  

			$pre=new Prenotazione();
			$pre->conn=$db;
			$ritorno=$pre-> CreatePercorsoBreve($ComuneAndataId,$ComuneRitornoId,$db,$TrattePercorse,$trattaPartenza,$trattaArrivo,$CorsaId,$lineaId);
		}  

		$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK WHERE Stato=1 and Cancella=0 and IsPickup=1 and  CorsaId=$CorsaId and ComuneId=$ComuneAndataId  and OdcIdRef=$user->OdcId and TrattaId=$trattaPartenza order by TrattaPeso desc ";      		
		$arr_fermate=$db->fetch_array($sql);
		$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE Stato=1 and Cancella=0 and  IsDropOff=1 and  CorsaId=$CorsaId and ComuneId=$ComuneRitornoId  and TrattaId=$trattaArrivo and OdcIdRef=$user->OdcId order by TrattaPeso asc";      
		$arr_fermate_d=$db->fetch_array($sql);

	}



   //   print_r($arr_fermate);
   // Array ( [0] => Array ( [FermataId] => 181 [FermataOrario] => ZOB (02:15:00) - Dortmund - Frankfurt (Siegen) [TrattaNome] => Dortmund - Frankfurt (Siegen) ) )   
      
       
	$prenotazione = new Prenotazione ();
	$prenotazione->conn = $db;

	print ("<br style=\"clear:both;\"/>") ;

	/*if (is_object ( ($prenotazione_wizard) )) {
		$prenotazione_wizard->conn = $db;
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$NomeCliente = $DatiGeneraliArr ['ClienteNome'];
		$CellulareCliente = $DatiGeneraliArr ['ClienteCellulare'];
		$SessoIdCliente = $DatiGeneraliArr ['ClienteSessoId'];
		$TipoViaggioId = $DatiGeneraliArr ['TipoViaggioId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( $Tp );
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$ComuneSalitaId = $DatiGeneraliPercorsoArr ['ComuneSalitaId'];
		$ComuneDiscesaId = $DatiGeneraliPercorsoArr ['ComuneDiscesaId'];
		$FermataSalitaId = $DatiGeneraliPercorsoArr ['FermataSalitaId'];
		$FermataDiscesaId = $DatiGeneraliPercorsoArr ['FermataDiscesaId'];
		$CorsaId = $DatiGeneraliPercorsoArr ['CorsaId'];
		$DataCorsa = $DatiGeneraliPercorsoArr ['CorsaDataPartenza'];
	}*/

	if ($Tp == "A") 	// viaggio di andata
	{

		if ($TipoFermata == 'P') 		// fermata di pickup
		{

			$def_fermata = 0;

			if (sizeof ( $arr_fermate ) == 1)
				$def_fermata = $arr_fermate [0] ['FermataId'];

			if ($FermataSalitaId > 0)
				$def_fermata = $FermataSalitaId;

			$page->create_select ( "Fermata di salita (andata):", "Fermata[FermataId$Tp$TipoFermata]", "FermataId$Tp$TipoFermata", "brain_campoForm", $arr_fermate, $def_fermata, "FermataId", "FermataOrario", array (
					"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
					"class" => "'required'"
			), 1 );
		}

		else 		// fermata di dropoff
		{

			$def_fermata = 0;

			if (sizeof ( $arr_fermate_d ) == 1)
				$def_fermata = $arr_fermate_d [0] ['FermataIdDrop'];

			if ($FermataDiscesaId > 0)
				$def_fermata = $FermataDiscesaId;

			$page->create_select ( "Fermata di discesa (andata):", "Fermata[FermataId$Tp$TipoFermata]", "FermataId$Tp$TipoFermata", "brain_campoForm", $arr_fermate_d, $def_fermata, "FermataIdDrop", "FermataOrarioDrop", array (
					"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
					"class" => "'required'"
			), 1 );
		}
	}

	if ($Tp == "R") 	// viaggio di ritorno
	{

		if ($TipoFermata == 'P') 		// fermata di pickup
		{

			$def_fermata = 0;

			if (sizeof ( $arr_fermate ) == 1)
				$def_fermata = $arr_fermate [0] ['FermataId'];

			if ($FermataSalitaId > 0)
				$def_fermata = $FermataSalitaId;

			$page->create_select ( "Fermata di salita (ritorno):", "Fermata[FermataId$Tp$TipoFermata]", "FermataId$Tp$TipoFermata", "brain_campoForm", $arr_fermate, $def_fermata, "FermataId", "FermataOrario", array (
					"onChange" => "'javascript:MostraTipoBiglietti((\"R\"));'",
					"class" => "'required'"
			), 1 );
		}

		else 		// fermata di dropoff
		{

			$def_fermata = 0;

			if (sizeof ( $arr_fermate_d ) == 1)
				$def_fermata = $arr_fermate_d [0] ['FermataIdDrop'];

			if ($FermataDiscesaId > 0)
				$def_fermata = $FermataDiscesaId;

			$page->create_select ( "Fermata di discesa (ritorno):", "Fermata[FermataId$Tp$TipoFermata]", "FermataId$Tp$TipoFermata", "brain_campoForm", $arr_fermate_d, $def_fermata, "FermataIdDrop", "FermataOrarioDrop", array (
					"onChange" => "'javascript:MostraTipoBiglietti(\"A\");'",
					"class" => "'required'"
			), 1 );
		}
	}

	exit ();
}

function GetCorse() {
	global $user, $prenotazione_wizard;
	$DataDal = $_REQUEST ['DataDal'];
	$DataAl = $_REQUEST ['DataAl'];
	$ComunePartenza = $_REQUEST ['PartenzaId'];
	$ComuneArrivo = $_REQUEST ['DestinazioneId'];
	$Tp = $_REQUEST ['Tp'];

	$CorsaId = 0;
	$db = new Database ();
	$db->connect ();

	if (is_object ( ($prenotazione_wizard) )) {
		$prenotazione_wizard->conn = $db;
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$NomeCliente = $DatiGeneraliArr ['ClienteNome'];
		$CellulareCliente = $DatiGeneraliArr ['ClienteCellulare'];
		$SessoIdCliente = $DatiGeneraliArr ['ClienteSessoId'];
		$TipoViaggioId = $DatiGeneraliArr ['TipoViaggioId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( $Tp );
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;

		$ComuneSalitaId = $DatiGeneraliPercorsoArr ['ComuneSalitaId'];
		$ComuneDiscesaId = $DatiGeneraliPercorsoArr ['ComuneDiscesaId'];
		$FermataSalitaAId = $DatiGeneraliPercorsoArr ['FermataSalitaId'];
		$FermataDiscesaAId = $DatiGeneraliPercorsoArr ['FermataDiscesaId'];
		$CorsaId = $DatiGeneraliPercorsoArr ['CorsaId'];
		$DataCorsa = $DatiGeneraliPercorsoArr ['CorsaDataPartenza'];
	}

	if (($ComunePartenza > 0) and ($ComuneArrivo > 0)) {
		include_once ("biglietto_corseandata_datatable.php");
		?>
<table cellpadding="0" cellspacing="0" border="0" class="display"
	id="brain_datatables<?=$Tp?>">
	<thead>
		<tr class="brain_tabellaTr">
			<th width="2%"></th>
			<th width="20%">corsa</th>
			<th width="20%">linea</th>
			<th width="10%">data</th>
			<th width="10%">giorno</th>
			<th width="10%">partenza</th>
			<th width="5%">Occ</th>
			<th width="5%">Dis</th>
		</tr>
		<tr class="brain_tabellaFilter">
			<th><input type="hidden" /></th>
			<th><input type="text" /></th>

			<th><input type="text" /></th>
			<th><input type="text" /></th>
			<th><input type="text" /></th>
			<th><input type="text" /></th>
			<th><input type="text" /></th>
			<th><input type="text" /></th>

		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="9" class="dataTables_empty">Caricamento in corso...</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="9"></td>
		</tr>
	</tfoot>
</table>


<!--
<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
                                    <tbody><tr class="rowIntestazione">
                                    <td>Seleziona</td>

                                      <td>Linea</td>
                                    <td>Corsa</td>
                                    <td>Data</td>
                                    <td>Ora</td>
                                    <td>Lib</td>
                                    <td>Occ</td>
                                    <td>Ris.</td>

                                    </tr>




                                <tr class="rowBianca">

                                     <td><span><input type="radio" name="CorsaAndata" value="" /></span></td>
                                    <td><span>2</span></td>
                                     <td><span>Cariati</span></td>
                                    <td><span>Lagonegro - Interscambio</span></td>

                                    <td><span>NO</span></td>
                                    <td><span>NO</span></td>
                                    <td><span>SI</span></td>

                                    <td><span>
                                    attiva                                    </span></td>
                                         <td><a title="edita" onclick="javascript:ExternalLoad('rt_fermata','fermata.php?do=edit&amp;FermataId=9');" href="#"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></td>

                                </tr>






                                                           </tbody>
                            </table>-->

<?

		/*
		 * $db->delete("DataRicercaTemp","UserId=".$user->OperatoreId); $db->delete("RT_ComuniRicercaTemp","UserId=".$user->OperatoreId);
		 */
	}

	exit ();
}

function rimborsa() {
	global $user, $prenotazione_wizard;
	$db = new Database ();
	$db->connect ();
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;
	$PrenotazioneId = $prenotazione_wizard->Id;
	$prenotazione_wizard->conn = $db;
	$x = $prenotazione_wizard->RimborsaBiglietti ();

	echo ("R@" . $PrenotazioneId);
	exit ();
}

function annulla() {
	global $user, $prenotazione_wizard;
	$db = new Database ();
	$db->connect ();
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;

	$prenotazione_wizard->conn = $db;
	$x = $prenotazione_wizard->AnnullaPrenotazione ( 4 );

	echo ("ok||La prenotazione &egrave; stata cancellata");
	exit ();
}

function annulla_forzato() {
	global $user, $prenotazione_wizard;
	$db = new Database ();
	$db->connect ();
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;

	$prenotazione_wizard->conn = $db;
	$x = $prenotazione_wizard->AnnullaPrenotazione ( 10 );

	echo ("ok");
	exit ();
}

function modifica() {
	create(1);
}

function create($modifica = 0) {
	global $user, $prenotazione_wizard;
	
	$db = new Database ();
	$db->connect ();
	
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;
	
	$arr_old_dati = null;
	$arr_old_dati_percorso_a = null;
	$arr_old_dati_percorso_r = null;
	$OldPax = 0;
	$OldDataPartenzaA = '';
	$OldDataPartenzaR = '';
	$OldCorsaPartenzaA = 0;
	$OldCorsaPartenzaR = 0;
	$OldPrenotazioneStato = 0;
	$oldtratte_a = null;
	$oldtratte_r = null;
	$daconfermare = 0;

	$cambioData = $_POST ['cambio_data'];
	$oldprenotazioneId = 0;
	if ($modifica == 1) {
		$prenotazione_wizard->conn = $db;
		$old_prenotazioneid = $prenotazione_wizard->Id;
		$oldprenotazioneId = $old_prenotazioneid;
		$prenotazione_wizard->inizializzaDatiGenerali ();
		$arr_old_dati = $prenotazione_wizard->DatiGenerali;
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( 'A' );
		$arr_old_dati_percorso_a = $prenotazione_wizard->DatiGeneraliPercorso;
		$oldTipoViaggio = $arr_old_dati ['TipoViaggioId'];
		$OldPax = $arr_old_dati ['TotalePaxPrenotati'];
		$OldPrenotazioneStato = $arr_old_dati ['PrenotazioneStato'];
		$OldDataPartenzaA = $arr_old_dati_percorso_a ['CorsaDataPartenza'];
		$OldCorsaPartenzaA = $arr_old_dati_percorso_a ['CorsaId'];

		$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaA and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";

		$oldtratte_a = $db->fetch_array ( $s );

		// print_r($oldtratte_a);

		if ($oldTipoViaggio == 2) {
			$prenotazione_wizard->inizializzaDatiGeneraliPercorso ( 'R' );
			$arr_old_dati_percorso_r = $prenotazione_wizard->DatiGeneraliPercorso;

			$OldDataPartenzaR = $arr_old_dati_percorso_r ['CorsaDataPartenza'];
			$OldCorsaPartenzaR = $arr_old_dati_percorso_r ['CorsaId'];

			$s = "select * from RT_PrenotazioneTratta where CorsaId=$OldCorsaPartenzaR and  Stato=1 and Cancella=0 and PrenotazioneId=$old_prenotazioneid order by PrenotazioneTrattaId asc";
			$oldtratte_r = $db->fetch_array ( $s );
		}
	}

	$prenotazione = new Prenotazione ();
	$prenotazione->conn = $db;
	
	// prelevo i dati del form ed aggiorno tutte le propriet� dell'oggetto
	$StatoPrenotazione = 1;
    if (isset($_POST ['Avanti']))
    	$StatoPrenotazione = 13;
        
    //imposta stato 13 per agenzia non verificata
   	$sql = "SELECT Verificato FROM Gestore WHERE GestoreId=$user->GestoreId";
    $row = $db->query_first($sql);
    if($row['Verificato']==0){
		$StatoPrenotazione = 13;
	}
        
	$data_prenotazione = $_POST['Prenotazione'];
	$data_prenotazione['PrenotazioneStato'] = $StatoPrenotazione;
	//$data_prenotazione ['CodicePrenotazione'] = strtoupper ( uniqid () );
	
	//se multi prenotazione
	if ($data_prenotazione['Multi']) {
		if (!isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();	
		} else {
			$data_prenotazione['CodicePrenotazione'] = $_SESSION['PRENOTAZIONE_MULTI'][0]['CodicePrenotazione'];
		}
	} else {
		$data_prenotazione['CodicePrenotazione'] = $prenotazione->GetProgressivoCodicePrenotazione();
	}

	$data_prenotazione = $storico->operazioni_insert($data_prenotazione, $user);
	$ritornoOpen = $_POST ['Prenotazione'] ['RitornoOpen'];
	$data_fermate = $_POST ['Fermata'];
	$PrenotazioneNote = $_POST ['PrenotazioneNote'];

	$conferma_errori = $_POST ['RichiestaCofermaErrori'];

	
        
        $sql="select FermataId,ComuneId,CorsaId,LineaId,TrattaPeso from RT_ElencoFermataOrarioPK where CorsaId=$CorsaAndata order by RAND()";
          $a=$db->query_first($sql);
          $ComuneAndataId=$a['ComuneId'];
          $trattaPartenza=$a['TrattaId'];
          $lineaId=$a['LineaId'];
         
        
          $sql="select FermataIdDrop,ComuneId,CorsaId,TrattaPeso from RT_ElencoFermataOrarioDO where CorsaId=$CorsaAndata order by RAND()";
          $a=$db->query_first($sql);
          $ComuneRitornoId=$a['ComuneId'];
          $trattaArrivo=$a['TrattaId'];
          
      
          
        $grafo = new GrafoTratte($lineaId, $CorsaAndata, $db, $ComuneAndataId, $ComuneRitornoId);
        
       if(!isset($grafo->flotta[0]))
           return false;
            
        
$trattaPartenza = null;
$trattaArrivo = null;
$TrattePercorse = $grafo->getTratte($grafo->flotta[0]->percorso, $trattaPartenza, $trattaArrivo);



$sql = "SELECT distinct FermataId,FermataOrario,TrattaNome From RT_ElencoFermataOrarioPK WHERE IsPickup=1 and  CorsaId=$CorsaAndata and ComuneId=$ComuneAndataId  and OdcIdRef=$user->OdcId and TrattaId=$trattaPartenza order by TrattaPeso desc ";      

$arr_fermate=$db->fetch_array($sql);




$sql = "SELECT distinct FermataIdDrop,FermataOrarioDrop,TrattaNome From RT_ElencoFermataOrarioDO WHERE IsDropOff=1 and  CorsaId=$CorsaAndata and ComuneId=$ComuneRitornoId  and TrattaId=$trattaArrivo and OdcIdRef=$user->OdcId order by TrattaPeso asc";      

$arr_fermate_d=$db->fetch_array($sql);
  
 
          
          
         
          
        $FermataAndataP = $arr_fermate[0] ['FermataId'];
	$FermataAndataD = $arr_fermate_d[0] ['FermataIdDrop'];
         
        if (!($FermataAndataP>0))
            return false;
        if (!($FermataAndataD>0))
            return false;
        
            
	$IdTratteAndata = $prenotazione->GetTratte ( $CorsaAndata, $FermataAndataP, $FermataAndataD );
       $listini_id = $prenotazione->GetListini ( $IdTratteAndata, $FermataAndataP, $FermataAndataD, $CorsaAndata );
        
       $TratteComplete = $IdTratteAndata;
       
	$ListiniCompleti = $listini_id;
	$CorsaRitorno = 0;
	$DataSelezionataR = "";
	if ($data_prenotazione ['TipoViaggioId'] == 2) {
		$CorsaRitorno = $_POST ['CorsaSelezionataR'];
		$DataSelezionataR = $_POST ['DataSelezionataR'];
		$FermataRitornoP = $data_fermate ['FermataIdRP'];
		$FermataRitornoD = $data_fermate ['FermataIdRD'];
		$IdTratteRitorno = $prenotazione->GetTratte ( $CorsaRitorno, $FermataRitornoP, $FermataRitornoD );
		$listini_idr = $prenotazione->GetListini ( $IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno );

		$c = 0;

		while ( $c < sizeof ( $IdTratteRitorno ) ) {
			$TratteComplete [] = $IdTratteRitorno [$c];
			$TrattaId = $IdTratteRitorno [$c] ['TrattaId'];
			$ListiniCompleti [$TrattaId] = $listini_idr [$TrattaId];
			$c ++;
		}
		$c = 0;
	}

	// Check

	$check = true;
	$err = "";

	// verifica da confermare
	$dcr = 0;

	$dca = $prenotazione->GetDaConfermare ( $IdTratteAndata );
	if ($CorsaRitorno > 0)
		$dcr = $prenotazione->GetDaConfermare ( $IdTratteRitorno );

	$arr_Fermate [0] ['FermataId'] = $FermataAndataP;
	$arr_Fermate [1] ['FermataId'] = $FermataAndataD;
	if ($CorsaRitorno > 0) {
		$arr_Fermate [2] ['FermataId'] = $FermataRitornoP;
		$arr_Fermate [3] ['FermataId'] = $FermataRitornoD;
	}
	$FdaConf = $prenotazione->GetFermateDaConfermare ( $arr_Fermate );

	/*
	 *
	 */
	// ********************************************************************************

	// controllo la validita del numero di posti
	$arr_biglietti_prenotati = $_POST ['BigliettoTipologiaPax'];
	$arr_biglietti_riduzione = $_POST ['BigliettoTipologiaPaxRid'];
	$arr_biglietti_aumento = $_POST ['BigliettoTipologiaPaxAum'];
	
	$ar1 = 0;
	if ($CorsaRitorno > 0)
		$ar1 = 1;
	$NumeroTotalePax = $prenotazione->ScriviTbBiglietti ( $arr_biglietti_prenotati, 0, $arr_biglietti_riduzione, $arr_biglietti_aumento, $TratteComplete, $ListiniCompleti, $FermataAndataP, $FermataAndataD, $DataSelezionataA, $ar1, $IdTratteAndata );
	$err .= $prenotazione->CheckNumeroPax ( $NumeroTotalePax );

	// Controllo corsa Ritorno se viaggio
	if ($user->SedeLegale != 1) {
		$err .= $prenotazione->CheckDisponibilitaPax ( $DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata" );
		if ($CorsaRitorno > 0)
			$err .= $prenotazione->CheckDisponibilitaPax ( $DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno" );
	}

	// Controllo stato della prentoazione con yes no
	if ($data_prenotazione ['TipoViaggioId'] == 2)
		$err .= $prenotazione->CheckCoerenzaAR ( $DataSelezionataA, $DataSelezionataR );

	$TipoPreR = - 1;

	$TipoPreAP = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaAndata, $FermataAndataP );
	$TipoPreAD = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaAndata, $FermataAndataD );

	if ($CorsaRitorno > 0) {
		$TipoPreRP = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaRitorno, $FermataRitornoP );
		$TipoPreRD = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaAndata, $FermataRitornoD );
	}

	/*if (($TipoPreAP == 1) or ($TipoPreAP == 1) or ($TipoPreAP == 1) or ($TipoPreAP == 1))
		$StatoPrenotazione = 3;*/

		// get ultima prenotazione per utente
	$sql = "select DataIns from RT_Prenotazione where OdcIdRef=$user->OdcId and OpeIns=$user->OperatoreId order by DataIns desc";

	$row = $db->query_first ( $sql );
	$DataIns = '';
	

	if ($CorsaRitorno > 0) {
		$sqA = "SELECT rt_linea.PercorsoId AS PercorsoId
		FROM RT_Corsa AS rt_corsa 
		LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
		LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
		WHERE rt_corsa.Cancella = 0
		AND rt_corsa.CorsaId = $CorsaAndata";
		$rowA = $db->query_first ( $sqA );
		$PercorsoAndataId = $rowA ['PercorsoId'];

		$sqR = "SELECT rt_linea.PercorsoId AS PercorsoId 
		FROM RT_Corsa AS rt_corsa 
		LEFT JOIN RT_Linea AS rt_linea ON (rt_corsa.LineaId = rt_linea.LineaId)
		LEFT JOIN RT_Percorso AS rt_percorso ON (rt_linea.PercorsoId = rt_percorso.PercorsoId)
		WHERE rt_corsa.Cancella = 0
		AND rt_corsa.CorsaId = $CorsaRitorno";
		$rowR = $db->query_first ( $sqR );
		$PercorsoRitornoId = $rowR ['PercorsoId'];

		if ($PercorsoAndataId != $PercorsoRitornoId)
			$err .= "Il servizio di linea di andata risulta differente dal servizio di linea di ritorno. Per prenotare andata e ritorno e' necessari selezionare lo stesso servizio di linea per l'andata ed il ritorno";
	}

	// $err="true";
	if ($err != '') 	// chiedo di corregere gli errori
	{
		echo ($err);
		exit ();
	}
	$tratta_confermare = "salita";
	if ($dcr)
		$tratta_confermare = "discesa";

	if (($user->SedeLegale != 1) and ($dca + $dcr + $FdaConf > 0) and ($StatoPrenotazione == 3)) {

		echo ("Impossibile procedere con l'emissione del titolo in quanto una o più fermate risultano da confermare. La prenotazione può essere gestita solo dalla sede principale.");
		exit ();
	}
	$trattedifferenti = false;
	if ($modifica == 1) {
		if (((sizeof ( $oldtratte_a )) != (sizeof ( $IdTratteAndata ))) or ((sizeof ( $oldtratte_r )) != (sizeof ( $IdTratteRitorno ))))
			$trattedifferenti = true;
		else {
			$qtr = 0;
			while ( $qtr < sizeof ( $oldtratte_a ) ) {
				if ($oldtratte_a [$qtr] ['TrattaId'] != $IdTratteAndata [$qtr] ['TrattaId'])
					$trattedifferenti = true;
				$qtr ++;
			}
			$qtr = 0;
			if (sizeof ( $oldtratte_r ) > 0) {
				while ( $qtr < sizeof ( $oldtratte_r ) ) {
					if ($oldtratte_r [$qtr] ['TrattaId'] != $IdTratteRitorno [$qtr] ['TrattaId'])
						$trattedifferenti = true;
					$qtr ++;
				}
			}
		}
	}

	if (($conferma_errori > 0) and (($conferma_errori < 100)) and ($user->SedeLegale = 1)) {
		$daconfermare = 0;
		if ((($dca + $dcr + $FdaConf) > 0)) {
			if ($modifica == 1) {
				if ($trattedifferenti) {
					echo ("La prenotazione e' soggetta a conferma da parte della direzione in quanto una o più fermate risultano da confermare. Per la prenotazione da confermare non saranno emessi titoli di viaggio. Continuare?##100");
					$data_prenotazione ['PrenotazioneStato'] = 2;
					exit ();
					$StatoPrenotazione = 2;
					$daconfermare = 1;
				} else {
					if ($OldPrenotazioneStato == 2) {
						$StatoPrenotazione = 2;
						$daconfermare = 1;
					}
				}
			} else {
				echo ("La prenotazione e' soggetta a conferma da parte della direzione in quanto la tratta $tratta_confermare è da confermare. Per la prenotazione da confermare non saranno emessi titoli di viaggio. Continuare?##100");
				$data_prenotazione ['PrenotazioneStato'] = 2;
				exit ();
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			}
		} else
			$trattedifferenti = false;
	}

	// verifico se il numero di pax è cambiato

	if (($conferma_errori > 0) and ($conferma_errori < 200) and ($user->SedeLegale == 1)) {
		$daconfermare = 0;

		$OldPax = $arr_old_dati ['TotalePaxPrenotati'];
		$OldDataPartenzaA = $arr_old_dati_percorso_a ['CorsaDataPartenza'];
		$OldCorsaPartenzaA = $arr_old_dati_percorso_a ['CorsaId'];

		if ($modifica == 1) {

			if (($OldDataPartenzaA != $DataSelezionataA) or ($OldCorsaPartenzaA != $CorsaAndata) or ($NumeroTotalePax > $OldPax))
				$errore = $prenotazione->CheckDisponibilitaPax ( $DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata" );
			else {
				if ($OldPrenotazioneStato == 5) {
					$data_prenotazione ['PrenotazioneStato'] = 5;
					// echo($OldPrenotazioneStato);
					$daconfermare = 1;
				}
			}
		} else
			$errore = $prenotazione->CheckDisponibilitaPax ( $DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata" );

		if ($CorsaRitorno > 0) {

			if ($modifica == 1) {
				if (($OldDataPartenzaR != $DataSelezionataR) or ($OldCorsaPartenzaR != $CorsaRitorno) or ($NumeroTotalePax > $OldPax))
					$errore = $prenotazione->CheckDisponibilitaPax ( $DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno" );
				else {
					if ($OldPrenotazioneStato == 5) {
						$data_prenotazione ['PrenotazioneStato'] = 5;
						$daconfermare = 1;
					}
				}
			} else
				$errore .= $prenotazione->CheckDisponibilitaPax ( $DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno" );
		}

		if ($errore != '') {
			$errore .= ". Inserire la prenotazione in lista d'attesa?##200";
			echo ($errore);
			exit ();
			$daconfermare = 1;
			$data_prenotazione ['PrenotazioneStato'] = 5;
			$StatoPrenotazione = 5;
		}
	}

	$arr_PostoSceltoA = $_POST ['PostoSceltoA'];
	$length_a = count ( array_keys ( $arr_PostoSceltoA, "0" ) );
	$selezionati_andata = sizeof ( $arr_PostoSceltoA ) - $length_a;

	$arr_PostoSceltoR = $_POST ['PostoSceltoR'];
	$length_r = count ( array_keys ( $arr_PostoSceltoR, "0" ) );
	$selezionati_ritorno = sizeof ( $arr_PostoSceltoR ) - $length_r;

	if (($selezionati_andata > 0) and ($selezionati_andata != $NumeroTotalePax)) {
		echo ("E' necessario indicare la preferenza, per l'andata, per tutti i posti prenotati");
		$check = false;
		exit ();
	}
	if ($CorsaRitorno > 0) {

		if (($selezionati_andata > 0) and ($selezionati_ritorno != $NumeroTotalePax)) {
			echo ("E' necessario indicare la preferenza, per il ritorno, per tutti i posti prenotati");
			$check = false;
			exit ();
		} elseif ($selezionati_andata != $selezionati_ritorno) {
			echo ("E' necessario indicare la preferenza, sia per la corsa di andata che per quella di ritorno");
			$check = false;
			exit ();
		}
	}

	if (($data_prenotazione ['TipoViaggioId'] == 2) and (! ($CorsaRitorno > 0)) and ($ritornoOpen == 0)) {
		echo ("La corsa per il ritorno non è stata selezionata!");
		$check = false;
		exit ();
	}

	if (! $CorsaAndata > 0) {
		echo ("La corsa di andata non è stata selezionata!");
		$check = false;
		exit ();
	}

	if (($conferma_errori < 400) and (isset ( $_POST ['Emetti'] )) and ($_POST ['Emetti'] != '')) {
		echo ("Sicuro di voler emettere i titoli di viaggio?##400");
		$check = false;
		exit ();
	}

	if ($check == true) {

		if ($modifica == 1) {
			$prenotazione_wizard->conn = $db;
			$PrenotazioneId = $prenotazione_wizard->Id;
			$data ['Stato'] = 0;
			$data ['Cancella'] = 1;
			$data1 = $data;
			$data1 ['PrenotazioneStato'] = 6;
			$data = $storico->operazioni_update ( $data, $user );
			$result = $db->update ( "RT_Prenotazione", $data1, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId" );
			$result = $db->update ( "RT_PrenotazionePercorso", $data1, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId" );
			$result = $db->update ( "RT_PrenotazionePosto", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId" );
			$result = $db->update ( "RT_PrenotazionePercorsoNote", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId" );
			$result = $db->update ( "RT_PrenotazioneTariffa", $data, "PrenotazioneId=" . $old_prenotazioneid . " and OdcIdRef=$user->OdcId" );
			$result = $db->update ( "RT_PrenotazioneTratta", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId" );
			// faccio update a stato cancellato / modificata e creo una nuova prenotazione
			
			//aggiorno il dettaglio per escludere la prenotazione
			$dataDettaglio = null;
			$dataDettaglio['Escludi'] = 1;
			$dataDettaglio = $storico->operazioni_update($dataDettaglio, $user);
			
			$db->update("RT_PrenotazioneDettaglio", $dataDettaglio, "PrenotazioneId = $old_prenotazioneid");
		}
		// verifico se le fermate sono da confermare

		if (($modifica != 1) or ($trattedifferenti)) {

			if ((($dca + $dcr + $FdaConf) > 0)) {
				$data_prenotazione ['PrenotazioneStato'] = 2;
				$StatoPrenotazione = 2;
				$daconfermare = 1;
			}
		}

		if (($modifica != 1) or ($conferma_errori == 200)) {

			$errore = $prenotazione->CheckDisponibilitaPax ( $DataSelezionataA, $CorsaAndata, $NumeroTotalePax, "Andata" );

			if ($CorsaRitorno > 0)
				$errore .= $prenotazione->CheckDisponibilitaPax ( $DataSelezionataR, $CorsaRitorno, $NumeroTotalePax, "Ritorno" );

			if ($errore != '') {

				$data_prenotazione ['PrenotazioneStato'] = 5;
				$StatoPrenotazione = 5;
				$daconfermare = 1;
			}
		}

		$lastidA = $db->insert("RT_Prenotazione", $data_prenotazione );
		$NuovaPrenotazioneId = $lastidA;

		if ($lastidA != false) {

			$ok = $prenotazione->SettaId ( $lastidA );
			
			$ar1 = 0;
			$TV = 1;
			if ($CorsaRitorno > 0) {
				$ar1 = 1;
				$TV = 2;
			}

			//inserisco i passeggeri
			$passeggeri = $_POST['Passeggeri'];
			foreach ($passeggeri as $passeggero) {
				$passeggero['PrenotazioneId'] = $NuovaPrenotazioneId;
                                $passeggero['Cognome']=  get_rndCognome();
                                $passeggero['Nome']=  get_rndNome();
                                
				$passeggero = $storico->operazioni_insert($passeggero, $user);
				$db->insert("RT_PrenotazionePasseggeri", $passeggero);
                                $d['ClienteNome']=$passeggero['Cognome']." ".$passeggero['Nome'];
                                $d['ABordo']=1;
                                
                                $db->update("RT_Prenotazione", $d,"PrenotazioneId=$NuovaPrenotazioneId");
			}

			// $x=$prenotazione->ScriviTbBiglietti($arr_biglietti_prenotati,1,$arr_biglietti_riduzione,$arr_biglietti_aumento,$TratteComplete,$ListiniCompleti,$FermataAndataP,$FermataAndataD,$DataSelezionataA,$ar1,$IdTratteAndata);
                       
                    if ($OldId>0)
                        $PrenotazioneOld=$OldId;
                    else {
                        $OldId=null;
                    }

			$arrPrezzi = $prenotazione->GetTipologiaBigliettiPrezzi ( 1, $NuovaPrenotazioneId, $prenotazione_wizard, $DataSelezionataA, $CorsaAndata, $FermataAndataP, $FermataAndataD, $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $TV, $arr_biglietti_prenotati, $arr_biglietti_aumento, $arr_biglietti_riduzione,$PrenotazioneOld,$old_prenotazioneid, $OldPrenotazioneStato);
			
			// if ($modifica!=1)
			$xx = $prenotazione->GeneraCodiciPrenotazione ( $NumeroTotalePax );
			$x = $prenotazione->ScriviTbTratte ( $CorsaAndata, $IdTratteAndata, 'A' );

			$DataPartenza = $_POST ['DataSelezionataA'];
			$x = $prenotazione->ScriviTbPercorso ( $CorsaAndata, $FermataAndataP, $FermataAndataD, $DataPartenza, 'A', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax );
			$x = $prenotazione->ScriviTbTariffe ( $listini_id, $IdTratteAndata, $CorsaRitorno, $FermataAndataP, $FermataAndataD, $DataPartenza );
			$x = $prenotazione->ScriviTbPostoScelto ( $arr_PostoSceltoA, $arr_PostoSceltoAI, $CorsaAndata, $DataPartenza );

			if ($CorsaRitorno > 0) {
				$x = $prenotazione->ScriviTbTratte ( $CorsaRitorno, $IdTratteRitorno, 'R' );
				$DataPartenza = $_POST ['DataSelezionataR'];
				$x = $prenotazione->ScriviTbPercorso ( $CorsaRitorno, $FermataRitornoP, $FermataRitornoD, $DataPartenza, 'R', $StatoPrenotazione, $PrenotazioneNote, $NumeroTotalePax );
				$listini_id_r = $prenotazione->GetListini ( $IdTratteRitorno, $FermataRitornoP, $FermataRitornoD, $CorsaRitorno );
				// $x=$prenotazione->ScriviTbTariffe($listini_id_r,$IdTratteRitorno,$CorsaRitorno);
				$x = $prenotazione->ScriviTbPostoScelto ( $arr_PostoSceltoR, $arr_PostoSceltoIR, $CorsaRitorno, $DataPartenza );
			} else
				$CorsaRitorno = 0;

			$abilitazioneprenotazione = $prenotazione->GetTipologiaPrenotazioneAbilitata ( $CorsaAndata, $FermataAndataP );
			// $abilitazioneprenotazione=2;

			// echo($CorsaAndata." ".$FermataAndataP);
			if ($modifica == 1) {
				$OldId = $prenotazione_wizard->Id;

				$PrenotazioneOld = new Prenotazione ( $OldId );
				$PrenotazioneOld->conn = $db;
				$PrenotazioneOld->inizializzaDatiGenerali ();
				$arr_generali = $PrenotazioneOld->DatiGenerali;

				$dupd = null;
				// $dupd['DataIns']=$arr_generali['DataIns'];
				$dupd['PrenotazioneId'] = $NuovaPrenotazioneId;
				// $dupd['IpIns']=$arr_generali['IpIns'];
				$dupd['OdcIdRef'] = $arr_generali['OdcIdRef'];
				$dupd['GestoreIdRef'] = $arr_generali['GestoreIdRef'];
				// $dupd['OpeIns']=$arr_generali['OpeIns'];
				$dupd = $storico->operazioni_update($dupd, $user);
				$dupd1 = $dupd;
				$dupd1['CodicePrenotazione'] = $arr_generali['CodicePrenotazione'];
				$dupd1['ScadenzaPrenotazione'] = ($arr_generali['ScadenzaPrenotazione'] != '')? $arr_generali['ScadenzaPrenotazione'] : 'NULL';
				$result = $db->update("RT_Prenotazione", $dupd1, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");

				$dupd['PrenotazioneId'] = $lastidA;
				$result = $db->update("RT_PreparazioneBus", $dupd, "PrenotazioneId=" . $OldId . " and OdcIdRef=$user->OdcId");
				$result = $db->update("RT_PreparazioneNavette", $dupd, "PrenotazioneId=" . $OldId . " and OdcIdRef=$user->OdcId");
				// $result=$db->update("RT_PrenotazioneNumero", $dupd,"PrenotazioneId=".$OldId." and OdcIdRef=$user->OdcId");

				//aggiorno la prenotazione
				//$prenotazioneTot['TotaleDaPagare'] = $arr_generali['TotaleDaPagare'];
				//$prenotazioneTot['TotalePagato'] = $arr_generali['TotalePagato'];
				//$prenotazioneTot['TotaleResiduo'] = $arr_generali['TotaleResiduo'];
				//$prenotazioneTot['Pagato'] = $arr_generali['Pagato'];
				//$prenotazioneTot['ScadenzaPrenotazione'] = ($arr_generali['ScadenzaPrenotazione'] != '')? $arr_generali['ScadenzaPrenotazione'] : 'NULL';
				//$prenotazioneTot = $storico->operazioni_update($prenotazioneTot, $user);
				//$result = $db->update("RT_Prenotazione", $prenotazioneTot, "PrenotazioneId=".$NuovaPrenotazioneId." AND OdcIdRef=".$user->OdcId);
			}

			/*if (($abilitazioneprenotazione == 1) or ((isset ( $_POST ['Emetti'] )) and ($_POST ['Emetti'] != '')))
			{

				if (($daconfermare == 0) and ($cambioData != 3))
					$x = $prenotazione->EmettiBiglietti ( $NumeroTotalePax, $CorsaAndata, $CorsaRitorno );
			}*/

			/*
			 * if ($user->OperatoreId!=1) { $return=$prenotazione->CreateDettaglioPrenotazione($CorsaAndata); } else{
			 */
			$TipoViaggio1 = "Corsa Semplice";
			if ($CorsaRitorno > 0)
				$TipoViaggio1 = "Andata/Ritorno";

			$return = $prenotazione->CreateDettaglioPrenotazione ( $CorsaAndata, "Andata", $TipoViaggio1, $StatoPrenotazione );
			if ($CorsaRitorno > 0)
				$return = $prenotazione->CreateDettaglioPrenotazione ( $CorsaRitorno, "Ritorno", $TipoViaggio1, $StatoPrenotazione );

				// }
			if ($user->SedeLegale == 1) {

				$data1a ['DaFatturare'] = 0;
				$data1a ['DaBonificare'] = 0;

				$result = $db->update ( "RT_PrenotazioneDettaglio", $data1a, "GestoreIdRef=1" );
			}

			if (($modifica == 1) and ($cambioData == 3)) {

				$data11 ['PrenotazioneStato'] = 3;

				$result = $db->update ( "RT_Prenotazione", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId" );
				$result = $db->update ( "RT_PrenotazionePercorso", $data11, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId" );

				$data1ag ['PrenotazioneId'] = $NuovaPrenotazioneId;
				$result = $db->update ( "RT_PrenotazioneTitolo", $data1ag, "PrenotazioneId=$old_prenotazioneid" );

				// echo("cambio data per la prenotazione ".$old_prenotazioneid." con ".$NuovaPrenotazioneId);
				$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$NuovaPrenotazioneId";
				$ArrObjectP = $db->fetch_array ( $sql );

				$sql = "Select PrenotazioneNumeroId from RT_PrenotazioneNumero where PrenotazioneId=$old_prenotazioneid";
				$ArrObjectPOld = $db->fetch_array ( $sql );

				$npre = 0;
				while ( $npre < sizeof ( $ArrObjectP ) ) {
					$PrenotazioneNumeroId = $ArrObjectP [$npre] ['PrenotazioneNumeroId'];
					$PrenotazioneNumeroIdOld = $ArrObjectPOld [$npre] ['PrenotazioneNumeroId'];

					$data1ag1 ['PrenotazioneNumeroId'] = $PrenotazioneNumeroId;
					$result = $db->update ( "RT_PrenotazioneTitolo", $data1ag1, "PrenotazioneNumeroId=$PrenotazioneNumeroIdOld" );

					$npre ++;
				}
			}
			
			if ($modifica == 1) {
				$prenotazione->inizializzaDatiGenerali();
				$totaliImporti = $prenotazione->GetTotaliPrenotazione();

				$prenotazione_wizard->inizializzaDatiGenerali();
				$totaliImportiOld = $prenotazione_wizard->GetTotaliPrenotazione();
				
				//Copio i movimenti e li metto come annullati
				$OldId = $prenotazione_wizard->Id;
					
				$movimentoObj = new PrenotazioneMovimento();
				$movimentoObj->conn = $db;
					
				$oldMovimenti = $movimentoObj->getAllPrenotazioneMovimento($OldId);
					
				foreach ($oldMovimenti as $newMovimento) {
					$newMovimento['PrenotazioneId'] = $NuovaPrenotazioneId;
					$newMovimento['Data'] = ($newMovimento['Data'] != '')? $newMovimento['Data'] : 'NULL';
					$newMovimento['DataPagamento'] = ($newMovimento['DataPagamento'] != '')? $newMovimento['DataPagamento'] : 'NULL';
					$newMovimento['Scadenza'] = ($newMovimento['Scadenza'] != '')? $newMovimento['Scadenza'] : 'NULL';
						
					if ($totaliImporti['TotalePrenotazione'] != $totaliImportiOld['TotalePrenotazione']) {
						//annullo i movimenti copiati se la prenotazione non ha biglietti emessi
						if ($prenotazione->DatiGenerali['PrenotazioneStato'] != 3) {
							$newMovimento['Cancella'] = 1;
							$newMovimento['TipoMovimento'] = "A";
							$newMovimento['Causale'] = "Annullato per modifica";
						} else if ($prenotazione->DatiGenerali['PrenotazioneStato'] == 3 && $newMovimento['TipoMovimento'] == "P") {
							$newMovimento['Cancella'] = 1;
							$newMovimento['TipoMovimento'] = "A";
							$newMovimento['Causale'] = "Annullato per modifica dopo emissione";
						}
					}
					
					$db->insert("RT_PrenotazioneMovimento", $newMovimento);
				}
			
				//crea un nuovo movimento contabile con il totale della prenotazione modificata
				if ($totaliImporti['TotalePrenotazione'] != $totaliImportiOld['TotalePrenotazione']) {
					$movimento = array();
					$movimento['PrenotazioneId'] = $NuovaPrenotazioneId;
					$movimento['PagamentoTipoId'] = 7;
					$movimento['Data'] = date("Y-m-d");
					$movimento['Supplemento'] = 0;
					$movimento['DataPagamento'] = 'NULL';
					$movimento['ImportoPagato'] = 0;
					$movimento['Scadenza'] = 'NULL';
					$movimento['CodicePagamento'] = 'NULL';
					$movimento['CanalePagamentoId'] = 'NULL';
					$movimento['Importo'] = $totaliImporti['TotaleResiduo'];
					if ($prenotazione->DatiGenerali['PrenotazioneStato'] == 3) {
						$movimento['TipoMovimento'] = "P";
						$movimento['Causale'] = "Richiesta del residuio";
					} else {
						$movimento['TipoMovimento'] = "I";
						$movimento['Causale'] = "Rettifica nuovo importo";
					}
					$movimento = $storico->operazioni_insert($movimento, $user);
					$db->insert("RT_PrenotazioneMovimento", $movimento);
					
					// aggiorno la prenotazione impostando pagamento a bordo
					$dup = array();
					$dup['ABordo'] = 1;
					$result = $db->update("RT_Prenotazione", $dup, "PrenotazioneId=" . $NuovaPrenotazioneId . " and OdcIdRef=$user->OdcId");
					
					$modifica = 2;
				}
			}

			if (($abilitazioneprenotazione == 1) or ((isset ( $_POST ['Emetti'] )) and ($_POST ['Emetti'] != '')) and ($daconfermare == 0)) // emissione ticket obbligatorio
				echo ("E@" . $NuovaPrenotazioneId);
			else
				echo ("ok" . '_' . $modifica . '_' . $NuovaPrenotazioneId . '_' . $data_prenotazione['Multi']);
			
			$prenotazione_wizard = new Prenotazione($NuovaPrenotazioneId);
			$_SESSION['PRENOTAZIONE_WIZARD'] = serialize($prenotazione_wizard);
			
			//se multi prenotazione
			if (!$modifica && $data_prenotazione['Multi']) {
				if (!isset($_SESSION['PRENOTAZIONE_MULTI'])) {
					$prenotazione_multi = array();
				} else {
					$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];
				}
				
				$data_prenotazione['PrenotazioneId'] = $lastidA;
				$prenotazione_multi[] = $data_prenotazione;
				$_SESSION['PRENOTAZIONE_MULTI'] = $prenotazione_multi;
			} else {
				unset($prenotazione_multi);
				unset($_SESSION['PRENOTAZIONE_MULTI']);
			}
		} else
			echo ("no1");
		exit ();
	} else {
		echo ("no2");
		exit ();
	}
	exit ();
}

function get_rndCognome()
{
    $arr[]='Esposito';
    $arr[]='Cipolla';
    $arr[]='Casaburi';
    $arr[]='Manfredini';
    $arr[]='Lamberti';
    $arr[]='Cuofano';
    $arr[]='Rossi';
    $arr[]='Bianchi';
    $arr[]='Capano';
    $arr[]='De Rosa';
    $arr[]='Aliberti';
    $arr[]='Colace';
    $arr[]='Capuano';
    $arr[]='Mirto';
    $arr[]='Izzo';
    $arr[]='Milito';
    $arr[]='Stasi';
    
    
    $s=sizeof($arr)-1;
    $i=  rand(0, $s);
    return $arr[$i];
    
}
function ConfermaPrenotazione() {
	$PrenotazioneId = $_REQUEST ['PrenotazioneId'];
	global $user, $prenotazione_wizard;
	$db = new Database ();
	$db->connect ();
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;

	$data ['PrenotazioneStato'] = 1;
	$data = $storico->operazioni_update ( $data, $user );
	$result = $db->update ( "RT_Prenotazione", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId" );
	$result = $db->update ( "RT_PrenotazionePercorso", $data, "PrenotazioneId=" . $PrenotazioneId . " and OdcIdRef=$user->OdcId" );

	echo ("ok");
	exit ();
}

function ConfermaPrenotazioneLista() {
	$PrenotazioneId = $_REQUEST ['PrenotazioneId'];
	global $user, $prenotazione_wizard;
	$db = new Database ();
	$db->connect ();
	$storico = new StoricoOperazioni ();
	$storico->conn = $db;
	$dt = new DT ();

	// verifica posti disponibili
	$pre = new Prenotazione ( $PrenotazioneId );
	$pre->conn = $db;
	$pre->inizializzaDatiGenerali ();
	$arr_pre = $pre->DatiGenerali;
	$NumeroPax = $arr_pre ['TotalePaxPrenotati'];

	$pre->inizializzaDatiGeneraliPercorso ( 'A' );
	$arr_old_dati_percorso_a = $pre->DatiGeneraliPercorso;
	$oldTipoViaggio = $arr_pre ['TipoViaggioId'];
	$DataPartenza = $arr_old_dati_percorso_a ['CorsaDataPartenza'];
	// print_r($arr_old_dati_percorso_a);
	$CorsaIdPartenza = $arr_old_dati_percorso_a ['CorsaId'];
	$LineaNome = $arr_old_dati_percorso_a ['LineaNome'];
	$CorsaNome = $arr_old_dati_percorso_a ['CorsaNome'];
	$l = $LineaNome . " - ($CorsaNome)";
	$sql = "select PostiRealmenteDisponibili,CorsaId from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaIdPartenza and AppCalendarioData='$DataPartenza'";
	$DataPartenza1 = $dt->format ( $DataPartenza, "Y-m-d", "d/m/Y" );
	$row = $db->query_first ( $sql );
	$posti_disponibili = 0;
	if ($row ['CorsaId'] > 0)
		$posti_disponibili = $row ['PostiRealmenteDisponibili'];
	$errore_posti = 0;
	if ($NumeroPax > $posti_disponibili) {
		$errore_posti = 1;
		$posti = $NumeroPax - $posti_disponibili;
		echo ("\nPer poter validare la prenotazione è necessario aggiungere alla corsa $l del $DataPartenza1 . n. " . $posti . " posti");
	}
	if ($oldTipoViaggio == 2) {
		$pre->inizializzaDatiGeneraliPercorso ( 'R' );
		$arr_old_dati_percorso_a = $pre->DatiGeneraliPercorso;
		$oldTipoViaggio = $arr_pre ['TipoViaggioId'];
		$DataPartenza = $arr_old_dati_percorso_a ['CorsaDataPartenza'];
		$DataPartenza1 = $dt->format ( $DataPartenza, "Y-m-d", "d/m/Y" );
		$CorsaIdPartenza = $arr_old_dati_percorso_a ['CorsaId'];
		$LineaNome = $arr_old_dati_percorso_a ['LineaNome'];
		$CorsaNome = $arr_old_dati_percorso_a ['CorsaNome'];
		$l = $LineaNome . " - ($CorsaNome)";
		$sql = "select PostiRealmenteDisponibili,CorsaId from RT_ViewOperativitaBlocchi where OdcIdRef=$user->OdcId and CorsaId=$CorsaIdPartenza and AppCalendarioData='$DataPartenza'";
		// echo($sql);
		$row = $db->query_first ( $sql );
		$posti_disponibili = 0;
		if ($row ['CorsaId'] > 0)
			$posti_disponibili = $row ['PostiRealmenteDisponibili'];

		if ($NumeroPax > $posti_disponibili) {
			$errore_posti = 1;
			$posti = $NumeroPax - $posti_disponibili;
			echo ("\nPer poter validare la prenotazione è necessario aggiungere alla corsa $l del $DataPartenza1 . n. " . $posti . " posti");
		}
	}
	if ($errore_posti == 1)
		exit ();
		// exit();
		// verifica se ci sono tratte da confermare
	$sql = "SELECT * FROM 
		
		(SELECT rt_prenotazionetratta.PrenotazioneId AS PrenotazioneId, rt_tratta.DaConfermare AS DaConfermare, rt_prenotazionetratta.OdcIdRef AS OdcIdRef
		FROM 
		RT_PrenotazioneTratta AS rt_prenotazionetratta
		JOIN RT_Tratta AS rt_tratta ON (rt_prenotazionetratta.TrattaId = rt_tratta.TrattaId)
		JOIN RT_Prenotazione AS rt_prenotazione ON (rt_prenotazione.PrenotazioneId = rt_prenotazionetratta.PrenotazioneId)
		WHERE
		rt_tratta.DaConfermare = 1
		AND rt_prenotazione.Stato = 1
		AND rt_prenotazione.Cancella = 0) AS RT_CheckPrenotazioneDaConfermare
		 
	WHERE OdcIdRef = $user->OdcId AND PrenotazioneId = $PrenotazioneId";
	$row = $db->query_first ( $sql );

	if (! empty ( $row ['PrenotazioneId'] )) {
		$data = null;
		$data ['PrenotazioneStato'] = 2;
		$res = $db->update ( "RT_Prenotazione", $data, "PrenotazioneId=$PrenotazioneId" );
		$res1 = $db->update ( "RT_PrenotazionePercorso", $data, "PrenotazioneId=$PrenotazioneId" );
		echo ("La prenotazione e' stata posta nello stato DA CONFERMARE in quanto la tratta scelta è soggetta a conferma. E' importante aggiungere posti alla corsa nella sezione gestione.");
		exit ();
	} else {
		$data = null;
		$data ['PrenotazioneStato'] = 1;
		$data = $storico->operazioni_update ( $data, $user );
		$res = $db->update ( "RT_Prenotazione", $data, "PrenotazioneId=$PrenotazioneId" );
		$res1 = $db->update ( "RT_PrenotazionePercorso", $data, "PrenotazioneId=$PrenotazioneId" );
		echo ("La prenotazione e' stata posta nello stato: CONFERMATA. E' importante aggiungere posti alla corsa nella sezione gestione.");
		exit ();
	}
	exit ();
}

function EmettiTitoliDiViaggio() {
	global $db, $prenotazione_wizard, $user;
	
	$db = new Database ();
	$db->connect ();

	$PrenotazioneId = $prenotazione_wizard->Id;
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

	$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
	$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
	$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];

	$prenotazione_wizard->conn = $db;
	if (!$DatiGeneraliArr['Multi']) {
		$prenotazione_wizard->EmettiBiglietti();
	} else {
		$sql = "SELECT PrenotazioneId FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0 AND OdcIdRef = " . $user->OdcId;
		$prenotazioni = $db->fetch_array($sql);

		foreach ($prenotazioni as $prenotazione) {
			$prenotazioneObj = new Prenotazione($prenotazione['PrenotazioneId']);
			$prenotazioneObj->conn = $db;
			$prenotazioneObj->EmettiBiglietti();
		}
	}

	$output = array();
	$output['prenotazioneId'] = $PrenotazioneId;
	$output['corsaId'] = $CorsaId;

	echo json_encode($output);
}

/*
 * function update($id) { global $user; $db= new Database(); $db->connect(); $storico=new StoricoOperazioni(); $storico->conn=$db; // prelevo i dati del form ed aggiorno tutte le proprietÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  dell'oggetto $data=$_POST['Tratta']; $data=$storico->operazioni_update($data,$user); $result=$db->update("RT_Tratta", $data, "TrattaId=".$id." and OdcIdRef=$user->OdcId"); if ($result) echo("ok"); else echo("no"); exit(); }
 */

if (is_object ( $user )) {
	$db = new Database ();
	$db->connect ();
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo ( $ModuloId );
	if (sizeof ( $permessi ) > 0) {

		if (! empty ( $_POST )) {
			switch ($_POST['action']) {
                                
               case "RimborsoParziale":        
               		$FunzioneId=2;
                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                    if (sizeof($permesso))
                    	RimborsoParziale();   
                    else
                    	Errors::$ErrorePermessiModuloFunzione;
				break;
				
				case "GetPasseggeri":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						GetPasseggeri();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;
				
				case "GetImportoMassimoRimborsabile" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						GetImportoMassimoRimborsabile();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;
                            
				case "create" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						create();
					else
						Errors::$ErrorePermessiModuloFunzione;

						// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni

					break;

				case "del" :
					$FunzioneId = 3;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

				case "update" :

					if (isset ( $_POST ['Rimborsa'] )) {
						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
						if (sizeof ( $permesso ))
							rimborsa ();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}
					if (isset ( $_POST ['Annulla'] )) {
						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
						if (sizeof ( $permesso ))
							annulla ();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset ( $_POST ['AnnullaForzato'] )) {
						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
						if (sizeof ( $permesso ))
							annulla_forzato ();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					if (isset ( $_POST ['Modifica'] )) {
						$FunzioneId = 2;
						$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
						if (sizeof ( $permesso ))
							modifica ();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}

					else {

						$FunzioneId = 4;
						$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
						if (sizeof ( $permesso ))
							modifica ();
						else
							Errors::$ErrorePermessiModuloFunzione;
					}
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
			}
		}

		elseif (! empty ( $_REQUEST )) {

			switch ($_REQUEST['do']) {

				case "ConfermaPrenotazione" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						ConfermaPrenotazione ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "ConfermaPrenotazioneLista" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						ConfermaPrenotazioneLista ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "GetCorse" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						GetCorse ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "GetFermate" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						GetFermate();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "GetTipologiaBiglietti" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						GetTipologiaBiglietti ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "AbilitaTipoPrenotazione" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						AbilitaTipoPrenotazione ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "GetNotePerTratta" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						GetNotePerTratta ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "MostraSchemaBus" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						MostraSchemaBus ();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;

				case "EmettiTitoliDiViaggio" :
					$FunzioneId = 2;
					$permesso = $user->ControllModuloFunzionePermesso ( $ModuloId, $FunzioneId );
					if (sizeof ( $permesso ))
						EmettiTitoliDiViaggio();
					else
						Errors::$ErrorePermessiModuloFunzione;
				break;
			}
		}
	} 	// end verifica permessi
	else {
		Errors::$ErrorePermessiModulo;
	}
}

// se l'utente non ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¨ loggato
else {
	header ( "Location: /logout.php" );
}
?>