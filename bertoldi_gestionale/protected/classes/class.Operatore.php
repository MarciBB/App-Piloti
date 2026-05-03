<?PHP
class Operatore
{

    public $OperatoreId;
    public $Username;
    public $GestoreId;
    public $SedeId;
    public $SedeAutonoma;

    public $Gestore;
    public $GestorePrimario;
    public $SedeLegale;
    public $OdcId;
    public $OdcNome;
    public $Cognome;
    public $Nome;
    public $Password;
    public $Email;
    public $Stato;
    public $OperatoreTipoId;
    public $MediatoreId;
    public $ServerLogin;
    public $IsAdmin;
    public $Moroso;
    public $ModelliWord;
    public $CompensoMediatoreObbligatorio;
    public $SessionId;
    public $DataScadenzaSessione;


    public $conn; // connessione al database

    function inizializza($OperatoreId)
    {

        global $user;
        $db = $this->conn;
        $sql = "SELECT * From ElencoOperatoreView WHERE OdcId = $user->OdcId and OperatoreId = $OperatoreId";
		$row = $db->query_first($sql);
        $sql = "SELECT * From Sede WHERE GestoreId = " . $row['GestoreId'];
        $rowSede = $db->query_first($sql);
        $sql = "SELECT * From Operatore WHERE OperatoreId=$OperatoreId";
        $rowOperatore = $db->query_first($sql);
        
        // se esiste l'anagrafica
        if (!empty($row['OperatoreId'])) {

            $this->Username = $row['Username'];
            $this->GestoreId = $row['GestoreId'];
            $this->Gestore = $row['RagioneSociale'];
            $this->GestorePrimario = $row['GestorePrimario'];
            $this->SedeId = $rowSede['SedeId'];
			$this->SedeLegale = $rowSede['Indirizzo'];
            $this->Cognome = $row['Cognome'];
            $this->Nome = $row['Nome'];
            $this->Password = $rowOperatore['Password'];
            $this->Email = $row['Email'];
            $this->Stato = $row['Stato'];
            $this->OperatoreTipoId = $row['OperatoreTipoId'];
            $this->MediatoreId = $row['MediatoreId'];
            $this->IsAdmin = $row['IsAdmin'];
            $this->CompensoMediatoreObbligatorio = null;
			$this->OdcId = $row['OdcId'];
			$this->OperatoreWeb = null;
        } else {
            print("errore");
            exit();
        }
    }

    function inizializzaMobile($OperatoreId)
    {
        if (isset($OperatoreId)) {
            global $user;
            $db = $this->conn;
            $sql = "SELECT * From ElencoOperatoreView WHERE OperatoreId=$OperatoreId";
            $row = $db->query_first($sql);
            $sql = "SELECT * From Sede WHERE GestoreId=" . $row['GestoreId'];
            $rowSede = $db->query_first($sql);
            $sql = "SELECT * From Operatore WHERE OperatoreId=$OperatoreId";
            $rowOperatore = $db->query_first($sql);
            // se esiste l'anagrafica
            if (!empty($row['OperatoreId'])) {
                $this->OperatoreId = $row['OperatoreId'];
                $this->Username = $row['Username'];
                $this->GestoreId = $row['GestoreId'];
                $this->Gestore = $row['RagioneSociale'];
                $this->SedeId = $rowSede['SedeId'];
                $this->SedeLegale = $rowSede['Indirizzo'];
                $this->Cognome = $row['Cognome'];
                $this->Nome = $row['Nome'];
                $this->Password = $rowOperatore['Password'];
                $this->Email = $row['Email'];
                $this->Stato = $row['Stato'];
                $this->OperatoreTipoId = $row['OperatoreTipoId'];
                $this->MediatoreId = $row['MediatoreId'];
                $this->IsAdmin = $row['IsAdmin'];
                $this->CompensoMediatoreObbligatorio = null;
                $this->OdcId = $row['OdcId'];
                $this->OperatoreWeb = null;
            } else {
                print("errore");
                exit();
            }
        }
    }

