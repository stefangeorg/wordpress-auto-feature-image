<?php
/**
* Plugin Name: Auto Fetch Post Thumbnail
* Description: Get first image link in content and assign it as post thumbnail
* Version: 1.0
* Author: Innovo JSC
* Author URI: www.innovo.vn
* License: 
*/

function auto_fetch_post_thumbnail_admin() {
    include('auto_fetch_post_thumbnail_admin.php');
}
function auto_fetch_post_thumbnail_admin_actions() {
    add_options_page("Auto Fetch Post Thumbnail Setting", "Auto Fetch Post Thumbnail Setting", '10', "Auto_Fetch_Post_Thumbnail_Setting", "auto_fetch_post_thumbnail_admin");
}
add_action('admin_menu', 'auto_fetch_post_thumbnail_admin_actions');

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

function get_allow_filetypes(){
    $auto_fetch_image_type = get_option('auto_fetch_image_type');        
    return $auto_fetch_image_type;
}

function auto_fetch_thumbnail_publish( $ID, $post ) {    
    $content = $post->post_content;
    $pattern = '~(http.*\.)('.get_allow_filetypes().')~i';
    preg_match($pattern, $content, $matches);
    if(count($matches)>0){
        $image_link = $matches[0];
        $extension = $matches[2];
        try{
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
        }catch(Exception $e){
            continue;
        }
    }
}
add_action(  'publish_post',  'auto_fetch_thumbnail_publish', 10, 2 );


function auto_fetch_thumbnail_save_post( $post_id ){
	if ( ! wp_is_post_revision( $post_id ) ){	
		// unhook this function so it doesn't loop infinitely
		remove_action('save_post', 'auto_fetch_thumbnail_save_post');
        
        $content = $post->post_content;
        $pattern = '~(http.*\.)('.get_allow_filetypes().')~i';
        preg_match($pattern, $content, $matches);
        if(count($matches)>0){
            $image_link = $matches[0];
            $extension = $matches[2];
            try{
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
                $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                update_post_meta($post_id, '_thumbnail_id', $attach_id, true);
            }catch(Exception $e){
                continue;
            }
        }        

		// re-hook this function
		add_action('save_post', 'auto_fetch_thumbnail_save_post');
	}
}
add_action('save_post', 'auto_fetch_thumbnail_save_post');
?>
