$( document ).ready(function(){
	$('body').on('click', '.page-scroll a', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top - 60
        }, 600);
        event.preventDefault();
    });

	$('.uudiem_btn').on('click',function(e){
    	$('body, html').stop().animate({
    		scrollTop: $('#footer').offset().top
    	},600);
    	e.preventDefault();
	});
	$('.btn_dk').on('click',function(e){
    	$('body, html').stop().animate({
    		scrollTop: $('#footer').offset().top
    	},600);
    	e.preventDefault();
	});
	
	$('.carousel').bind('slide.bs.carousel', function (e) {
		// $('.video').pause();
		$('.video').get(0).pause();
		// console.log('aaa');
	});
	

  	(function() {
	"use strict";

	var docElem = document.documentElement,
		didScroll = false,
			changeHeaderOn = 550;
			document.querySelector( '#back-to-top' );
		function init() {
			window.addEventListener( 'scroll', function() {
				if( !didScroll ) {
					didScroll = true;
					setTimeout( scrollPage, 50 );
				}
			}, false );
		}
		
	})();


	$('.scroll_top').on('click',function(){
	    $('html, body').animate({scrollTop: 0}, 'slow');
	    return false;
	});


	
	// var stringPathName = window.location.pathname;

	// function updateURL() {
    //   if (history.pushState) {
    //       var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?para=hello';
    //       window.history.pushState({path:newurl},'',newurl);
    //   }
    // }

	$('.navbar-nav li a').on('click',function(){
		var $this = $(this).attr('href');
		var stringPathName = window.location.pathname;
		var newurl = stringPathName + $this;
		if (history.pushState) {
           window.history.pushState({path:newurl},'',newurl);
      	}
	});
});
// $('#myCarousel .item img').on('click',function(){
// 	let h = $(this).height();
// 	console.log(h);
// 	video = '<iframe id="iframe-video" src="' + $(this).attr('data-video') + '" height="' + h + '"frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
// 	$(this).replaceWith(video);
// })


if($(window).width() > 768){
	$(window).on('load', () => {
	let h = $('.dauhieu_img').height();
	console.log(h);
	$('.bg_xam').height(h);
})
}
	// end srcoll chuot xuong mat menu, croll len hien lai
else{
	$('.nav li a').on('click', function(){
	    $('.navbar-toggle').click() //bootstrap 3.x by Richard
	});

}




function isValidEmail(emailText) {
	var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(emailText);
  };
  function checkPhoneNumber() {
	var flag = false;
	var phone = $('#phone').val().trim(); // ID của trường Số điện thoại
	phone = phone.replace('(+84)', '0');
	phone = phone.replace('+84', '0');
	phone = phone.replace('0084', '0');
	phone = phone.replace(/ /g, '');
	if (phone != '') {
		var firstNumber = phone.substring(0, 2);
		if ((firstNumber == '09' || firstNumber == '08') && phone.length == 10) {
			if (phone.match(/^\d{10}/)) {
				flag = true;
			}
		} else if (firstNumber == '01' && phone.length == 11) {
			if (phone.match(/^\d{11}/)) {
				flag = true;
			}
		}
	}
	return flag;
  }

function checkPhoneNumber_ft() {
  var flag = false;
  var phone = $('#phone_ft').val().trim(); // ID của trường Số điện thoại
  phone = phone.replace('(+84)', '0');
  phone = phone.replace('+84', '0');
  phone = phone.replace('0084', '0');
  phone = phone.replace(/ /g, '');
  if (phone != '') {
	  var firstNumber = phone.substring(0, 2);
	  if ((firstNumber == '09' || firstNumber == '08') && phone.length == 10) {
		  if (phone.match(/^\d{10}/)) {
			  flag = true;
		  }
	  } else if (firstNumber == '01' && phone.length == 11) {
		  if (phone.match(/^\d{11}/)) {
			  flag = true;
		  }
	  }
  }
  return flag;
}


function kiemtra()
{
	if($('#fullname').val() == "")
	{
		alert("Vui lòng nhập Họ tên!");
		$('#fullname').focus();
	}    

	else if(!checkPhoneNumber()){
		alert("Số điện thoại bạn điền không hợp lệ !");
		$('#phone').focus(); 
		return false;
	}
	else if($('#email').val()=="" || !isValidEmail($('#email').val())){   
		alert("Email phải hợp lệ !");
		$('#email').focus();
		return false;
	}
	else
	{ 
		var phone =  $('#phone').val();    
		var fullname =  $('#fullname').val();      
		var email = $('#email').val();
		$.ajax({
			type: "POST",
			url: "api/register.php",      
			data: {fullname:fullname,phone:phone,email:email},
			success: function(data){  
				try 
				{
					var dataDmp = {
						'campaign_id': '5bf77860f4c06a21c34fe8c2',
						// 'service': array['type'],
						'fullname': fullname,
						'phone': phone,
						'email': email
						// 'company': array['company'],
						// 'content': array['content']
					};

					$.ajax({
						type: 'POST',
						url: '//log.urekamedia.com/customers',
						data: dataDmp,
						dataType: "json",
						success: function (data_dmp) {
							var checkFunction = (typeof pushData === 'function');
							if (checkFunction ==true) {
								pushData(data_dmp);
								data = JSON.parse(data);
								console.log(data);
							}
							else{
								data = JSON.parse(data);
								console.log(data);
							}
							
						}
					});
				} 
				catch (e) {
					console.log(e);
					data = JSON.parse(data);
					 console.log(data);
				}
				
			}
		}); 
	} 
}


function kiemtra_footer()
{
	if($('#fullname_ft').val() == "")
	{
		alert("Vui lòng nhập Họ tên!");
		$('#fullname_ft').focus();
	}
	else if(!checkPhoneNumber_ft()){
		alert("Số điện thoại bạn điền không hợp lệ !"); 
		$('#phone_ft').focus();   
		return false;
	}   
	else if($('#email_ft').val()=="" || !isValidEmail($('#email_ft').val())){   
		alert("Email phải hợp lệ !");
		$('#email_ft').focus();    
		return false;
	}
	else
	{		
		var phone =  $('#phone_ft').val();		
		var fullname =  $('#fullname_ft').val();			
		var	email = $('#email_ft').val();
		$.ajax({
			type: "POST",
			url: "api/register.php", 			
			data: {fullname:fullname,phone:phone,email:email},
			success: function(data){ 	
				try {
						var dataDmp = {
							'campaign_id': '5bf77860f4c06a21c34fe8c2',
							// 'service': array['type'],
							'fullname': fullname,
							'phone': phone,
							'email': email
							// 'company': array['company'],
							// 'content': array['content']
						};

						$.ajax({
							type: 'POST',
							url: '//log.urekamedia.com/customers',
							data: dataDmp,
							dataType: "json",
							success: function (data_dmp) {
								var checkFunction = (typeof pushData === 'function');
								if (checkFunction ==true) {
									pushData(data_dmp);
									data = JSON.parse(data);
                                    console.log(data);
								}
								else{
									data = JSON.parse(data);
									 console.log(data);
								}
								
							}
						});
					} 
				catch (e) {
					console.log(e);
					data = JSON.parse(data);
					 console.log(data);
				}
				
			}
		});	
	}	
}


