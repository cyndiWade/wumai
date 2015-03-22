<?php

/*
*	文章
*
*
*/

class ArticleModel extends ApiBaseModel {

	public function article_vote($id,$article_id,$vote_info)
	{
		$ArticleBehaviorLog = D('ArticleBehaviorLog');
		$count = $ArticleBehaviorLog->where(array('article_id'=>$article_id,'user_id'=>$id))->count();
		if($count==0)
		{
			//1 文明 2 不文明
			$sup = $this->where(array('id'=>$article_id))->field('support,nonsupport')->find();
			switch($vote_info)
			{
				case 1:
					$super = array('support'=>$sup['support']+1);
					$bool = $this->where(array('id'=>$article_id))->save($super);
				break;
				case 2:
					$super = array('nonsupport'=>$sup['nonsupport']+1);
					$bool = $this->where(array('id'=>$article_id))->save($super);
				break;
			}
			$log = array('user_id'=>$id,'attention_id'=>$article_id,'status'=>1);
			parent::listen(__CLASS__,__FUNCTION__,$log);
			$new_arr = array('article_id'=>$article_id,'user_id'=>$id);
			$ArticleBehaviorLog->add($new_arr);
			return $bool ? true : false;
		}else{
			return false;
		}
	}

	//照片详情
	public function article_info($article_id)
	{
		$big_arr = array();

		$big_arr['photo_info'] = $this->where(array('id'=>$article_id))->find();

        $big_arr['user_info'] = parent::get_user_info($big_arr['photo_info']['user_id']);

        $big_arr['photo_info']['time'] = date('Y-m-d H:i:s',$big_arr['photo_info']['create_time']);

        $big_arr['photo_info']['tag_info'] = parent::get_label_info($article_id);

        $big_arr['photo_info']['like_list'] = parent::get_contentpraise_info($article_id);

		$big_arr['photo_info']['like_num'] = parent::get_contentpraise_count($article_id);

		parent::public_file_dir($big_arr,array('head_img','article_img','background_img'));

		parent::public_file_dir($big_arr['photo_info']['like_list'],array('head_img'));

		return $big_arr;
	}

	//首页
	public function article_index($city,$type,$index,$page_count,$lng,$lat)
	{
		if($city!='')
            $where['city_id'] = $l_where['city_id'] = $city;
		$p = $index =='' ? 0 : $index;
		$page_count = $page_count == '' ? 10 : $page_count;
		$list = array();
		
		switch($type)
		{
			//最新
			case 1:
                $where['status'] = 0;
				$list_info = $this->where($where)->limit($p * $page_count,$page_count)->order('create_time desc')->select();
				$list['all_count'] = $this->where($where)->count();
			break;
			//最近
			case 2:
                $l_where['status'] = 0;
				//计算经纬度
				$square_arr = _SquarePoint($lng,$lat,100);
				//纬度
				$l_where['latitude'] = array(
				   // array('gt',0),
				    array('gt',$square_arr['right-bottom']['lat']),
				    array('lt',$square_arr['left-top']['lat']),
				    'AND'
				);
				    
				//经度
				$l_where['longitude'] = array(
				    array('gt',$square_arr['left-top']['lng']),
				    array('lt',$square_arr['right-bottom']['lng']),
				    'AND'
				);

				$list_info = $this->where($l_where)->order('longitude asc')->order('latitude asc')
                    ->order('create_time desc')->limit(200)->select();

				$list['all_count'] = count($list_info);

			break;
		}

        if (!empty($list_info)) {
            //计算距离
            foreach ($list_info AS $key=>$val) {
                $list_info[$key]['distance'] = round(GetDistance($lat,$lng,$val['latitude'], $val['longitude']),2) * 1000;
            }
        }

        //固定拉200条数据排序之后再取
        if($type==2)
        {
            $list_info = list_sort_by($list_info,'distance');
            $new_list_info = array();
            for($i= $p * $page_count;$i< $p * $page_count + $page_count;$i++)
            {
                if($i < $list['all_count'])
                {
                    $new_list_info[] = $list_info[$i];
                }
            }
            //更话指针地址
            $list_info = &$new_list_info;
        }

		if($list_info!='')
		{
			foreach($list_info as $key=>$value)
			{

                $list['info'][$key]['user_info'] = parent::get_user_info($value['user_id']);

				parent::public_file_dir($list['info'][$key],array('head_img','background_img'));

				$list['info'][$key]['photo_info'] = $value;

				parent::public_file_dir($list['info'][$key],array('article_img'));

				$list['info'][$key]['photo_info']['photo_time'] = date('Y-m-d H:i:s',$value['create_time']);

                $list['info'][$key]['photo_info']['tag_info'] = parent::get_label_info($value['id']);

                $list['info'][$key]['photo_info']['like_info']['like_num'] = parent::get_contentpraise_count($value['id']);

                $list['info'][$key]['photo_info']['like_info']['like_list'] = parent::get_contentpraise_info($value['id']);

				parent::public_file_dir($list['info'][$key]['photo_info']['like_info']['like_list'],array('head_img'));

                $list['info'][$key]['photo_info']['comment_num'] = parent::get_comment_count($value['id']);
			}

			return $list;

		}else{
            return '';
        }
	}

