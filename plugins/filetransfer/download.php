<?php

/*

CometChat
Copyright (c) 2016 Inscripts
Nuller-Team : DarkGoth 2019 (nullcamp)

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."config.php");

$file = preg_replace("/[^a-zA-Z0-9\. ]/", "", $_GET['file']);
$file = str_replace(" ", "_", $file);

if(defined('AWS_STORAGE') && AWS_STORAGE == '1') {
	$error_flag = true;
	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."storage".DIRECTORY_SEPARATOR."s3.php");
	$s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
	$filedata = $s3->getObject(AWS_BUCKET, $bucket_path.'filetransfer/'.$file);
	if(!$filedata) {
		$filepath = "http://".$aws_bucket_url."/filetransfer/".$file;
		$response = get_headers($filepath, 1);
		$filesize = $response['Content-Length'];
		$filedata = true;
		$error_flag = false;
	}else{
		$filesize = $filedata->headers['size'];
	}
}else {
	$filepath = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'filetransfer'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.($file);
	$filedata = file_exists($filepath);
	$filesize = filesize($filepath);
}

if ($filedata){
	header('Content-Description: File Transfer');
	header('Content-Type: application/force-download');
	header('Content-Disposition: attachment; filename='.rawurldecode($_GET['unencryptedfilename']));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . $filesize);
	if(defined('AWS_STORAGE') && AWS_STORAGE == '1' && $error_flag) {
		echo $filedata->body;
	}else {
		ob_start();
		ob_clean();
		flush();
		readfile($filepath);
	}
}else {
	header("HTTP/1.0 404 Not Found");
}
