<?PHP
    class Html_Page 
    {
         
        function __construct() {
            
        }

		function html_header()
			{
                    global $user;
			?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>                            
                            <?php                            
                            if ((browser_detection('browser') != 'moz' )and(browser_detection('browser') != 'saf' )){ ?>
                           <script type="text/javascript">
                                    window.location = "getfirefox.php";
                            </script>
                            <?php } ?>
                            
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title><?=$user->OdcNome?> - <?=Config::$application_name?> Ver. <?=Config::$application_version?> - <?=Config::$application_company?></title>
			    
			    <link rel="stylesheet" type="text/css" href="/css/reset.css" />
			    <link rel="stylesheet" type="text/css" href="/css/style.css" />
                            <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
                            
			    <script type="text/javascript" src="/js/jquery.min.js"></script>
                            <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
  

                            
			    <script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
			    <script type="text/javascript" src="/js/menu_hover.js"></script> 
			    <script type="text/javascript" language="javascript" src="/js/dialogbox.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/common.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
			    <script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
                              <script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
                          
                             
                            
                            
				<!--[if lte IE 8]>
				<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
				<![endif]-->             
			</head>
			<body>
                        <div style="position: absolute; display: none;" id="tooltip">
                        <!-- menu stuff in here -->
                        </div>
                            <!-- loading div tab -->

      
			    <div id="brain_loading">Caricamento dati in corso...</div>

                            <div id="layer_nero2" class="notifica">
                                    <div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="Caricamento in corso, attendere prego." /><p>Caricamento in corso, attendere prego.</p></div>
                            </div>
                            <!-- loading div -->

                            <div id="layer_nero" class="notifica">
                                    <div id="loading_big_loading"><img src="/images/ajax-loader.gif" alt="Caricamento in corso, attendere prego." /><p>Caricamento in corso, attendere prego.</p></div>

                            </div>
                            <div id="loading_big_ok" class="notifica">
                            <div id="loading_big_loading"><img src="/images/loading_ok.png" alt="Operazione completata con successo." /><p>Operazione completata con successo.</p></div>
                            </div>
                            <div id="loading_big_no" class="notifica">
                            <div id="loading_big_loading"><img src="/images/loading_err.png" alt="Si Ã¨ verificato un errore durante l'operazione." /><p>Si Ã¨ verificato un errore durante l'operazione.</p></div>
                            </div>

                            <!-- loading div -->
                                    
                                    
                                 <div id="brain_oscura" class="brain_oscura">
				    <div id="brain_boxCentrato">
					<div id="brain_listaSelezione" class="brain_dialogbox"></div>
					<br style="clear:both;"/>
				    </div>
                                </div>   
                            
                            <div id="brain_oscura_pre" class="brain_oscura">
				   
                                </div>   
                                    
			<?php
			}
			
			function html_footer() {               
			?>
            </div>
            <div class="hid">a</div>
            <div class="footer">RoccoAuto - Biglietteria Online &copy;<?=Date('Y')?> - Tutti i diritti riservati powered by Brain Computing S.p.A</div>
            </body>
			</html>
                        	

			<?php
			}
                        
                       public function html_titolo_pagina($titolo,$torna,$modulo,$pagina)
                       {
                        ?>
                        <div class="brain_titoloPagina">
                        <?php if ($torna==1)
                        {
                            ?>                        
                        <div id="back_list"><img src="/images/back_list.png" width="20" height="19" alt="Torna all'elenco" /><a href="javascript:void(0);" onclick="loadMainContent('<?=$modulo?>','<?=$pagina?>',this);">Torna all'elenco</a></div>
                        <?php
                        }
                        ?>
                        
                        <h2><?=$titolo?></h2>
                        </div>
                        <div id="brain_page-content">
                        <?php
                           
                           
                       }
                       
                       public function html_titolo_box($titolo)
                       {
                           ?>
                            <span class="brain_titleBox">
                             <h1><?=$titolo?></h1>
                             <a class="brain_buttonChiudi" href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi">Chiudi</a></span>
                            <?php
                           
                       }
                       
                       public function html_tasto_lista($classe,$cartella,$page,$titolo)
                       {
                           ?>
                            <div class="brain_servicebar">
                                <a class="<?=$classe?>" href="javascript:void(0);" onclick="loadMainContent('<?=$cartella?>','<?=$page?>',this);" title="<?=$titolo?>"><?=$titolo?></a>
                            </div>
                        
                            <?php
                           
                       }
}
?>	