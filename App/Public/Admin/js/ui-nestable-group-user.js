var UINestable = function () {

	/**
	 * 提交服务器
	 */
    var subServer = function () {
    	var e = $('#nestable_list_1');
    	var list = e.length ? e : $(e.target),
            output = list.data('output');
            if (window.JSON) {
            	var json = window.JSON.stringify(list.nestable('serialize'));
            	var group_id = $("input[name='group_id']").val();

            	/**
            	 * 提交server
            	 */
            	$.post('?s=/Admin/Rbac/group_user_edit/',{
            		data : json,
            		group_id : group_id
            	},function(obj){
            		if (obj.status != 0) {
            			alert(obj.msg);
            		}
            	},'json');
            	
            	
            } else {
              //  output.val('暂不支持！.');	//JSON浏览器支持需要这个演示
            }
    }

    return {
        //main function to initiate the module
        init: function () {

            // activate Nestable for list 1
        	//https://github.com/dbushell/Nestable 说明
            $('#nestable_list_1').nestable({
                group: 1,
                maxDepth :1,			//最大支持层数
            }).on('change',subServer);

            // activate Nestable for list 2
            $('#nestable_list_2').nestable({
                group: 1,
                maxDepth :1	//最大支持层数
            })

            // output initial serialised data (输出初始序列化数据)
           // updateOutput($('#nestable_list_1').data('output', $('#nestable_list_1_output')));
            //updateOutput($('#nestable_list_2').data('output', $('#nestable_list_2_output')));
//
            $('#nestable_list_menu').on('click', function (e) {
                var target = $(e.target),
                    action = target.data('action');
                if (action === 'expand-all') {
                    $('.dd').nestable('expandAll');
                }
                if (action === 'collapse-all') {
                    $('.dd').nestable('collapseAll');
                }
            });


        }

    };

}();