window.addEventListener('load', (event) => {
    jQuery(document).on("booked-on-new-app", function (event) {
        const $details = jQuery('.booked-appointment-details');
        let timeslot = $details.find('input[name="timeslot[]"]').val();
        let date = $details.find('input[name="date[]"]').val();
        let calendar_id = $details.find('input[name="calendar_id[]"]').val();
        booked_appt_form_options = { 'action': 'booked_online_vacation_get', 'nonce': booked_js_vars.nonce, 'date': date, 'timeslot': timeslot, 'calendar_id': calendar_id };

        jQuery.ajax({
            url: booked_js_vars.ajax_url,
            type: 'post',
            data: booked_appt_form_options,
            success: function (response) {
                if (response.success && response.data.onlineVacationCheckbox) {
                    const $container = jQuery('.cf-block');
                    $container.find('input[value="Élőben veszek részt"]').parent().hide();
                    $container.find('input[value="Utólag megnézem"]').prop('checked', true);
                }
            },
            error: function (error) {
                // console.log(error);
            }
        });
    });
})