<?PHP
    class NotificaAutomaticaMessaggi
    {
    
       
        
        function __construct() {
           
        }
        
        public function Msg_NuovaMediazione($OdcId,$MediazioneId,$CanaleId)
        {
             global $db;
             $dt=new DT();
            
            
            $ArrObjOdc=$this->InizializzaOggettoOdc($OdcId);
            $ArrObjMediazione=$this->InizializzaOggettoMediazione($MediazioneId);
            $ArrObjOperatore=$this->InizializzaOggettoOperatore($ArrObjMediazione['OpeIns']);
            $ArrObjSede=$this->InizializzaOggettoSede($ArrObjMediazione['SedeIns']);
            
            $OdcNome=$ArrObjOdc['Odc'];
            $MediazioneOperatoreInsNome=$ArrObjOperatore['Cognome']." ".$ArrObjOperatore['Nome'];
            $MediazioneSedeIns=$ArrObjSede['Indirizzo']." ".$ArrObjSede['Comune'];
            $MediazioneOggetto=$ArrObjMediazione['Oggetto'];
            $MediazioneDataIns=$dt->format($ArrObjMediazione['DataIns'],"Y-m-d H:i:s","d/m/Y H:i:s");
            $MediazioneCodice=$ArrObjMediazione['Codice'];
            $Destinatari=$ArrObjOdc['EmailsNotificaNuovaMediazione'];
       
            
            if ($CanaleId==2) // canale email
               {
                $oggetto='Nuova Mediazione - protocollo '.$MediazioneCodice;
                $message= "Gentile responsabile dell'Organismo $OdcNome,\n";
                $message.="La informiamo che sul sistema ".Config::$application_name." e' stata protocollata una nuova istanza di mediazione \n";
                $message.=" dall'operatore $MediazioneOperatoreInsNome ";
                $message.="in data $MediazioneDataIns dalla sede di $MediazioneSedeIns \n";
                $message.="avente come oggetto: $MediazioneOggetto. \n";
                $message.="\n Sistema automatico di notifica ".Config::$application_name;
                $invio=new NotificaAutomaticaMessaggiInvio();
                $result=$invio->InvioEmailSemplice($message, $oggetto, $Destinatari,$OdcId);
                $data=null;
               
                if ($result)
                    {
                    $data['Oggetto']=$oggetto;
                    $data['Messaggio']=$message;
                    $data['Destinatario']=$Destinatari;
                    $data['StatoNotifica']=1;
                    $data['DataNotifica']=date('Y-m-d H:i:s');
                   } 
                else
                 $data['StatoNotifica']=0;   
             
                return $data;
               }
               else
                   {
                      $data['StatoNotifica']=2;   
                      return $data;
                   }
             
        } // 
        
        public function InizializzaOggettoOdc($OdcId)
                {
                global $db;

              
                $sql = "SELECT * From Odc WHERE OdcId=$OdcId";      
                $row = $db->query_first($sql);

                if (!empty($row['OdcId']))
                return $row;
                else
                {
                print("errore");
                die();


                }
        }   
        
        public function InizializzaOggettoOperatore($OperatoreId)
                {
                global $db;

               
                $sql = "SELECT * From Operatore WHERE OperatoreId=$OperatoreId";      
                $row = $db->query_first($sql);

                if (!empty($row['OperatoreId']))
                return $row;
                else
                {
                print("errore");
                die();


                }
        }   
        
        
        private function InizializzaOggettoSede($SedeId)
          {
                global $db;

               
                $sql = "SELECT * From Sede WHERE SedeId=$SedeId";      
                $row = $db->query_first($sql);

                if (!empty($row['SedeId']))
                return $row;
                else
                {
                print("errore");
                die();


                }
        }   
        
        private function InizializzaOggettoMediazione($MediazioneId)
          {
                global $db;

               
                $sql = "SELECT * From Mediazione WHERE Mediazioneid=$MediazioneId";      
                $row = $db->query_first($sql);

                if (!empty($row['Mediazioneid']))
                return $row;
                else
                {
                print("errore");
                die();


                }
        }
        
        
        
        
        
       
    }