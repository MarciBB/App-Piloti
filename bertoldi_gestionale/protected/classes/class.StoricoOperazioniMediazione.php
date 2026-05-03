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
class StoricoOperazioniMediazione extends Operatore {

    public $conn;

    
    public function AggiornaStoricoMediazione($MediazioneId,$user,$OperazioneId)
    {
        
        
        $data['OpeIns']=$user->OperatoreId;
        $data['SedeIns']=$user->SedeId;
        $data['DataIns']=date('Y-m-d H:i:s');
        $data['IpIns']=getenv('REMOTE_ADDR');  
        $data['OdcIdRef']=$user->OdcId;
        $data['GestoreIdRef']=$user->GestoreId;
        $data['MediazioneId']=$MediazioneId;
        $data['StoricoOperazioneId']=$MediazioneId;
        $result=$this->conn->insert("StoricoOperazioniMediazione", $data); 
        
        
    }
    
    
    
    
        
        
        
        
    



}
?>
