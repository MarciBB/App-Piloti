<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author l.casaburi MDT Software
 */
class UtenteWeb {
    
  
    public $Id;
    public $conn;
    public $DatiGenerali;
    
    public $OperatoreId=5;
    public $SedeId=1;
    public $GestoreId=1;
    public $OdcId = 1;



function __construct($Id=null) {
    $this->Id = $Id;
}

public function inizializzaDatiGenerali()
{
        $db=$this->conn;
        $Id=$this->Id;
        $sql = "SELECT * From RT_MembershipClub WHERE MembershipClubId=$Id";      
    //  echo($sql);
        $row = $db->query_first($sql);
        
        if (!empty($row['OdcIdRef']))
        $this->DatiGenerali=$row;
        else
        {
            print("errore");
            exit();
        }
    
    
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

public function registrazione($nome, $cognome, $email, $password, $cell, $sesso, $codiceFiscale, $partitaIva, $comuneId, $indirizzo, $tel, $telFamiliare, $cap, $termini = 1, $trattamentoDati = 1, $newsletter = 1, $comunicazioni = 1)
{
    global $user;
    $db=$this->conn;
    
    $sql = "SELECT * FROM RT_MembershipClub WHERE Email = '$email'";
    $row = $db->fetch_array($sql);
    if(count($row)>0){
        return false;
    }
    
    $codice = time();
    $validazione = 0;
    $codValidazione = $this->generateRandomString(10);
    $storico=new StoricoOperazioni();
    $data['MembershipClubCode'] = $codice;
    $data['Validazione'] = $validazione;
    $data['ValidazioneCodice'] = $codValidazione;
    $data['AnagraficaTipoId'] = 1;
    $data['CognomeRagioneSociale'] = $cognome;
    $data['Nome'] = $nome;
    $data['CodiceFiscale'] = $codiceFiscale;
    $data['PartitaIva'] = $partitaIva;
    $data['ComuneResidenzaId'] = $comuneId;
    $data['CapResidenza'] = $cap;
    $data['IndirizzoResidenza'] = $indirizzo;
    $data['Telefono'] = $tel;
    $data['Cellulare'] = $cell;
    $data['TelefonoFamiliare'] = $telFamiliare;
    $data['Email'] = $email;
    $data['PasswordWeb'] = $password;
    $data['Sesso'] = $sesso;
    $data['Termini'] = $termini;
    $data['TrattamentoDati'] = $trattamentoDati;
    $data['Comunicazioni'] = $comunicazioni;
    $data['Newsletter'] = $newsletter;
    
    $data=$storico->operazioni_insert($data,$user);
    $data['OdcIdRef'] = 1;
    $data['GestoreIdRef'] = 1;
    
    $result = $db->insert("RT_MembershipClub", $data);
    return $result;
}


public function autenticazione($email, $password, $get = false)
{
	$db=$this->conn;
	$sql = "SELECT * FROM RT_MembershipClub WHERE Email = '$email'";
	$row = $db->query_first($sql);
	if($get) {
		return $row;
	}

    if(md5($password) == $row['PasswordWeb']) {
        $sql = "UPDATE RT_MembershipClub Set 
        PasswordWeb = '".password_hash($password, PASSWORD_DEFAULT)."'
        WHERE Email = '$email'";
        $update_password = $db->query($sql);
        if($update_password) {
            return $row;
        }
    } else {
        $verify = password_verify($password, $row['PasswordWeb']);
        if($verify) {
            return $row;
        }
    }
}

public function validazione($email, $codiceValidazione)
{
	$db=$this->conn;
	$sql = "SELECT * FROM RT_MembershipClub WHERE Email = '$email' AND ValidazioneCodice = '$codiceValidazione'";
	$row = $db->fetch_array($sql);
	
	if(count($row) == 0){
		return false;
	}
	
	$sql = "UPDATE RT_MembershipClub Set Validazione = 1 WHERE Email = '$email'"; 
	$row = $db->query($sql);
	return true;
}

public function modifica($nome, $cognome, $email, $cell, $sesso, $codiceFiscale, $partitaIva, $comuneId, $indirizzo, $tel, $telFamiliare, $password)
{
	global $db, $user;
        
        $storico = new StoricoOperazioni();
        $storico->conn = $db;

        /*if(isset($cognome)){
        	$data['CognomeRagioneSociale'] = $cognome;
        }
        if(isset($nome)){
        	$data['Nome'] = $nome;
        }*/
        if(isset($codiceFiscale)){
        	$data['CodiceFiscale'] = $codiceFiscale;
        }
        if(isset($partitaIva)){
        	$data['PartitaIva'] = $partitaIva;
        }
        if(isset($comuneId) && $comuneId != ''){	
        	$data['ComuneResidenzaId'] = $comuneId;
        } else {
        	$comuneId = 0;
        }
        if(isset($indirizzo)){
        	$data['IndirizzoResidenza'] = $indirizzo;
		}
		if(isset($tel)){
			$data['Telefono'] = $tel;
		}
		if(isset($cell)){
			$data['Cellulare'] = $cell;
		}
		if(isset($telFamiliare)){
			$data['TelefonoFamiliare'] = $telFamiliare;
		}
		if(isset($sesso)){
			$data['Sesso'] = $sesso;
		}
		if(isset($password) && $password != ''){
        	$data['PasswordWeb'] = $password;
		}
		
        $data = $storico->operazioni_update($data,$this);
        $sql = "UPDATE RT_MembershipClub Set 
				CognomeRagioneSociale = '$cognome', 
				Nome = '$nome', 
				CodiceFiscale = '$codiceFiscale',
				PartitaIva = '$partitaIva',
				ComuneResidenzaId = $comuneId,
				IndirizzoResidenza = '$indirizzo',
				Telefono = '$tel',
				Cellulare = '$cell',
				TelefonoFamiliare = '$telFamiliare',
				Sesso = $sesso,
				password = '$password'
				WHERE Email = '$email'";

        $result = $db->update("RT_MembershipClub", $data, "Email='".$email."'");
 		return $result;   
 		
}

public function recuperoPassword($email)
{
	$db=$this->conn;
	
	$codiceRecupero = $this->generateRandomString(10);
	$date = date("Y-m-d H:i:s");
	$sql = "UPDATE RT_MembershipClub Set RecuperoCodice = '$codiceRecupero', RecuperoData = '$date' WHERE Email = '$email'"; 
	$row = $db->query($sql);
	$sql = "SELECT * FROM RT_MembershipClub WHERE Email = '$email'";
	$row = $db->query_first($sql);
	
	return $row;
}

public function modificaPassword($email, $password, $codice)
{
	$db=$this->conn;

	$date = date("Y-m-d H:i:s", time() - 60 * 60 * 24);
	
	$sql = "SELECT * FROM RT_MembershipClub WHERE Email = '$email'";
	$row = $db->query_first($sql);
	if($codice == $row['RecuperoCodice'] && $date<$row['RecuperoData']){
		$sql = "UPDATE RT_MembershipClub Set PasswordWeb = '$password' WHERE Email = '$email'";
		$row = $db->query($sql);
		return true;
	} else {
		return false;
	}
	
}

public function getMembershipProfilo($MembershipProfiloId){
	global $db;
	$sql = "SELECT * FROM RT_MembershipProfilo WHERE MembershipProfiloId = $MembershipProfiloId";
	$row = $db->query_first($sql);
	return $row;
}

    
    
}
