define([
    'jquery','customerRules','swal'
], function ($,rules,swal) {
    'use strict';

    return function (config) {

      var dataarray = config.result;
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
             var date1 = data[0].created_at;
             var date2 = data[0].updated_at;
             var date_create = new Date(date1.replace(/-/g,'/'));
             var date_login = new Date(date2.replace(/-/g,'/'));

             var created_date_time = Number(date_create.getMonth() + 1) + '-' + date_create.getDate() + '-' + date_create.getFullYear() + '   ' + date_create.getHours() + ':' + date_create.getMinutes() + ':' + date_create.getSeconds();
             var lastlogin_date_time = Number(date_login.getMonth() + 1) + '-' + date_login.getDate() + '-' + date_login.getFullYear() + '   ' + date_login.getHours() + ':' + date_login.getMinutes() + ':' + date_login.getSeconds();

             $('#key_hidden').val(passedID);
             $(".edit-user .registrationPage p.created span.ans").text(created_date_time);
             $(".edit-user .registrationPage p.last_login span.ans").text(lastlogin_date_time);              
             $(".edit-user #user-ModalLabel").html("Edit User: " + data[0].fullname);
             $(".edit-user .registrationPage #fullname").val(data[0].fullname);
             $(".edit-user .registrationPage #reg_email_address").val(data[0].email);
             $(".edit-user .registrationPage #regpassword ,.edit-user .registrationPage #password-confirmation").val('00000000');
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


      /*
      * Delete user
      */
      $('#Delete_the_customer').on('click',function(e){

          swal({
            title: "Are you sure?",
            text: "You will not be able to recover this imaginary file!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#87a2b6',
            confirmButtonText: 'Yes, delete it!',
            closeOnConfirm: false,
            allowEscapeKey : true,
            closeOnEsc: true,
          },
          function(){

              form = $('#register-child-form-validate');
              var entityid = $('#key_hidden').val();
              var data = $.grep(dataarray, function( n, i ) {return n.entity_id===entityid; });
              var formData = rules.getFormData(form);          
              formData['parent_id'] = data[0]['c_id'];
              $.ajax({
                      url: deleteUrl,
                      dataType: 'json',
                      type: 'POST',                                                        
                      data:  formData,
                      beforeSend: function() {    
                         $('body').trigger('processStart');
                      }
                  }).done(function(data) {   
                    $('body').trigger('processStop');  
                    
                    rules.swalPopup(data);                          
                    setTimeout(function(){                        
                        $('tr[data-id="'+entityid+'"]').remove();
                        $("#user-Modal .close").trigger('click');
                    },2000);
                  
                  });
            });

      })


      /*
      */
      // $(".action.reloadbtn").on('click',function(e){    
      $(document).one('click','.action.reloadbtn',function(e){        
          $.ajax({
              url: dataurl,
              dataType: 'json',
              type: 'GET', 
              beforeSend: function() {    
                 $('body').trigger('processStart');
              }
          }).done(function(data) {   
            $('body').trigger('processStop');  
            if(!data.error){
                if(data.html.length > 5)
                  $('.user-gird-conatiner').html(data.html).trigger('contentUpdated');      
              }else{
                  swal("Error !", "Data not reload", "error");
              }           
          
          });


      });
    }

});