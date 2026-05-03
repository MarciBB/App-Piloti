<?PHP 
/* versione aggiornata al d.m. 145 del 2011*/
    class Config
    {
        // Server Name
       	static private $__productionServers = array('service.bertoldiboats.com');
        static private $__productionServersAlternate = array('service.bertoldiboats.com');
        static private $__localServers      = array('services.bertoldiboats.local');
		static private $__stagingServers      = array('bboatsservice.wbb.it');
//
        
        
        static public $ServerName="service.bertoldiboats.com";

        static public $auth_domain; // Domain to set for the cookie
        static public $auth_hash;   // Store hashed passwords in database? (versus plain-text)
        static public $auth_salt;   // Sale per criptazione password
		static public $auth_key;   //  Chiave di autenticazione
		static public $application_name;   // Nome Applicazione
		static public $application_version;   // Versione Applicazione
        static public $application_company;   // Versione Applicazione
        
		static public $email_admin;   // Email di amministrazione

        static public $dbserver; // Database server
        static public $dbname;   // Database name
        static public $dbuser;   // Database username
        static public $dbpass;   // Database password
        static public $dberror;  // What do do on a database error (see class.database.php for details)
	 	
        static public $encode_type;
        static public $codificaUTF;
        
		static public $ftpserver; // Ftp server
        static public $ftpuser;   // Ftp username
        static public $ftppass;   // Ftp password
        static public $docroot;   // Ftp password
        static public $ftproot;   // Ftp password
        static public $docupload;   // cartella di upload dei documenti
        static public $odcfile;   // cartella contenente i template dell'odc
        
        static public $basepath; // Ftp server
        static public $modulespath;   // Ftp username
        static public $classespath;   // Ftp password
        
        static public $useDBSessions; // Set to true to store sessions in the database

		static public $httpHost;	//url base del sito

		static public $twilloSid;
		static public $twilloAuthToken;
		static public $twilloNumero;
		
		static public $accessTokenAPI;
		static public $gestionale;
		
		//FattureInCloud
		static public $fattureInCloudUrl;
		static public $fattureInCloudAPIUID;
		static public $fattureInCloudAPIKey;
		
		//FattureInCloud v2
		static public $fattureInCloudUrl_v2;
		static public $fattureInCloudUrl_v2_ClientID;
		static public $fattureInCloudUrl_v2_APIToken;
		static public $fattureInCloudUrl_v2_CompanyID;

		
       // Codice e variabili che sono comuni sia al server di produzione che al server di test
        static public function everywhere()
        {
            self::$application_name   = 'Re.Ticket';
            self::$application_version   = '3.1';
            self::$application_company   = 'Bertoldi Boats';
            self::$email_admin   = 'info@braincomputing.com';

            
		    // Registra sessioni nel db?
            self::$useDBSessions = false;

            // Settaggio per l'autenticazione
            self::$auth_domain   = '217.72.102.148';
            self::$auth_hash     = false;
	    	self::$auth_salt     = '4aalidoS3ff%$4xepress5W2Ãƒâ€šÃ‚Â£ant&6Tre';
	   	 	self::$auth_key     = 'token';
            
            // settaggio dei percorsi
            self::$basepath     = $_SERVER['DOCUMENT_ROOT'];
            
            self::$modulespath     = self::$basepath."/protected/modules/";
            self::$classespath     = self::$basepath."/protected/classes/";
            self::$docupload = self::$basepath.'/upload/';
            self::$odcfile = self::$basepath.'/odcfile/';
            
            self::$httpHost = "http://".$_SERVER['HTTP_HOST'];          
            
        }

        // Codice e variabili che valgono solo per il server di produzione
        static public function production()
        {
        	if (!defined('WEB_ROOT')){
            	define('WEB_ROOT', dirname(__FILE__));
            }
            
	    	self::$dbserver = 'localhost';
			self::$dbname   = 'office';
			self::$dbuser   = 'office';
			self::$dbpass   = 'GBIxhJrxrqJRB1r';
            self::$dberror  = true;
            
	    	self::$ftpserver   = '';
            self::$ftpuser   = '';
            self::$ftppass   = '';
            self::$docroot   = self::$basepath;
            self::$ftproot   = self::$basepath;           
			
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
            
            self::$encode_type='utf8';
            self::$codificaUTF=true;
            
            //twillo whatsapp
        	self::$twilloSid = 'twilloSidXXXXXXXXXXXXXXXXXXXXX';
        	self::$twilloAuthToken = 'twilloAuthTokenXXXXXXXXXXXXX';
        	self::$twilloNumero = '+14155238886';
        	
        	self::$accessTokenAPI = 'B3sT9kX7Yz';
        	self::$gestionale = 'https://office.bertoldiboats.com';
        	
        	//FattureInCloud
        	self::$fattureInCloudUrl = 'https://api.fattureincloud.it/v1/';
        	self::$fattureInCloudAPIUID = '1286393';
        	self::$fattureInCloudAPIKey = 'fca07bd23243a7e3504c038d4ad38698';
        	
        	self::$fattureInCloudUrl_v2 = 'https://api-v2.fattureincloud.it/';
        	self::$fattureInCloudUrl_v2_ClientID = 'C2KmeoiBFdJqwqNYPRrCXPrxGnz9zTqS';
        	self::$fattureInCloudUrl_v2_APIToken = 'a/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZWYiOiJPeXFjUHdpSlo3Q0FPUEVJY2RKQm9NSHBMN2x6anN1TiJ9.E5B2IByuqWWVCQfzBmrlGVhxE9A4B6zAPVzFXe5qD8E';
        	self::$fattureInCloudUrl_v2_CompanyID = 1286393;
        }

        static public function local()
        {
            self::$application_name   = 'BERTOLDI BOATS DEV';
            self::$application_version   = '3.1';
            self::$application_company   = 'BERTOLDI BOATS DEV';
            
            if (!defined('WEB_ROOT')){
            	define('WEB_ROOT', dirname(__FILE__));
            }
            self::$dbserver = 'localhost';
            self::$dbname   = 'csreisen';
            self::$dbuser   = 'root';
            self::$dbpass   = '';
            self::$dberror  = true;
			
	    	self::$ftpserver   = '';
            self::$ftpuser   = '';
            self::$ftppass   = '';
            self::$docroot   = self::$basepath;
            self::$ftproot   = self::$basepath;

            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
            
        	//twillo whatsapp
        	self::$twilloSid = 'AC95b0cd4fd9645098d2921c1ccef0a2e9';
        	self::$twilloAuthToken = 'a2ddf6febccd9fe193aee5cf8e45ae69';
        	self::$twilloNumero = '+393399958454';
        	
        	self::$accessTokenAPI = 'B3sT9kX7Yz';
        	self::$gestionale = 'https://office.bertoldiboats.com';
        	
        	//FattureInCloud
        	self::$fattureInCloudUrl = 'https://api.fattureincloud.it/v1/';
        	self::$fattureInCloudAPIUID = '513205';
        	self::$fattureInCloudAPIKey = 'fca07bd23243a7e3504c038d4ad38698';
        	
        	self::$fattureInCloudUrl_v2 = 'https://api-v2.fattureincloud.it/';
        	self::$fattureInCloudUrl_v2_ClientID = 'qhH7W0FggdnEBoUE3s1rsdgfOpCG0wuL';
        	self::$fattureInCloudUrl_v2_APIToken = 'a/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZWYiOiJaRkQ1MFhCYTR2ZXd4RXA4VE1UMDVWVkpxOHVWNUV6diJ9.ZDXsx_x-x1GJMZBWJyXKXuCUA9oIzxXK0mA-gY9I-vc';
        	self::$fattureInCloudUrl_v2_CompanyID = 513205;
        }

        // Codice e variabili che valgono solo per il server di staging
        static public function staging()
        {
        	if (!defined('WEB_ROOT')){
            	define('WEB_ROOT', dirname(__FILE__));
            }
            self::$dbserver = 'localhost';
            self::$dbname   = 'csreisendev';
            self::$dbuser   = 'dbadmin';
            self::$dbpass   = 'AccediDb2011!';
            self::$dberror  = true;
			
	    	self::$ftpserver   = '';
            self::$ftpuser   = '';
            self::$ftppass   = '';
            self::$docroot   = self::$basepath;
            self::$ftproot   = self::$basepath;
            			
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
		
            //twillo whatsapp
            self::$twilloSid = 'AC95b0cd4fd9645098d2921c1ccef0a2e9';
            self::$twilloAuthToken = 'a2ddf6febccd9fe193aee5cf8e45ae69';
            self::$twilloNumero = '+14155238886';
            
            self::$accessTokenAPI = 'B3sT9kX7Yz';
            self::$gestionale = 'https://bertoldiboats.wbb.it';
            
            //FattureInCloud
            self::$fattureInCloudUrl = 'https://api.fattureincloud.it/v1/';
            self::$fattureInCloudAPIUID = '513205';
            self::$fattureInCloudAPIKey = 'fca07bd23243a7e3504c038d4ad38698';
            
            self::$fattureInCloudUrl_v2 = 'https://api-v2.fattureincloud.it/';
            self::$fattureInCloudUrl_v2_ClientID = 'qhH7W0FggdnEBoUE3s1rsdgfOpCG0wuL';
            self::$fattureInCloudUrl_v2_APIToken = 'a/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZWYiOiJaRkQ1MFhCYTR2ZXd4RXA4VE1UMDVWVkpxOHVWNUV6diJ9.ZDXsx_x-x1GJMZBWJyXKXuCUA9oIzxXK0mA-gY9I-vc';
            self::$fattureInCloudUrl_v2_CompanyID = 513205;
        }

        static public function load()
        {
           
            self::everywhere();
	
            if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
                self::production();
			elseif(in_array($_SERVER['SERVER_NAME'], self::$__productionServersAlternate))
                self::production();
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
                self::local();
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__stagingServers))
                self::staging();
            	else
                self::production();
        }


        static public function whereAmI()
        {
           
            if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
                return 'production';
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__productionServersAlternate))
                return 'production';
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
                return 'local';
            else
                return false;
        }
    }