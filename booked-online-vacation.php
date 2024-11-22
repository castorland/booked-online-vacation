<?php
/*
* Plugin Name: Booked Online Vacation
* Description: This plugin add functionality to Booked plugin to set vacation to online appointments.
* Version: 1.1.0
* Author: Gábor Hódos
* Author URI: https://hodos.me
* License: GPL2
*/

// BACKEND

function booked_online_vacation_enqueue_assets () {
    wp_enqueue_style( 'booked-online-vacation-styles', plugin_dir_url( __FILE__ ) . 'css/booked-online-admin.css' );
    wp_enqueue_script( 'booked-online-vacation-scripts', plugin_dir_url( __FILE__ ) . 'js/booked-online-admin.js', array('jquery') );
}
add_action ('admin_enqueue_scripts', 'booked_online_vacation_enqueue_assets');

function booked_custom_online_vacation($this_timeslot, $timeslot, $booked_calendar_id ) {
    $timeslots_details = $this_timeslot['booked_this_custom_timelots_details'];
    ?>
    <span class="customOnlineVacation">
        <label for="customOnlineVacationCheckbox">Csak utólag nézhető</label>
        <input id="customOnlineVacationCheckbox" name="customOnlineVacationCheckbox" type="checkbox" value="1" <?php if (isset($timeslots_details[$timeslot]['customOnlineVacationCheckbox']) && $timeslots_details[$timeslot]['customOnlineVacationCheckbox']): echo ' checked="checked"'; endif; ?>>
        </span>
    <?php
}
add_action( 'booked_single_custom_timeslot_end', 'booked_custom_online_vacation', 10, 3);

function booked_online_vacation( $day, $time, $calendar_id ) {
    if ($calendar_id == 93) {
        $timeslot = implode('-', $time);
        $details = get_option('booked_defaults_'.$calendar_id);
        $onlineVacationBool = filter_var($details[$day.'-details'][$timeslot]['onlineVacationCheckbox'], FILTER_VALIDATE_BOOLEAN);

        ?>
        <span class="onlineVacation">
            <label for="onlineVacationCheckbox">Csak utólag nézhető</label>
            <input id="onlineVacationCheckbox" name="onlineVacationCheckbox" type="checkbox" value="1" <?php if ($onlineVacationBool): echo ' checked="checked"'; endif; ?>>
            </span>
        <?php
    }
}
add_action( 'booked_single_timeslot_end', 'booked_online_vacation', 10, 3);

function booked_online_vacation_set () {
    if (isset($_POST['onlineVacationCheckbox']) && isset($_POST['day']) && isset($_POST['timeslot'])):

		$calendar_id = (isset($_POST['calendar_id']) ? $_POST['calendar_id'] : false);

		$day = $_POST['day'];
		$timeslot = $_POST['timeslot'];
		$onlineVacation = $_POST['onlineVacationCheckbox'];

		if ($calendar_id):
			$booked_defaults = get_option('booked_defaults_'.$calendar_id);
		else :
			$booked_defaults = get_option('booked_defaults');
		endif;

		if (!empty($booked_defaults[$day][$timeslot])):
		    $booked_defaults[$day.'-details'][$timeslot]['onlineVacationCheckbox'] = $onlineVacation;

			if ($calendar_id):
				update_option('booked_defaults_'.$calendar_id,$booked_defaults);
			else :
				update_option('booked_defaults',$booked_defaults);
			endif;

		endif;
        return $booked_defaults;
	endif;
	return 'fail';
	wp_die();
}
add_action('wp_ajax_booked_online_vacation_set', 'booked_online_vacation_set');

//add_action( 'get_header', 'my_log' );

function my_log( $txt ) {
    $file = plugin_dir_path( __FILE__ ) . '/logs.txt';
    $open = fopen( $file, "a" );

    fwrite($open, date('Y-m-d H:i:s') . PHP_EOL);

    if (is_array($txt) || is_object($txt)) {
        fwrite($open, print_r($txt, true));
    } else {
        fwrite( $open, $txt );
    }
    fwrite( $open, PHP_EOL );

    fclose( $open );
}


// FRONTEND
function booked_online_vacation_enqueue_front_assets(){
    wp_enqueue_script( 'booked_online_vacation_enqueue_front_jf', plugin_dir_url( __FILE__ ) . 'js/booked-online-front.js', array('jquery'), false, true );
}

add_action('wp_enqueue_scripts', 'booked_online_vacation_enqueue_front_assets');

function booked_online_vacation_get()
{
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        die('Required "nonce" value is not here, please let the developer know.');
    }

    if (isset($_POST['date']) && isset($_POST['timeslot']) && isset($_POST['calendar_id'])) :

        $calendar_id = (isset($_POST['calendar_id']) ? $_POST['calendar_id'] : false);

        $date = $_POST['date'];
        $timeslot = $_POST['timeslot'];
        $day = date('D', strtotime($date));

        if ($calendar_id) :
            $booked_defaults = get_option('booked_defaults_' . $calendar_id);
        else :
            $booked_defaults = get_option('booked_defaults');
        endif;


        if (!empty($booked_defaults[$day][$timeslot])) :
            $onlineVacationBool = filter_var($booked_defaults[$day . '-details'][$timeslot]['onlineVacationCheckbox'], FILTER_VALIDATE_BOOLEAN);
        else :
            $booked_custom_timeslots_encoded = get_option('booked_custom_timeslots_encoded');
            $booked_custom_timeslots_decoded = json_decode($booked_custom_timeslots_encoded, true);

            foreach ($booked_custom_timeslots_decoded['booked_custom_calendar_id'] ?? [] as $key => $value) {
                $booked_custom_start_date = $booked_custom_timeslots_decoded['booked_custom_start_date'];

                if ($value == $calendar_id && $booked_custom_start_date[$key] == $date) {
                    $booked_defaults = json_decode($booked_custom_timeslots_decoded['booked_this_custom_timelots_details'][$key], true);
                    $onlineVacationBool = filter_var($booked_defaults[$timeslot]['customOnlineVacationCheckbox'], FILTER_VALIDATE_BOOLEAN);
                }
            }
        endif;
        wp_send_json_success(array(
            'onlineVacationCheckbox' => $onlineVacationBool
        ));
    endif;

    return 'fail';
    wp_die();
}

add_action('wp_ajax_booked_online_vacation_get', 'booked_online_vacation_get');
add_action('wp_ajax_nopriv_booked_online_vacation_get', 'booked_online_vacation_get');
