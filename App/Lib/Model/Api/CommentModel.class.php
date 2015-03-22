<?php

//评论
class CommentModel extends ApiBaseModel {

	public function add_comment($id,$article_id,$comment_content)
	{
        $new_add = array(
            'article_id' => $article_id,
            'user_id' => $id,
            'content' => $comment_content,
            'create_time' => time()
        );

        $log = array('user_id'=>$id,'attention_id'=>$article_id,'status'=>1);

        parent::listen(__CLASS__,__FUNCTION__,$log);

        $bool = $this->add($new_add);
        return $bool ? true : false;
	}

	public function select_info($p,$index,$article_id)
	{
		$first = $p =='' ? 0 : $p;
		$long = $index == '' ? 10 : $index;
		$new_list = array();
		$new_list['count'] = $this->where(array('article_id'=>$article_id))->count();
		$list = $this->where(array('c.article_id'=>$article_id))
            ->table('app_comment as c')->join('app_users as u on u.id = c.user_id')->limit($first * $long,$long)
            ->field('c.id,c.content,c.create_time,u.id as user_id,u.nickname,u.head_img')
            ->order('c.create_time desc')->select();
		foreach($list as $key=>$value)
		{
			$new_list['commemt_info'][$key] = $value;
            $new_list['commemt_info'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
		}

		parent::public_file_dir($new_list['commemt_info'],array('head_img'));
		
		return $new_list;
	}
}