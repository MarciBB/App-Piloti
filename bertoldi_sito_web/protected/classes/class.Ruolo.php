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
class Ruolo {
    
    public $conn;
    public $OdcId;
    public $Odc;
    public $Nome; 
    public $Descrizione; 
    public $Stato;
    public $GestoreId;
    

    function __construct() {
        
    }
    
     function inizializza($RuoloId) {
         global $user;        
        $db=$this->conn;
        $sql = "SELECT * From RuoloView WHERE  OdcId=$user->OdcId and RuoloId=$RuoloId";
       
        $row = $db->query_first($sql);
            // se esiste l'anagrafica
            if(!empty($row['RuoloId']))
            {
                $this->OdcId=$row['OdcId'];
                $this->OdcRagioneSociale=$row['Odc'];
                $this->Nome=$row['Nome'];
                $this->Descrizione=$row['Descrizione'];
                $this->Stato=$row['Stato'];
            }
            else
            {
                print("errore");
                exit();
                
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
    
    public function getRuoliAttivi($OperatoreId,$OdcId){
        $out = array();
       
        $db=$this->conn;
        $sql="SELECT * FROM ViewRuoliOperatore WHERE OdcId=$OdcId";
        $queryid = $db->query($sql);
        $ruoloId="";
        $i=-1;
        while($row=$db->fetch($queryid)){
            if($ruoloId!=$row['RuoloId']){
                $i++;            
                $ruoloId=$row['RuoloId'];
                $out[$i]['RuoloId']=$row['RuoloId'];
                $out[$i]['RuoloNome']=$row['Nome'];
                $out[$i]['RuoloDescrizione']=$row['Descrizione'];
                $out[$i]['Attivo']=0;
            }
            if(($OperatoreId==$row['OperatoreId'])&&($out[$i]['Attivo']==0)) $out[$i]['Attivo']=1;                      
        }
        return $out;
    }

     public function getMergeRuoli($ruoli)
    {         
         $where="1=1 and ";
         $arr_ruoli=explode(",", $ruoli);
         foreach ($arr_ruoli as &$value) {
            $where.="(RuoloId=".$value.") or ";
        }
        $where=substr($where,0,strlen($where)-4);
         $db=$this->conn;
         $sql = "SELECT * FROM RuoliMergePermessi WHERE $where ";
         
        $queryid = $db->query($sql);
       $menu="";
       $modulo="";
       $funzione="";
       $fun_count=7;
     
          $table="<table class=\"TabellaOperatoreRuoli\">";
          $table.="<tr class='rowIntestazione'><td>modulo</td><td>lista</td><td>aggiunta</td><td>cancellazione</td><td>modifica</td><td>esportazione</td><td>importazione</td><td>stampa</td></tr>";                    
            while($row=$db->fetch($queryid)){
            
                if($menu!=$row['AppMenu']){
                    while($fun_count<7){                                                            
                        $fun_count++;
                        //$table.="<td>".$fun_count."</td>";
                        $table.="<td></td>";
                       }
                    $fun=0;   
                    $menu=$row['AppMenu'];
                    $table.="<tr class='rowIntestazione1'><td>$menu</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";                    
                }
                if(($menu==$row['AppMenu'])&&($modulo!=$row['AppModulo'])){
                     while($fun_count<7){                                                            
                        $fun_count++;
                         //$table.="<td>".$fun_count."</td>";                        
                        $table.="<td></td>";
                       } 
                    if($tr_open){
                        $table.="</tr>";
                        $tr_open=0;
                    }
                    $fun=0;
                    $modulo=$row['AppModulo'];
                    $table.="<tr class='rowBianca'><td>$modulo</td>";
                    $tr_open=1;
                    $fun_count=0;
                }
                if(($menu==$row['AppMenu'])&&($modulo==$row['AppModulo'])&&($fun!=$row['AppModuloFunzioneId'])){
                    $fun=$row['AppModuloFunzioneId'];
                    $fun_count++;
                    if($fun_count==$row['AppModuloFunzioneId']){
                        //$funzioneId=$row['AppModuloFunzioneId'].",".$row['AppPermessoId'];                
                        $funzioneId=$row['AppPermesso'];
                        $table.="<td>".$funzioneId."</td>";
                    }else{
                       while(($fun_count!=$row['AppModuloFunzioneId'])and($fun_count<=7)){                                        
                        //$table.="<td>".$fun_count."</td>";
                           $table.="<td></td>";
                        $fun_count++;
                       }
                       //$funzioneId=$row['AppModuloFunzioneId'].",".$row['AppPermessoId'];                
                       $funzioneId=$row['AppPermesso'];
                       $table.="<td>".$funzioneId."</td>";
                    }
                }                                              
            }
            $table.="</tr></table>";
                
          return($table);
    }
     
    
     public function getMergeEditRuolo($ruolo)
    {         
         
         
         
         $where="1=1 and ";
         $arr_ruoli=explode(",", $ruolo);
         foreach ($arr_ruoli as &$value) {
            $where.="(RuoloId=".$value.") or ";
        }
        $where=substr($where,0,strlen($where)-4);
         $db=$this->conn;
         
       $sql="SELECT * FROM AppPermesso Order by AppPermessoPeso";       
       $data=$db->fetch_array($sql);             
       
       $sql = "SELECT * FROM RuoliMergePermessi WHERE $where";       
       $queryid = $db->query($sql);
       $menu="";
       $modulo="";
       $funzione="";
       $fun_count=7;
       $attivo=0;
          $table="<table class=\"TabellaOperatoreRuoli\">";
          $table.="<tr class='rowIntestazione'><td>modulo</td><td>lista</td><td>aggiunta</td><td>cancellazione</td><td>modifica</td><td>esportazione</td><td>importazione</td><td>stampa</td></tr>";                    
            while($row=$db->fetch($queryid)){
                $attivo=1;
                if($menu!=$row['AppMenu']){
                    while($fun_count<7){                                                            
                        $fun_count++;                        
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                        
                       }
                    $fun=0;   
                    $menu=$row['AppMenu'];
                    $table.="<tr class='rowIntestazione1'><td>$menu</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";                    
                }
                if(($menu==$row['AppMenu'])&&($modulo!=$row['AppModulo'])){
                     while($fun_count<7){                                                            
                        $fun_count++;
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                                            
                       } 
                    if($tr_open){
                        $table.="</tr>";
                        $tr_open=0;
                    }
                    $fun=0;
                    $modulo=$row['AppModulo'];
                    $table.="<tr class='rowBianca'><td>$modulo</td>";
                    $tr_open=1;
                    $fun_count=0;
                }
                if(($menu==$row['AppMenu'])&&($modulo==$row['AppModulo'])&&($fun!=$row['AppModuloFunzioneId'])){
                    $fun=$row['AppModuloFunzioneId'];
                    $fun_count++;
                    if($fun_count==$row['AppModuloFunzioneId']){                        
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$row['AppModuloFunzioneId']."]",$data,$row['AppPermessoId'])."</td>";
                    }else{
                       while(($fun_count!=$row['AppModuloFunzioneId'])and($fun_count<=7)){                                        
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                       
                        $fun_count++;
                       }                       
                       $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$row['AppModuloFunzioneId']."]",$data,$row['AppPermessoId'])."</td>";
                    }
                }                                              
            }
            $table.="</tr></table>";
          if ($attivo)      
            return($table);
          else
            return($this->getRuoloVuota());  
    }
    
