var System = function () {
	var i =1;
}

System.prototype.init = function () {
	this.validateImage = $('.validateImage');
}



System.prototype.validateImageFn = function () {
	var _fater_this = this;
	_fater_this.validateImage.click(function () {
		var _this = $(this);
		_this.attr('src',_this.data('image_url')+'&'+Math.random());
	});
}

/**
 * 同步模式AJAX提交
 */
System.prototype.ajax_post_setup = function ($url,$data,$type) {
	$type = $type || 'JSON';
    $.ajaxSetup({
		async: false//async:false 同步请求  true为异步请求
	});
	var result = false;
	//提交的地址，post传入的参数
	$.post($url,$data,function(content){
		result = content;
	},$type);
	
	return result;
}


/**
 * @desc 判断数组内是否包含字符串
 * @param str
 * @param arr
 * @returns {boolean}
 */
System.prototype.in_array = function (str, arr) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === str) {
            return true;
        }
    }
    return false;
}


/**
 * 格式化日期，成时间戳
 * @param {Object} $date_string 2013-10-10 12:13
 * return 111111111111
 */
System.prototype.fomat_date = function ($date_string) {
	if ($date_string == '') {
		return 0;
	} else {
		return Date.parse($date_string.replace(/-/ig,'/'));
	}
	
}


System.prototype.create_num_for_length = function ($number,$length) {
	
	var temp = '000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
	var resutl = $number+''+temp.substr(0,$length);
	
	return resutl;
}


/**
 * 根据微博名称，获取微博URL
 */
System.prototype.getUrlInfo = function () {
	var _fater_this = this; 
	var weibo_account_name_Obj = $('.weibo_account_name_Obj');
	
	weibo_account_name_Obj.click(function () {
		var _this = $(this);
		var account_name =  _this.data('account_name');
		var type = _this.data('type');
		
		if(type == 1)
		{
			var result = _fater_this.ajax_post_setup('/Advert/Weibo/getWeiboUrl',{'account':account_name, 'type': type});
			if (result.status == 0) {
				window.open(result.data);
			}
		} else {
			window.open('http://t.qq.com/'+ account_name);	 
		}
		
	});
}

System.prototype.goToUrl = function () {
	var _fater_this = this; 
	
	var go_to_url = $('.go_to_url');
	
	go_to_url.click(function () {
		var _this = $(this);
		var is_new_window = _this.data('is_new_window');
		var url = _this.data('url');
		if (is_new_window == 1) {
			window.open(url);	
		} else {
			window.location.href = url;
		}
	});
}

System.prototype.run = function () {
	var _fater_this = this;
	_fater_this.validateImageFn();
	
	_fater_this.getUrlInfo();
	
	_fater_this.goToUrl();
}


var System = new System();

window.onload = function () {

	System.init();
	System.run();	
}
