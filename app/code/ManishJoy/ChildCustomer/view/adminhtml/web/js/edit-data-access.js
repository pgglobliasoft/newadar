define([
    'jquery','swal'
], function ($,swal) {
    'use strict';

    return function (config) {

      var dataarray = config.config_customer;
      var deleteUrl = config.actionDurl;
      var dataurl = config.dataurl;

      /*
      * Edit popup click event
      */
      $(".action.userbtn").on('click',function(e){
        $('.registrationPage.child-customer').find('div.mage-error').remove()
        var passedID = $(this).attr('id');
        
        if(passedID){
          $("#user-Modal").addClass("edit-user").attr('data-action', 'edit');
            var data = $.grep(dataarray, function( n, i ) {return n.entity_id===passedID; });
            var roles = data[0].permission;
            var obj = JSON.parse(roles);


            $('.edit-user .child-lable input').each(function() {
               var perm = $(this).attr('value');
               var check = false;
              $.each(obj, function(k, v) {
                 $.each(obj[k], function(k, v) {
                    if(v == perm){
                      check = true;
                    }       
              });  
            });
              $(this).prop('checked',check);
           });


          $('.filter').each(function() {
            var allcheck = false;
            $(this).find('.child-lable input').each(function(){
              if($(this).prop("checked") == true){
                  allcheck = true;
              }else{
                allcheck = false;
                return false;   
              }
            })
            $(this).find("span.all input[type='checkbox']").prop("checked",allcheck);
          })
             var active = data[0].status == 0 ? 'active' : 'inactive';
             $('#key_hidden').val(passedID);
             $(".edit-user #user-ModalLabel").html("Edit User: " + data[0].fullname);
             $(".edit-user .registrationPage #fullname").val(data[0].fullname);
             $(".edit-user .registrationPage #reg_email_address").val(data[0].email);
             $(".edit-user .registrationPage #regpassword ,.edit-user .registrationPage #password-confirmation").val('00000000');
             $(".edit-user .registrationPage p.created span.ans").text(data[0].created_at);
             $(".edit-user .registrationPage p.last_login span.ans").text(data[0].updated_at);              
             $(".edit-user .registrationPage p.active_status").removeClass('active inactive').addClass(active).find('span.ans').text(active);             
             $(".edit-user button.registrationBtn").html("Save Changes");
             $(".edit-user input#allow_user_login").attr("checked" , data[0].status == 1 ? false : true);

        }else{

          if($("#user-Modal").hasClass("edit-user")){
              $("#user-Modal").removeClass("edit-user").attr('data-action',' ');
              $("#user-ModalLabel").html("Create A New User");
              $(".registrationPage p.active_status").removeClass('active inactive')
              $('#register-child-form-validate').trigger("reset"); 
              $(".registrationBtn").html("Create a New User");
          }
       }
      })

    }

});