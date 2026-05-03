<?php
global $basepath;
$type="";
if (defined('STDIN')) {
  $type = $argv[1];
  
  if ($type=='production')
     $basepath='/home/office/public_html';
  elseif ($type=='staging')
     $basepath='/home/bertoldiboats/public_html';
  elseif ($type=='local_luca')
  	  $basepath='C:/wamp64New/www2/bertoldi_git/bertoldi_gestionale';
  else {
      die('argomento non passato');    
  }
  
  
} else { 
  $basepath=$_SERVER['DOCUMENT_ROOT'];
}

