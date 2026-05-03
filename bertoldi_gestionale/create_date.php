 <?PHP
  ini_set('display_errors', 0);
 ini_set('error_reporting', E_ALL);
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$db=new Database();
$db->connect();

$startDate = ('2026-01-01');
$endDate = ('2026-12-31');
//$db->delete("RT_AppCalendario","AppCalendarioData<='2015-12-31'");
//die();
$dateArr = getDateForSpecificDayBetweenDates($startDate, $endDate); // sabato fino a lunedì2




function getDateForSpecificDayBetweenDates($startDate, $endDate)
{
    global $db;
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    $dateArr = array();
    $datePar = array();
 


    while($startDate <= $endDate)
    {
        $ggsett=date("w", $startDate);
        $dataCal=null;
        $dataCal['GiornoSettimana']=$ggsett;
        $dataCal['Festivo']=0;
        $dataCal['Prefestivo']=0;
        $dataCal['Feriale']=0;
        
        if ($ggsett==0)
         $dataCal['Festivo']=1;
        elseif ($ggsett==6)
         $dataCal['Prefestivo']=1;
        else
         $dataCal['Feriale']=1;   
        
        
         
        
        $DataInizio = date('Y-m-d', $startDate);
        $dt=new DT($DataInizio,'Y-m-d');
        $dt->addDays($day);
        $DataFine=$dt->getDate('Y-m-d');
        
        
        $sql="select FestivitaId from RT_Festivita where DataFestivita='$DataInizio'";
       // echo("<br />".$sql);
         $r=$db->query_first($sql);
         if ($r['FestivitaId']>0)
         {
          $dataCal['Festivo']=1;   
          $dataCal['Prefestivo']=0;
          $dataCal['Feriale']=0;
         }
        
        $dataCal['AppCalendarioData']=$DataInizio;
        //$datePar[]=$DataFine;
        
        $db->insert('RT_AppCalendario',$dataCal);
        
        $startDate += (1 * 24 * 3600); // add 7 days
        print "<pre>";
        print_r($dataCal);
    }

    
  
}
  ?>