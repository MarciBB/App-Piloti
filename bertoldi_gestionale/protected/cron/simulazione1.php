<?php

               


ini_set('display_errors', 1);
            ini_set('error_reporting', E_ALL);
$basepath=$_SERVER['DOCUMENT_ROOT'];
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$date_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($date_include);
$config=new Config();
$run=$config->load();
global $db;
$db=new Database();
$db->connect();

$CorsaId=1;

$sql="select PrenotazioneId,ComuneSalitaId, ComuneDiscesaId,ComuneSalita, ComuneDiscesa from RT_PrenotazionePercorso where CorsaId=$CorsaId and (PrenotazioneStato=1 or PrenotazioneStato=3) 
    GROUP BY PrenotazioneId,ComuneSalitaId, ComuneDiscesaId,ComuneSalita, ComuneDiscesa";


$ArrObject = $db->fetch_array($sql);
$n_com=sizeof($ArrObject); 

$nc=0;
//$n_prenotazioni=50;
//while ($nt<$numerotratte)

$DataTitolo=date('Y-m-d H:i:s');
$ArrCombinazioni=array();
$ArrPrenotazioni=array();
  
while ($nc<$n_com)
{
    $ComuneSalita=$ArrObject[$nc]['ComuneSalitaId'];
    $ComuneDiscesa=$ArrObject[$nc]['ComuneDiscesaId'];
    
    $ComuneSal=$ArrObject[$nc]['ComuneSalita'];
    $ComuneDis=$ArrObject[$nc]['ComuneDiscesa'];
    
    $PrenotazioneId=$ArrObject[$nc]['PrenotazioneId'];
    
    $sql="SELECT max(RT_Fermata.FermataId) as FermataId,TrattaId FROM RT_Fermata WHERE RT_Fermata.IsPickup = 1 AND RT_Fermata.ComuneId = $ComuneSalita group by TrattaId";
    
    $ArrObjectFP = $db->fetch_array($sql);
    $n_fer=sizeof($ArrObjectFP); 
   
    $nf=0;
    while ($nf<$n_fer)
    {
       $FermataPickupId=$ArrObjectFP[$nf]['FermataId'];
       $TrattaIdPickup=$ArrObjectFP[$nf]['TrattaId'];
      // echo("oooo".$FermataPickupId." ".$PrenotazioneId."<br />");
       //die();
       
        $sql="SELECT max(RT_Fermata.FermataId) as FermataId,TrattaId FROM RT_Fermata WHERE RT_Fermata.IsDropOff = 1 AND RT_Fermata.ComuneId = $ComuneDiscesa group by TrattaId";
       
        //echo($sql."<br />");
        $ArrObjectFD = $db->fetch_array($sql);
        $n_ferd=sizeof($ArrObjectFD); 
      
        $nfd=0;
        while ($nfd<$n_ferd)
        {
           $FermataDropOffId=$ArrObjectFD[$nfd]['FermataId'];
           $TrattaIdDropOff=$ArrObjectFD[$nfd]['TrattaId'];
           
               $mysqli=new mysqli(Config::$dbserver,Config::$dbuser,Config::$dbpass,Config::$dbname);
		$SQL = "call TrattaConfig('S',0,0,0,'')";  
                
                
                
                // 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore  
                
                if (($result = $mysqli->query($SQL))===false) {
		printf("Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL);
		exit();
		} 
               // $result = $mysqli->query($SQL);
                $out = array();
		 $arrTdrop=array();
                 $conta=0;
                $Tr=0;
               $row=array();
                
                while ($myrow=$result->fetch_array(MYSQLI_ASSOC))
                {
                  //print($TrattaIdDropOff);
                   $Tr=$TrattaIdDropOff;
                 $TrattaIdRow=(string)$myrow['TrattaId'];
               $row[]=$myrow;
               
                // print($TrattaIdRow." ".$TrattaIdDropOff."<br />");
                    if ( ($TrattaIdRow == $TrattaIdDropOff))
                    {
                       
                        
                       //   print($TrattaIdRow);
                          $arrTdrop[]=$conta;
                        //$arrTdrop[]['TrattaId']=$TrattaIdDropOff;
                        
                        
                    }
                  $conta++;  
                }
               
              if (sizeof($arrTdrop))
              {
                  print("<pre>");
                  //  print("Prenotazione ".$PrenotazioneId." ".$TrattaIdDropOff);
                  //   print_r($arrTdrop);
                 
                
              }
                  
                  
               if (sizeof($arrTdrop)>0)
               {
                
                  //print_r($arrTdrop);
                   
                   $c=0;
                   
                   while ($c<sizeof($arrTdrop))
                   {
                      
                      $posizione=(int)$arrTdrop[$c];
                      
                      $fine=false;
                      $arrCount=array();
                      $arrTra=array();
                      $arrGest=array();
                      $arrPadre=array();
                      $count=0;
                      $conta2=0;
                     // print_r($arrTdrop[$c]);
                      
                     
                          
                       
                      
                     // print_r($row);
                      
                      while ($fine==false)
                      {
                          
                          $rag=$row[$posizione]['RagioneSociale'];
                          $tratt=$row[$posizione]['TrattaId'];  //tratta albero
                          $gest=$row[$posizione]['GestoreId'];
                          $padre=$row[$posizione]['GestorePadre'];
                            if (!empty($tratt))
                            {
                                
                            
                          
                          $count=(int)substr_count($rag, '@');
                          
                       
                          
                         if ($tratt==$TrattaIdPickup) // tratta di pickup
                         {
                             if ($tratt==$TrattaIdPickup)
                             {
                                   $arrTra[]=$tratt;
                                   $arrGest[]=$gest;
                                    $arrPadre[]=$padre;
                                   $fine=true;
                             }
                         }
                         else
                         {
                           if (!in_array($count, $arrCount)) {
                            $arrCount[]=$count;
                            $arrTra[]=$tratt;
                             $arrGest[]=$gest;
                              $arrPadre[]=$padre;
                         }  
                             
                              $fine=false;
                         }
                            
                              //echo($tratt." ".$TrattaIdPickup."<br /><br />");
                             
                            
                              
                      }    
                     
                          $len=sizeof($arrPadre);
                          if ($len>1)
                          {
                             
                              if ($arrPadre[$len-1]==$arrPadre[$len-2])
                              {
                                  // die("qui");
                                  unset($arrTra);
                                  $arrTra=null;
                                 $fine=true;
                              }
                             /* if ($arrPadre[$len-1]!=$arrGest[$len-2])
                              {
                                  // die("qui");
                                  unset($arrTra);
                                  $arrTra=null;
                                 $fine=true;
                              }*/
                                 
                              
                              
                          }
                          
                      
                      
                          $posizione--;
                          $conta2++;
                          if($conta2>20)
                          {
                                unset($arrTra);
                                 $arrTra=null;
                              $fine=true;
                              
                          }
                          
                          
                          
                          
                      }
                      
                 
                              
                      //$arrTratteFin[]=$arrTra;
              $arrTra = array_reverse($arrTra);
              $arrGest = array_reverse($arrGest);        
               // controllo coerenza    
              
          
              
              
              
                            //print("PrenId".$PrenotazioneId."<br />");
                      if (sizeof($arrTra))
                      {
                          
                           $gestoreId=$arrGest[0];
                        //    print($gestoreId);
                            $arr_gestori=getGestoreFigli($gestoreId);
                            
                         
                            
                               $newArray = array_intersect_assoc($arrGest, $arr_gestori);
                               $salta=false;
                               
                               $arr=compare($arrGest,$arr_gestori);
                               
                               
                               
                              // $arr=  explode(" ",$c);
                               
                               
                          /*    print_r($arrTra);
                              print_r($arrGest);
                              print_r($arr_gestori);*/
                               echo("Prenotazione ".$PrenotazioneId);
                               
                               if (sizeof($arr)==sizeof($arrGest))
                               {
                                   echo("<br />coerente");
                                   $x=true;
                               }
                                   
                               else
                               {
                                   echo("<br />NON coerente");
                                   $salta=true;
                               }
                                   
                       
                            
                            
                       
                          
                       if ($salta==false)   
                           {
                      $d=0;
                      $trovato=false;
                      $trova=0;
                    
                      $arrTrova=array();
                      while($d<sizeof($ArrCombinazioni))
                      {
                         
                          $containsSearch = count(array_intersect($search_this, $all)) == count($search_this);
                          
                          //if ($ArrCombinazioni[$d]==$arrTra)
                       
                          $newArray = compare($arrTra, $ArrCombinazioni[$d]);
                          
                          $newArray = array_reverse($newArray);          
                         


                            // print("qui");
                   //       print_r($arrTra);
                     //     print_r($ArrCombinazioni[$d]);
                          //print_r($newArray);
                       //   print("--------------------------<br />");
                          $arr=$ArrCombinazioni[$d];
                         
                          
                          if (($arrTra==$ArrCombinazioni[$d]))
                          {
                               //die("qui");
                              
                               if (!in_array($PrenotazioneId, $ArrPrenotazioni[$d]))
                               $ArrPrenotazioni[$d][]=$PrenotazioneId;
                              
                                $trovato=true;
                                $arrTrova[]=$d;
                               
                                
                          }
                        
                          
                         $d++; 
                      }
                   
                     // print_r($arrTra);
                      if (($trovato==false))
                      {
                          $ArrCombinazioni[]=$arrTra;
                          
                         /* $cc=0;
                          while($cc<sizeof($arrTrova))
                          {
                             
                              $val=$arrTrova[$cc];
                              $ArrCombinazioni[$val]['Prenotazione'][]=$PrenotazioneId;
                      
                              
                              $cc++;
                          }*/
                          /*if ($cc==0)
                          {*/
                               $s=sizeof($ArrPrenotazioni);
                               //$s=$s-1;
                              $ArrPrenotazioni[$s][]=$PrenotazioneId;
                              
                          //}
                     }
                      
                       
                     
                    // $ArrCombinazioni[$s]['Prenotazioni'][]=$PrenotazioneId;
                      
                       //print_r($arrTra);
                       
                       }
                      }
                       
                       $c++;
                   }
                   
                   
                   
                 }
                
                
                //return $out;
           

        $nfd++;    
        }
       
       
        
        
    $nf++;    
    }
    
    
    
    
    
    $nc++;
    
     
   
}

print_r($ArrCombinazioni);
print_r($ArrPrenotazioni);
//die();

 //$res=array_intersect($ArrCombinazioni[0],$ArrCombinazioni[2]);
 $ArrCombinazioniNew=array();
 $ArrPrenotazioniNew=array();
  $ArrPrenotazioniNew1=array();
$x=0;
while($x<sizeof($ArrCombinazioni))
{
    $current=$ArrCombinazioni[$x];
    
    $y=0;
    while($y<sizeof($ArrCombinazioni))
    {
        $next=$ArrCombinazioni[$y];
        
        //echo("<br /><br />cerco ".$x." in ".$y."<br />");
        
        $res=array_intersect($current,$next);
        
       // print_r($res);
        if (($res==$current) and ($x!=$y))
        {
            echo("chiave ".$x." in ".$y."<br />");
            $ArrCombinazioniNew[]=  $next;
            $ArrPrenotazioniNew[]=  $ArrPrenotazioni[$y];
            
           // $ArrPrenotazioniNew1[$ArrCombinazioniNew[$y]][]=sizeof($ArrCombinazioniNew)-1;
            
        }
        
        
        $y++;
    }
    
    
    $x++;
}

$x=0;
while($x<sizeof($ArrObject))
{
   
    $PrenotazioneId=$ArrObject[$x]['PrenotazioneId'];
   
    //die("qui");
    $c=0;
    while($c<sizeof($ArrPrenotazioniNew))
    {
        //echo("qui ".$PrenotazioneId."<br />");
       //  print_r ($ArrPrenotazioniNew[$c]);
        if (in_array($PrenotazioneId, $ArrPrenotazioniNew[$c]))
        {
          //  die("qui");
             $ArrPrenotazioniNew1[$PrenotazioneId][]=$c;
            
        }
        
        $c++;
    }
    
    
   
    
    
    
    
    
    
    $x++;
}



print("array combinazioni");
print_r(($ArrCombinazioniNew));
print("array prenotazioni");
print_r($ArrPrenotazioniNew);

print("array prenotazioni1");
print_r($ArrPrenotazioniNew1);
/*$arr1=usort($ArrCombinazioni, function ($a, $b) { return strlen($b[0]) - strlen($a[0]); });
print_r($arr1);*/

/*
$list1 = $ArrCombinazioni['CombinazioneTratte'][0][0]['TrattaId'];
$list2 = $ArrCombinazioni['CombinazioneTratte'][1][0]['TrattaId'];
$commonElements = array_intersect($list1,$list2);

var_dump($commonElements);*/
function umerge($arrays){
 $result = array();
 foreach($arrays as $array){
  $array = (array) $array;
  foreach($array as $value){
   if(array_search($value,$result)===false)$result[]=$value;
  }
 }
 return $result;
}


     function getGestoreFigli($idgestore)
    {
        
                $mysqli=new mysqli(Config::$dbserver,Config::$dbuser,Config::$dbpass,Config::$dbname);
		$SQL = "call TrattaConfig('S',$idgestore,0,0,'')";  
                
                
                
                // 1. (S=Select, I=Insert, U=Update, D=delete) 2. tipo,gestoreid,odcid,nuovo gestore padre,nuovo nome gestore  
                
                if (($result = $mysqli->query($SQL))===false) {
		printf("Invalid query: %s\nWhole query: %s\n", $mysqli->error, $SQL);
		exit();
		} 
                //$result = $mysqli->query($SQL);
                $out = array();
		
                while ($myrow=$result->fetch_array(MYSQLI_ASSOC))
                {
                    $out[]=$myrow["GestoreId"];
                   //echo($myrow["GestoreId"]."-".$myrow['RagioneSociale']."<br />");
                    
                }
                $result->close();
                $mysqli->close();
                return $out;
        
    }
function compare($arr1,$arr2){
 //contiamo gli elementi degli array per stabilire quale e' il maggiore
 $count1=count($arr1);
 $count2=count($arr2);
 //definiamo il valore maggiore e minore per il ciclo for successivo
 if ($count1>=$count2){
 $arrayMaj=$count1;
 $arrayMin=$count2;
 }
 if($count2>=$count1){
 $arrayMaj=$count2;
 $arrayMin=$count1;
 }
 $c=array();
 //il primo ciclo fa scorrere tutti gli elementi dell' array piu' grande
 for ($i=0; $i<=$arrayMaj; $i++){
  //questo for compara un elemento alla volta dell' array piu' grande con tutti quelli del minore
  for($k=0; $k<=$arrayMin; $k++){
  //nell' if il primo array da mettere sara'  quello piu grande
  if($arr1[$i]==$arr2[$k]){
      
      if (!empty($arr1[$i]))
      $c[]=$arr1[$i];
     
      
      $common=$arr1[$i].'--'.$common;
  }
  }
  }
 return $c;
 }
?>
