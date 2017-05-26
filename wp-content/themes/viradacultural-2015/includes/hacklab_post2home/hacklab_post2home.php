<?php
//********* SELECIONA DESTAQUES DA HOME *****************//

add_action('manage_posts_custom_column', 'hacklab_post2home_select', 10, 2);
add_filter('manage_posts_columns','hacklab_post2home_add_column');
add_action('manage_noticias_posts_custom_column', 'hacklab_post2home_select', 10, 2);
#add_filter('manage_noticias_posts_columns','hacklab_post2home_add_column');
add_action('load-edit.php', 'hacklab_post2home_JS');
add_action('load-edit-pages.php', 'hacklab_post2home_JS');

function hacklab_post2home_add_column($defaults){
    global $post_type;
    if ('post' == $post_type || 'noticias' == $post_type || 'imprensa' == $post_type)
        $defaults['destaques'] = 'Destaque';
    return $defaults;
}

function hacklab_post2home_select($column_name, $id){

    if ($column_name=="destaques"){
        $highlighted = get_post_meta($id, "_home", true) == 1 ?  "checked" : "";
    ?>  
        <input type="checkbox" class="hacklab_post2home_button" id="hacklab_post2home_<?php echo $id; ?>" <?php echo $highlighted; ?>>
    <?php
    }
}

function hacklab_post2home_JS() {
	wp_enqueue_script('hacklab_post2home', get_template_directory_uri() . '/includes/hacklab_post2home/admin.js', array('jquery'));
	wp_enqueue_style('hacklab_post2home', get_template_directory_uri() . '/includes/hacklab_post2home/post2home.css');
	wp_localize_script('hacklab_post2home', 'hacklab', array('ajaxurl' => admin_url('admin-ajax.php') ));
}

//********* FIM SELECIONA DESTAQUES DA HOME *****************//

add_action('wp_ajax_destaque_add', 'hacklab_post2home_add');
add_action('wp_ajax_destaque_remove', 'hacklab_post2home_remove');

function hacklab_post2home_add() {
    update_post_meta($_POST['post_id'], '_home', 1);
    echo 'ok';
    die;
}

function hacklab_post2home_remove() {
    delete_post_meta($_POST['post_id'], '_home');
    echo 'ok';
    die;
}

add_action('pre_get_posts', function($wp_query) {

    if (!$wp_query->is_main_query())
        return $wp_query;
    
    if (is_front_page()) {
        global $wpdb;
        $wp_query->query_vars['post__not_in'] = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_home' AND meta_value = 1");
    
    }

});


?>
