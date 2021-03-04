require(['jquery'], function ($,jquery)
{
	
	$(document).on("click","li.deshboard",function(e) {
		e.preventDefault();
		
        if($(window).width() <= 1400){
            $('.block.left-side-navcontainer, .sidebar.sidebar-main').toggleClass('opened1');               
        }else{
            $('.block.left-side-navcontainer, .sidebar.sidebar-main').toggleClass('opened');      
        }     	       
    })

    // $('.left-envelope a').click(function(){ 
    //     if($('li.left-search.hidehover.hidehover1').length > 0 ){
    //        $('.sidesearch').trigger('click');
    //     }
    //     if($('.envelope-conatiner.show.bounceInLeft.animated').length > 0){
    //         $('#envelope-conatiner').addClass("bounceInRight");
    //         setTimeout(function(){  $('#envelope-conatiner').removeClass("show bounceInLeft animated"); },400);
    //     }else{
    //         $('#envelope-conatiner').removeClass("bounceInRight");
    //     }
    //     $('#envelope-conatiner').addClass("show bounceInLeft animated");
    //     $(this).parent('.left-envelope').toggleClass("hidehover"); 
    //     return false;
        
    // });

    // $('#envelope-conatiner .close').click(function(){
    //     // $('#envelope-conatiner').toggleClass("show bounceInLeft animated ");
    //     setTimeout(function(){  $('#envelope-conatiner').removeClass("show bounceInLeft animated"); },400);
    //     $('.left-envelope').toggleClass("hidehover");  
    //     $('#feedbacksuccessdiv').css({'display':'none'});
    //             $('#fedbackformdeiv').css({'display':'block'});
    //             $('#feedbackformmyaccount').trigger("reset");                
    //     return false;
    // });
});


