<?PHP

include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();

$modulespath_= Config::$modulespath;
$classespath_=Config::$classespath;
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');
    class NotificaAutomaticaMessaggiInvio
    {
    
       
        
        function __construct() {
           
        }
        
        public function InvioEmailSemplice($Messaggio, $Oggetto, $Destinatari, $OdcId)
        {
            global $db;
            $sql = "SELECT * From Odc WHERE OdcId=$OdcId";   
		
            $row = $db->query_first($sql);
            
			$mail = new PHPMailer(); // create the mail
			//oggetto
			$mail->Subject = $Oggetto;
			//destinatari TO
			$arr_email=explode(";", $Destinatari);
            $n_email = count($arr_email);
            if ($n_email == 0) {
				echo trim($Destinatari);
				$mail->AddAddress(trim($Destinatari));
				
            } else {
                $n=0;
                while($n<$n_email)
                {
					echo $arr_email[$n];
                    $mail->AddAddress(trim($arr_email[$n]));
                    $n++;

                }
            }
			$mail->MsgHTML($Messaggio);
			$mail->IsSMTP();
			$mail->SMTPDebug  = 2;
			$mail->SMTPSecure = 'ssl';// SMTP account password
			$mail->SMTPAuth = true;
			$mail->IsHTML(true);
		
			
			
				
			// setto il from    
			$from = $row['EmailSmtp'];
			$fromName = $row['NomeEmailSmtp'];
			$mail->SetFrom($from, $fromName);  
			$mail->Host = $row['ServerSmtp'];				// Server SMTP
			$mail->Port = $row['PortaSmtp'];				// Server SMTP Port
			$mail->Username = $row['UserSmtp'];           	// SMTP account username
			$mail->Password = $row['PwdSmtp'];
			
            if ($Destinatari!='') {
				$retuls = $mail->Send();
				return true;
            } else {
                return false;
            }

        }
        
        public function InvioFax($messaggio,$Oggetto,$destinatario)
        {
            
            
        }
        
        public function InvioSMS($messaggio,$Oggetto,$destinatario)
        {
            
            
        }
        
       
    }