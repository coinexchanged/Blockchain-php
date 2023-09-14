<?php
/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App;
 
class MakePoster
{
 
	/**
	 * 生成宣传海报
	 * @param array  参数,包括图片和文字
	 * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
	 * @return [type] [description]
	 */
	public  function createPoster($config=array(),$filename=""){
	  //如果要看报什么错，可以先注释调这个header
	  /*if(empty($filename)) header("content-type: image/png");*/
	  $imageDefault = array(
	    'left'=>0,
	    'top'=>0,
	    'right'=>0,
	    'bottom'=>0,
	    'width'=>100,
	    'height'=>100,
	    'opacity'=>100
	  );
	  $textDefault = array(
	    'text'=>'',
	    'left'=>0,
	    'top'=>0,
	    'fontSize'=>32,       //字号
	    'fontColor'=>'255,255,255', //字体颜色
	    'angle'=>0,
	  );
	  $background = $config['background'];//海报最底层得背景
	  //背景方法
	  $backgroundInfo = getimagesize($background);
	  $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
	  $background = $backgroundFun($background);
	  $backgroundWidth = imagesx($background);  //背景宽度
	  $backgroundHeight = imagesy($background);  //背景高度
	  $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
	  $color = imagecolorallocate($imageRes, 0, 0, 0);
	  imagefill($imageRes, 0, 0, $color);
	  // imageColorTransparent($imageRes, $color);  //颜色透明
	  imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
	  //处理了图片
	  if(!empty($config['image'])){
	    foreach ($config['image'] as $key => $val) {
	      $val = array_merge($imageDefault,$val);
	      $info = getimagesize($val['url']);
	      $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
	      if($val['stream']){   //如果传的是字符串图像流
	        $info = getimagesizefromstring($val['url']);
	        $function = 'imagecreatefromstring';
	      }
	      $res = $function($val['url']);
	      $resWidth = $info[0];
	      $resHeight = $info[1];
	      //建立画板 ，缩放图片至指定尺寸
	      $canvas=imagecreatetruecolor($val['width'], $val['height']);
	      imagefill($canvas, 0, 0, $color);
	      //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
	      imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
	      $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
	      $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
	      //放置图像
	      imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
	    }
	  }
	  //处理文字
	  if(!empty($config['text'])){
	    foreach ($config['text'] as $key => $val) {
	      $val = array_merge($textDefault,$val);
	      list($R,$G,$B) = explode(',', $val['fontColor']);
	      $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
	      $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
	      $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
	      imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
	    }
	  }
	  //生成图片
	  if(!empty($filename)){
	    $res = imagejpeg ($imageRes,$filename,90); //保存到本地
	    imagedestroy($imageRes);
	    if(!$res) return false;
	    return $filename;
	  }else{
	    imagejpeg ($imageRes);     //在浏览器上显示
	    imagedestroy($imageRes);
	  }
	}


	//文字 二维码 生成一张图片
    /*public  function mergepic(){

        $string='/mobile/register.html';
        $img="http://qr.liantu.com/api.php?w=300&text=".$string;
        return $img;

    	$str='111\n222222\n3333\n';
    	$im = imagecreate(100,120);
		$white = imagecolorallocate($im,0xFF,0xFF,0xFF);
		imagecolortransparent($im,$white);  //imagecolortransparent() 设置具体某种颜色为透明色，若注释
		$black = imagecolorallocate($im,0x00,0x00,0x00);
		$ttf='./font-test.ttf';
		imagettftext($im,15,0,50,40,$black,$ttf,$str); //字体设置部分linux和windows的路径可能不同
		header("Content-type:image/png");
		imagepng($im);//文字生成的图片
		return $im;

		//两张图片合成方法
		$A = "1.png";
		$B = "test.png";
		 
		$x = 20;
		$y = 20;
		$rh = 50;
		 
		$im1 = imagecreatefromstring(file_get_contents($A));
		$im2 = imagecreatefromstring(file_get_contents($B));
		 
		imagecopymerge($im1, $im2, $x, $y, 0, 0, imagesx($im2), imagesy($im2), $rh);
		 
		imageGif($im1); 

        
    
    }*/

    


}