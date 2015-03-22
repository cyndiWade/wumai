<?php

/*
*	赞的列表
*
*
*/

class ContentPraiseModel extends ApiBaseModel {

	public function getLike($id,$p,$index)
	{
		$first = $p =='' ? 0 : $p;
		$offset = $index == '' ? 10 : $index;
		$list = array();

		$like_list = $this->where(array('p.article_id'=>array('eq',$id)))
            ->table('app_content_praise as p')
            ->join('app_users as u on u.id = p.user_praise_id')
            ->join('app_city as c on c.id = u.city_id')
            ->field('u.id,u.nickname,u.head_img,c.title,p.create_time')
            ->order('p.create_time desc')->limit($first * $offset,$offset)->select();

        $list['like_num'] = $this->where(array('article_id'=>array('eq',$id)))
            ->count();

        if($like_list!='')
        {
            parent::public_file_dir($like_list, array('head_img'));

            $UserFriends = D('UserFriends');

            foreach ($like_list as $key => $value) {
                $list['like_list'][$key] = $value;
                $list['like_list'][$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);

                $user_status = $UserFriends->where(array('user_id' => $user_id, 'friend_id' => $value['id'], 'friend_statis' => 1))
                    ->find();

                if ($user_status != '') {
                    $list['like_list'][$key]['is_friend'] = 1;
                } else {
                    $list['like_list'][$key]['is_friend'] = 2;
                }
            }
        }else{
            $list['like_list'] = '';
        }


		return $list;
	}

	public function set_like($user_id,$article_id)
	{
		$where = array('article_id'=>$article_id,'user_praise_id'=>$user_id);
		$count = $this->where($where)->count();
		$log = array('user_id'=>$user_id,'attention_id'=>$article_id,'status'=>1);
		parent::listen(__CLASS__,__FUNCTION__,$log);
		if($count==0)
		{
			$where['create_time'] = time();
			$bool = $this->add($where);
			return $bool ? true : false;
		}else{
			$bool = $this->where($where)->delete();
			return $bool ? true : false;
		}
	}
}