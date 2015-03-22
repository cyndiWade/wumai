<?php
/**
 * Created by PhpStorm.
 * User: zhuchencong
 * Date: 15/2/12
 * Time: 下午12:45
 */

class LabelArticleModel extends ApiBaseModel
{

    //获取标签ID相关的信息
    public function get_tag_info($tag_id,$p,$index)
    {
        $first = $p !='' ? 0 : $p;
        $offset = $index != '' ? 10 : $index;

        $list = $this->table('app_label_article as a')->where(array('a.label_id'=>$tag_id))
            ->join('app_article as c on c.id = a.article_id')->limit($first * $offset,$offset)
            ->order('c.create_time desc')->select();

        parent::public_file_dir($list,array('article_img'));

        $new_list = array();

        $new_list['all_count'] = $this->where(array('label_id'=>$tag_id))->count();

        if($list!='')
        {
            foreach($list as $key=>$value) {

                $new_list['info'][$key]['user_info'] = parent::get_user_info($value['user_id']);

                parent::public_file_dir($new_list['info'][$key], array('head_img','background_img'));

                $new_list['info'][$key]['photo_info'] = $value;

                $new_list['info'][$key]['photo_info']['photo_time'] = date('Y-m-d H:i:s', $value['create_time']);

                $new_list['info'][$key]['photo_info']['tag_info'] = parent::get_label_info($value['id']);

                $new_list['info'][$key]['photo_info']['like_info']['like_num'] = parent::get_contentpraise_count($value['id']);

                $new_list['info'][$key]['photo_info']['like_info']['like_list'] = parent::get_contentpraise_info($value['id']);

                parent::public_file_dir($new_list['info'][$key]['photo_info']['like_info']['like_list'], array('head_img'));

                $new_list['info'][$key]['photo_info']['comment_num'] = parent::get_comment_count($value['id']);
            }

            return $new_list;

        }else{
            return '';
        }
    }
}