	//推荐文章
	public function getRemmend()
	{
		$list = $this->where(array('recommend'=>1))->select();

		$list_arr = array();

		foreach($list as $key=>$value)
		{
            $list_arr[$key]['user_info'] = parent::get_user_info($value['user_id']);

			$list_arr[$key]['content_info'] = $value;

            $list_arr[$key]['content_info']['create_time'] = date('Y-m-d H:i:s',$value['create_time']);

			parent::public_file_dir($list_arr[$key],array('head_img','article_img'));

            $list_arr[$key]['like_num'] = parent::get_contentpraise_count($value['id']);
		}

		return $list_arr;
	}


	//获得个人中心数据
	public function getOwnInfo($p,$index,$user_id,$type,$other_id=NULL)
	{
		$first = $p == '' ? 0 : $p;
		$offset = $index == '' ? 10 : $index;
		$arr_list = array();

        $arr_list['user_info'] = parent::get_user_info($user_id);

		parent::public_file_dir($arr_list,array('head_img','background_img'));

		$arr_list['user_info']['save_num'] = parent::get_collection_count($user_id);

		$arr_list['user_info']['friend_num_yes'] = parent::get_is_friends($user_id,1);

		$arr_list['user_info']['artcile_num'] = $this->where(array('user_id'=>$user_id))->count();

		$arr_list['user_info']['integral_num'] = parent::check_integral_num($user_id);

		if($type==false)
		{
			$arr_list['user_info']['friend_num_no'] = parent::get_is_friends($user_id,0);
		}

		if($type==true)
		{
            if($other_id==NULL)
            {
                if(parent::is_no_friends($user_id,$other_id)==0)
                {
                    $arr_list['user_info']['is_friend'] = 2;
                }else{
                    $arr_list['user_info']['is_friend'] = 1;
                }
            }else{
                $arr_list['user_info']['is_friend'] = 2;
            }
		}

		$list = $this->where(array('user_id'=>$user_id))->limit($first * $offset,$offset)
		->field('id,content,article_img,create_time,longitude,latitude')->order('create_time desc')->select();


        if($list!='')
        {
            parent::public_file_dir($list,array('article_img'));
            
            foreach($list as $key=>$value)
            {
                $arr_list['photo_info'][$key] = $value;
                $arr_list['photo_info'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);

                $arr_list['photo_info'][$key]['tag_info'] = parent::get_label_info($value['id']);

                $arr_list['photo_info'][$key]['like_info']['like_list'] = parent::get_contentpraise_info($value['id']);

                parent::public_file_dir($arr_list['photo_info'][$key]['like_info']['like_list'],array('head_img'));

                $arr_list['photo_info'][$key]['like_info']['like_num'] = parent::get_contentpraise_count($value['id']);

                $arr_list['photo_info'][$key]['comment_num'] = parent::get_comment_count($value['id']);
            }
        }else{
            $arr_list['photo_info'] = array();
        }
		$arr_list['article_num'] = $this->where(array('user_id'=>$user_id))->count();

		return $arr_list;
	}

	//上传文章
	public function upload_article($arr,$tags)
	{
		$new_insert_id = $this->add($arr);
		if($new_insert_id!='')
		{
			$log = array('user_id'=>$arr['user_id'],'attention_id'=>$new_insert_id,'status'=>3);
			parent::listen(__CLASS__,__FUNCTION__,$log);
			if(is_array($tags))
			{
                $LabelArticle = D('LabelArticle');
				foreach($tags as $value)
				{
					$tag_arr = array('article_id'=>$new_insert_id,'label_id'=>$value);
                    $LabelArticle->add($tag_arr);
				}
			}
			return true;
		}else{
			return false;
		}
	}


    //获取文章到详情
    public function get_advert_info($advert_id)
    {
        $info = $this->where(array('id'=>$advert_id))->find();
        $info['person_number'] = $info['support'] + $info['nonsupport'];
        $info['support'] = round($info['support'] / $info['person_number'] * 100);
        $info['nonsupport'] = round($info['nonsupport'] / $info['person_number'] * 100);
        return $info;
    }
}