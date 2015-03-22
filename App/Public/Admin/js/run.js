/**
 * 验证浏览器版本
 */
(function ($) {
	var userAgent = window.navigator.userAgent.toLowerCase();
	 
	$.browser.msie10 = $.browser.msie && /msie 10\.0/i.test(userAgent);
	$.browser.msie9 = $.browser.msie && /msie 9\.0/i.test(userAgent); 
	$.browser.msie8 = $.browser.msie && /msie 8\.0/i.test(userAgent);
	$.browser.msie7 = $.browser.msie && /msie 7\.0/i.test(userAgent);
	$.browser.msie6 = !$.browser.msie8 && !$.browser.msie7 && $.browser.msie && /msie 6\.0/i.test(userAgent);

	$.browser.mozilla = /firefox/.test(navigator.userAgent.toLowerCase());	//火狐
	$.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());		//google
	$.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());		//苹果浏览器
	//$.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());		//IE
	var check_browser = false;
	var arr_browser = [
			//$.browser.msie10,
			//$.browser.msie9,
			$.browser.mozilla,
			$.browser.webkit,
			$.browser.opera
	]
	for (var i in arr_browser) {
		if (arr_browser[i] == true) {
			check_browser = true;
			break;
		}
	}
	
	if (check_browser == false) {
		alert('亲爱的用户，您的浏览器版本太低，为了更好用户体验，请下载最新的浏览器');
		window.location.href='http://www.google.com/intl/zh-CN/chrome/'	;
	}
	
})(jQuery);


//动弹导航样式
	(function ($) {
		//82
		var menu_li = $('ul.wade_menu').children('li').slice(1);		//一级导航栏
		var menu_children_li = menu_li.children().find('li');			//二级导航
		var cookie = new Cookie();												//Cookie操作类
	
		var menu_index = cookie.getCookie('menu_index') || 1;				//点击一级导航后获取的索引		
		var two_menu_index = cookie.getCookie('two_menu_index') || 0;	//点击二级导航后获取的索引

		var one_menu_list = menu_li.eq(menu_index -1 );	//当前选中一级导航
		one_menu_list.addClass('active');								//为点击的导航加上样式
		var two_menu_list = one_menu_list.children().find('li');			//当前选中二级导航
		two_menu_list.eq(two_menu_index).addClass('active');			//为点击样式加上首选
		
		//一级导航没有子导航时加样式
		menu_li.click(function () {
			var _this = $(this);
			if (_this.children().is('ul') == false) {
				cookie.setCookie('menu_index',_this.index());				//保存一级导航名索引到Cookie
			}
		});
		
		//二级导航选中后加样式
		menu_children_li.click(function () {
			var _this = $(this);
			cookie.setCookie('two_menu_index',_this.index());			
			cookie.setCookie('menu_index',_this.parent().parent().index());		//保存导航名到Cookie
		});
		

	})(jQuery);
	
	
	(function ($) {

		$('.check').click(function () {
			return confirm('确定操作？') ? true : false;
		});

	})(jQuery);
	

	//日历
	(function () {
		wade_jquery_date().init();
		
		wade_bootstrap_date('.bootstrap_date').bootstrap_date();
	})();
	
	
	//返回方法处理
	(function ($) {
			
		//点击返回
		$('.btn_bak').click(function () {
		//	return false;
			var _this = $(this);
			var go_num = _this.data('go');		//返回次数的条数
			var url = _this.data('url');				//需要跳转的URL
			var target = _this.data('target');			//打开新页面
			var record_prev = _this.data('record_prev');			//记录上一页地址
			var return_prev = _this.data('return_prev');		//返回上一页标识
			var cookie = new Cookie();

			//记录上一页地址
			if (record_prev != undefined) cookie.setCookie('prev',window.location);

			//返回上一页地址
			if (return_prev != undefined) {
				window.location.href = cookie.getCookie('prev');
			} else if (url != undefined) {	//跳转到指定的页面	
				if (target != undefined) {	//重新打开窗口
					 window.open(url, '_blank', '');				
				} else {		//跳转到指定页面
					window.location.href = url;
				}
			} else if (go_num !=undefined) {		//返回到指定的页面
				history.go(go_num);			//返回指定层级页面
			}
	
		});
		
	})(jQuery);
	
	
	(function ($) {
		//日历控件
			var wade_datetimepicker = function () {
				var options = {
				    format: 'yyyy-mm-dd',
					language:  'zh-CN',
			        weekStart: 1,
			        todayBtn:  1,
					autoclose: 1,
					todayHighlight: 1,
					startView: 2,
					forceParse: 0,
			        showMeridian:1,
					 todayBtn:1,
					forceParse: 0,
					minView: 2,
				};
				$('.wade_datetimepicker').datetimepicker(options);
			}();
	})(jQuery);
	
	
	
	(function ($) {
		
		var operation_order = $('.operation_order');
		
		var html = '<div class="operation_order_div" title="编辑" style="display:none;">';
		html += '<input type="hidden" value="" />';
		html += '<textarea class="dialogTextarea" name=""></textarea>';
		html += '</div>'
			
		//插入
		$('body').append(html);
     
		//重新获取
		var operation_order_div = $('.operation_order_div');
		
		
		operation_order.click(function () {
			var _this = $(this);
			
			var dialogTextarea = operation_order_div.find('.dialogTextarea');
			
			var post_data = {};
			post_data.act = _this.data('act');
			post_data.type = _this.data('type');
			post_data.order_id = _this.data('order_id');
			post_data.account_id = _this.data('account_id');
			
			dialogTextarea.val('');
			
			$('.operation_order_div').dialog({
				//打开特效、关闭特效动画	
				autoOpen: true,
				show: {
			        effect: "blind",	//slide
			        duration: 1000
			      },
			     hide: {
			         effect: "slide",//slide
			     	 duration: 1000
			     },		
				modal : true,				//对话框开启期间，阻止交互
				position:'center',			//设置打开的位置参数有：left 、 right、bottom  
				//title : '123',					//设置对话框的标题名，也可以在标签中自定义

				//width: '100',			//宽度  默认300
				//height: '100',		//高度
				
				//创建自定义按钮函数
				buttons:{
					'提交':function (event) {
						var content =  dialogTextarea.val();
						if (content == '') {
							alert('请输入内容');
							return false;
						}
						
						post_data.content = content;
						
						var result = ajax_post_setup('/Admin/Order/setPostLogStatus',post_data);
						
						window.location.reload();
						//$(this).dialog('close');
					}
//					'':function (event) {		
//						$(this).dialog('close');
//					},
				},
			});
		});
	
		
	})(jQuery);
	
	
	
	
	
	