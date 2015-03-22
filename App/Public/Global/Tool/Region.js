var Region = function ($url) {
	this.post_url = $url
}


Region.prototype.init = function () {
	this.region_left = $('.region_left');
	this.region_right = $('.region_right');
}

Region.prototype.get_sf_data_fn = function () {
	var _father_this = this;
	_father_this.init();
	_father_this.region_left.append('<option value="">请选择</option>');
	//AJAX获取数据
	var post_data = {'parent_id':1};
	var result = System.ajax_post_setup(_father_this.post_url,post_data,'JSON');
	if (result.status == 0) {
		//console.log(result.data);
		for (var key in result.data) {
			_father_this.region_left.append('<option value="'+result.data[key].region_id+'">'+result.data[key].region_name+'</option>');
		}
	}
}

Region.prototype.select_city_fn = function () {
	var _father_this = this;
	_father_this.init();
	var no_show_all = _father_this.region_right.data('no_show_all');
	
	_father_this.region_right.append('<option value="">请选择</option>');
	_father_this.region_left.change(function () {
		var _this = $(this);
		_father_this.region_right.empty();
		if (_this.val() == '') {
			_father_this.region_right.append('<option value="">请选择</option>');
			return false;
		};
		
		post_data = {'parent_id':_this.val()};
		var result = System.ajax_post_setup(_father_this.post_url,post_data,'JSON');
		if (result.status == 0) {
			//console.log(result.data);
			
			if (no_show_all != 1) {
				var all_region_ids = [];
				for (var key in result.data) {
					all_region_ids.push(result.data[key].region_id);
				}
				_father_this.region_right.append('<option value="'+all_region_ids+'">所有</option>');
			}
			
			for (var key in result.data) {
				_father_this.region_right.append('<option value="'+result.data[key].region_id+'">'+result.data[key].region_name+'</option>');
			}
			
		
		}
	});
}

Region.prototype.run = function () {
	var _father_this = this;
	_father_this.get_sf_data_fn();
	_father_this.select_city_fn();
}


//运行
//var Region = new Region('?s=/Advert/Tool/get_Region_Data/');
//window.onload = function () {
//	Region.init();
//	Region.run();	
//}
