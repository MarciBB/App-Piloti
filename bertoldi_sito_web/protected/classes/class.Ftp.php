<?php
/**
 * PHPSense FTP Class
 *
 * PHP tutorials and scripts
 *
 * @package		PHPSense
 * @author		Jatinder Singh Thind
 * @copyright	Copyright (c) 2006, PHPSense.com
 * @license		http://www.phpsense.com/license.html 
 * @link		http://www.phpsense.com
 * @since		Version 1.0
 */
 
// ------------------------------------------------------------------------

class Ftp {
	var $docroot;
	var $ftproot;
	var $host;
	var $username;
	var $password;
	var $ftpstream;
	var $debug;
	
	/**
	 * Constructor
	 *
	 * The constructor opens a connection to the FTP server
	 * and logins to the server using the specified details.
	 * @param string $docroot The document root of your web server . Example : /home/jatinder/public_html/
	 * @param string $ftproot The document root of your FTP server. Example : /public_html/
	 * @param string $host The domain name of your FTP server
	 * @param string $username FTP server login ID
	 * @param string $password FTP server password
	 */
	 
	    function __construct ($docroot, $ftproot, $host, $username, $password) {
                
               
		$this->docroot = $docroot;
		$this->ftproot = $ftproot;
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->debug = true;
		
               /* echo($this->docroot."<br />");
                echo($this->ftproot."<br />");
                echo($this->host."<br />");
                echo($this->username."<br />");
                echo($this->password."<br />");*/
                
                
		$ftpstream = @ftp_connect($host);
		if($ftpstream) {
			$login = @ftp_login($ftpstream, $username, $password);
			if($login) {
				$this->ftpstream = $ftpstream;
				return true;
			}
			else {
				if($this->debug) echo "Failed to login to FTP server!<br />";
				@ftp_close($ftpstream);
				return false;
			}
		}
		else {
			if($this->debug) echo "Failed to connect to FTP server!<br />";
			return false;
		}
	}
	
	/**
	 * Create a folder on the server
	 *
	 * @access public
	 * @param string $pathname Path and name of the folder to create. The path must be realtive to your website root. Example : test
	 * @return bool
	 */
	function f_mkdir($pathname) {
		if($this->ftpstream) {
			$pathname = @trim($pathname);
			$pathname = @str_replace('..','',$pathname);
			if($pathname != '') {
				if($this->debug) echo "mkdir: ",$this->ftproot.$pathname,"<br />";
				@ftp_mkdir($this->ftpstream, $this->ftproot.$pathname);
			}
			else {
				return false;
			}
		}
		else {
			if($this->debug) echo "Not connected to the FTP server<br />";
			return false;
		}
	}
	
	/**
	 * Create a file
	 *
	 * @access public
	 * @param string $filename Path and name of the file to create. The path should be relative to the website root. Example : test/config.txt
	 * @return bool
	 */
	function f_fopen($filename) {
		if($this->ftpstream) {
			$filename = @trim($filename);
			$filename = @str_replace('..','',$filename);
			if($filename != '') {
				if($this->debug) echo "fopen: ",$this->ftproot.$filename,"<br />";
				$file = basename($filename);
				$temp = tmpfile();
				@ftp_fput($this->ftpstream, $this->ftproot.$filename, $temp, FTP_ASCII);
				fclose($temp);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			if($this->debug) echo "Not connected to the FTP server<br />";
			return false;
		}
	}
	
	/**
	 * Write to a string to file
	 *
	 * @access public
	 * @param string $filename Path and name of the file to which data is to be written. The path should be relative to the website root. Example : test/config.txt
	 * @param string $mode The mode parameter behaves similar to the mode parameter in fopen function
	 * @param string $data String data to write to the file
	 * @return bool
	 */
	function f_fputs($filename, $mode, $data) {
		if($this->ftpstream) {
			$filename = @trim($filename);
			$filename = @str_replace('..','',$filename);
			if($filename != '') {
				if($this->debug) echo "chmod ",$this->ftproot.$filename," to 0777<br />";
				
				@ftp_site($this->ftpstream,"CHMOD 0777 ".$this->ftproot.$filename);
				$fp = fopen($this->docroot.$filename, $mode);
				
				if($this->debug) echo "Opening file ",$this->ftproot.$filename,"<br />";
				
				fputs($fp,$data);
				
				if($this->debug) echo "Writting to file ",$this->ftproot.$filename,"<br />";
				
				fclose($fp);
				
				if($this->debug) echo "chmod ",$this->ftproot.$filename," to 0644<br />";
				
				@ftp_site($this->ftpstream,"CHMOD 0644 ".$this->ftproot.$filename);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			if($this->debug) echo "Not connected to the FTP server<br />";
			return false;
		}
	}
	
	/**
	 * Clean up
	 *
	 * @access public
	 * @return bool
	 */
	function cleanup() {
		if($this->ftpstream) {
			ftp_close($this->ftpstream);
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Set debug flag
	 *
	 * @access public
	 * @param bool $debug
	 * @return void
	 */
	function setDebug($debug) {
		$this->debug = $debug;
	}
}
?>