    function RegistraAccesso()
    {
        $db = $this->conn;
        $campi_tabella['OperatoreId'] = $this->OperatoreId;
        //         session_start();

        //         session_regenerate_id();
        $session_id = session_id();


        $this->SessionId = $session_id;
        $campi_tabella['SedeIns'] = $this->SedeId;
        $campi_tabella['DataIns'] = date('Y-m-d H:i:s');

        $campi_tabella['IpIns'] = getenv('REMOTE_ADDR');
        $campi_tabella['OdcIdRef'] = $this->OdcId;
        $campi_tabella['GestoreIdRef'] = $this->GestoreId;
        $campi_tabella['SessionId'] = $session_id;
        $campi_tabella['TipoRegistrazione'] = "LogIn";
        $campi_tabella['IsActiveSession'] = 1;
        $db->insert("OperatoreAccesso", $campi_tabella);
        $this->AggiornaSessione();
    }




    function AggiornaSessione()
    {
        $db = $this->conn;
        $session_id = session_id();
        $datacorrente = date('Y-m-d H:i:s');
        $dup['DataUltimaOpearazione'] = $datacorrente;
        $tempodisessione = 30;

        $data_exipiration_notice1 =  strtotime($datacorrente . " +" . $tempodisessione . " minutes");
        $data_exipiration_notice = date('Y-m-d H:i:s ', ($data_exipiration_notice1));

        $dup['DataScadenzaSessione'] = $data_exipiration_notice;
        $this->DataScadenzaSessione = $data_exipiration_notice;

        $db->update("OperatoreAccesso", $dup, "OdcIdRef=$this->OdcId and TipoRegistrazione='LogIn' and IsActiveSession=1 and OperatoreId=$this->OperatoreId and SessionId='$session_id'");
    }

    function RegistraUscita()
    {



        $db = $this->conn;
        $campi_tabella['OperatoreId'] = $this->OperatoreId;
        $session_id = session_id();
        $this->SessionId = $session_id;
        $campi_tabella['SedeIns'] = $this->SedeId;
        $campi_tabella['DataIns'] = date('Y-m-d H:i:s');
        $campi_tabella['IpIns'] = getenv('REMOTE_ADDR');
        $campi_tabella['OdcIdRef'] = $this->OdcId;
        $campi_tabella['GestoreIdRef'] = $this->GestoreId;
        $campi_tabella['SessionId'] = $session_id;
        $campi_tabella['TipoRegistrazione'] = "LogOut";
        $campi_tabella['IsActiveSession'] = 1;
        $db->insert("OperatoreAccesso", $campi_tabella);

        $dup['IsActiveSession'] = 0;
        $db->update("OperatoreAccesso", $dup, "OdcIdRef=$this->OdcId and OperatoreId=$this->OperatoreId and TipoRegistrazione='Login'");


        return true;
    }


    function ControllaValiditaSessione()
    {
        // verifico se l'utente ha già una sessione attiva
        // se  ha la sessione attiva la disattivo

        $db = $this->conn;
        $datacorrente = date('Y-m-d H:i:s');
        $sql = "SELECT * From OperatoreAccesso WHERE OperatoreId=$this->OperatoreId 
                 and IsActiveSession=1 and TipoRegistrazione='Login' and SessionId='$this->SessionId' order by OperatoreAccessoId desc Limit 1";
        $row = $db->query_first($sql);

        return true;
        /*
         if(!empty($row['OperatoreAccessoId']))
          return true; // sessione attiva
       else
       return false; // sessine disattiva
          */
    }


