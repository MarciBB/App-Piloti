<?
$basepath = $_SERVER['DOCUMENT_ROOT'];

include_once($basepath . "/main_include.php");

$PageTitle = $dizionario['chi_siamo']['titolo'];
$PageDescription = $dizionario['chi_siamo']['descr'];
$PageKeywords = $dizionario['chi_siamo']['key'];

$page_title = $dizionario['chi_siamo']['titolo'];
$page_parent = $dizionario['chi_siamo']['descr'];

$LinkActive = 4;

if ( ! session_id() ) {
	session_start();
}
?>

<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body class="home page-template page-template-homepage page-template-homepage-php page page-id-1036 fullwidth-bg wpb-js-composer js-comp-ver-5.0 vc_responsive">
	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        
        <!-- Top Header
        ================================================== -->
        <? include_once($basepath."/include/top_header.php") ?>       

        <section id="main" class="clearfix">
            <div class="container">
        		<div id="content" class="site-content" role="main">
        			
        			<!-- Main
                    ================================================== -->
        			<div class="main_content_article">
                        <br>
                    	<ul class="breadcrumb">
                            <li><a href="/<?= $_SESSION['code_gestore'] ?>"><?=$dizionario['area_clienti']['area_agenzie']?></a></li>
                            <li><?=$dizionario['area_clienti']['registrati']?></li>
                        </ul>
                        <hr/>
                          
                        <div class="clear"></div>
                        
                        <?=$dizionario['area_clienti']['agenzia_conferma']?>
                                
                        <br><br>
                                
                        <div class="submit_holder">
                        	<input class="blue_btn" type="submit" value="<?=$dizionario['area_clienti']['torna']?>" style="width:auto !important" id="recupero-button"/>
                    	</div><!-- submit_holder -->
                    </div>
                    
        		</div>
                <!--/#content-->
            </div>
            <!--/container-->

            <!-- Bottom
            ================================================== -->
        	<? include_once($basepath."/include/bottom.php") ?>
        	 
        </section>
        <!--/#main-->
        
        
        <!-- Footer
        ================================================== -->
        <? include_once($basepath."/include/footer.php") ?>    
        
    </div>
    <!-- #page -->
  
	<? include_once($basepath."/include/html_close.php") ?>   

	<script type="text/javascript">
		$(document).ready(function(){
			$('#recupero-button').click(function(){
				var formData = {
						action: 'recupero-password',
				        email: $('#email').val()
				};
				window.location.replace("/");
			});
		});
  	</script>
</body>
</html>