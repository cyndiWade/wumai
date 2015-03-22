var FormSamples = function () {


    return {
        //main function to initiate the module
        init: function () {

            // use select2 dropdown instead of chosen as select2 works fine with bootstrap on responsive layouts.
            $('.select2_category').select2({
	            placeholder: "Select an option",
	            allowClear: true
	        });

            $('.select2_sample1').select2({
                placeholder: "Select a State",
                allowClear: true
            });

            $(".select2_sample2").select2({
                placeholder: "Type to select an option",
                allowClear: true,
                minimumInputLength: 1,
                query: function (query) {
                    var data = {
                        results: []
                    }, i, j, s;
                    for (i = 1; i < 5; i++) {
                        s = "";
                        for (j = 0; j < i; j++) {
                            s = s + query.term;
                        }
                        data.results.push({
                            id: query.term + i,
                            text: s
                        });
                    }
                    query.callback(data);
                }
            });

            $(".select2_sample3").select2({
                tags: ["red", "green", "blue", "yellow", "pink"]
            });
            
            
            $.ajaxSetup({
    			async: false,//async:false 同步请求  true为异步请求
            });
            /* AJAX请求 */
            var ajax_post = function ($url,$parameter) {
            	var result = 0;
            	//提交的地址，post传入的参数
            	$.post($url,$parameter,function(obj){
            		result = obj.status;		
            	},'json');
            	return result;
            }
            
            
            
            
            /* AJAX请求 */
            var tab = $('#wade_tab li');
            var base_url = '?s=/Admin/Staff/';
            var arr_content = $('')
            tab.click(function () {
            	var stauts = false;
            	var _this = $(this);
            	var url_action = base_url+_this.data('action');
            	/* 权限验证 */
            	var result = ajax_post(url_action,{});
            	if (result == 3001) {
            		stauts = true;
            	} else {
            		alert('没有权限');
            	}
            	return stauts;
            	
            });

        }

    };

}();