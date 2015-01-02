<?php
/**
 * 人脸识别模块处理程序
 *
 * @author topone4tvs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class FacechkModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$picurl = $this->message['picurl'];
		WeUtility::logging('tips', $picurl);
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
		$facepp = new FaceReco();
		$repmsg = '识别结果：';

        if( $faceres = $facepp->getFaceDetect($picurl) ){
        	WeUtility::logging('tips', '找到图片');
            if(isset($faceres)){
            	WeUtility::logging('tips', '找到脸');
                foreach ($faceres as $key => $value) {
                    $repmsg .= $value['race'].' '.$value['age'].'岁左右 性别:'.$value['gender'];
                    if ( '' != $value['glass'] ) {
                        $repmsg .= ' 佩戴'.$value['glass'];
                    }
                    $repmsg .= ' 心情'.$value['smile']."\n";
                }
            }else{
            	WeUtility::logging('tips', '没有脸');
                $repmsg = '抱歉，小人眼拙，没看清您的尊荣……';
            }
        }else{
        	WeUtility::logging('tips', '没有图片');
            $repmsg = '抱歉，小人眼拙，没看清您的尊荣……';
        }
		return $this->respText($repmsg);
	}
}

/**
 * 人脸识别模块
 * Enter description here ...
 * @author topone4tvs
 *
 */
class FaceReco{
	CONST FACEPLUS_URL 		= 'http://apicn.faceplusplus.com';
	CONST API_VERSION 		= '/v2';
	CONST API_FUNC_DETECT 	= '/detection/detect?';
	CONST API_KEY 			= '5b0e8d58a7b33a5b1003d6cef7b0b3aa';
	CONST API_SECRET 		= 'kasSOJjoGS8efwWYHPCwz_FXpTeKC1ct';
	
	/**
	 * 识别人脸信息：性别(gender), 年龄(age), 种族(race), 微笑程度(smiling), 眼镜(glass)和姿势(pose)
	 * @param 	string $faceImg 图片地址
	 * @return	
	 */
	public function getFaceDetect($faceImg){
		//查询内容
		$faces   = array();
		$attr	= 'glass,pose,gender,age,race,smiling';
		$url 	= self::FACEPLUS_URL.self::API_VERSION.self::API_FUNC_DETECT.
				  'api_key='.self::API_KEY.'&api_secret='.self::API_SECRET.'&url='.$faceImg.'&attribute='.$attr;
		WeUtility::logging('tips', 'req-url:'.$url);
		//获取信息
		if ( $retVal = file_get_contents($url) ){
			$retVal = json_decode($retVal,true);
			//人脸列表
			$faceres 	= $retVal['face'];
			foreach ($faceres as $key => $value) {
				//degug
				$faces[$key]['age'] 		= $value['attribute']['age']['value'];
				if ( 0 >= $value['attribute']['smiling']['value'] ) {
					$faces[$key]['smile'] 	= '难过';
				}else if ( (0 < $value['attribute']['smiling']['value']) && (20 >= $value['attribute']['smiling']['value']) ){
					$faces[$key]['smile'] 	= '一般';
				}else if ( (20 < $value['attribute']['smiling']['value']) && (40 >= $value['attribute']['smiling']['value']) ){
					$faces[$key]['smile'] 	= '不错';
				}else if ( (40 < $value['attribute']['smiling']['value']) && (60 >= $value['attribute']['smiling']['value']) ){
					$faces[$key]['smile'] 	= '很好';
				}else if ( (60 < $value['attribute']['smiling']['value']) && (80 >= $value['attribute']['smiling']['value']) ){
					$faces[$key]['smile'] 	= '非常好';
				}else if ( (80 < $value['attribute']['smiling']['value']) && (100 >= $value['attribute']['smiling']['value']) ){
					$faces[$key]['smile'] 	= '很兴奋';
				}
				//性别判断
				if ( 60 <= $value['attribute']['gender']['confidence'] ) {
					if ('Female' == $value['attribute']['gender']['value']) {
						$faces[$key]['gender'] = '女性';
					}else{
						$faces[$key]['gender'] = '男性';
					}
				}else{
					$faces[$key]['gender'] 	= '未知';
				}
				//是否佩戴眼镜
				if ( 60 <= $value['attribute']['glass']['confidence'] ) {
					if ( 'Normal' == $value['attribute']['glass']['value'] ) {
						$faces[$key]['glass'] = '普通眼镜';
					}else if ( 'Dark' == $value['attribute']['glass']['value'] ){
						$faces[$key]['glass'] = '墨镜';
					}else{
						$faces[$key]['glass'] = '';
					}
				}else{
					$faces[$key]['glass'] 	= '';
				}
				//人种
				if ( 60 <= $value['attribute']['race']['confidence'] ) {
					if ( 'Asian' == $value['attribute']['race']['value'] ) {
						$faces[$key]['race'] = '亚洲人';
					}else if ( 'White' == $value['attribute']['race']['value'] ){
						$faces['$key']['race'] = '欧美人';
					}else{
						$faces[$key]['race'] = '非洲人';
					}
				}else{
					$faces[$key]['race']    = '外星人';
				}
				//面部动作
			}
			return $faces;
		}else{
			//degug
			//查询失败
			return false;
		}
	}
}