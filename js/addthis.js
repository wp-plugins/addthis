var at_total_shares = 0,
    at_populated_svcs = 0,
    at_populated_content = 0;

function addthis_toggle_tabs(servicesOn) {
    var on = 'services',
        off = 'posts';

    if (!servicesOn) {
        var tmp = off;
        off = on;
        on = tmp;
    }

    jQuery('#addthis_data_'+off+'_table').hide();
    jQuery('#addthis_data_'+on+'_table').show();

    jQuery('#addthis_'+on+'_tab').attr("class","addthis_tab atb-active");
    jQuery('#addthis_'+off+'_tab').attr("class","addthis_tab");
    return false;
}

function addthis_populate_services_table(username, password, max, table_id, header_id) {
    if (!at_populated_svcs) {
        // load service json
        jQuery.getJSON("http://api.addthis.com/analytics/1.0/pub/shares/service.json?suppress_response_codes=true&username="+encodeURIComponent(username)+"&password="+encodeURIComponent(password)+"&callback=?", 
            function(data){
                if (!data || data.length == 0) {
                    jQuery(header_id).text("No shares yesterday.");
                    jQuery('#addthis_data_container').hide();
                    return;
                }
                if (data.error) {
                    jQuery('#addthis_data_container').hide();
                    jQuery(header_id).text("Error connecting to AddThis.");
                    return;
                } 
                jQuery(header_id).text("Yesterday At a Glance");
                jQuery.each(data, function(i,item){
                    at_total_shares += item.shares;
                    if ( i < max ) {
                        (i % 2 == 0 ? jQuery("<tr>").attr("id", "at_data_"+i).attr("class", i == 0 ? "first" : "") : jQuery("#at_data_"+(i-1)))
                        .append(jQuery("<td>").attr("class", "b").text(item.service))
                        .append(jQuery("<td>").attr("class", "t").text(item.shares))
                        .appendTo(table_id);
                    }
                });

                if (jQuery('#at_data_total_services').length) {
                    jQuery('#at_data_total_services').text(at_total_shares);
                } else {
                    jQuery("<tr>").attr("class", "last")
                    .append(jQuery("<td>").attr("class", "b atb").text("Total Shares:"))
                    .append(jQuery("<td>"))
                    .append(jQuery("<td>"))
                    .append(jQuery("<td>").attr("id","at_data_total_services").attr("class", "t atb").text(at_total_shares))
                    .appendTo(table_id);
                }

                jQuery("#at_post_total").text(at_total_shares);
                jQuery('#addthis_tab_table').show();
        });
        at_populated_svcs = 1;
    }
}

function addthis_populate_posts_table(username, password, max, table_id, header_id) {
    if (!at_populated_content) {
        // get the top content data
        jQuery.getJSON("http://api.addthis.com/analytics/1.0/pub/shares/content.json?suppress_response_codes=true&username="+encodeURIComponent(username)+"&password="+encodeURIComponent(password)+"&callback=?", 
            function(data){
                var other = 0;
                jQuery.each(data, function(i,item){
                        var title = item.title;
                        try { title = decodeURIComponent(title); } catch (e) { title = unescape(title); }
                        if (title.length > 53) title = title.substr(0, 50) + '...';
                        if ( i < max) {
                            jQuery("<tr>").attr("id", "at_post_data_"+i).attr("class", i == 0 ? "first" : "")
                            .append(jQuery("<td>").attr("class", "b").text(title))
                            .append(jQuery("<td>").attr("class", "t").text(item.shares))
                            .appendTo(table_id);
                        }
                    });

                if (jQuery('#at_post_total').length) {
                    jQuery('#at_post_total').text(at_total_shares);
                } else {
                    jQuery("<tr>").attr("class", "last").attr("id","at_data_total")
                    .append(jQuery("<td>").attr("class", "b atb").text("Total Shares:"))
                    .append(jQuery("<td id='at_post_total'>").attr("class", "t atb").text(at_total_shares))
                    .appendTo(table_id);
                }

                jQuery('#addthis_tab_table').show();
                jQuery(table_id+"_table").show();
        });
        at_populated_content = 1;
    }
}
