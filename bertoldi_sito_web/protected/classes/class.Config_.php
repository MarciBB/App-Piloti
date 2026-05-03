<?PHP
    class Config
    {
        // Server Name
        static private $__productionServers = array('resolve.braincomputing.com');
        static private $__localServers      = array('resolve.braincomputing.com');

        // Define any config settings you want to use here, and then set them in the appropriate
        // location functions below (everywhere, production, staging, and local).

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
        
        static public $AppIva;
	
		
		
      
        static public $useDBSessions; // Set to true to store sessions in the database


       // Codice e variabili che sono comuni sia al server di produzione che al server di test
        static public function everywhere()
        {
            self::$application_name   = 're.Solve dev';
            self::$application_version   = '1.0';
            self::$application_company   = 'AREA SVILUPPO';
            self::$email_admin   = 'info@braincomputing.com';
            self::$AppIva   = 20;
			
		    // Registra sessioni nel db?
            self::$useDBSessions = false;

            // Settaggio per l'autenticazione
            self::$auth_domain   = '217.72.102.151';
            self::$auth_hash     = false;
	    self::$auth_salt     = '4aalidoS3ff%$4xepress5W2£ant&6Tre';
	    self::$auth_key     = 'token';
            
            // settaggio dei percorsi
            self::$basepath     = $_SERVER['DOCUMENT_ROOT'];
            
            
            self::$modulespath     = self::$basepath."/protected/modules/";
            self::$classespath     = self::$basepath."/protected/classes/";
            self::$docupload = self::$basepath.'/upload/';
            self::$odcfile = self::$basepath.'/odcfile/';
           
			
			
        }

        // Codice e variabili che valgono solo per il server di produzione
        static public function production()
        {
            define('WEB_ROOT', dirname(__FILE__));
	    self::$dbserver = 'localhost';
            self::$dbname   = 'resolve_dev_odc';
            self::$dbuser   = 'dbadmin';
            self::$dbpass   = 'dbadmin';
            self::$dberror  = true;
			
			
            self::$ftpserver   = '217.72.102.151';
            self::$ftpuser   = 'ftpadmin';
            self::$ftppass   = 'ftpadmin';
            self::$docroot   = self::$basepath;
            self::$ftproot   = self::$basepath;           
			
			
           ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
            
        }

       

        // Codice e variabili che valgono solo per il server di test
        static public function local()
        {
            define('WEB_ROOT',dirname(__FILE__));

            self::$dbserver = 'localhost';
            self::$dbname   = 'resolve_dev_odc';
            self::$dbuser   = 'dbadmin';
            self::$dbpass   = 'dbadmin';
            self::$dberror  = true;
			
	    self::$ftpserver   = '217.72.102.151';
            self::$ftpuser   = 'ftpadmin';
            self::$ftppass   = 'ftpadmin';
            self::$docroot   = self::$basepath;
            self::$ftproot   = self::$basepath;            
			
            ini_set('display_errors', 0);
            ini_set('error_reporting', E_ALL);
		
			
        }

        static public function load()
        {
            self::everywhere();
			self::local();
			
            /*if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
                self::production();
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
                self::local();
            else
                die('Where am I? (You need to setup your server names in class.config.php) $_SERVER[\'SERVER_NAME\'] reported: ' . $_SERVER['SERVER_NAME']);*/
        }

        static public function whereAmI()
        {
            if(in_array($_SERVER['SERVER_NAME'], self::$__productionServers))
                return 'production';
            elseif(in_array($_SERVER['SERVER_NAME'], self::$__localServers))
                return 'local';
            else
                return false;
        }
    }
