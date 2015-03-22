//2014.7.15
(function($) {	
	$.fn.extend({
		popOn: function(option) {	
			var _this=$(this);
			var ie6,_box,_mask;
			var _y,_flow,_fade;
			var _middle=true;
			var _close;
			if(option){
				if(option.y){
					_middle=false;
					_y=option.y;
					_y=_y<=0?0:_y;
				}//end if
				_flow=option.flow!=null?option.flow:true;//默认赋值
				_fade=option.fade!=null?option.fade:250;
			}//end if
			else{
				_flow=true;//默认赋值
				_fade=250;
			}//end else
			init();
			function init(){
				ie6=document.all && navigator.userAgent.indexOf("MSIE 6.0")>0;
				if(!_this.parent().hasClass("popBox"))_this.wrap("<div class='popBox'></div>").before("<div class='popMask'></div>");
				_box=_this.parent();
				_mask=_this.siblings();
				if(_flow && !ie6){_box.css("position","fixed");}else{_box.css("position","absolute");}
				if(_fade<=1){_this.show();}else{_this.fadeIn(_fade);}
				maskReset();
				_this.on('close',closeFunc);
				_close=_this.find(".close");
				if(_close.length>0) _close.one('click',closeFunc);//end on
				$(window).on('resize',maskReset).on('scroll',scrollFunc);//end on
			}//end func	
			function closeFunc(event){
				_mask.remove();
				if(_close.length>0) _close.off();
				$(window).off('resize',maskReset).off('scroll',scrollFunc);
				_this.off().unwrap().hide();
			}//end func
			function maskReset(event){
				//alert("window resize");//测试on用
				_mask.width($(window).width());
				if(_flow && !ie6){_mask.height($(window).height());}else{_mask.height($(document).height()>=$(window).height()?$(document).height():$(window).height());}
				scrollFunc();
			}//end func
			function scrollFunc(event){
				if(_middle){
					_y=($(window).height()-_this.outerHeight())/2;
					_y=_y<=0?0:_y;
				}//end if
				_this.css("top",_y);
				_this.css("left",Math.floor($(window).width()/2-_this.outerWidth()/2));
				if(_flow && ie6)_this.css("top",$(document).scrollTop()+_y);
			}//end func
		},//end fn	
		popOpen: function(option) {	
			var _this=$(this);
			var _box,_mask,_parent;
			var _y,_fade;
			if(option){
				_y=option.y!=null?option.y:_this.parent().outerHeight()/2-_this.outerHeight()/2; //默认居中
				_fade=option.fade!=null?option.fade:250;
			}//end if
			else{
				_y=_this.parent().outerHeight()/2-_this.outerHeight()/2; //默认居中
				_fade=250;
			}//end else
			init();
			function init(){
				if(!_this.parent().hasClass("popBox"))_this.wrap("<div class='popBox'></div>").before("<div class='popMask'></div>");
				_parent=_this.parent().parent();
				_box=_this.parent();
				_mask=_this.siblings().show().width(_parent.width()).height(_parent.height());
				if(_fade<=1){_this.show();}else{_this.fadeIn(_fade);}
				_this.css({"left":Math.floor(_parent.width()/2-_this.outerWidth()/2),"top":_y}).one('close',closeFunc);
				if(_this.children(".close").length>0) _this.children(".close").one('click',closeFunc);//end on
			}//end func	
			function closeFunc(event){
				_mask.remove();
				_this.unwrap().hide();
			}//end func
		},//end fn
		popOff: function() {
			$(this).trigger('close');
		}//end fn
	});//end extend	
})(jQuery);//闭包