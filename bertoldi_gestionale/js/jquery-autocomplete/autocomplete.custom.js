/*
 * Copyright (c) 2009 Massimiliano Balestrieri
 * @requires jQuery v1.3.2
 * @requires jquery.metadata.js
 * @requires jquery.autocomplete.js OR jquery.autocomplete.mod.js
 * 
 * Copyright (c) 2009 Massimiliano Balestrieri
 * Examples and docs at: http://maxb.net/blog/
 * Licensed GPL licenses:
 * http://www.gnu.org/licenses/gpl.html
 */ 

;(function($){
	$(document).ready(function(){
		
		////////////////////////////////////////////////////
				
		$(".suggest").each(function(){
			var _alt = this.alt;
			var _options = $(this).metadata();
			if(_alt.length > 0){
				$(this).autocomplete(_alt, _options);
			}
		});
	
		////////////////////////////////////////////////////
			
		$(".suggest_keys").each(function(){
			
			var _id = this.id;
			var _name = this.name;
			
			//il name è obbligatorio
			if(!_name)
				return;
				
			var _alt = this.alt;
			
			var _hidden_id = _id ? _id + "_hidden" : false;
			var _hidden_name = '';
			//name
			//console.log(_name);
			if(_name && _name.indexOf("[") !== -1  && _name.indexOf("]") !== -1){
				var _cre =  /\[.*\]/;
				var _m = _cre.exec(_name);
				var _t = _m.slice(0).toString().replace("[","").replace("]","");
				_hidden_name = _name.replace(_t, ""+_t +"_hidden");
			}else if(_name){
				_hidden_name = _name + "_hidden";
			}
			//console.log(_hidden_name);
			//DEBUG : var _field = $('<input type="text" value="" style="background:#000;color:#fff" />');
			var _field = $('<input type="hidden" value="" />');
			
			if(_hidden_id)
				_field.attr("id", _hidden_id);
				
			if(_hidden_name)
				_field.attr("name", _hidden_name);
				
			$(this).after(_field);
					
			var _options = $(this).metadata();
			_options.formatItem = function(row){
				return row[1];
				//row[0] + "  : <strong> " + row[1] + "</strong>";
			};
			_options.formatResult = function(row){
				//_field.val(row[0]);	
				return row[1].replace(/(<.+?>)/gi, '');
			};
			
			
			//console.log(_options);
			if(_alt.length > 0){
				$(this).autocomplete(_alt, _options);
			}
		}).result(function(event, item) {
			var _hidden_id = this.id + "_hidden";
			$("#" + _hidden_id).val(item[0]);
		});	
		
		////////////////////////////////////////////////////
		
		$(".suggest_table").each(function(){
		
			var _alt = this.alt;
			
			var _options = $(this).metadata();
			var _autocomplete_options = _options.autocomplete || {};
			_autocomplete_options.formatItem = function(row, pos){
				return _table_format(_options, row, pos);
			};
			_autocomplete_options.formatResult = function(row){
				return _table_result(_options, row);
			};
			
			if(_alt.length > 0){
				$(this).autocomplete(_alt, _autocomplete_options);
			}
		}).result(function(event, item) {
			$(this).trigger("autocomplete.table.data", [item]);
			var _hidden_id = this.id + "_hidden";
			$("#" + _hidden_id).val(item[0]);
		});
		
		function _table_format(options, row, pos){
			if(!options.separator) options.separator = "#";
			var _arr = row[1].split(options.separator);
			var _str = "";
			for(var _x = 0;_x < _arr.length;_x++){
				_str += '<div class="fld_'+(_x + 1)+'">'+_arr[_x]+'</div>';
			}
			_str = "<div class=\"tbl\">" + _str + "</div>";
			if(pos == 1){
				jQuery(".ac_results > ul").css("position","relative");
				var _int = '';
				for(var _x = 0;_x < options.th.length;_x++){
					_int += '<div id="int_'+(_x + 1)+'">'+options.th[_x]+'</div>';
				} 
				jQuery('<li class="ac_intestazione"><div class="tbl">'+_int+'</div></li>')
				.appendTo(".ac_results > ul");
			}else if(pos == 2){
				jQuery(".ac_results > ul > li").not(".ac_intestazione").css("margin-top","20px");
			}
			return _str;
		}
		function _table_result(options, row){
			return row[1].split("#").join(",");
		}
	});
    })(jQuery);