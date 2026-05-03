/*global $:false */
jQuery(document).ready(function($){'use strict';

	/* -------------------------------------- */
	// 		RTL Support Visual Composer
	/* -------------------------------------- */	
	var delay = 100;
	function themeum_rtl() {
		if( jQuery("html").attr("dir") == 'rtl' ){
			var count = jQuery( ".entry-content > div.vc_row-has-fill" ).length;
			if( count==1 ){
				if ( jQuery( ".entry-content > div.vc_row-has-fill" ).attr( "data-vc-full-width" ) ) {
				    if( jQuery( ".entry-content > div.vc_row-has-fill" ).data( "vc-full-width" ) === true ){
						jQuery( '.entry-content > div.vc_row-has-fill' ).css({'left':'auto','right':jQuery('.entry-content > div.vc_row-has-fill').css('left')});
					}
				}
			}else{
				for (var i = 0; i < count; ++i) {
					if ( jQuery( ".entry-content > div.vc_row-has-fill" ).eq(i).attr( "data-vc-full-width" ) ) {
					    if( jQuery( ".entry-content > div.vc_row-has-fill" ).eq(i).data( "vc-full-width" ) === true ){
							jQuery( '.entry-content > div.vc_row-has-fill' ).eq(i).css({'left':'auto','right':jQuery('.entry-content > div.vc_row-has-fill').eq(i).css('left')});	
						}
					}
				}
			}
		}
	}
	setTimeout( themeum_rtl , delay);

	jQuery( window ).resize(function() {
		setTimeout( themeum_rtl , 1);
	});	
	// RTL end.


	// Sticky Nav
	jQuery(window).on('scroll', function(){'use strict';
		if ( jQuery(window).scrollTop() > 0 ) {
			jQuery('#masthead').addClass('sticky');
		} else {
			jQuery('#masthead').removeClass('sticky');
		}
	});

	// for package search wrapper v2
	$( ".package-search-wrapper-v2" ).prepend( "<span class='border'></span>" );

	//section border
	$( ".section-with-border" ).prepend( "<span class='thm-border'></span>" );
	$( ".section-with-border-gray" ).prepend( "<span class='thm-border-gray'></span>" );

	// $("#YourElementID").css("display","block");
	// .thm-flight-results-wrap{
	//
	// }
	// SOcial Share ADD
	$('.prettySocial').prettySocial();

	/* -------------------------------------- */
	// 	Product Single Page Plus Minus Button
	/* -------------------------------------- */
	if ($('.single-product-details').length) {
		$('.single-product-details .quantity input[type="number"]').attr( 'type','text');
		var html = '<button type="button" class="btn-minus">-</button>'+$('.quantity').html()+'<button type="button" class="btn-plus">+</button>';
		$('.single-product-details .quantity').html(html);
	}

	$('.btn-minus').on('click', function(event) { 'use strict';
		var sp = $('.quantity input[name="quantity"]').val();
		if( sp != 1 ){
			$('.quantity input[name="quantity"]').attr( 'value', (parseInt(sp)-1) );
		}
	});
	$('.btn-plus').on('click', function(event) { 'use strict';
		var sp = $('.quantity input[name="quantity"]').val();
		$('.quantity input[name="quantity"]').attr( 'value', (parseInt(sp)+1) );
	});

	//package details tab
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	  	var target = $(this).attr('href');

	  	console.log(target);

	    $(target).css('bottom','-'+30+'px');
	    var left = $(target).offset().bottom;
	    $(target).css({left:left}).animate({"bottom":"0px"}, "10");
	});

	$("#paypal-buy-btn").on('click', function (e) {
		e.preventDefault();

		var paypal_form = $("#paypal-order-form"),
			thisForm = $(this).parents('form'),
			name = thisForm.find("#btnfield_name").val(),
			email = thisForm.find("#btnfield_email").val(),
			phone = thisForm.find("#btnfield_phone").val();

		var data = {
			name: name,
			email: email,
			phone: phone,
		};

		paypal_form.append('<input type="hidden" name="custom" value="'+JSON.stringify(data)+'">');


		paypal_form.submit();
	});


	/**
	 * Search Form Tabs
	 *
	 */

	$('.thm-tk-search .thm-tk-search-nav ul li').on('click', 'a', function (e) {
		e.preventDefault();

		var tab = $($(this).attr('href'));

		$('.thm-tk-search .thm-tk-search-nav ul li a.active').removeClass('active');

		$(this).addClass('active');

		$('.thm-tk-tab').hide();
		tab.show();
	});

	//comming soon
	if (typeof loopcounter !== 'undefined') {
		loopcounter('counter-class');
	}
	

});
