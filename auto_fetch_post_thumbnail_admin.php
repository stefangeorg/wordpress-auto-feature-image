<?php
    if($_POST['auto_fetch_post_thumbnail_hidden'] == 'Y') {
        $auto_fetch_image_type = $_POST['auto_fetch_image_type'];
        update_option('auto_fetch_image_type', $auto_fetch_image_type);
 
?>
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
<?php
    } else {
        //Normal page display
        $auto_fetch_image_type = get_option('auto_fetch_image_type');        
    }
?>

        <div class="wrap">
            <?php    echo "<h2>" . __('Auto Fetch Post Thumbnail Options') . "</h2>"; ?>
             
            <form name="auto_fetch_post_thumbnail_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <input type="hidden" name="auto_fetch_post_thumbnail_hidden" value="Y">
                <?php    echo "<h4>" . __( '') . "</h4>"; ?>
                <p><?php _e("Allowed filetypes: " ); ?><br/><input type="text" name="auto_fetch_image_type" value="<?php echo $auto_fetch_image_type; ?>" size="100"><?php _e("e.g.:jpeg|jpg|gif|png" ); ?></p>
                <hr />
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
                </p>
            </form>
        </div>
