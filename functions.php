<?php
/**
* Plugin Name: Auto Fetch Post Thumbnail
* Description: Get first image in content and assign it as featured image
* Version: 1.0
* Author: Stefan Georg, Innovo JSC (stefan@innovo.vn)
* Author URI: www.innovo.vn
*/

function save_data($data,$destination){        
        $destination = $destination;
        $file = fopen($destination, "w+");
        $result = fputs($file, $data);
        fclose($file);
        return $result;
}
function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);        
        return $data;
}
function auto_fetch_thumbnail_publish( $ID, $post ) {    
    $content = $post->post_content;
    $pattern = '~(http.*\.)(jpe?g|png|gif)~i';
    preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, 3);
    
    $image_link = $matches[0][0];
    $extension = $matches[2][0];
    
    $downloaded_file = get_data($image_link);    
    $arrayPath = wp_upload_dir();
    
    save_data($downloaded_file,$arrayPath["basedir"]."/".$post->post_name.".".$extension);
    
    $filename = $arrayPath["basedir"]."/".$post->post_name.".".$extension;
    $wp_filetype = wp_check_filetype(basename($filename), null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $filename, $post->ID );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    add_post_meta($post->ID, '_thumbnail_id', $attach_id, true);
    
}
add_action(  'publish_post',  'auto_fetch_thumbnail_publish', 10, 2 );
