<?php
class QRCode {

  public static $_ENCODING_UTF8 = "UTF-8";
  public static $_ENCODING_Shift_JIS = "Shift_JIS";
  public static $_ENCODING_ISO_8859_1 = "ISO-8859-1";

  public static $_OUTPUT_FORMAT_PNG = "png";
  public static $_OUTPUT_FORMAT_GIF = "gif";

  private $baseUrl = "http://api.qrserver.com/v1/create-qr-code";
  private $width=250;
  private $height=250;
  private $map = array();

  function __construct() {
	$this->map['size']=$this->width."x".$this->height;
    $this->map['format'] = QRCode::$_OUTPUT_FORMAT_PNG;
  }

  public function setOutputEncoding($type) { 
	$this->map['charset-target'] = $type;
  }
  public function setOutputFormat($type) { 
	$this->map['format'] = $type;
  }
  public function getOuputFormat() { 
	return $this->map['format']; 
  }
  public function setData($data) { 
	$this->map['data'] = urlencode($data); 
  }
  public function setImageSize($width, $height) {
	  $this->map['size'] = $width."x".$height; 
  }
  public function setMargin($margin) { 
	$this->map['margin'] = $margin; 
  } 
  public function getMap() { 
	return $this->map; 
  }

  public function setErrorCorrectionLevel($errorCorrectionLevel) {
	  $this->map['ecc'] = $errorCorrectionLevel;
  }

  public function getUrlQuery() {
    return $this->baseUrl."?".$this->getQuery();
  }

  public function getQuery() {
    $query = "";
    $keys = array_keys($this->map);
    $i = 0;
    $length = count($this->map);
    foreach($keys as $key) {
      $query .= $key."=".$this->map[$key];
      $i++;
      if($i<$length) $query.="&";
    }
    return $query;
  }

}
?>