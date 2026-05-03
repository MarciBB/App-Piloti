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
class StoricoOperazioni extends Operatore {



    
    public function operazioni_insert($campi_tabella,$user)
    {
        
        
        $campi_tabella['OpeIns']=$user->OperatoreId;
        $campi_tabella['SedeIns']=$user->SedeId;
        $campi_tabella['DataIns']=date('Y-m-d H:i:s');
        $campi_tabella['IpIns']=getenv('REMOTE_ADDR');  
        $campi_tabella['OdcIdRef']=$user->OdcId;
        $campi_tabella['GestoreIdRef']=$user->GestoreId;
        $campi_tabella['Cancella']=0;
        
        
        if (!(isset($campi_tabella['Stato'])))
        $campi_tabella['Stato']=1;
       
        
        return $campi_tabella;
        
        
    }
    
    public function operazioni_update($campi_tabella,$user)
    {
        
        
        $campi_tabella['OpeAgg']=$user->OperatoreId;
        $campi_tabella['SedeAgg']=$user->SedeId;
        $campi_tabella['DataAgg']=date('Y-m-d H:i:s');
        $campi_tabella['IpAgg']=getenv('REMOTE_ADDR');  
       // $campi_tabella['OdcIdRef']=$user->OdcId;
       // $campi_tabella['GestoreIdRef']=$user->GestoreId;
        
        return $campi_tabella;
        
        
    }
    
    function printRecordIUInformation($RecordId,$TableName)
    {
        
        
        // dato id del record e tabella estapolo e stampo il box html sul lato destro della schermata:
        /*
        OpeIns    
        DataIns
        SedeIns
        OpeAgg
        DataAgg
        SedeAgg
        */
        ?>
                      <div class="brain_mediazioniAssociate">
				<h2>Informazioni sul record (<strong>2</strong>)</h2>
				<span class="rigaAssociata">
					<a href="">OpeIns</a> Esposito Antonio
				</span>
				<span class="rigaAssociata">
					<a href="">DataIns</a> 12/12/2011
				</span>
				<a class="visualizza" href="">visualizza tutte</a>
			</div>
			

        <?php
    
        
        
    }
        
        
        
        
    



}
?>
