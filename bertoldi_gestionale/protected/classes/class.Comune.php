<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author a.esposito
 */
class Comune  {

	public $Id;
	public $Comune;
	public $ArrComune=Array();
	public $DatiGenerali;
	public $conn;

	function __construct($ComuneId) {
	    $this->Id = $ComuneId;
	}
	
	
	public function inizializzaDatiGenerali()
	{
	        global $user;
	        $db=$this->conn;
	        $Id=$this->Id;
	        $sql = "SELECT 
			regione.idnazione AS idnazione,
			nazione.Nazione AS Nazione,
			regione.Regione AS Regione,
			regione.RegioneId AS RegioneId,
			provincia.Provincia AS Provincia,
			provincia.ProvinciaId AS ProvinciaId,
			comune.ComuneId AS ComuneId,
			comune.Comune AS Comune,
			comune.cap AS cap,
			comune.prefisso_tel AS prefisso_tel,
			comune.codice_istat AS codice_istat,comune.codice_catastale AS codice_catastale,
			comune.sito_comune AS sito_comune,
			nazione.NazioneId AS NazioneId 
			FROM Nazione AS nazione 
			JOIN Regione AS regione ON (nazione.NazioneId = regione.idnazione)
			JOIN Provincia AS provincia ON (regione.RegioneId = provincia.RegioneId)
			JOIN Comune AS comune ON (provincia.ProvinciaId = comune.provincia)
	        WHERE comune.ComuneId = $Id";
	        //echo($sql);
	        $row = $db->query_first($sql);
	        
	        if (!empty($row['Comune']))
	        	$this->DatiGenerali=$row;
	        else
	        {
	            print("errore");
	            exit();
	        }
	        $this->Comune = $this->DatiGenerali['Comune'];
	}


	public function getComuneByIdRegione($RegioneId_)
	{
	        $db=$this->conn;
	        $sql = "SELECT ComuneId,Comune From Comune where RegioneId=$RegioneId_";
	        $this->ArrComune = $db->fetch_array($sql);
	}
}
?>
