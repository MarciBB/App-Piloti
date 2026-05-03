<?php

/**
 * Description of class
 *
 * @author Luca Casaburi (MDT)
 */
class PrenotazioneDettaglio  {

	public $Id;
	public $DatiGenerali;
	public $conn;

	function __construct($PrenotazioneDettaglioId) {
	    $this->Id = $PrenotazioneDettaglioId;
	}
	
	public function inizializzaDatiGenerali()
	{
	        global $user;
	        $db=$this->conn;
	        $sql = "SELECT * From RT_PrenotazioneDettaglio WHERE PrenotazioneDettaglioId=$this->Id";
	        $row = $db->query_first($sql);
	        
	        if (!empty($row['Comune']))
	        	$this->DatiGenerali=$row;
	        else
	        {
	            print("errore");
	            exit();
	        }
	}


	public function getPrenotazioneDettaglio($prenotazioneNumero, $lineaId, $corsaId, $dataPartenza)
	{
	        $db=$this->conn;    
	        $sql = "SELECT  RT_PrenotazioneDettaglio.*, RT_PrenotazionePercorso.ComuneSalitaId, RT_PrenotazionePercorso.ComuneDiscesaId
					FROM RT_PrenotazionePercorso
					LEFT JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazionePercorso.ComuneSalita = RT_PrenotazioneDettaglio.ComunePartenza)
					WHERE RT_PrenotazioneDettaglio.PrenotazioneNumero=$prenotazioneNumero 
					AND RT_PrenotazionePercorso.LineaId=$lineaId
					AND RT_PrenotazionePercorso.CorsaId=$corsaId
					AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza'";
	        $row = $db->query_first($sql);
	        
