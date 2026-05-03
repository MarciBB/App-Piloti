<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author a.esposito
 */
class Sms  {

public $conn;

function __construct() {
   
}



public function SmsCliccaTel($prefisso,$cellulare,$message)
{
        global $user;
        $db=$this->conn;
        
      
        $prefisso=str_replace("+","",$prefisso);
        $prefisso=trim($prefisso);
        $url="http://api.clickatell.com/soap/webservice.php?WSDL";
        
        $url="http://api.clickatell.com/soap/document_literal/webservice";
        $cellulare=trim($cellulare);
        $companyName="EURO BUS SERVICE";
        $user = "euritalia";
        $password = "UQKQCADKWVaKFR";
        $api_id = "3439183";
        
        $to = $prefisso.$cellulare;
        $text = $message;
        $eol="";
        
        
//         $api='<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://api.clickatell.com/soap/webservice" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
//    <soapenv:Header/>
//    <soapenv:Body>
//       <web:sendmsg soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
//         <api_id xsi:type="xsd:int">'.$api_id.'</api_id>
//          <user xsi:type="xsd:string">'.$user.'</user>
//          <password xsi:type="xsd:string">'.$password.'</password>
//          <concat xsi:type="xsd:int">2</concat>
//          <to xsi:type="web:ArrayOfString" soapenc:arrayType="xsd:string[]">
//           <item xsi:type="xsd:string">'.$to.'</item>
//          </to>
//          <from xsi:type="xsd:string">'.$companyName.'</from>
//          <text xsi:type="xsd:string">'.$message.'</text>
//       </web:sendmsg>
//    </soapenv:Body>
// </soapenv:Envelope>';
        
        $api1 = '<?xml version="1.0" encoding="ISO-8859-1"?>
	<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAPENC="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:tns="soap.clickatell.com">
	<SOAP-ENV:Body>
		<tns:sendmsg xmlns:tns="soap.clickatell.com">
			<api_id xsi:type="xsd:int">'.$api_id.'</api_id>
         	<user xsi:type="xsd:string">'.$user.'</user>
         	<password xsi:type="xsd:string">'.$password.'</password>
         	<concat xsi:type="xsd:int">2</concat>
        	<to xsi:type="web:ArrayOfString" soapenc:arrayType="xsd:string[]">
          		<item xsi:type="xsd:string">'.$to.'</item>
         	</to>
         	<from xsi:type="xsd:string">'.$companyName.'</from>
         	<text xsi:type="xsd:string">'.$message.'</text>
		</tns:sendmsg>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
	
//         echo $api1;
//         echo "<br><br><br>";

        $api1 = '<?xml version="1.0" encoding="UTF-8"?>
		<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
        		xmlns:ns1="http://api.clickatell.com/soap/document_literal/webservice">
    		<SOAP-ENV:Body>
        		<ns1:sendmsg>
	        		<api_id>'.$api_id.'</api_id>
	        		<user>'.$user.'</user>
	        		<password>'.$password.'</password>
	        		<from>'.$companyName.'</from>
	        		<text>'.$message.'</text>
	        		<to>'.$to.'</to>
	        	</ns1:sendmsg>
	    	</SOAP-ENV:Body>
		</SOAP-ENV:Envelope>';
// echo $api1;echo "<br><br><br>";
	$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml','Connection: Close'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $api1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = (curl_exec($ch));
        $err = curl_error($ch);  
        
        echo $output;
     
}

public function SmsCliccaTelNoPrefisso($cellulare,$message)
{
	global $user;
	$db=$this->conn;


	$cellulare=str_replace("+","",$cellulare);
	$url="http://api.clickatell.com/soap/webservice.php?WSDL";
	$cellulare=trim($cellulare);
	$companyName="EURO BUS SERVICE";
	$user = "euritalia";
	$password = "UQKQCADKWVaKFR";
	$api_id = "3439183";

	$to = $cellulare;
	$text = $message;
	$eol="";


	//         $api='<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://api.clickatell.com/soap/webservice" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
	//    <soapenv:Header/>
	//    <soapenv:Body>
	//       <web:sendmsg soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	//         <api_id xsi:type="xsd:int">'.$api_id.'</api_id>
	//          <user xsi:type="xsd:string">'.$user.'</user>
	//          <password xsi:type="xsd:string">'.$password.'</password>
	//          <concat xsi:type="xsd:int">2</concat>
	//          <to xsi:type="web:ArrayOfString" soapenc:arrayType="xsd:string[]">
	//           <item xsi:type="xsd:string">'.$to.'</item>
	//          </to>
	//          <from xsi:type="xsd:string">'.$companyName.'</from>
	//          <text xsi:type="xsd:string">'.$message.'</text>
	//       </web:sendmsg>
	//    </soapenv:Body>
	// </soapenv:Envelope>';


	$api1 = '<?xml version="1.0" encoding="ISO-8859-1"?>
	<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAPENC="http://schemas.xmlsoap.org/soap/encoding/"
	xmlns:tns="soap.clickatell.com">
	<SOAP-ENV:Body>
		<tns:sendmsg xmlns:tns="soap.clickatell.com">
			<api_id xsi:type="xsd:int">'.$api_id.'</api_id>
         	<user xsi:type="xsd:string">'.$user.'</user>
         	<password xsi:type="xsd:string">'.$password.'</password>
         	<concat xsi:type="xsd:int">2</concat>
        	<to xsi:type="web:ArrayOfString" soapenc:arrayType="xsd:string[]">
          		<item xsi:type="xsd:string">'.$to.'</item>
         	</to>
         	<from xsi:type="xsd:string">'.$companyName.'</from>
         	<text xsi:type="xsd:string">'.$message.'</text>
		</tns:sendmsg>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';


	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml','Connection: Close'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $api1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = (curl_exec($ch));
	$err = curl_error($ch);

	echo $output;
	 
}


}
?>
