<?PHP
    class Errors
    {
        function __construct() {
            
        }

       static public $ErrorePermessiModulo="Permessi insufficienti per accedere al modulo selezionato"; 
       static public $ErrorePermessiModuloFunzione="Permessi insufficienti per accedere alla funzione specificata"; 
   
         function stampa_errore($erroreId)
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