    function ControllaAccessoDuplicato()
    {
        // verifico se l'utente ha già una sessione attiva
        // se  ha la sessione attiva la disattivo

        $db = $this->conn;
        $datacorrente = date('Y-m-d H:i:s');
        $sql = "SELECT * From OperatoreAccesso WHERE OperatoreId=$this->OperatoreId 
                 and IsActiveSession=1 and TipoRegistrazione='LogIn' and DataScadenzaSessione>'$datacorrente' order by OperatoreAccessoId desc Limit 1";
        $row = $db->query_first($sql);
        if (!empty($row['OperatoreAccessoId'])) {
            // disattivo la vecchia sessione
            $OperatoreAccessoId = $row['OperatoreAccessoId'];
            $dup['IsActiveSession'] = 0;
            $db->update("OperatoreAccesso", $dup, "OdcIdRef=$this->OdcId and OperatoreId=$this->OperatoreId and OperatoreAccessoId=$OperatoreAccessoId");

            return true;
        }

        return false;
    }

    function login($user_post, $pw_post, $sede_post)
    {

        $db = $this->conn;
        $pass_crypt = crypt($pw_post, Config::$auth_salt);
        $sql = "SELECT * From OperatoreLoginView WHERE Password='" . $pass_crypt . "' and Username='" . $db->escape($user_post) . "' and CodiceSede='$sede_post'";
        $row = $db->query_first($sql);
        if (isset($row) && $pass_crypt == $row['Password']) {
            $encrypted_pw = password_hash($pw_post, PASSWORD_DEFAULT);
            $update_sql = "UPDATE Operatore SET Password = '" . $encrypted_pw . "' WHERE Username='" . $db->escape($user_post) . "'";
            $db->query($update_sql);
            $row['Password'] = $encrypted_pw;
        } else {
            $user_sql = "SELECT * From OperatoreLoginView WHERE Username='" . $db->escape($user_post) . "' and CodiceSede='$sede_post'";
            $user = $db->query_first($user_sql);
            if (password_verify($pw_post, $user['Password'])) {
                $row = $user;
            };
        }
        // se l'operatore esiste ed appartiene ad un gestore per cui esiste la sede indicata
        if (!empty($row['OperatoreId'])) {
            $this->OperatoreId = $row['OperatoreId'];
            $this->Username = $row['Username'];
            $this->GestoreId = $row['GestoreId'];
            $this->SedeId = $row['SedeId'];
            $this->SedeAutonoma = $row['SedeAutonoma'];
            $this->OdcId = $row['OdcId'];
            $this->OdcNome = $row['OdcNome'];
            $this->Cognome = $row['Cognome'];
            $this->Nome = $row['Nome'];
            $this->Password = $row['Password'];
            $this->OperatoreTipoId = $row['OperatoreTipoId'];
            $this->MediatoreId = $row['MediatoreId'];
            $this->ServerLogin = Config::$ServerName;
            $this->IsAdmin = $row['IsAdmin'];
            $this->CompensoMediatoreObbligatorio = $row['CompensoMediatoreObbligatorio'];
            $this->Moroso = $row['Moroso'];
            $this->ModelliWord = $row['ModelliWord'];
            $this->SedeLegale = $row['SedeLegale'];
            $cad = $this->ControllaAccessoDuplicato();
            $this->RegistraAccesso();

            return true;
        } else
            return false;
    }



    function logout()
    {
        unset($_SESSION['OPERATORE']);
    }


    public function set_NewPassword($pw)
    {
        $this->Password = $pw;
    }

    public function get_OperatoreId()
    {
        return $this->OperatoreId;
    }

    public function get_permessi_modulo($moduloId)
    {

        $db = $this->conn;
        $sql = "SELECT * From OperatorePermessi WHERE OperatoreId=$this->OperatoreId and OdcId=$this->OdcId and AppModuloId=$moduloId";
        return $db->fetch_array($sql);
    }

    public function GetIFPassword($user, $pw)
    {
        $db = $this->conn;
        $sql = "SELECT OperatoreId From Operatore WHERE Username='$user' and Password='$pw'";
        $row = $db->query_first($sql);
        if ($row['OperatoreId'])
            return true;
        else
            return false;
    }

    public function UsernameExistByEmail($user, $email)
    {
        $db = $this->conn;
        $sql = "SELECT OperatoreId From Operatore WHERE Username='$user' and Email='$email'";
        $row = $db->query_first($sql);
        if ($row['OperatoreId'])
            return $row['OperatoreId'];
        else
            return 0;
    }

