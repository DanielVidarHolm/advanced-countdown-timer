<?php

function teledele_hurrytimer_enqueue_admin_scripts(){
    global $pagenow;
    wp_enqueue_script('teledele-hurrytimer-admin-scripts', plugins_url('teledele-hurrytimer/scr/assets/js/admin.js'), array('jquery'),'1.0.0',true);

    wp_enqueue_style('teledele-hurrytimer-admin-stylesheet', plugins_url('teledele-hurrytimer/src/assets/css/admin.css'), array(), '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'teledele_hurrytimer_enqueue_admin_scripts');
?>