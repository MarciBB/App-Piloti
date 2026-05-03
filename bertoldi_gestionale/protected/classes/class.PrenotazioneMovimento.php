<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author m.casaburi
 */
class PrenotazioneMovimento {


	public $Id;
	public $conn;
	public $DatiGenerali;

	function __construct($Id = null) {
		$this->Id = $Id;
	}

	public function inizializzaDatiGenerali() {
		global $user;
		$db = $this->conn;
		$Id = $this->Id;
		
		$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId=$Id and OdcIdRef=$user->OdcId";
		
		$row = $db->query_first($sql);
		
		if (!empty($row['OdcIdRef']))
			$this->DatiGenerali = $row;
		else
		{
			print("errore");
			exit();
		}
	}
	
	public function getTotaliImporti($PrenotazioneId) {
		global $user;
		$db = $this->conn;
		
		/*$sql = "SELECT TotalePrenotazione, 
			(TotalePrenotazione + SUM(Supplemento)) TotaleDaPagare, 
			SUM(ImportoPagato) TotalePagato
			FROM 
				RT_PrenotazioneMovimento pm LEFT JOIN RT_Prenotazione p ON (pm.PrenotazioneId = p.PrenotazioneId)
			WHERE 
				pm.PrenotazioneId=$PrenotazioneId and pm.OdcIdRef=$user->OdcId GROUP BY pm.PrenotazioneId";*/
// 		$sql = "Select Multi, CodicePrenotazione from RT_Prenotazione where PrenotazioneId = $PrenotazioneId";
// 		$row = $db->query_first($sql);
		
		$sql = "SELECT TotalePrenotazione,
			(TotalePrenotazione + SUM(Supplemento) ) TotaleDaPagare,
			SUM(ImportoPagato) TotalePagato, SUM(Supplemento) TotaleSupplemento
			FROM
			RT_PrenotazioneMovimento pm LEFT JOIN RT_Prenotazione p ON (pm.PrenotazioneId = p.PrenotazioneId)
			WHERE
			pm.PrenotazioneId=$PrenotazioneId AND
			pm.Cancella = 0 AND
			pm.OdcIdRef = $user->OdcId
			GROUP BY pm.PrenotazioneId";
			$totali = $db->query_first($sql);
// 		if($row['Multi'] == 0){
			return $totali;
// 		} else {
// 			$sql =  "Select Multi, SUM(TotaleDaPagare) TotMulti from RT_Prenotazione where CodicePrenotazione = '".$row['CodicePrenotazione']."' and (PrenotazioneStato = 3 OR  PrenotazioneStato = 1)";
// 			$rowPrenotazioni = $db->query_first($sql);
// 			$totali['TotalePrenotazione']=$rowPrenotazioni['TotMulti'];
// 			$totali['TotaleDaPagare']=$rowPrenotazioni['TotMulti'] + $totali['TotaleSupplemento'];
// 			return $totali;
// 		}
	}
	
	public function getMinScadenza($PrenotazioneId){
		global $user;
		$db = $this->conn;
		$sql = "SELECT MAX(Scadenza) MinScadenza FROM RT_PrenotazioneMovimento WHERE TipoMovimento = 'I' AND PrenotazioneId=$PrenotazioneId AND OdcIdRef=$user->OdcId GROUP BY PrenotazioneId";
		$row = $db->query_first($sql);
		return $row['MinScadenza'];
	}
	
	
	public function getAllPrenotazioneMovimento($PrenotazioneId){
		global $user;
		$db = $this->conn;
		$sql = "SELECT PagamentoTipoId, TipoMovimento, Causale, Data, Importo, Supplemento, DataPagamento, ImportoPagato, Scadenza,
		CodicePagamento, CanalePagamentoId, DataIns, OpeIns, IpIns, DataAgg, OpeAgg, IpAgg, Stato, Cancella, UpdateCount, OdcIdRef, GestoreIdRef, ScontrinoId,
		ScontrinoData, ScontrinoTipo, ScontrinoInvioAuto, ScontrinoIdAnnullato, ScontrinoDataAnnullato, ScontrinoNotifica, ScontrinoDataInvio, SedeIns
		 FROM RT_PrenotazioneMovimento WHERE PrenotazioneId=$PrenotazioneId";
		
		$row = $db->fetch_array($sql);
		
		return $row;
	}
}
?>