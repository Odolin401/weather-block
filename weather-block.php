<?php
/*
Plugin Name: Weather Block
Description: Plugin WordPress qui ajoute un bloc Gutenberg pour afficher la météo en fonction de la localisation de l’utilisateur.
Version: 1.0
Author: Odolin
*/

if (!defined('ABSPATH')) {
    exit;
}

// Enregistrement du bloc Gutenberg
function wb_register_weather_block()
{
    // Registre le script du bloc
    wp_register_script(
        'weather-block-editor',
        plugins_url('build/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    // Enregistre le bloc
    register_block_type('weather/block', array(
        'editor_script' => 'weather-block-editor',
    ));
}
add_action('init', 'wb_register_weather_block');
