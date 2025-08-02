<?php
/*
Plugin Name: Weather Block
Description: Plugin WordPress qui ajoute un bloc Gutenberg pour afficher la météo en fonction de la localisation de l’utilisateur.
Version: 1.0
Author: Odolin
*/

if (!defined('ABSPATH')) {
    exit; // Empêche un accès direct
}

// Enregistrement du bloc Gutenberg
function wb_register_weather_block() {
    // On enregistrera les scripts du bloc ici (JavaScript + CSS)
}
add_action('init', 'wb_register_weather_block');