   private function HtmlSelectPermesso($id,$name,$data,$value){
           
       if($value=="") $value="4";
       $content=("<select name='$name' id='$id' >");    
        foreach ($data as &$val) {
            $content.=("<option value='".$val['AppPermessoId']."'");
            if($val['AppPermessoId']==$value) $content.=("selected");
            $content.=(" >".$val['AppPermesso']."</option>");        }
        $content.=("</select>");        
        return $content;
        }
  
    private function getRuoloVuota(){

       $db=$this->conn;
         
       $sql="SELECT * FROM AppPermesso Order by AppPermessoPeso";       
       $data=$db->fetch_array($sql);
        $sql = "SELECT * FROM ViewMenuModuli";       
       $queryid = $db->query($sql);
       $menu="";
       $modulo="";
       $fun_count=7;
       $table="<table class=\"TabellaOperatoreRuoli\">";
          $table.="<tr class='rowIntestazione'><td>modulo</td><td>lista</td><td>aggiunta</td><td>cancellazione</td><td>modifica</td><td>esportazione</td><td>importazione</td><td>stampa</td></tr>";                    
            while($row=$db->fetch($queryid)){                
                if($menu!=$row['AppMenu']){
                    while($fun_count<7){                                                            
                        $fun_count++;                        
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                        
                       }                   
                    $menu=$row['AppMenu'];
                    $table.="<tr class='rowIntestazione1'><td>$menu</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";                    
                }
                if(($menu==$row['AppMenu'])&&($modulo!=$row['AppModulo'])){                    
                    while($fun_count<7){                                                                                    
                        $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                                            
                       } 
                    if($tr_open){
                        $table.="</tr>";
                        $tr_open=0;
                    }                  
                    $modulo=$row['AppModulo'];
                    $table.="<tr class='rowBianca'><td>$modulo</td>";
                    $tr_open=1;
                    $fun_count=1;
                }
                if(($menu==$row['AppMenu'])&&($modulo==$row['AppModulo'])){                    $
                    $fun_count=1;                   
                   while($fun_count<=7){                                        
                    $table.="<td>".$this->HtmlSelectPermesso("Permesso","Permesso[".$row['AppModuloId']."][".$fun_count."]",$data,"")."</td>";                       
                    $fun_count++;
                   }
                }
            }
            $table.="</tr></table>";
            
            return($table);
    }
    
}
?>
