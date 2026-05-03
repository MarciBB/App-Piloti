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
class Gestore {
	public $ArrGestore;
	public $GestoreId;
	public $conn;
	public $GestoreDatiGenerali;
	function __construct($GestoreId = null) {
		$this->GestoreId = $GestoreId;
	}
	public function inizializzaDatiGenerali($GestoreId) {
		global $user;
		$db = $this->conn;
		$sql = "SELECT * From ViewListaGestori WHERE OdcIdRef=$user->OdcId and GestoreId=$this->GestoreId";
		$row = $db->query_first ( $sql );
		
		if (! empty ( $row ['OdcIdRef'] ))
			$this->GestoreDatiGenerali = $row;
		else {
			print ("errore") ;
			exit ();
		}
	}
	public function getGestoreFigli($idgestore) {
		$mysqli = new mysqli ( Config::$dbserver, Config::$dbuser, Config::$dbpass, Config::$dbname );
		$SQL = "call Gestore_proc('S',$idgestore,0,0,'')";
		
		// 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore
		
		if (($result = $mysqli->query ( $SQL )) === false) {
			printf ( "Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL );
			exit ();
		}
		// $result = $mysqli->query($SQL);
		$out = array ();
		
		while ( $myrow = $result->fetch_array ( MYSQLI_ASSOC ) ) {
			$out [] = $myrow ["GestoreId"];
			// echo($myrow["GestoreId"]."-".$myrow['RagioneSociale']."<br />");
		}
		$result->close ();
		$mysqli->close ();
		return $out;
	}
	public function getGestoreAll($idgestore) {
		$mysqli = new mysqli ( Config::$dbserver, Config::$dbuser, Config::$dbpass, Config::$dbname );
		$SQL = "call Gestore_proc('S',$idgestore,0,0,'')";
		
		// echo($SQL);
		
		// 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore
		
		if (($result = $mysqli->query ( $SQL )) === false) {
			printf ( "Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL );
			exit ();
		}
		
		// $result = $mysqli->query($SQL);
		$out = array ();
		
		while ( $myrow = $result->fetch_array ( MYSQLI_ASSOC ) ) {
			
			$out [] = array (
					"GestoreId" => $myrow ["GestoreId"],
					"RagioneSociale" => $myrow ["RagioneSociale"] 
			);
		}
		$result->close ();
		$mysqli->close ();
		$this->ArrGestore = $out;
	}
	
	public function registrazione($ragioneSociale, $partitaIva, $codiceFiscale, $indirizzo, 
    	    $cap, $comuneId, $telefono, $fax, $email, $emailPec, $gruppo,
    	    $termini = 1, $trattamentoDati = 1, $comunicazioni = 1) {
		global $user;
		$db=$this->conn;
	
		$sql = "SELECT * FROM Gestore WHERE PartitaIva = '$partitaIva' OR CodiceFiscale = '$codiceFiscale'";
		$row = $db->fetch_array($sql);
		if(count($row)>0){
			return false;
		}
		$sql = "SELECT * FROM Gestore where Sx > 1400 and Sx < 1600 order by Sx desc";
		$row = $db->fetch_array($sql);
		$sx = (int)$row[0]['Sx']+1;
		$dx = $sx;
		$storico=new StoricoOperazioni();
		$data['OdcId'] = 1;
		$data['RagioneSociale'] = $ragioneSociale;
		$data['PartitaIva'] = $partitaIva;
		$data['CodiceFiscale'] = $codiceFiscale;
		$data['Indirizzo'] = $indirizzo;
		$data['Cap'] = $cap;
		$data['ComuneId'] = $comuneId;
		$data['Telefono'] = $telefono;
		$data['Fax'] = $fax;
		$data['Email'] = $email;
		$data['EmailPec'] = $emailPec;
		$data['Sx'] = $sx;
		$data['Dx'] = $dx;
		$data['GestorePadre'] = 1;
		$data['GestorePrimario'] = 0;
		$data['GestoreGruppoId'] = $gruppo;
		$data['Verificato'] = 0;
		$data['Termini'] = $termini;
		$data['TrattamentoDati'] = $trattamentoDati;
		$data['Comunicazioni'] = $comunicazioni;
	
		$data=$storico->operazioni_insert($data,$user);
		$data['OpeIns'] = 13;
		$data['SedeIns'] = 1;
		$data['OdcIdRef'] = 1;
		$data['GestoreIdRef'] = 1;
		$data['Stato'] = 1;
		$result = $db->insert("Gestore", $data);
		return $result;
	}
}
?>
