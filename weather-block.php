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
function wb_register_weather_block() {
    // Registre le script de l'éditeur
    wp_register_script(
        'weather-block-editor',
        plugins_url('build/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    // Enregistre un bloc dynamique
    register_block_type('weather/block', array(
        'editor_script'   => 'weather-block-editor',
        'render_callback' => 'wb_render_weather_block'
    ));
}
add_action('init', 'wb_register_weather_block');

// Callback pour afficher le bloc côté frontend
function wb_render_weather_block($attributes, $content) {
    return '<div class="weather-block">🌤️ Chargement de la météo...</div>';
}


// Charger JS côté frontend
function wb_enqueue_frontend_scripts() {
    wp_enqueue_script(
        'weather-block-frontend',
        plugins_url('frontend.js', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'frontend.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'wb_enqueue_frontend_scripts');

