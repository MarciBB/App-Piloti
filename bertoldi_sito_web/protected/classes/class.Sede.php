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
class Sede {
    
    public $conn;
    public $GestoreId;
    public $RagioneSociale;
    public $Indirizzo;
    public $ComuneId;
    public $Comune;
    public $Telefono;
    public $Fax;
    public $Email;
    public $Stato;
    public $CodiceSede;
    public $SedeLegale;
    public $VenditaOltreOrario;
    

    function __construct() {
        
    }
    
     function inizializza($SedeId) {
        
        $db=$this->conn;
        $sql = "SELECT * From ElencoSediView WHERE SedeId=$SedeId";      
        $row = $db->query_first($sql);       
            // se esiste l'anagrafica
            if(!empty($row['SedeId']))
            {
                    $this->GestoreId=$row['GestoreId'];
                    $this->RagioneSociale=$row['RagioneSociale'];
                    $this->ComuneId=$row['ComuneId'];
                    $this->Comune=$row['Comune'];
                    $this->Indirizzo=$row['Indirizzo'];                    
                    $this->Telefono=$row['Telefono'];
                    $this->Fax=$row['Fax'];
                    $this->Email=$row['Email'];
                    $this->Stato=$row['Stato'];
                    $this->CodiceSede=$row['CodiceSede'];
                    $this->SedeLegale=$row['SedeLegale'];
                    $this->VenditaOltreOrario=$row['VenditaOltreOrario'];
                    
            }            
        }
    
    public function getSediByGestori($arr_gestori_id)
    {
        $out = array();
       
        $db=$this->conn;
        $sql = "SELECT Sede.SedeId, Sede.GestoreId, Sede.ComuneId, Sede.Indirizzo, Comune.Comune, Comune.ComuneId, Gestore.RagioneSociale,Sede.Stato 
        FROM Sede 
        INNER JOIN Comune ON Sede.ComuneId = Comune.ComuneId 
        INNER JOIN Gestore ON Sede.GestoreId = Gestore.GestoreId 
        WHERE Sede.Cancella = 0 and Sede.GestoreId IN ($arr_gestori_id)
        order by Comune.Comune";
        
        
        
       $ArrObject = $db->fetch_array($sql);
        
        if ($ArrObject)
        {    
          $ArrObjectSize=count($ArrObject);
          $i=0;
            while ($i< $ArrObjectSize)
            {
                $value=$ArrObject[$i]['SedeId'];
                $label=$ArrObject[$i]['Comune']." - ".$ArrObject[$i]['Indirizzo']." (".$ArrObject[$i]['RagioneSociale'].")";
                $out[$i]['SedeId']=$value;
                $out[$i]['Sede']=$label;
                 $out[$i]['Stato']=$ArrObject[$i]['Stato'];
                
                

                $i++;
            }
          }
          return $out;
    }
    
    public function getSediByOdc($OdcId)
    {
        $out = array();
        $tmp=array();
        $db=$this->conn;
        $sql = "SELECT * FROM ViewListaGestoriSedi WHERE OdcId=$OdcId and SedeWeb=0 order by Comune,Indirizzo ";
       
        $queryid = $db->query($sql);
        while($row=$db->fetch($queryid)){
            $tmp['GestoreId']=$row['GestoreId'];
            $tmp['SedeId']=$row['SedeId'];
            $tmp['RagioneSociale']=$row['RagioneSociale'];
            $tmp['SedeIndirizzo']=$row['Indirizzo'];
            $tmp['SedeComune']=$row['Comune'];
            array_push($out, $tmp);
        }        
     
       return $out;
    }
    
    public function getSediPerWebByOdc($OdcId)
    {
        $out = array();
        $tmp=array();
        $db=$this->conn;
        $sql = "SELECT * FROM ViewListaGestoriSedi WHERE OdcId=$OdcId and SedeWeb=0 and Stato=1 and StatoGestore=1  and Cancella=0 order by Comune,Indirizzo ";
       
        $queryid = $db->query($sql);
        while($row=$db->fetch($queryid)){
            $tmp['GestoreId']=$row['GestoreId'];
            $tmp['SedeId']=$row['SedeId'];
            $tmp['RagioneSociale']=$row['RagioneSociale'];
            $tmp['SedeIndirizzo']=$row['Indirizzo'];
            $tmp['SedeComune']=$row['Comune'];
            array_push($out, $tmp);
        }        
     
       return $out;
    }

    private function makeUnique ($length=16)
    {
    	$salt       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ012345678';
    	$len        = strlen($salt);
    	$makepass   = '';
    	mt_srand(10000000*(double)microtime());
    	for ($i = 0; $i < $length; $i++) {
    		$makepass .= $salt[mt_rand(0,$len - 1)];
    	}
    	return $makepass;
    }
    
    public function registrazione($gestoreId, $comuneId, $indirizzo, $telefono,
    		$fax, $email, $codiceSede) {
    	global $user;
    	$db=$this->conn;
    	
    	$row = null;
    	do {
	    	$codiceSede = $this->makeUnique(5);
	    	$sql = "SELECT * FROM Sede WHERE CodiceSede = '$codiceSede'";
	    	$row = $db->query_first($sql);
    	}while (isset($row['CodiceSede']));
    	$storico=new StoricoOperazioni();
    	$data['GestoreId'] = $gestoreId;
    	$data['ComuneId'] = $comuneId;
    	$data['Indirizzo'] = $indirizzo;
    	$data['Telefono'] = $telefono;
    	$data['Fax'] = $fax;
    	$data['Email'] = $email;
    	$data['CodiceSede'] = $codiceSede;
    	$data['VenditaOltreOrario'] = 0;
    
    	$data=$storico->operazioni_insert($data,$user);
    	$data['OdcIdRef'] = 1;
    	$data['GestoreIdRef'] = 1;
    	$data['OpeIns'] = 13;
    	$data['SedeIns'] = 1;

    	$result = $db->insert("Sede", $data);
    	
		$linee= array(13,14,16,17);    	
    	foreach($linee as $l){
    		$dataConvenzione = array();
    		$dataConvenzione['GestoreId'] = $gestoreId;
    		$dataConvenzione['ValidaDal'] = date('Y-m-d');
    		$dataConvenzione['ValidaAl'] = date('Y-m-d', strtotime('+1 year'));;
    		$dataConvenzione['ProvvigioneId'] = 3 ;
    		$dataConvenzione['LineaId'] = $l;
    		$dataConvenzione['SoloPrenotazione'] = 0;
    		$dataConvenzione['ListaAttesa'] = 0;
    		
    		$dataConvenzione=$storico->operazioni_insert($dataConvenzione,$user);
    		$dataConvenzione['OdcIdRef'] = 1;
    		$dataConvenzione['GestoreIdRef'] = 1;
    		$dataConvenzione['OpeIns'] = 13;
    		$dataConvenzione['SedeIns'] = 1;
    		
    		$result2 = $db->insert("GestoreConvenzione", $dataConvenzione);
    		
    	}
    	

    	
    	
    	
    	return $codiceSede;
    }
    
}
?>
