jQuery(function($){
	$.datepicker.regional['en'] = {
	closeText: 'Close',
	prevText: 'Previous',
	nextText: 'Next',
	monthNames: ['January','February','March','April','May','June',
	'July','August','September','October','November','Dicember'],
	monthNamesShort: ['Jan.','Feb.','Mar','Apr','May','Jun',
	'Jul.','Aug','Sep','Oct','Nov','Dic'],
	dayNames: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
	dayNamesShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
	dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
	weekHeader: 'Week',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['en']);
});