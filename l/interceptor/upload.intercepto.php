<?php

/**
 * @author huanglei
 * @date Apr 19, 2010
 * 图片上传拦截器
 */
class UploadIntercepto extends AbstractInterceptor {
	
	public function doInterceptor(&$module, &$action, &$method) {

		if (!empty ($_FILES)) {

			if (!defined(ATTACHMENT_DIR)){
				define('ATTACHMENT_DIR',ROOTPATH.'attachment');
			}

			if (!defined(ATTACHMENT_SIZE)){
				//2MB
				define('ATTACHMENT_SIZE',1024*1024*2);
			}

 			if (!file_exists(ATTACHMENT_DIR)) {
				mkdir(ATTACHMENT_DIR, 0777, true);
			}

  			$fileField = $_POST['fileField'];
  			
 			list($width, $height, $type) = getimagesize($_FILES[$fileField]['tmp_name']);
			if (defined(IMAGE_TYPE)){
				if (in_array($type,IMAGE_TYPE)) {
					trigger_error(Util :: __('upload type error'),E_USER_NOTICE);
					return self :: INTERCEPT;
				}
			}
 			$size = $_FILES[$fileField]["size"];
 			if ($size > ATTACHMENT_SIZE) {
				trigger_error(Util :: __('upload size error'),E_USER_NOTICE);
				return self :: INTERCEPT;
			}
  			$hash = md5(microtime() . rand(0, 100)); 
  			
  			
  			//TODO: last char '/'
  			$filename = ATTACHMENT_DIR . $hash . "." . end(explode('.', strtolower($_FILES[$fileField]["name"])));
  			
 			$realFilename = ROOTPATH . $filename;
 
 			if (@ move_uploaded_file($_FILES[$fileField]['tmp_name'], $realFilename)) {
				$_POST[$fileField] = array (
				    'realFilename' => $realFilename,
					'filename' => $filename,
					'type' => $type,
					'size' => $size,
					'hash' => $hash
				);
			} else {
				trigger_error(Util :: __('upload error') . $_FILES[$fileField]['error'].'('.$_FILES[$fileField]['tmp_name'].'/'.$filename.')',E_USER_NOTICE);
				return self :: INTERCEPT;
			}
		}
		return self :: SKIP;
	}
}
?>
