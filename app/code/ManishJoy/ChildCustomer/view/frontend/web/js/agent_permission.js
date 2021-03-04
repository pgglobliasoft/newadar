define(['jquery','swal'],
    function($,swal) {
        'use strict';
        return {                    
            Checkall: function(element) {             	
            	var classtext =  $(element).attr('id');            	
            	if(element.checked) {
			       $('.'+classtext).find('input[type="checkbox"]' ).prop('checked', element.checked);
			    } else {
			        $('.'+classtext).find('input[type="checkbox"]' ).prop('checked', element.checked);
			    }

            },
            uncheckall : function(element)
			{
			   var classtext =  $(element).parent('div').attr('class');          
			   var flag = false;			   
			   $('.'+classtext +' label :checkbox').each(function() {			   	
			   	 if(this.checked) {
			   	 	flag = true;
			   	 }else{
			   	 	flag = false;
			   	 	return false
			   	 }
			   })
			   if(flag == true && !$('#'+classtext).is(':checked'))
			   {
					$('#'+classtext).prop('checked', true);
			   }else{
			   			$('#'+classtext).prop('checked',false);
			   }
            
       		},
       		/**
			* @return form data as array
			*/
       		getFormData : function(formElem){
			    var unindexed_array = formElem.serializeArray();
			    var indexed_array = {};

			    jQuery.map(unindexed_array, function(n, i){
			        indexed_array[n['name']] = n['value'];
			    });

			    return indexed_array;
			},

			/**
            * @return swal popup 
            */
            swalPopup : function(data)
            {
            	var title = !data.errors ? 'Success !' :' Error! ';
	            var classname = !data.errors ? 'success' :'error';
	            var message = data.message;
	            if(data){
                  swal({
                    title: title, 
                    text: message, 
                    type: classname,             
                    showCancelButton: true,
                    timer: 2000,
                    showCancelButton: false,   
                    className: "swal-customer-"+classname                   
                  }) 
	            }
	            return this;
            },

			/*
			* return true false for register customer or not
			*/
       		registerCustomer: function() {            			
       			
       			var that = this;
       			console
       			var changeText = "User Adding...";
	  			var changeSubmit = $('#user-Modal .registrationBtn').text();	  		
	  			if(changeSubmit == "Edit User"){
	  				var changeText = "User Data Updated...";
	  			}         
	  			var findButton = $('#register-child-form-validate').find('button[type=submit]'),
                form = $('#register-child-form-validate'); 
                var edit =  $("#user-Modal").attr('data-action');              
                if (form.validation('isValid')) {
                  	var url = form.attr('action');
                  	var formData = this.getFormData(form);
                  	formData['edit'] = edit;
                  	if(edit !== '')
                  		formData['status'] = $('#allow_user_login').prop("checked") ? 0 : 1;
                  	
	                $.ajax({
	                    url: url,
	                    dataType: 'json',
	                    type: 'POST',                                                        
	                    data:  formData,
	                    beforeSend: function() {    
	                       findButton.text(changeText);
	                       $('body').trigger('processStart');
	                    }
	                }).done(function(data) {   
	                	$('body').trigger('processStop');
	                	findButton.text(changeSubmit);		                 
	                 	if(data){
	                 		var title = !data.errors ? 'Success !' :' Error! ';
				            var classname = !data.errors ? 'success' :'error';
				            var message = data.message;
				            if(data){
			                  swal({
			                    title: title, 
			                    text: message, 
			                    type: classname,             
			                    showCancelButton: true,			                   
			                    showCancelButton: false,   
			                    className: "swal-customer-"+classname                   
			                  },function(e){
			                  		if(data.html.length > 5)
			                  		$('.user-gird-conatiner').html(data.html);						                            			
									    $("#user-Modal .close").trigger('click').trigger('contentUpdated');
								        form.trigger("reset");   
								        $("#collapseFive").addClass("show");  						       
			                  }) 
				            }
						}
						     
	                	
	                });
	            }

            }         


       	} // return finish


	});