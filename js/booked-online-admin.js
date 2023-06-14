window.addEventListener('load', (event) => {
    var currentlySaving = false;
    // Custom Time Slots
	const customTimeslotsContainter = jQuery('#customTimeslotsContainer');

	customTimeslotsContainter.find('.booked-customTimeslot').each(function(){
		let thisTimeslot = jQuery(this);
		let rand = Math.floor((Math.random() * 100000000) + 1);
		init_custom_timeslot_block_online_vacation(thisTimeslot,rand)
	});
	
	function init_custom_timeslot_block_online_vacation(thisTimeslot,rand){
	    thisTimeslot.find('#customOnlineVacationCheckbox').attr('id','customOnlineVacationCheckbox-'+rand);
		thisTimeslot.find('label[for="customOnlineVacationCheckbox"]').attr('for','customOnlineVacationCheckbox-'+rand);
		
		thisTimeslot.on('change','#customOnlineVacationCheckbox-'+rand,function(e){
		    e.preventDefault();
			thisCheckbox = jQuery(this);
			let currentArrayDetails = thisCheckbox.parents('.booked-customTimeslot').find('input[name=booked_this_custom_timelots_details]').val();
			let currentTimeslot = thisCheckbox.parent().parent().attr('data-timeslot');
			let currentArrayDetailsArray = JSON.parse(currentArrayDetails);
			
			if (thisCheckbox.is(':checked')){
				currentArrayDetailsArray[currentTimeslot]['customOnlineVacationCheckbox'] = true;
			} else {
				currentArrayDetailsArray[currentTimeslot]['customOnlineVacationCheckbox'] = false;
			}
			
			thisCheckbox.parents('.booked-customTimeslot').find('input[name=booked_this_custom_timelots_details]').val(JSON.stringify(currentArrayDetailsArray));
			
			var formData = JSON.stringify(jQuery('form#customTimeslots').serializeObject());
			jQuery('#custom_timeslots_encoded').val(formData);
			jQuery('#booked-saveCustomTimeslots').prop('disabled',false).addClass('button-primary');
		});
	}



    jQuery('#bookedTimeslotsWrap').bind('DOMNodeInserted', function(e) { 
        const timeslotsContainer = jQuery('#bookedTimeslotsWrap');
        var preventMultiClicks;
        
    	timeslotsContainer.find('.timeslot').children().each(function(){
    		let thisTimeslot = jQuery(this);
    		let rand = Math.floor((Math.random() * 100000000) + 1);
            init_timeslot_online_vacation(thisTimeslot,rand)
    	});
    	
    	function init_timeslot_online_vacation(thisTimeslot,rand){
    	    thisTimeslot.find('#onlineVacationCheckbox').attr('id','onlineVacationCheckbox-'+rand);
    		thisTimeslot.find('label[for="onlineVacationCheckbox"]').attr('for','onlineVacationCheckbox-'+rand);
    		
    		thisTimeslot.on('change','#onlineVacationCheckbox-'+rand,function(e){
    		    e.preventDefault();
    			
    			let $checkBox      	= jQuery(this),
				    $timeslot	 	= $checkBox.parents('.timeslot'),
				    day	 	 	 	= $checkBox.parents('td').attr('data-day'),
				    timeslot	 	= $checkBox.parents('.timeslot').attr('data-timeslot'),
				    calendar_id		= jQuery('table.booked-timeslots').attr('data-calendar-id');
				
				if ($checkBox.is(':checked')){
				    onlineVacationCheckbox = true;
    			} else {
    				onlineVacationCheckbox = false;
    			}
    			
    			clearTimeout(preventMultiClicks);
    			
    			preventMultiClicks = setTimeout(function(){
    
    				$timeslot.css({'opacity':0.5});
    
    				currentlySaving = true;
    
    				booked_js_vars.ajaxRequests.push = jQuery.bookedAjaxQueue({
    					type	: 'post',
    					url 	: booked_js_vars.ajax_url,
    					data	: {
    						'action'     	         : 'booked_online_vacation_set',
    						'onlineVacationCheckbox' : onlineVacationCheckbox,
    						'calendar_id'	         : calendar_id,
    						'day'     		         : day,
    						'timeslot'     	         : timeslot
    					},
    					success: function(data) {
    						currentlySaving = false;
    						$timeslot.css({'opacity':1});
    					},
    					error: function(resp) {
    					    console.log(resp)
    					}
    				});
    
    			},350);
    		});
    	}
    })	
})

