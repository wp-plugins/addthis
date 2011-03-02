jQuery(document).ready(function($) {  
    
    var data = {action: "at_show_dashboard_widget"};

    $.post(ajaxurl, data, function(response){
        $( "#dashboard_addthis > .inside ").replaceWith('<div class="inside">' + response + '</div>' );
        $( "#at_tabs").tabs();
    });


});