	        return $row;
	}
	
	public function getPrenotazioniDettaglio($lineaId, $corsaId, $dataPartenza)
	{
		$db=$this->conn;
		$sql = "SELECT RT_PrenotazioneDettaglio.Nome, RT_PrenotazioneDettaglio.Cognome, RT_PrenotazioneDettaglio.PrenotazioneNumero, RT_Prenotazione.ClienteNome 
				FROM
				RT_PrenotazionePercorso
				LEFT JOIN RT_Prenotazione ON RT_PrenotazionePercorso.PrenotazioneId = RT_Prenotazione.PrenotazioneId
				LEFT JOIN RT_PrenotazioneDettaglio ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId AND RT_PrenotazioneDettaglio.ComunePartenza=RT_PrenotazionePercorso.ComuneSalita AND RT_PrenotazioneDettaglio.PrenotazioneId=RT_Prenotazione.PrenotazioneId)
				WHERE 
				RT_PrenotazioneDettaglio.LineaId=$lineaId 
				AND RT_PrenotazioneDettaglio.CorsaId=$corsaId 
				AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza' 
				AND ((RT_Prenotazione.PrenotazioneStato=3 AND RT_PrenotazioneDettaglio.Escludi=0) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=1) OR (RT_Prenotazione.PrenotazioneStato=1 AND RT_Prenotazione.Pagato=0 AND RT_Prenotazione.ScadenzaPrenotazione>$dataPartenza) OR (RT_Prenotazione.ABordo=1))";
				
		$rows = $db->fetch_array($sql);
		return $rows;
	}
	
	public function getInfo($numeroPrenotazione, $idLinea, $idCorsa, $dataPartenza){
		$sql = "SELECT
				RT_PrenotazionePercorso.ComuneSalitaId, RT_PrenotazionePercorso.ComuneDiscesaId, RT_PrenotazioneDettaglio.ComunePartenza, RT_PrenotazioneDettaglio.ComuneArrivo,
				RT_PrenotazioneDettaglio.Nome, RT_PrenotazioneDettaglio.Cognome, RT_PrenotazioneDettaglio.TipologiaBiglietto
				FROM
				RT_PrenotazionePercorso
				LEFT JOIN RT_PrenotazioneDettaglio ON RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
				WHERE
				RT_PrenotazionePercorso.CorsaId=$idCorsa AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza'
				AND RT_PrenotazionePercorso.LineaId=$idLinea AND RT_PrenotazioneDettaglio.PrenotazioneNumero=$numeroPrenotazione";
		$row = $this->conn->query_first($sql);
		return $row;
	}
	
	public function isEscluso($numeroPrenotazione, $idLinea, $idCorsa, $dataPartenza){
		$sql = "SELECT
				RT_PrenotazioneDettaglio.Escludi
				FROM
				RT_PrenotazionePercorso
				LEFT JOIN RT_PrenotazioneDettaglio ON RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
				WHERE
				RT_PrenotazionePercorso.CorsaId=$idCorsa AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza'
				AND RT_PrenotazionePercorso.LineaId=$idLinea AND RT_PrenotazioneDettaglio.PrenotazioneNumero=$numeroPrenotazione
		AND and RT_PrenotazioneDettaglio.LineaId=$idLinea";
		$row = $this->conn->query_first($sql);
		if($row['Escludi']==1){
			return true;
		}else{
			return false;
		}
	}
	
	public function getIdPrenotazione($numeroPrenotazione, $idLinea, $idCorsa, $dataPartenza){
		$sql = "SELECT
				RT_PrenotazioneDettaglio.PrenotazioneId
				FROM
				RT_PrenotazionePercorso
				LEFT JOIN RT_PrenotazioneDettaglio ON RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId
				WHERE
				RT_PrenotazionePercorso.CorsaId=$idCorsa AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza'
				AND RT_PrenotazionePercorso.LineaId=$idLinea AND RT_PrenotazioneDettaglio.PrenotazioneNumero=$numeroPrenotazione";
		$row = $this->conn->query_first($sql);
		return $row['PrenotazioneId'];
	}
	
	public function getPrenotazioneClienteNome($numeroPrenotazione, $idLinea, $idCorsa, $dataPartenza){
		$sql = "SELECT RT_Prenotazione.ClienteNome 
				FROM RT_PrenotazionePercorso 
				LEFT JOIN RT_PrenotazioneDettaglio 
				ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId)
				LEFT JOIN RT_Prenotazione 
				ON (RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId)
				WHERE RT_PrenotazionePercorso.CorsaId=$idCorsa 
				AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza' 
				AND RT_PrenotazionePercorso.LineaId=$idLinea 
				AND RT_PrenotazioneDettaglio.PrenotazioneNumero=$numeroPrenotazione";

		$row = $this->conn->query_first($sql);
		return $row['ClienteNome'];
	}
	
	public function getCodicePrenotazione($numeroPrenotazione, $idLinea, $idCorsa, $dataPartenza){
		$sql = "SELECT RT_Prenotazione.CodicePrenotazione 
				FROM RT_PrenotazionePercorso 
				LEFT JOIN RT_PrenotazioneDettaglio 
				ON (RT_PrenotazionePercorso.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId)
				LEFT JOIN RT_Prenotazione 
				ON (RT_Prenotazione.PrenotazioneId = RT_PrenotazioneDettaglio.PrenotazioneId)
				WHERE RT_PrenotazionePercorso.CorsaId=$idCorsa 
				AND RT_PrenotazionePercorso.CorsaDataPartenza='$dataPartenza' 
				AND RT_PrenotazionePercorso.LineaId=$idLinea 
				AND RT_PrenotazioneDettaglio.PrenotazioneNumero=$numeroPrenotazione";
		
		$row = $this->conn->query_first($sql);
		return $row['CodicePrenotazione'];
	}
	
	public function getCellulare($codicePrenotazione){
		$sql = "SELECT ClienteCellularePrefisso, ClienteCellulare
		FROM RT_Prenotazione
		WHERE CodicePrenotazione='$codicePrenotazione'";
	
		$row = $this->conn->query_first($sql);
		return $row['ClienteCellularePrefisso'].$row['ClienteCellulare'];
	}
}
?>