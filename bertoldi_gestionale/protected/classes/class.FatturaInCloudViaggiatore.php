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
class FatturaInCloudViaggiatore {


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
		
		$sql = "SELECT * FROM FatturaInCloudViaggiatore WHERE FatturaId=$Id";
		
		$row = $db->query_first($sql);
		
		if (!empty($row['FatturaId']))
			$this->DatiGenerali = $row;
		else
		{
			print("errore");
			exit();
		}
	}
	
	public function getTipoFattura($PrenotazioneId){
		$db = $this->conn;
		$sql = "SELECT * FROM FatturaInCloudViaggiatore
                        WHERE PrenotazioneId = $PrenotazioneId
                        AND (Tipo = 'invoice' OR Tipo = 'receipt')";
		$fattura = $db->query_first($sql);
		
		return $fattura;
	}
	
	public function getTipoFatturaByMovimento($PrenotazioneMovimentoId){
	    $db = $this->conn;
	    $sql = "SELECT * FROM FatturaInCloudViaggiatore
                        WHERE PrenotazioneMovimentoId = $PrenotazioneMovimentoId";
	    $fattura = $db->query_first($sql);
	    return $fattura;
	}
	
	public function getFatture($PrenotazioneId){
	    $db = $this->conn;
	    $sql = "SELECT * FROM FatturaInCloudViaggiatore
                        WHERE PrenotazioneId = $PrenotazioneId";
	    $fattura = $db->fetch_array($sql);
	    
	    return $fattura;
	}
	
	
	public function getTipoNotaCredito($PrenotazioneId){
	    $db = $this->conn;
	    $sql = "SELECT * FROM FatturaInCloudViaggiatore
                        WHERE PrenotazioneId = $PrenotazioneId
                        AND 'credit_note'";
	    $notaCredito = $db->query_first($sql);
	    
	    return $notaCredito;
	}
}
?>