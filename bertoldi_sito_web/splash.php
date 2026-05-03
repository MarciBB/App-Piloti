<?
$basepath=$_SERVER['DOCUMENT_ROOT'];
$classespath_=$basepath."/protected/classes";
include_once($basepath."/include/meta.php");
?>
</head>
<body>
	
    <!-- Wrapper
	================================================== -->
	<div id="wrapper">
    	
        <!-- BG TOP
        ================================================== -->
        <div class="bg_top">
        
            <!-- Top Header
            ================================================== -->
            <? include_once($basepath."/include/top_header2.php"); ?>
        
        </div><!-- bg_top -->
        
        <!-- BG BOTTOM
        ================================================== -->
        <div class="bg_bottom">
        
        	
            <!-- Header
            ================================================== -->
            <div id="header2" style="padding-top: 80px;">
                <div class="container">
                    <div class="sixteen columns">
                    	<div class="info2_container">
                        	<h4 style="width: 400px; margin: 10px auto 0;">Attenzione</h4>
                            <hr class="near_separator"/>
                                                        
                            <p class="big_text" style="font-size: 20px; font-weight: bold; line-height: 24px;">
                            	<br/><br/><br/><br/>
                            	<img src="images/ajax_loader_red.gif" alt="Ajax loader" />
                                <br/><br/>
                            	<span>Ti stiamo reindirizzando sul sito PayPal per effettuare il pagamento</span><br/>Al termine dell'operazione verrai riportato sul sito per completare l'operazione
                            </p>
                            <br/><br/><br/><br/>
                            
                        </div><!-- banner_container -->
                    </div><!-- sixteen -->
                </div><!-- container -->
            </div><!-- header -->
            
            <!-- Main
            ================================================== -->
            <div id="main">
                <div class="container">
                    <div class="sixteen columns">                               		
                    		
                        <form action="">
                            <div class="submit_holder">
                            	<div class="prezzo_show" >
                                	Grazie per aver scelto Rocco Autolinee
                                </div><!-- prezzo_show -->
                            </div><!-- submit_holder -->                        
                        </form>
                        
                        <br/><br/><br/><br/><br/><br/>
                    </div><!-- sixteen -->
                    <div class="clear"></div>
                </div><!-- container -->
            </div><!-- main -->
            
                   
            
        </div><!-- BG BOTTOM -->
        
    </div><!-- wrapper -->
    
    <!-- Nivo Slider
	================================================== -->
    <script type="text/javascript" src="js/jquery.nivo.slider.js"></script>
    <script type="text/javascript">
		$(window).load(function() {
			$('#slider').nivoSlider();
		});
    </script>
    
    <!-- Pretty Photo
	================================================== -->
    <script type="text/javascript" charset="utf-8">
	  $(document).ready(function(){
		$("a[rel^='prettyPhoto']").prettyPhoto({
			theme: 'dark_rounded',	
			social_tools: false
		});
	  });
	</script>
	
<!-- End Document
================================================== -->
</body>
</html>