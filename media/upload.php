<?php

$uploaderrors = array(
	UPLOAD_ERR_OK => 'Archivo %s subió sin problemas.',
	UPLOAD_ERR_INI_SIZE=> 'Archivo %s demasiado grande para este servidor.',
	UPLOAD_ERR_FORM_SIZE => 'Archivo %s es más grande de lo especificado para esta aplicación.',
	UPLOAD_ERR_PARTIAL => 'Archivo %s subió parcialmente.',
	UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
	UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal, no se pudo subir archivo %s.',
	UPLOAD_ERR_CANT_WRITE => 'El archivo %s no se pudo crear en el servidor',
	UPLOAD_ERR_EXTENSION => 'Problema indeterminado por conflicto de extensiones, no se subió archivo %s.',
);

$imgtypeexts = array(
	'png'=>'png',
	'jpg'=>'jpeg',
	'jpeg'=>'jpeg',
	'gif'=>'gif',
);

function uploadimages($field,$folder,&$debug) {
	global $uploaderrors,$imgexts,$dir;
	$ff = $_FILES[$field];
	//$ud = $_SERVER['DOCUMENT_ROOT']."$dir/$folder/";
	if(!is_array($ff['error'])) {
		$error = $ff['error'];
		$name = $ff["name"];
		$uploaded = $ff["tmp_name"];
		if($error == UPLOAD_ERR_OK) {
			imagestore($folder,$name,$uploaded,$debug);
		} elseif($error != UPLOAD_ERR_NO_FILE) {
			$debug .= sprintf("<p>{$uploaderrors[$error]}</p>\n",$name);
		}
	} else foreach($ff['error'] as $key=>$error) {
		$name = $ff["name"][$key];
		$uploaded = $ff["tmp_name"][$key];
		if($error == UPLOAD_ERR_OK) {
			imagestore($folder,$name,$uploaded,$debug);
		} elseif($error != UPLOAD_ERR_NO_FILE) {
			$debug .= sprintf("<p>{$uploaderrors[$error]}</p>\n",$name);
		}
	}
}

function imagestore($path,$name,$tmp_name,&$debug) {
	global $imgtypeexts;
	$ans = true;
	$ext = ($r = strrpos($name,'.'))?strtolower(substr($name,$r+1)):'';
	#$debug.="<p>Cargando $name en $path. ($ext in ".print_r($imgtypeexts,true)."?)</p>\n";
	if(isset($imgtypeexts[$ext])) $type=$imgtypeexts[$ext]; else return false;
	$imagecf = "imagecreatefrom$type";
	$imagef = "image$type";
	$src = $imagecf($tmp_name);
	list($iw,$ih)=getimagesize($tmp_name);
	if(!file_exists($path."index.jpg"))
		imagestoreIdx($src,$iw,$ih,$path) or ($debug.="<p>index.jpg no fue creado en $path.</p>\n");
	if($iw>640||$ih>640) {
		imagestore640($src,$iw,$ih,$path,$name,$imagef) or ($debug.="<p>$name no fue creado en $path.</p>\n");
		if(!is_dir($path.'full/')) mkdir($path.'full/');
		$imagef($src,$path.'full/'.$name) or ($debug.="<p>$name no fue creado en {$path}full/.</p>\n");
	} else {
		$imagef($src,$path.$name) or ($debug.="<p>$name no fue creado en {$path}200/.</p>\n");
	}
	imagestore300($src,$iw,$ih,$path.'300/',$name,$imagef) or ($debug.="<p>$name no fue creado en {$path}200/.</p>\n");
	imagestore48($src,$iw,$ih,$path.'48/',$name,$imagef) or ($debug.="<p>$name no fue creado en {$path}48/.</p>\n");
	imagedestroy($src);
	return $ans;
}

function imagestore48(&$origImage,$W,$H,$path,$filename,$imgf='imagejpeg') {
	if(!is_dir($path)) mkdir($path);
	$tmp = imagecreatetruecolor(48,48);
	if($H>$W) {
		$yy = ($H-$W)/2;
		imagecopyresampled($tmp,$origImage,0,0,0,$yy,48,48,$W,$W);
	} else {
		$xx = ($W-$H)/2;
		imagecopyresampled($tmp,$origImage,0,0,$xx,0,48,48,$H,$H);
	}
	$ans = $imgf($tmp,$path.$filename);
	imagedestroy($tmp);
	return $ans;
}

function imagestoreIdx(&$origImage,$W,$H,$path) {
	if(!is_dir($path)) mkdir($path);
	$xx=0; $oW=$W;
	$yy=0; $oH=$H;
	if(4*$H>3*$W) {
		$oH = $W*0.75;
		$yy = ($H-$oH)/2;
	} else {
		$oW = $H/0.75;
		$xx = ($W-$oW)/2;
	}
	$tmp = imagecreatetruecolor(300,225);
	imagecopyresampled($tmp,$origImage,0,0,$xx,$yy,300,225,$oW,$oH);
	$ans1 = imagejpeg($tmp,$path."index.jpg");
	imagedestroy($tmp);
	$tmp = imagecreatetruecolor(200,150);
	imagecopyresampled($tmp,$origImage,0,0,$xx,$yy,200,150,$oW,$oH);
	$ans2 = imagejpeg($tmp,$path."thumb.jpg");
	imagedestroy($tmp);
	return $ans1 && $ans2;
}

function imagestore300(&$origImage,$W,$H,$path,$filename,$imgf='imagejpeg') {
	if(!is_dir($path)) mkdir($path);
	$xx=0; $oW=$W;
	$yy=0; $oH=$H;
	$NH = $H*300/$W;
	if($NH>225) {
		$oH=3*$W/4;
		$yy=($H-$oH)/2;
		$NH=225;
	}
	elseif($NH<200) {
		$oW=$H*3/2;
		$xx=($W-$oW)/2;
		$NH=200;
	}
	$tmp = imagecreatetruecolor(300,$NH);
	imagecopyresampled($tmp,$origImage,0,0,$xx,$yy,300,$NH,$oW,$oH);
	$ans = $imgf($tmp,$path.$filename);
	imagedestroy($tmp);
	return $ans;
}

function imagestore640(&$origImage,$W,$H,$path,$filename,$imgf='imagejpeg') {
	if(!is_dir($path)) mkdir($path);
	$xx=0; $NW=640;
	$yy=0; $NH=640;
	if($W>$H)     $NH=$H*640/$W;
	elseif($W<$H) $NW=$W*640/$H;
	$tmp = imagecreatetruecolor($NW,$NH);
	imagecopyresampled($tmp,$origImage,0,0,0,0,$NW,$NH,$W,$H);
	$ans = $imgf($tmp,$path.$filename);
	imagedestroy($tmp);
	return $ans;
}

?>