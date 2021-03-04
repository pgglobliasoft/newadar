require(['jquery','mage/url'], function($,url) {
	$(function(){
		var getUrl = window.location;
		var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";
		url = baseUrl + "customerorder/customer/Searchproductdata";
		console.log(url);
		$.ajax({
               url: url,
   			showLoader: true,
   			processData:false,
   			contentType: false,
   			cache: true,
               success: function(response) {
               	// console.log(response.color);
               	// var data = response;
   				if (response) {
                  if(document.getElementById("autosearch"))
      					autostylecomplete(document.getElementById("autosearch"), response.result,response.color);
      				}
               }
           });
		 function autostylecomplete(inp, arr,arrcolor){
		 	// console.log(arr);
            	arr = arr.sort();
            	arrcolor = arrcolor.sort();
            	var currentFocus;
   		  	 	var down = 1;
            	inp.addEventListener("input", function(e) {
            		var a, b, i, val = this.value;
            		var x = document.getElementsByClassName("autocomplete-items");
   			    for (var i = 0; i < x.length; i++) {
   			        x[i].parentNode.removeChild(x[i]);
   			    }
   			    if (!val) { return false;}
   			    currentFocus = 0;
   			    a = document.createElement("DIV");
   			    a.setAttribute("id", this.id + "autocomplete-list");
   			    a.setAttribute("class", "autocomplete-items");
   			    this.parentNode.appendChild(a);
   			    var res = [];
   			    for (i = 0; i < arr.length; i++) {
   			    	if(arr[i].sku.substr(0, val.length).toUpperCase() == val.toUpperCase()	){

   			    		res.push(arr[i]);
   			    	}
   		      }
   		      for (i = 0; i < arrcolor.length; i++) {
   			    	if(arrcolor[i].color.substr(0, val.length).toUpperCase() == val.toUpperCase()	){

   			    		res.push(arrcolor[i]);
   			    	}
   		      }
   		      var res = res.sort(function(a, b) {
   				  return parseInt(a.sku) - parseInt(b.sku);
   				});
   		      	for (i = 0; i < res.length; i++) {
   		      			b = document.createElement("DIV");
   			          if(res[i].sku){
   			          b.innerHTML = "<span>" + res[i].sku +"</span>";
   			          b.innerHTML += "<input type='hidden' value='" + res[i].sku + "'>";
	   			      }else{
	   			      	b.innerHTML = "<span>" + res[i].color +"</span>";
	   			          b.innerHTML += "<input type='hidden' value='" + res[i].color + "'>";

	   			      }
   			          b.addEventListener("click", function(e) {
										// console.log("Teesr");
   			              inp.value = this.getElementsByTagName("input")[0].value;
   			              $('button.search').trigger('click');
                          $('#autosearch').attr('readonly', 'readonly'); // Force keyboard to hide on input field. 
                          $('#autosearch').attr('disabled', 'true'); 
                          $('#autosearch').blur();  
                          $('.search-loader').css("display","block !important");
   			              var x = document.getElementsByClassName("autocomplete-items");
   						    for (var i = 0; i < x.length; i++) {
   						        x[i].parentNode.removeChild(x[i]);
   						   	}
   			          });
   			          a.appendChild(b);
   		      }
               if($('#autosearchautocomplete-list.autocomplete-items').html() != ''){
                  $('.autocomplete-items div').addClass('element');
                if($("#autosearchautocomplete-list").children().length){
                  $("#autosearchautocomplete-list").children().first().addClass("active");
                  // var x = $("#autosearchautocomplete-list.autocomplete-items .element.active input").val();
                  // $("#autosearch").attr("value",x);
                }
               }
            });
            $(document).on("input", "#autosearch",function(e){
			      if($("#autosearchautocomplete-list").is(":empty")){
			            $("#autosearchautocomplete-list").hide();
			         }
			      });

            $("#autosearch").keypress(function(e){
                  if($(".search-product").is(":visible")){
                    var keycode = (event.keyCode ? event.keyCode : event.which);
                    if(keycode == '13'){
                     var triggered = false;
                     e.preventDefault();
                        if($("#autosearchautocomplete-list.autocomplete-items").children().length > 0 && triggered == false){
                           var selected_v = $("#autosearchautocomplete-list.autocomplete-items").find(".element.active span").html();
                           $(".searchInput .input-text").val(selected_v);
                           triggered = true;
                        }
                     $("button.search").trigger("click");
                    }
                  }
               });

						// $(document).on("focusout", "#show_style",function(e){
						// 	if(!$(e.target).closest("#show_style").length){
						// 		$("#show_style").blur();
						// 	}
						// });

						$(document).on("click", function(event){
							if($("#autosearchautocomplete-list").is(":visible")){
								 if(!$(event.target).closest("#autosearchautocomplete-list, .cf.sidebarSearch #search_mini_form").length){
									 $("#autosearchautocomplete-list").slideUp();
								 }
							 }
						 });

           }

	});
});
