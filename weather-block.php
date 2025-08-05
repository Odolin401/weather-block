<?php
/*
Plugin Name: Weather Block
Description: Plugin WordPress qui ajoute un bloc Gutenberg pour afficher la météo en fonction de la localisation de l’utilisateur.
Version: 1.7
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
        hour tinyint(2) NOT NULL,
        temperature varchar(10) NOT NULL,
        weather_condition varchar(100) NOT NULL,
        icon varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY city_date (city, date, hour)
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

//  Page de réglages pour clé API
add_action('admin_menu', function () {
    add_options_page('Weather Block', 'Weather Block', 'manage_options', 'weather-block', 'wb_settings_page');
});
function wb_settings_page()
{
?>
    <div class="wrap">
        <h1>Weather Block - Réglages</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wb_settings_group');
            do_settings_sections('wb_settings_group');
            ?>
            <label for="wb_api_key">Clé API WeatherAPI :</label>
            <input type="text" id="wb_api_key" name="wb_api_key" value="<?php echo esc_attr(get_option('wb_api_key')); ?>" style="width: 400px;">
            <?php submit_button('Enregistrer la clé API'); ?>
        </form>
    </div>
<?php
}

add_action('admin_init', function () {
    register_setting('wb_settings_group', 'wb_api_key');
});

// AJAX handler pour récupérer la météo
add_action('wp_ajax_get_weather_data', 'wb_get_weather_data');         // Pour admin connecté
add_action('wp_ajax_nopriv_get_weather_data', 'wb_get_weather_data');   // Pour visiteurs

function wb_get_weather_data()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';

    // Récupération des coordonnées envoyées
    $lat = floatval($_POST['lat']);
    $lon = floatval($_POST['lon']);
    $date_today = date('Y-m-d');
    $current_hour = isset($_POST['hour']) ? intval($_POST['hour']) : intval(date('G')); // Heure locale ou actuelle



    // Tolérance en degrés (±0.01 ≈ ~1 km)
    $tolerance = 0.01;

    // Vérifier si la météo existe déjà dans la base pour cette zone
    $weather = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE ABS(lat - %f) <= %f 
             AND ABS(lon - %f) <= %f 
             AND date = %s AND hour = %d",
            $lat,
            $tolerance,
            $lon,
            $tolerance,
            $date_today,
            $current_hour
        )
    );

    if ($weather) {
        // Retourner la météo depuis la base
        wp_send_json_success($weather);
    }

    // Sinon, appeler WeatherAPI
    // Clé API depuis réglages
    $api_key = get_option('wb_api_key');
    if (empty($api_key)) {
        wp_send_json_error('Clé API manquante. Configurez-la dans Réglages > Weather Block.');
    }

    // Appel à l'API WeatherAPI
    // On utilise la clé API et les coordonnées
    // Langue française
    $response = wp_remote_get("http://api.weatherapi.com/v1/forecast.json?key=$api_key&q={$lat},{$lon}&days=1&lang=fr");


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
    $forecast_hours = $data->forecast->forecastday[0]->hour;

    // Enregistrer dans la base les météo de 24 heures
    foreach ($forecast_hours as $hour_data) {
        $hour_time = intval(date('G', strtotime($hour_data->time)));
        $wpdb->insert($table_name, array(
            'lat'              => $lat,
            'lon'              => $lon,
            'date'             => $date_today,
            'hour'             => $hour_time,
            'city'             => $city,
            'temperature'      => $hour_data->temp_c,
            'weather_condition' => $hour_data->condition->text,
            'icon'             => $hour_data->condition->icon
        ));
    }

    // Retourner météo de l’heure actuelle
    $current_weather = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE city = %s AND date = %s AND hour = %d",
            $city,
            $date_today,
            $current_hour
        )
    );

    wp_send_json_success($current_weather);
}

// Planifier le cron lors de l'activation du plugin
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('wb_cleanup_weather_data')) {
        // Heure locale
        $heure_locale = 0; 
        $decalage_secondes = 3 * HOUR_IN_SECONDS; 

        // Calcul du prochain timestamp UTC correspondant à minuit Madagascar
        $timestamp_local = strtotime('tomorrow ' . $heure_locale . ':00') - $decalage_secondes;

        wp_schedule_event($timestamp_local, 'daily', 'wb_cleanup_weather_data');
    }
});


// Fonction exécutée par le cron
add_action('wb_cleanup_weather_data', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';
    // Supprimer les données météo de plus de 3 jours  
    $wpdb->query(
        "DELETE FROM $table_name 
         WHERE date < (CURDATE() - INTERVAL 3 DAY)"
    );
});

// Nettoyer le cron à la désactivation du plugin
register_deactivation_hook(__FILE__, function () {
    // Supprime la tâche planifiée quand on désactive le plugin
    wp_clear_scheduled_hook('wb_cleanup_weather_data');
});

