<?PHP
    class NotificaAutomatica
    {
        public $conn;
        function __construct() {
            
        }

       
         function scrivi_notifica($CodiceNotifica,$TipoSoggetto,$SoggettoId,$NotificaTipoId)
         {
             
            switch($erroreId) {
                case "1":
                    
                    print(Errors::$ErrorePermessiModulo);
                break;
            
                case "2":
                    print(Errors::$ErrorePermessiModuloFunzione);
                break;
             
            }
         }
    }