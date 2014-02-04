jQuery(document).ready(function( $ ) { 
	var agendaHeading = $('.tribe-events-weekly .weekly-event-block');
			agendaHeading.click(function(){
				eventLink = $(this).find('.url').attr('href');
               window.location.href = eventLink;
			});	
});	
