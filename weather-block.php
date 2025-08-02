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
function wb_render_weather_block($attributes, $content)
{
    return '<div class="weather-block">🌤️ Chargement de la météo...</div>';
}


// Charger JS côté frontend
function wb_enqueue_frontend_scripts()
{
    wp_enqueue_script(
        'weather-block-frontend',
        plugins_url('frontend.js', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'frontend.js'),
        true
    );
    // On passe l'URL AJAX à JS
    wp_localize_script('weather-block-frontend', 'wb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'wb_enqueue_frontend_scripts');



// === Création de la table à l'activation ===
register_activation_hook(__FILE__, 'wb_create_weather_table');
function wb_create_weather_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        city varchar(100) NOT NULL,
        lat decimal(10,6) NOT NULL,
        lon decimal(10,6) NOT NULL,
        date date NOT NULL,
        temperature varchar(10) NOT NULL,
        weather_condition varchar(100) NOT NULL,
        icon varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY city_date (city, date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Exécution
    dbDelta($sql);

    // Log pour confirmer l’exécution
    error_log("Activation plugin : table météo créée ou mise à jour.");
}


// === Suppression de la table à la désinstallation ===
register_uninstall_hook(__FILE__, 'wb_delete_weather_table');
function wb_delete_weather_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}


// AJAX handler pour récupérer la météo
add_action('wp_ajax_get_weather_data', 'wb_get_weather_data');         // Pour admin connecté
add_action('wp_ajax_nopriv_get_weather_data', 'wb_get_weather_data');   // Pour visiteurs

function wb_get_weather_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';

    // Récupération des coordonnées envoyées
    $lat = floatval($_POST['lat']);
    $lon = floatval($_POST['lon']);
    $date_today = date('Y-m-d');

     // Tolérance en degrés (±0.01 ≈ ~1 km)
    $tolerance = 0.01;

     // 1️⃣ Vérifier si la météo existe déjà dans la base pour cette zone
    $weather = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE ABS(lat - %f) <= %f 
             AND ABS(lon - %f) <= %f 
             AND date = %s",
            $lat, $tolerance,
            $lon, $tolerance,
            $date_today
        )
    );

    if ($weather) {
        // ✅ Retourner la météo depuis la base
        wp_send_json_success($weather);
    }

    // 2️⃣ Sinon, appeler WeatherAPI
    $api_key = '7a3383d971da4775b4462059250208'; 
    $response = wp_remote_get("http://api.weatherapi.com/v1/current.json?key=$api_key&q={$lat},{$lon}&lang=fr");

    if (is_wp_error($response)) {
        wp_send_json_error('Erreur API.');
    }

    $data = json_decode(wp_remote_retrieve_body($response));

    if (isset($data->error)) {
        wp_send_json_error('Localisation introuvable.');
    }

    // Données utiles
    $city = $data->location->name;
    $temperature = $data->current->temp_c;
    $condition = $data->current->condition->text;
    $icon = $data->current->condition->icon;

     // 3️⃣ Enregistrer dans la base
    $wpdb->insert($table_name, array(
        'lat'              => $lat,
        'lon'              => $lon,
        'date'             => $date_today,
        'city'             => $city,
        'temperature'      => $temperature,
        'weather_condition'=> $condition,
        'icon'             => $icon
    ));

     // 4️⃣ Retourner la météo
    wp_send_json_success(array(
        'city'        => $city,
        'temperature' => $temperature,
        'condition'   => $condition,
        'icon'        => $icon
    ));
}



