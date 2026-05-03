	<script type="text/javascript">
        (function() {
            function addEventListener(element, event, handler) {
                if (element.addEventListener) {
                    element.addEventListener(event, handler, false);
                } else if (element.attachEvent) {
                    element.attachEvent('on' + event, handler);
                }
            }

            function maybePrefixUrlField() {
                if (this.value.trim() !== '' && this.value.indexOf('http') !== 0) {
                    this.value = "http://" + this.value;
                }
            }

            var urlFields = document.querySelectorAll('.mc4wp-form input[type="url"]');
            if (urlFields && urlFields.length > 0) {
                for (var j = 0; j < urlFields.length; j++) {
                    addEventListener(urlFields[j], 'blur', maybePrefixUrlField);
                }
            } /* test if browser supports date fields */
            var testInput = document.createElement('input');
            testInput.setAttribute('type', 'date');
            if (testInput.type !== 'date') {

                /* add placeholder & pattern to all date fields */
                var dateFields = document.querySelectorAll('.mc4wp-form input[type="date"]');
                for (var i = 0; i < dateFields.length; i++) {
                    if (!dateFields[i].placeholder) {
                        dateFields[i].placeholder = 'YYYY-MM-DD';
                    }
                    if (!dateFields[i].pattern) {
                        dateFields[i].pattern = '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])';
                    }
                }
            }

        })();
		

		document.addEventListener('DOMContentLoaded', function() {
			if (window.self !== window.top) {
				document.body.classList.add('iframe-hidden');
			}
		});

    </script>
    
    <!--[if lte IE 8]>
	<style>
		.attachment:focus {
			outline: #1e8cbe solid;
		}
		.selected.attachment {
			outline: #1e8cbe solid;
		}
	</style>
	<![endif]-->

    <script type="text/html" id="tmpl-media-modal">
        <div class="media-modal wp-core-ui">
            <button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span>
            </button>
            <div class="media-modal-content"></div>
        </div>
        <div class="media-modal-backdrop"></div>
    </script>
    <script type="text/html" id="tmpl-uploader-status-error">
        <span class="upload-error-filename">{{{ data.filename }}}</span>
        <span class="upload-error-message">{{ data.message }}</span>
    </script>
    <script type="text/html" id="tmpl-edit-attachment-frame">
        <div class="edit-media-header">
            <button class="left dashicons <# if ( ! data.hasPrevious ) { #> disabled <# } #>"><span class="screen-reader-text">Edit previous media item</span></button>
            <button class="right dashicons <# if ( ! data.hasNext ) { #> disabled <# } #>"><span class="screen-reader-text">Edit next media item</span></button>
        </div>
        <div class="media-frame-title"></div>
        <div class="media-frame-content"></div>
    </script>
    <script type="text/javascript">
        function revslider_showDoubleJqueryError(sliderID) {
            var errorMessage = "Revolution Slider Error: You have some jquery.js library include that comes after the revolution files js include.";
            errorMessage += "<br> This includes make eliminates the revolution slider libraries, and make it not work.";
            errorMessage += "<br><br> To fix it you can:<br>&nbsp;&nbsp;&nbsp; 1. In the Slider Settings -> Troubleshooting set option:  <strong><b>Put JS Includes To Body</b></strong> option to true.";
            errorMessage += "<br>&nbsp;&nbsp;&nbsp; 2. Find the double jquery.js include and remove it.";
            errorMessage = "<span style='font-size:16px;color:#BC0C06;'>" + errorMessage + "</span>";
            jQuery(sliderID).show().html(errorMessage);
        }
    </script>
    <script type='text/javascript' src='/plugins/contact-form-7/includes/js/scripts.js'></script>
    <script type='text/javascript' src='/js/underscore.min.js'></script>
    <script type='text/javascript' src='/js/shortcode.min.js'></script>
    <script type='text/javascript' src='/js/wp-util.min.js'></script>
    <script type='text/javascript' src='/js/backbone.min.js'></script>
    <script type='text/javascript' src='/js/media-models.min.js'></script>
    <script type='text/javascript' src='/js/plupload/wp-plupload.min.js'></script>
    
    <script type='text/javascript' src='/js/bootstrap.min.js'></script>
    <script type='text/javascript' src='/js/loopcounter.js'></script>
    <script type='text/javascript' src='/js/jquery.prettySocial.min.js'></script>
    <script type='text/javascript' src='/js/ajax-booking-btn.js'></script>
    <script type='text/javascript' src='/js/main.js'></script>
    <script type='text/javascript' src='/plugins/js_composer/assets/js/dist/js_composer_front.min.js'></script>

    <!-- Nivo Slider
	================================================== -->
    <script type="text/javascript" src="/js/jquery.nivo.slider.js"></script>
    <script type="text/javascript">
		$(window).load(function() {
			$('#slider').nivoSlider({
				pauseTime: 20000
			});
		});
    </script>
     
    <!-- Pretty Photo
	================================================== -->
    <script type="text/javascript" charset="utf-8">
	  $(document).ready(function(){
		$("a[rel^='prettyPhoto']").prettyPhoto({
			theme: 'dark_rounded',	
			social_tools: false,
                        deeplinking: false
		});
	  });
	</script>
	
	
	<!-- LOGOUT
	================================================== -->
	<script>
	$('#logout, #logout-profilo').click(function(){
				var formData = {
					action: 'logout'
				};
				$.ajax({
				    type: "POST",
				    url: "/gestione_utente.php",
				    // The key needs to match your method's input parameter (case-sensitive).
				    data: formData,
				    dataType: "json",
				    success: function(data){
							window.location.replace("/");
					},
				    failure: function(errMsg) {
				        alert(errMsg);
				    }
				});
			});
	</script>
	<!-- End LOGOUT
    ================================================== -->