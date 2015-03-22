/**
 * cookie操作类
 * @returns {Cookie}
 */
function Cookie() {
	//this.name = name;
	//this.val = val;
}
Cookie.prototype.setCookie = function (name,value) {//添加方法
	var Days = 1; 				//此 cookie 将被保存 30 天
	var exp = new Date();    //new Date("December 31, 9998");
	exp.setTime(exp.getTime() + Days*24*60*60*1000);
	document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString()+ ";path=/;";
	+ ";path=/;"	//设置cookie的保存路径
	+ "domain = cookieDomain";		//cookie的域名 
}
//取cookies函数qwe
Cookie.prototype.getCookie = function (name)  {
	var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
	if(arr != null) return unescape(arr[2]); return null;
}
//取cookies函数
Cookie.prototype.delCookie = function(name) {//删除cookie
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval=this.getCookie(name);
	if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}


/**
 * 获取数据类型
 */
function getTypeof(obj) {
		var type =null;
		if (obj instanceof Array) {
			type =  'Array';
		} else if (obj instanceof Function) {
			type = 	'Function';
		} else if (obj instanceof Object) {
			type = 'Object';
		} else if (obj instanceof RegExp) {
			type = 'RegExp';
		} else {
			switch (typeof obj) {
				case 'string' :
					type = 'string';
					break;
				case 'number':
					type = 'number'	;
					break;
			}
		} 
		return type;
	}
	
/**
 * 判断值是否在顺组中
 * @param value
 * @param arr
 * @returns {Boolean}
 */
function in_array(value,arr) {
	for (var i in arr) {
		if (value == arr[i]) {
			return true;
		}
	}
	return false;
}	


/* 日期控件 */
var wade_jquery_date = function () {
		
	var options = {
			//attr 属性 ，更多格式参加书本
		//	altField:'#otherField',			//同步元素日期到其他元素上
			dateFormat:'yy-mm-dd',		//日期格式设置
		//	minDate: new Date(),		//最小选择日期为今天
			showButtonPanel:true,		//开启今天标示
			changeYear:true,				//显示年份
			changeMonth:true,				//显示月份
			showMonthAfterYear:true,	//互换位置
			
			
			//fn 执行函数
			onSelect : function () {			//选择日期执行函数
			},
			onClose : function () {			//关闭窗口执行函数
				
			}
			
	};	
	
	return  {
		init : function () {
			$('.wade_date').datepicker(options);
		}
	};

};

/*得到2个日期间的差值*/
function daysBetween(DateOne,DateTwo)
{    
    var OneMonth = DateOne.substring(5,DateOne.lastIndexOf ('-'));   
    var OneDay = DateOne.substring(DateOne.length,DateOne.lastIndexOf ('-')+1);   
    var OneYear = DateOne.substring(0,DateOne.indexOf ('-'));   
   
    var TwoMonth = DateTwo.substring(5,DateTwo.lastIndexOf ('-'));   
    var TwoDay = DateTwo.substring(DateTwo.length,DateTwo.lastIndexOf ('-')+1);   
    var TwoYear = DateTwo.substring(0,DateTwo.indexOf ('-'));   
   
    var cha=((Date.parse(TwoMonth+'/'+TwoDay+'/'+TwoYear) - Date.parse(OneMonth+'/'+OneDay+'/'+OneYear))/86400000);    
    return cha;
}	

/**
 * 格式化日期，成时间戳
 * @param {Object} $date_string 2013-10-10 12:13
 * return 111111111111
 */
function fomat_date ($date_string) {
	if ($date_string == undefined || $date_string == '') return false;
	return Date.parse($date_string.replace(/-/ig,'/'));
}


/**
 * 日期控件
 */
var  wade_bootstrap_date = function (object) {
	//object = object || '.wade_bootstrap_date';	//默认都是无触发函数的。

	return {
		/* 有触发函数的 */
		bootstrap_date_fn : function (fn) {	
			var options = {
			    format: 'yyyy-mm-dd hh:ii',
				language:  'zh-CN',
		        weekStart: 1,
		        todayBtn:  1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0,
		        showMeridian: 1
			};
			$(object).datetimepicker(options).on('changeDate', function(ev){
			    fn();
			});
		},
		
		/* 无触发函数的。 */
		bootstrap_date : function ()	{
			var options = {
			    format: 'yyyy-mm-dd hh:ii',
				language:  'zh-CN',
		        weekStart: 1,
		        todayBtn:  1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0,
		        showMeridian: 1
			};
			$(object).datetimepicker(options);
		}
		
		
		
	};
	
};


/**
 * 同步模式AJAX提交
 */
var ajax_post_setup = function ($url,$data) {
	$.ajaxSetup({
		async: false,//async:false 同步请求  true为异步请求
	});
	var result = false;
	//提交的地址，post传入的参数
	$.post($url,$data,function(content){
		result = content;
	},'json');
	
	return result;
}


/**
 * 百度地图API
 * 需要引入：<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=D0e39c6e68bd900c80a2a50882556bae"></script>
 */


//获取URL后的参数
function getQueryString(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null) return unescape(r[2]); return null;
}

