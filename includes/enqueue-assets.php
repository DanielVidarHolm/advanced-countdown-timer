<?php

function act_enqueue_admin_scripts(){
    global $pagenow;
    wp_enqueue_script('act-admin-scripts', plugins_url('act/scr/assets/js/admin.js'), array('jquery'),'1.0.0',true);

    wp_enqueue_style('act-admin-stylesheet', plugins_url('act/src/assets/css/admin.css'), array(), '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'act_enqueue_admin_scripts');
?>