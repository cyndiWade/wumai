<?php 

/**
 * API-积分模型
 */

class IntegralAllModel extends ApiBaseModel {

    //1是未领取 2是已领取
	public function getInfo($id)
	{
		$info_list = $this->where(array('status'=>0))->order('id desc')->select();
        $IntegralSameday = D('IntegralSameday');
		foreach($info_list as $key=>$value)
		{
            if($value['id']==1)
            {
                $info = $IntegralSameday->where(array('user_id'=>$id,'integral_id'=>1))->find();
                if($info!='')
                {
                    //直接写死
                    $value['end_number'] = 1;
                    $info['status']==0 ? $value['is_end'] = 1 : $value['is_end'] = 2;
                }else{
                    $value['end_number'] = 0;
                    $value['is_end'] = 3;
                }
            }else{
                $where = array('sameday'=>strtotime(date('Y-m-d')),'user_id'=>$id,'integral_id'=>$value['id']);
                $new_info = $IntegralSameday->where($where)->select();
                $is_no = 0;
                $all_count = 0;
                foreach($new_info as $val)
                {
                    if($val['status']==0)
                        $is_no++;
                    $all_count++;
                }
                $value['end_number'] = $all_count;
                $is_no!=0 ? $value['is_end'] = 1 : $value['is_end'] = 2;
            }

			$now_info[$key] = $value;
		}
		return $now_info;
	}

    //完成任务获取积分
    public function insert_user_score($user_id,$score_id)
    {
        $IntegralSameday = D('IntegralSameday');
        $where = array('sameday'=>strtotime(date('Y-m-d')),'user_id'=>$user_id,'integral_id'=>$score_id,'status'=>0);
        $int_integral = $this->where(array('id'=>$score_id))->getField('integral');
        $Users = D('Users');
        //如果是领取第一次注册的积分就跳过查询
        if($score_id!=1)
        {
            $count = $IntegralSameday->where($where)->count();
        }else{
            $count = 1;
        }
        if($score_id!=10)
        {
            $user_integral = $Users->where(array('id'=>$user_id))->getField('integral');
            $new_integral['integral'] = $count * $int_integral + $user_integral;
        }else{
            //领取基金
            $user_integral = $Users->where(array('id'=>$user_id))->getField('fund');
            $new_integral['fund'] = $count * $int_integral + $user_integral;
        }
        $user_bool = $Users->where(array('id'=>$user_id))->save($new_integral);
        if($user_bool)
        {
            $update = array('status'=>1);
            if($score_id!=1)
            {
                $IntegralSameday->where($where)->save($update);
            }else{
                $IntegralSameday->where(array('user_id'=>$user_id,'integral_id'=>1))->save($update);
            }
            return true;
        }else{
            return false;
        }
    }
}
?>