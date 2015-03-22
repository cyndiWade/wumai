<?php
/**
 * 极光推送
 * @author jason
 *文档地址:https://github.com/jpush/jpush-api-php-client/blob/master/doc/api.md
 *
 *
 * 流程
指定推送的平台(platform)
指定推送的用户(audience)
构建推送的notification或者message
指定额外的配置options
调用推送
 */

require_once 'App/vendor/autoload.php';

use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

class JgPush { 
		
    private $app_key = 'b630b69041d1ec8a7bdef983';
    private $master_secret = '25610a966468d2f2b1ed926f';
    
    //发送给所有的用户
    public function seedToAll ($content) {
        $result_data = array();
        $app_key = $this->app_key;
        $master_secret = $this->master_secret;
         
        $client = new JPushClient($app_key, $master_secret);
         
        try {
            $result = $client->push()
            ->setPlatform(M\all)
            ->setAudience(M\all)
            ->setNotification(M\notification($content))
            ->send();
            $result_data[] = 'Push Success.' . $br;
            $result_data[] = 'sendno : ' . $result->sendno . $br;
            $result_data[] = 'msg_id : ' .$result->msg_id . $br;
            $result_data[] = 'Response JSON : ' . $result->json . $br;
        } catch (APIRequestException $e) {
            $result_data[] = 'Push Fail.' . $br;
            $result_data[] = 'Http Code : ' . $e->httpCode . $br;
            $result_data[] = 'code : ' . $e->code . $br;
            $result_data[] = 'message : ' . $e->message . $br;
            $result_data[] = 'Response JSON : ' . $e->json . $br;
            $result_data[] = 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
            $result_data[] = 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
            $result_data[] = 'rateLimitReset : ' . $e->rateLimitReset . $br;
        } catch (APIConnectionException $e) {
            $result_data[] = 'Push Fail.' . $br;
            $result_data[] = 'message' . $e->getMessage() . $br;
        }
        
        return $result_data;
    }
    
    
    /**
     * 推送给单个用户
     * @param unknown $user
     * @param unknown $content
     */
    public function seedToOne ($user,$content) {
        //$user = '041b3e4ac6e';
        if (!is_array($user)) {
            $user_ids = array($user);
        } else {
            $user_ids = $user;
        }
        
        $app_key = $this->app_key;
        $master_secret = $this->master_secret;
        
        
        $client = new JPushClient($app_key, $master_secret);
        
        
        try {
            $result = $client->push()
            
            //1.指定推送的平台(platform)
            ->setPlatform(M\all)        
            
            //2.指定推送的用户(audience)
            ->setAudience(              
                M\audience(
                    //M\tag(array('tag1','tag2')),
                    //M\alias(array('alias1', 'alias2'),
                    //M\alias($user_ids),
                    M\registration_id($user_ids)    //
                 )       
            )
            
            //3.构建推送的notification或者message
            ->setNotification(M\notification($content)) 
            
            //指定额外的配置options
            //->setOptions(M\options(123456, null, null, false, 60)) 
                        
            //调用推送
            ->send();
            
            $result_data[] = 'Push Success.' . $br;
            $result_data[] = 'sendno : ' . $result->sendno . $br;
            $result_data[] = 'msg_id : ' .$result->msg_id . $br;
            $result_data[] = 'Response JSON : ' . $result->json . $br;
        } catch (APIRequestException $e) {
            $result_data[] = 'Push Fail.' . $br;
            $result_data[] = 'Http Code : ' . $e->httpCode . $br;
            $result_data[] = 'code : ' . $e->code . $br;
            $result_data[] = 'message : ' . $e->message . $br;
            $result_data[] = 'Response JSON : ' . $e->json . $br;
            $result_data[] = 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
            $result_data[] = 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
            $result_data[] = 'rateLimitReset : ' . $e->rateLimitReset . $br;
        } catch (APIConnectionException $e) {
            $result_data[] = 'Push Fail.' . $br;
            $result_data[] = 'message' . $e->getMessage() . $br;
        }
        
        return $result_data;
        
        //根据设备注册ID，请求获得详细数据，接口为：Device API
        //$client->getDeviceTagAlias('041b3e4ac6e');
    }
	
}




?>