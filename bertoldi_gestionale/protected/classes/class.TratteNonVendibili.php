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
class TratteNonVendibili {
    
    public $Id;
     public $conn;
	function __construct() {
	   
	}
	
	public function eliminaFermateNonVendibili()
	{
	        global $user;
	        $db=$this->conn;
	        $Id=$this->Id;
	        $sql ="SELECT
RT_TratteNonVendibili.TratteNonVendibiliId,
fp.FermataId as fpickup,
fd.FermataId as fdropoff
FROM
RT_TratteNonVendibili
INNER JOIN RT_Fermata fp ON RT_TratteNonVendibili.ComunePickUpId = fp.ComuneId
INNER JOIN RT_Fermata fd ON RT_TratteNonVendibili.ComuneDropOffId = fd.ComuneId";     
	      
	        
                 $row_edit = $db->fetch_array($sql);
                 
                foreach ($row_edit as $key=>$value)
                {
                    $fp=$value['fpickup'];
                    $fd=$value['fdropoff'];
                    
                    
                    $db->delete('RT_CorsaTariffa',"FermataPickup=$fp and FermataDropOff=$fd");
    
                }
                
                $sql="truncate table RT_PercorsoBreve";
                $db->query($sql);
    
                $sql="truncate table RT_PercorsoBreveTratte";
                $db->query($sql);
                
                
                return true;
	}

}
?>
