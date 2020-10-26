<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Origin: https://mosaic-mitxtile.myshopify.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$result = [];
if(isset($_FILES['upload_imgs'])){
    $datas = [];
    $extensions= array("jpeg","jpg","png");
    foreach($_FILES['upload_imgs']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['upload_imgs']['name'][$key];
        $file_tmp = $_FILES['upload_imgs']['tmp_name'][$key];
        $file_ext = strtolower(end(explode('.',$file_name)));
        $data = [];
        if(in_array($file_ext, $extensions)) {
            $unique = uniqid('-image-', true);
            $newOriginalFileName = time() . "{$unique}-original.{$file_ext}";
            //$newCroppedFileName = time() . "{$unique}-cropped.{$file_ext}";
            
            move_uploaded_file($file_tmp, "images/" . $newOriginalFileName);
            //resize_crop_image("images/" . $newOriginalFileName, "images/" . $newCroppedFileName);
            $base64 = base64_encode(file_get_contents("images/" . $newOriginalFileName));

            $data['err'] = 0;
            $data['img'] = "https://nutjobdata.com/mixtile/images/{$newOriginalFileName}";
            $data['base64'] = "data:image/jpg;base64,{$base64}";
            $data['size'] = getimagesize($data['img']);
            $data['msg'] = "Success!";
        } else {
            $data['err'] = 1;
            $data['msg']="extension not allowed, please choose a JPEG or PNG file.";
        }
        $datas[] = $data;
    }
      
    //if($file_size > 2097152){
    //$errors[]='File size must be excately 2 MB';
    //}
    $result['datas'] = $datas;
    $result['err'] = 0;
    $result['msg'] = "Success!";
} else {
    $result['err'] = 1;
	$result['msg'] = "There's no image";
}
echo json_encode($result);

//resize and crop image by center
function resize_crop_image($source_file, $dst_dir, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
 
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
 
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
 
        default:
            return false;
            break;
    }
    
    $max_width = $max_height = min($width, $height);
    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file);
     
    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $width){
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }
     
    $image($dst_img, $dst_dir, $quality);
 
    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}

?>