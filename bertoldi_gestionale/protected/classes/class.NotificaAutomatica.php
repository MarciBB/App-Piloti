<?PHP
    class NotificaAutomatica
    {
    
       
        
        function __construct() {
           
        }
        
        public function ElaboraNotifiche()
        {
            
           
            $this->Admin_NotificaNuovaMediazione();
            $this->Controparte_InvitoAdAderire();
            $this->Parti_ConfermaIncontro();
            $this->AssRapp_ConfermaIncontro();
            $this->ProcessaNotifica();
           
        }    
        
        public function InviaNotifiche()
        {
            $this->ProcessaNotifica();
        }    
         
        
        
        private function Admin_NotificaNuovaMediazione()
        {
             global $db;
              $datarif="2012-04-01";
              $sql="select * from Notifiche_NuovaMediazioneDaElaborare where DataPresentazioneIstanza>'$datarif' order by Mediazioneid desc";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=1;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=5;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
        } // end Admin_NotificaNuovaMediazione
        
        
        
        private function Controparte_InvitoAdAderire()
        {
            // se non è congiunta inserisco come notifica da inviare la lettera di invito alla controparte
              global $db;
              $datarif="2012-04-01";
              $sql="select * from Notifiche_ControparteInvitoAdAderireDaElaborare where DataPresentazioneIstanza>'$datarif' order by Mediazioneid";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                    $SoggettoId=$Object[$i]['MediazioneSoggettoParteId'];
                    
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=3;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=2;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $DatiNotifica['SoggettoId']=$SoggettoId;
                      
                      
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
        } // end Controparte_InvitoAdAderire
        
        
        
        
        
        private function Parti_ConfermaIncontro()
        {
            // se non è congiunta inserisco come notifica da inviare la lettera di invito alla controparte
               global $db;
              $datarif="2012-04-01";
              $sql="select * from Notifiche_PartiConfermaIncontroDaElaborare where MediazioneSoggettoParteTipoId=1 and DataPresentazioneIstanza>'$datarif' order by Mediazioneid";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                    $SoggettoId=$Object[$i]['MediazioneSoggettoParteId'];
                    
                    
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=4;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=1;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $DatiNotifica['SoggettoId']=$SoggettoId;
                      
                      
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
              
              
              $sql="select * from Notifiche_PartiConfermaIncontroDaElaborare where MediazioneSoggettoParteTipoId=2 and DataPresentazioneIstanza>'$datarif' order by Mediazioneid desc";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                    $SoggettoId=$Object[$i]['MediazioneSoggettoParteId'];
                    
                    
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=4;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=2;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $DatiNotifica['SoggettoId']=$SoggettoId;
                      
                      
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
              
              
        } // Parti_ConfermaIncontro
        
        
        
        private function AssRapp_ConfermaIncontro()
        {
            // se non è congiunta inserisco come notifica da inviare la lettera di invito alla controparte
               global $db;
              $datarif="2012-04-01";
              
              // assistenti
              $sql="select * from Notifiche_AssRappConfermaIncontroDaElaborare where MediazioneSoggettoTipoEstId=2 and DataPresentazioneIstanza>'$datarif' order by Mediazioneid desc";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                    $SoggettoId=$Object[$i]['MediazioneSoggettoParteId'];
                    
                    
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=5;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=3;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $DatiNotifica['SoggettoId']=$SoggettoId;
                      
                      
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
              
              //rappresentanti
              $sql="select * from Notifiche_AssRappConfermaIncontroDaElaborare where MediazioneSoggettoTipoEstId=1 and DataPresentazioneIstanza>'$datarif' order by Mediazioneid desc";
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                    $MediazioneId=$Object[$i]['Mediazioneid'];
                    $OdcId=$Object[$i]['OdcIdRef'];
                    $GestoreId=$Object[$i]['GestoreIdRef'];
                    $SoggettoId=$Object[$i]['MediazioneSoggettoParteId'];
                    
                    
                
                  $ArrCanale=$this->DeterminaCanaleNotificaOdcPerTipoSoggettoId(1,$OdcId);
                  $c=0;
                  while($c<sizeof($ArrCanale))
                  {
                      $CanaleNotifica=$ArrCanale[$c];
                      $DatiNotifica['NotificaOggettoId']=5;
                      $DatiNotifica['AppNotificaTipoSoggettoId']=4;
                      $DatiNotifica['NotificaCanaleTipoId']=$CanaleNotifica;
                      $DatiNotifica['MediazioneId']=$MediazioneId;
                      $DatiNotifica['OdcIdRef']=$OdcId;
                      $DatiNotifica['GestoreIdRef']=$GestoreId;
                      $DatiNotifica['SoggettoId']=$SoggettoId;
                      
                      
                      $lastid=$this->ScriviNotifica($DatiNotifica);
                   
                      $c++;
                  }
                  $i++;
              }
              
              
        } // AssRapp_ConfermaIncontro
        
        
        
        private function DeterminaCanaleNotificaOdcPerTipoSoggettoId($OggettoId,$OdcId)
        {
            
            
            // DETERMINO QUALI CANALI DI COMUNICAZIONE SONO DISPONIBLI PER ODC
            // 
            // 
            // DETERMINO QUALI CANALI DI COMUNICAZIONE SONO STATI DEFINITI PER IL SOGGETTO
            
            
            $data[0]=2;
            return $data;
            
        }
        
       
         private function ScriviNotifica($data)
         {
              global $db;
              $data['DataIns']=date('Y-m-d H:i:s');
              $data['IpIns']=getenv('REMOTE_ADDR');
            print_r($data);
             
              $lastid=$db->insert("MediazioneNotificaAutomatizzata", $data);
              
              return $lastid;
              
      
         }
         
         
         public function ProcessaNotifica()
         {
              
             
             global $db;
              // seleziono tutte le notifiche per nuove mediazioni da inviare
              $sql="select * from MediazioneNotificaAutomatizzata where StatoNotifica=0 and NotificaOggettoId=1 order by MediazioneNotificaAutomatica asc limit 1";
            
              
              $Object=$db->fetch_array($sql);
              $sizeObject=sizeof($Object);
              $i=0;

            while ($i<$sizeObject)
            {
                $NotificaId=$Object[$i]['MediazioneNotificaAutomatica'];
                $OdcId=$Object[$i]['OdcIdRef'];
                $MediazioneId=$Object[$i]['MediazioneId'];
                $CanaleId=$Object[$i]['NotificaCanaleTipoId'];
             
                $MessaggioNotifica=new NotificaAutomaticaMessaggi();
                $dataupdate=$MessaggioNotifica->Msg_NuovaMediazione($OdcId, $MediazioneId, $CanaleId);
           
                $res=$db->update('MediazioneNotificaAutomatizzata', $dataupdate, "MediazioneNotificaAutomatica=".$NotificaId);
                
                
                
                $ArrObjOdc=null;
                $i++;
            }
             
              
         }
         
            
         
        
         
         private function InviaNotifica($data)
         {
              global $db;
              $data['DataIns']=date('Y-m-d H:i:s');
              $data['IpIns']=getenv('REMOTE_ADDR');
              print_r($data);
              $lastid=$db->insert("MediazioneNotificaAutomatizzata", $data);
              
              return $lastid;
              
      
         }
         
         
    }