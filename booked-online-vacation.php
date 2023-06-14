<?php
/*
* Plugin Name: Booked Online Vacation
* Description: This plugin add functionality to Booked plugin to set vacation to online appointments.
* Version: 1.0.0
* Author: Gábor Hódos
* Author URI: https://cv.hodos.me
* License: GPL2
*/

// BACKEND

//include CSS and JS
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

add_action( 'get_header', 'my_log' );

function my_log( $txt ) {
    $file = plugin_dir_path( __FILE__ ) . '/logs.txt'; 
    $open = fopen( $file, "a" );
    
    $write = fwrite($open, date('Y-m-d H:i:s') . PHP_EOL);

    if (is_array($txt) || is_object($txt)) {
        $write = fwrite($open, print_r($txt, true));
    } else {
        $write = fwrite( $open, $txt );     
    }
    
    fclose( $open );
}


// FRONTEND
function booked_online_vacation_enqueue_front_assets(){
    wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
    wp_enqueue_script( 'booked_online_vacation_enqueue_front_jf', plugin_dir_url( __FILE__ ) . 'js/booked-online-front.js', array('jquery'), false, true );
}

add_action('wp_enqueue_scripts', 'booked_online_vacation_enqueue_front_assets');


/*
function check_online_vacation( $field_type, $field_name, $field_value, $is_required, $look_for_subs, $numbers_only, $data_attributes ) {
    my_log('check_online_vacation'.PHP_EOL);
    my_log(compact('field_type', 'field_name', 'field_value', 'is_required', 'look_for_subs', 'numbers_only', 'data_attributes'));
    if ($field_name == 'required---3091567' && $field_value == 'Élőben veszek részt') {
        $data_attributes .= ' disabled';
        my_log($data_attributes.PHP_EOL);
        return $data_attributes;
    }
}

add_filter( 'booked_custom_fields_add_template_subs', 'check_online_vacation', 10, 7 );
*/

/*
function check_online_vacation2( $default_value, $field_type, $field_name, $field_value, $is_required, $look_for_subs, $numbers_only, $data_attributes ) {
    my_log('check_online_vacation2'.PHP_EOL);
    my_log(compact('default_value', 'field_type', 'field_name', 'field_value', 'is_required', 'look_for_subs', 'numbers_only', 'data_attributes'));
    if ($field_name == 'single-radio-button---3091567' && $field_value == 'Élőben veszek részt') {
        $data_attributes .= ' disabled';
        
        my_log($data_attributes.PHP_EOL);
        return $data_attributes;
    }
}

add_filter( 'booked_custom_fields_add_template_main', 'check_online_vacation2', 10, 8 );
*/
/*
add_filter( 'booked_fe_calendar_date_appointments', 'online_vacation_date_appointments', 10, 7);

function online_vacation_date_appointments($html,$time_format,$timeslot_parts,$spots_available,$available,$timeslot,$date){
    //my_log('online_vacation_date_appointments'.PHP_EOL);
    //my_log(compact('html', 'time_format', 'timeslot_parts', 'spots_available', 'available', 'timeslot', 'date'));
    return $html;
}
*/

function online_vacation_booked_fe_appt_form_bookings( $bookings ){
    foreach ($bookings as &$booking) {
        foreach ($booking as &$timeSlot) {
            $details = get_option('booked_defaults_'.$timeSlot['calendar_id']);
            $day = date('D', strtotime($timeSlot['date']));
            $timeslot = $timeSlot['timeslot'];
            $onlineVacationBool = filter_var($details[$day.'-details'][$timeslot]['onlineVacationCheckbox'], FILTER_VALIDATE_BOOLEAN);
            $timeSlot['onlineVacation'] = $onlineVacationBool;
        }
    }
    my_log('booked_fe_appt_form_bookings');
    my_log($bookings);
    return $bookings;
}

add_filter( 'booked_fe_appt_form_bookings', 'online_vacation_booked_fe_appt_form_bookings', 10, 1 );


function add_appt_form_date_time_after( $value, $this_timeslot_timestamp, $timeslot, $calendar_id ) {
    my_log('add_appt_form_date_time_after');
    $details = get_option('booked_defaults_'.$calendar_id);
    //my_log($details);
    my_log(compact('value', 'this_timeslot_timestamp', 'timeslot', 'calendar_id'));
    
}

add_filter( 'booked_fe_appt_form_date_time_before', 'add_appt_form_date_time_after', 10, 4);

/*
add_filter( 'booked_custom_fields_add_template_main', 'test', 10, 8 );

function test_two( $field_type, $field_name, $field_value, $is_required, $look_for_subs, $numbers_only, $data_attributes ) {
    my_log('test two');
    my_log( compact('field_type', 'field_name', 'field_value', 'is_required', 'look_for_subs', 'numbers_only', 'data_attributes') );
}

add_filter( 'booked_custom_fields_add_template_subs', 'test_two', 10, 7 );
*/
function test_three( $field_type, $look_for_subs ) {
    my_log('test three');
    my_log( compact('field_type', 'look_for_subs') );
}

add_action( 'booked_custom_fields_add_template_subs_end', 'test_three' );

