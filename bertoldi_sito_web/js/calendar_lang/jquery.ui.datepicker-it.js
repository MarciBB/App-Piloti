jQuery(function($){
	$.datepicker.regional['it'] = {
	closeText: 'Chiudi',
	prevText: 'Prec',
	nextText: 'Pross',
	monthNames: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
				'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
	monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
				'Lug','Ago','Set','Ott','Nov','Dic'],
	dayNames: ['Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato'],
	dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
	dayNamesMin: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
	weekHeader: 'Sett',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['it']);
});