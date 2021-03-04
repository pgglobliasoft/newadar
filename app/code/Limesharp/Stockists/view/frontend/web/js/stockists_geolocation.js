define([
    'jquery',
    'stockists_geolocation',
    'mousewheelScroll'
    ], 
    function($) {        

        return {
	        
            search : function(map, coords, latLng, config) {                        
                
                $(".stockists-results").empty();
                $(".stockists-results").append("<span class='results-word'>Closest stores:</span><br />");
				map.setCenter(latLng);
                map.setZoom(9);
				var marker = new google.maps.Marker({
                    record_id: "" + coords.latitude + coords.longitude,
                    position: latLng,
					visible: false,
                    gestureHandling: 'cooperative',
                    map:map
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
                    if (distance < config.radius) {
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
						var getregion = "";
							if(markers[i].global_region != null){
								getregion = markers[i].global_region;
							}
                        if (markers[i].global_city) {
                            contentToAppend += "<p>"+markers[i].global_city+" "+getregion+", "+markers[i].global_postcode+"</p>";
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
						
                    }
                }   
                var $wrapper = $('.stockists-results');
                
                //sort the result by distance
                $wrapper.find('.results-content').sort(function(a, b) {
                    return +a.dataset.miles - +b.dataset.miles;
                })
                .appendTo($wrapper);
                              
            }
        
        }

    }                
);