    public function UsernameExistByUser($user, $email)
    {
        $db = $this->conn;
        $sql = "SELECT OperatoreId From Operatore WHERE Username='$user'";
        $row = $db->query_first($sql);
        if ($row['OperatoreId'])
            return $row['OperatoreId'];
        else
            return 0;
    }

    public function SetPasswordByUser($user, $pw_post)
    {
        $db = $this->conn;
        $pass_crypt = password_hash($pw_post, PASSWORD_DEFAULT);
        $sql = "UPDATE Operatore SET Password='$pass_crypt',LinkRecuperaPwd='',RecuperaPwd=0 WHERE Username='$user'";
        if ($db->query($sql))
            return true;
        else
            return false;
    }

    public function PwdResetByRnd($rnd)
    {
        $db = $this->conn;
        $sql = "SELECT Username From Operatore WHERE LinkRecuperaPwd='$rnd' and RecuperaPwd=1";
        $row = $db->query_first($sql);
        if ($row['Username'])
            return $row['Username'];
        else
            return 0;
    }

    public function UsernameExist($newusername, $opeId)
    {
        $db = $this->conn;
        $sql = "SELECT OperatoreId From Operatore WHERE Username='$newusername'";
        $row = $db->query_first($sql);
        if ($row['OperatoreId']) {
            if ($row['OperatoreId'] <> $opeId) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function ControllModuloFunzionePermesso($ModuloId, $ModuloFunzioneId)
    {

        $db = $this->conn;
        $sql = "SELECT * From OperatorePermessi WHERE OperatoreId=$this->OperatoreId 
                and OdcId=$this->OdcId 
                and AppModuloId=$ModuloId 
                and AppModuloFunzioneId=$ModuloFunzioneId and AppPermessoId<>4";

        return $db->fetch_array($sql);
    }

    public function SendMailResetPassword($userId)
    {
        $db = $this->conn;
        $sql = "SELECT * From Operatore WHERE OperatoreId=$userId";
        $row = $db->query_first($sql);
        $odcId = $row['OdcIdRef'];
        $nome = $row['Nome'];
        $cognome = $row['Cognome'];
        $email = $row['Email'];
        $username = $row['Username'];
        /*
         Config smtp ODC
         */
        $sql = "SELECT * From Odc WHERE OdcId=$odcId";
        $row = $db->query_first($sql);
        $odc_fromMail = $row['EmailSmtp'];
        $odc_fromName = $row['NomeEmailSmtp'];
        $odc_serverSmtp = $row['ServerSmtp'];
        $odc_portaSmtp = $row['PortaSmtp'];
        $odc_username = $row['UserSmtp'];
        $odc_password = $row['PwdSmtp'];
        $mail = new Email; // create the mail

        $mail->SetFrom($odc_fromMail, $odc_fromName);

        $mail->Subject = 'Richiesta reset password';
        $mail->AddAddress($email);

        $random = $db->random_string(25);
        $link_reset = "http://" . Config::$ServerName . "/index.php?do=get_password&rnd=" . $random;
        $sql = "UPDATE Operatore SET RecuperaPwd=1,LinkRecuperaPwd='$random' WHERE OperatoreId=$userId";
        $db->query($sql);

        $message = "Gentile $nome $cognome,\n";
        $message .= "per attivare la procedura di ripristino password del tuo account $username devi cliccare sul link sottostante:\n\n";
        $message .= $link_reset;
        $message .= "\n\nGrazie";
        $mail->SetBody($message);

        // Autenticazione SMTP
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = $odc_serverSmtp; // Server SMTP
        $mail->Port = $odc_portaSmtp;                    // Porta SMTP
        $mail->Username = $odc_username; // SMTP account username
        $mail->Password = $odc_password;        // SMTP account password
        //$mail->Send();
        if ($mail->Send()) {
            $res = 'Email inviata correttamente';
        } else {
            $res = 'Errore: email non inviata. ' . $mail->ErrorInfo;
        }
    }
}
