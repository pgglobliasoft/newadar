define([
    'jquery',
    'stockists_countries',
    'stockists_search',
    'mousewheelScroll'
    ], 
    function($,country_list) {        

        return {
            geocoderObject : function() {
                return new google.maps.Geocoder();
            },
            address : function() {
                return $("#stockist-search-term").val()
            },
            getCountryCode : function() {
                name = this.address();
                for(var i = 0, len=country_list.length; i < len; i++) {
                    if (country_list[i].name.toUpperCase() == name.toUpperCase()) {
                        return country_list[i].code
                    }
                }
            },
            search : function(map,config) {                

                //$("#sl_cnt").val(0);
                //$("#sl_show").val(0);

                var geocoder = this.geocoderObject();
                
                $(".stockists-results").empty();
                $(".stockists-results").append("<span class='results-word'>Results for <span class='italic'>" + this.address() + ":</span></span><br />");
                    
                var code_country = this.getCountryCode();    
                geocoder.geocode(
                    {'address': this.address()},
                    function(results, status) {
                        $('.result-notfound').remove();
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                if (results[0]["types"][0] == "country") {
                                    map.setZoom(5);
                                    map.setCenter(results[0].geometry.location);
                                    var marker = new google.maps.Marker({
                                        map: map,          
                                        visible: false,
                                        position: results[0].geometry.location
                                    });
                                    for (i = 0; i < markers.length; i++) { 
                                        if (markers[i].global_country == code_country) {
                                            if(config.unit == "default"){
                                                var store_distance = parseFloat(distance*0.001).toFixed(2);
                                                var unitOfLength = "kilometres";
                                            } else if(config.unit == "miles"){
                                                var store_distance = parseFloat(distance*0.000621371192).toFixed(2);
                                                var unitOfLength = "miles away";
                                            }
                                            var contentToAppend = "<li class='results-content' data-miles='"+store_distance+"' data-marker='"+markers[i].record_id+"'><h3 class='results-title'>"+markers[i].global_name+"<span class='distance'>"+store_distance+" "+unitOfLength+"</span></h3>";
                                            if (markers[i].global_address) {
                                                contentToAppend += "<p>"+markers[i].global_address+"</p>";
                                            }
                                            if (markers[i].global_city) {
                                                contentToAppend += "<p>"+markers[i].global_city+" "+markers[i].global_region+", "+markers[i].global_postcode+"</p>";
                                            }
                                            if (markers[i].global_link) {
                                                var link = global_link.indexOf("http") > -1 ? global_link : "http://"+global_link;
                                                contentToAppend += "<a href='" +link+"' target='_blank'>"+link+"</a>";
                                            }
                                            if (markers[i].global_phone) {
                                                contentToAppend += "<span class='storeContact'><strong>T |</strong>"+markers[i].global_phone+"</span>";
                                            }
                                            if (markers[i].global_email) {
                                                contentToAppend += "<span class='storeContact'><strong>E |</strong><a href='mailto:" +markers[i].global_email+"' target='_blank'>"+markers[i].global_email+"</a></span>";
                                            }
                                            contentToAppend += '<span class="btn-primary ask-for-directions get-directions" data-directions="DRIVING"><a href="javascript:void(0);">Get Directions</a></span>';
                                            contentToAppend += "</li>";
                                            $(".stockists-results").append(contentToAppend);
                                            $('.results-content:first').click();
                                        }
                                        else
                                        {
                                            if($('.stockists-results li').length == 0)
                                            {
                                                $('.result-notfound').remove();
                                                $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                                            }
                                        } 
                                    }
                                }
                                else{
                                    map.setZoom(9);
                                    map.setCenter(results[0].geometry.location);
                                    var marker = new google.maps.Marker({
                                        map: map,   
                                        visible: false,
                                        position: results[0].geometry.location
                                    });
                                    var circle = new google.maps.Circle({
                                        map: map,
                                        radius: config.radius,    // value from admin settings
                                        fillColor: config.fillColor,
                                        fillOpacity: config.fillOpacity, 
                                        strokeColor: config.strokeColor,
                                        strokeOpacity: config.strokeOpacity,
                                        strokeWeight: config.strokeWeight
                                    });
                                    circle.bindTo('center', marker, 'position');
                                    for (i = 0; i < markers.length; i++) { 
                                        var distance = google.maps.geometry.spherical.computeDistanceBetween(marker.position, markers[i].position);
                                        //var search_keyword = this.address();
                                        search_keyword = $("#stockist-search-term").val();
                                        if (distance < config.radius || markers[i].global_region.toLowerCase() == search_keyword.toLowerCase() || markers[i].global_city.toLowerCase() == search_keyword.toLowerCase()) {
                                            if(config.unit == "default"){
                                                var store_distance = parseFloat(distance*0.001).toFixed(2);
                                                var unitOfLength = "kilometres";
                                            } else if(config.unit == "miles"){
                                                var store_distance = parseFloat(distance*0.000621371192).toFixed(2);
                                                var unitOfLength = "miles away";
                                            }
                                            var contentToAppend = "<li class='results-content' data-miles='"+store_distance+"' data-marker='"+markers[i].record_id+"'><h3 class='results-title'>"+markers[i].global_name+"<span class='distance'>"+store_distance+" "+unitOfLength+"</span></h3>";
                                            if (markers[i].global_address) {
                                                contentToAppend += "<p>"+markers[i].global_address+"<br/>";
                                            }
                                            var getregion = "";
                                            if(markers[i].global_region != null){
                                                getregion = markers[i].global_region;
                                            }
                                            if (markers[i].global_city) {
                                                contentToAppend += markers[i].global_city+" "+getregion+","+markers[i].global_postcode+"</p>";
                                            }
                                            if (markers[i].global_link) {
                                                contentToAppend += "<a href='" +markers[i].global_link+"' target='_blank'>"+markers[i].global_link+"</a>";
                                            }
                                            if (markers[i].global_phone) {
                                                contentToAppend += "<span class='storeContact'><strong>T |</strong>"+markers[i].global_phone+"</span>";
                                            }
                                            if (markers[i].global_email) {
                                                contentToAppend += "<span class='storeContact'><strong>E |</strong><a href='mailto:" +markers[i].global_email+"' target='_blank'>"+markers[i].global_email+"</a></span>";
                                            }
                                            contentToAppend += '<span class="btn-primary ask-for-directions get-directions" data-directions="DRIVING"><a href="javascript:void(0);">Get Directions</a></span>';
                                            contentToAppend += "</li>";
                                            $(".stockists-results").append(contentToAppend);
                                        }
                                        /* else
                                        {
                                            if($('.stockists-results li').length == 0)
                                            {
                                                $('.result-notfound').remove();
                                                $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                                            }
                                        } */
                                    }
                                    var $wrapper = $('.stockists-results');
                                    
                                    //sort the result by distance
                                    $wrapper.find('.results-content').sort(function(a, b) {
                                        return +a.dataset.miles - +b.dataset.miles;
                                    })
                                    .appendTo($wrapper);
                                    $('.results-content:first').click();
                                    
                                    
                                    
                                    if($('.stockists-results li').length == 0)
                                    {
                                        $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                                    }
                                    else
                                    {
                                        /* if($('.stockists-results li').length > 10)
                                        {
                                            jQuery("#sl_show_more").show()
                                        } */
                                        $("#sl_cnt").val($('.stockists-results li').length);
                                        $('.stockists-results li').each(function( index, element ) {
                                            if(index > $("#sl_show").val())
                                            {
                                                $( element ).hide();
                                            }
                                        });
                                        
                                        if($('.stockists-results li').length >= $("#sl_show").val())
                                        {
                                            $("#sl_show_more").show();
                                        }
                                        else
                                        {
                                            $("#sl_show_more").hide();
                                        }
                                        $("#sl_show").val(parseInt($("#sl_show").val())+10);
                                    }
                                }
                            }
                            /* else
                            {
                                $('.result-notfound').remove();
                                $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                            //alert("No stores near your location.");
                            } */
                        }
                         else {
                                if($('.stockists-results li').length == 0)
                                {
                                    $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                                }
                                /* $('.result-notfound').remove();
                                $("<span class='result-notfound'>No stores found near you, please check out our <a href='#retailers'>online retailers</a></span>").insertAfter(".stockists-results");
                            //alert("No stores near your location."); */
                        } 
                    }
                );
            }        
        }

    }                
);
