<?php
/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;
 
class IdCardIdentity
{
 //验证身份证号码的合法性

    /*
	# 函数功能：计算身份证号码中的检校码
	# 函数名称：idcard_verify_number
	# 参数表 ：string $idcard_base 身份证号码的前十七位
	# 返回值 ：string 检校码
	# 更新时间：Fri Mar 28 09:50:19 CST 2008
	*/
	function idcard_verify_number($idcard_base){
	if (strlen($idcard_base) != 17){
	   return false;
	}
	    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //debug 加权因子
	    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); //debug 校验码对应值
	    $checksum = 0;
	    for ($i = 0; $i < strlen($idcard_base); $i++){
	        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
	    }
	    $mod = $checksum % 11;
	    $verify_number = $verify_number_list[$mod];
	    return $verify_number;
	}

	/*
	# 函数功能：将15位身份证升级到18位
	# 函数名称：idcard_15to18
	# 参数表 ：string $idcard 十五位身份证号码
	# 返回值 ：string
	# 更新时间：Fri Mar 28 09:49:13 CST 2008
	*/
	function idcard_15to18($idcard){
	    if (strlen($idcard) != 15){
	        return false;
	    }else{
	    // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
	        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
	            $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 15);
	        }else{
	            $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 15);
	        }
	    }
	    $s=$this->idcard_verify_number($idcard);
	    $idcard = $idcard .$s;
	    return $idcard;
	}

	/*
	# 函数功能：18位身份证校验码有效性检查
	# 函数名称：idcard_checksum18
	# 参数表 ：string $idcard 十八位身份证号码
	# 返回值 ：bool
	# 更新时间：Fri Mar 28 09:48:36 CST 2008
	*/
	function idcard_checksum18($idcard){
	    if (strlen($idcard) != 18){ return false; }
	    $idcard_base = substr($idcard, 0, 17);
	    $re=$this->idcard_verify_number($idcard_base);
	    if ($re != strtoupper(substr($idcard, 17, 1))){
	        return false;
	    }else{
	        return true;
	    }
	}

	/*
	# 函数功能：身份证号码检查接口函数
	# 函数名称：check_id
	# 参数表 ：string $idcard 身份证号码
	# 返回值 ：bool 是否正确
	# 更新时间：Fri Mar 28 09:47:43 CST 2008
	*/
	function check_id($idcard) {
		if(strlen($idcard) == 15 || strlen($idcard) == 18){
		   if(strlen($idcard) == 15){
		    $idcard =$this->idcard_15to18($idcard);
		   }
		   if($res=$this->idcard_checksum18($idcard)){
		    return true;
		   }else{
		    return false;
		   }
		}else{
		   return false;
		}
	}



}