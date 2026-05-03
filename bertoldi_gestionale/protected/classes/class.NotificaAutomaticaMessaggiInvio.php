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
        
        public function InvioEmailSemplice($Messaggio,$Oggetto,$Destinatari,$OdcId)
        {
            global $db;
            
           
           
            
            $sql = "SELECT * From Odc WHERE OdcId=$OdcId";        
            $row = $db->query_first($sql);
            
            $odc_fromMail=$row['EmailSmtp'];
            $odc_fromName=$row['NomeEmailSmtp'];
            $odc_serverSmtp=$row['ServerSmtp'];
            $odc_portaSmtp=$row['PortaSmtp'];
            $odc_username=$row['UserSmtp'];
            $odc_password=$row['PwdSmtp'];
            
            
            $mail             = new PHPMailer();
            $mail->SetFrom($odc_fromMail, $odc_fromName);   
            $mail->Subject = $Oggetto;
        
            
            
            $arr_email=explode(";", $Destinatari);
            $n_email=count($arr_email);
            if ($n_email==0)
            $mail->AddAddress(trim($Destinatari));
            else
            {
                $n=0;
                while($n<$n_email)
                {
                //    print($arr_email[$n]);
                    $mail->AddAddress(trim($arr_email[$n]));
                    $n++;

                }
            }
            
            
            $mail->MsgHTML($Messaggio);
            
            //$mail->Body($Messaggio); 
        
           
             $mail->IsSMTP(); // telling the class to use SMTP
            
            $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
            $mail->SMTPAuth=true;
            $mail->SMTPSecure = 'ssl';// SMTP account password
            $mail->Host=$odc_serverSmtp; // Server SMTP
            $mail->Port=$odc_portaSmtp;                    // Porta SMTP
            $mail->Username=$odc_username; // SMTP account username
            $mail->Password=$odc_password;  
            
            
            
            if ($Destinatari!='')
                {
            $mail->Send();
            return true;
                }
            else
                return false;
            
            
        }
        
        public function InvioFax($messaggio,$Oggetto,$destinatario)
        {
            
            
        }
        
        public function InvioSMS($messaggio,$Oggetto,$destinatario)
        {
            
            
        }
        
       
    }