//can cai dat : jquery.magnific-popup , jquery.nicescroll.min.js,jquery.html5_upload.js, jquery.filedrop.js, webcamjs

(function($) {
    jQuery.fn.modal_upload = function(options) {
    	var template = '<div class="preview-img-box" >'+
                    '<div class="image-holder">'+
                        '<img />'+
                    '</div>'+
                    '<div class="progress-holder" >'+
                        '<div class="progress"><span class="meter" style="width:0" ></span></div>'+
                    '</div>'+
                '</div>';

        var defaults = {
			hasUploadChoose: true,
			hasUploadDrop: true,
			hasUploadCapture: true,
			hasLibrary: true,
			template: template,
			width_preview : 100,
			height_preview : 100,
			el_bind : '',
			callbacks : function(response, exdata){
				console.log(response);
				console.log(exdata);
			},
			error : function(error){
				console.log(error);
			},
			list_picture_select_choose : {},
			list_picture_select_drop : {},
			list_picture_select_camera : {},
			list_picture_select_library : {},
			list_picture : {},
			template: template,
			url_upload_base64: baseUrl +'/api/pictures/uploadBase64?trash=false',
			url_upload: baseUrl +'/api/pictures/upload?trash=false',
			url_delete: baseUrl+'/api/pictures/delete?',
			url_get: baseUrl+'/api/member/getPicture?',
			afterFinishUpload: function(){},
			createImagePreview: function(appendbox, template, file, width, height, class_){
			    var preview = $(template), 
			        image = $('img', preview),
			        preview_box = $('.preview-img-box', preview),
			        meter = $('.meter', preview);
			    var reader = new FileReader();
			    image.width = width;
			    image.height = height;
			    reader.onload = function(e){
			        image.attr('src',e.target.result).attr('height',height);
			    };
			    if(typeof class_ == 'undefined')
			        class_ = '';
			    reader.readAsDataURL(file);
			    preview.addClass('curent-preview-uplo').attr('class',preview.attr('class')+' '+class_).appendTo(appendbox);
			    $.data(file,preview);
			},
		}

		var options =  $.extend(defaults, options);

		function addRanParamToUrl(url){
			if(typeof url !='undefined' && url.length>0 ){
				char_last = url.charAt(url.length-1);
				if(url.indexOf("?") >-1)
					if(char_last == '?')
						return url+'rand=' + new Date().getTime();
					else
						return url+'&rand=' + new Date().getTime();
				else
					return url+'?rand=' + new Date().getTime();
			}else
				return '';
		};

		function isElementInViewport (el) {
		    if (typeof jQuery === "function" && el instanceof jQuery) {
		        el = el[0];
		    }
		    if(typeof el == 'undefined' || !el)
		        return false;
		    else{
		        var rect = el.getBoundingClientRect();
		        return (
		            rect.top >= 0 &&
		            rect.left >= 0 &&
		            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
		            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		        );
		    }
		}

		function updateViewPopUploadAfterRemove(obj, id, tab, trash){
		    if(tab == 'choose'){
		    	if(options.list_picture_select_choose.hasOwnProperty(id))
		    		delete options.list_picture_select_choose[id];
		        $('.mfpop-item-pic-'+id).fadeOut(300,function(){
		            $(this).remove();
		        });
		    }else if(tab == 'drop'){
		    	if(options.list_picture_select_drop.hasOwnProperty(id))
		    		delete options.list_picture_select_drop[id];
		        $('.mfpop-item-pic-'+id).fadeOut(300,function(){
		            $(this).remove();
		            if($('#img-drop-preview .preview-img-box').length<=0){
		                $('.dropbox-canvas').show();
		                $('.dropbox-end,.btn-box-save-mf-uldrop').hide();
		            }
		        });
		    }else if(tab == 'library'){
		    	if(options.list_picture_select_choose.hasOwnProperty(id))
		    		delete options.list_picture_select_choose[id];
		    	if(options.list_picture_select_drop.hasOwnProperty(id))
		    		delete options.list_picture_select_drop[id];
		        $('.mfpop-item-pic-'+id).fadeOut(300,function(){
		            is_ob = 0;
		            if($(this).hasClass('it-img'))
		                is_ob = 1;
		            $(this).remove();
		            if(is_ob == 1){
		                page = parseInt($('.list-img-on-library li.it-img').length/10)+1;
		                btn_l = $('.btn-load-more-im-library');
		                btn_l.attr('data-page', page);
		                loadMorePicOnLibrary();
		            }
		        });
		    }
		    if(options.list_picture_select_library.hasOwnProperty(id))
		    	delete options.list_picture_select_library[id];
		    if(options.list_picture.hasOwnProperty(id))
		    	delete options.list_picture[id];
		}

		function synsUploadFileToLibrary(picture, trash){
			if(picture){
				options.list_picture_select_library[picture._id] = {trash: trash ,picture : picture};
				$('.list-img-on-library').prepend('<li class="divider mfpop-item-pic-'+picture._id+'" ></li><li class="it-img mfpop-item-pic-'+picture._id+'" data-id="'+picture._id+'" data-ref="'+picture._id+'" data-trash="no" data-folder="'+picture.folder+'" data-name="'+picture.name+'" data-type="'+picture.type+'" ><div class="row" ><div class="large-2 columns" ><div class="th" ><img alt="Embedded Image" src="'+picture.folder+'/'+picture.name+'" ></div></div><div class="large-3 columns end" ><span class="lb-pic-ca">'+picture.name+'</span></div><div class="large-4 columns" >'+picture.caption+'</div><div class="large-1 columns" ><input type="checkbox" name="is_thumb" data-id="'+picture._id+'" class="cbx-mf-slect-img cbx-'+picture._id+'" value="'+picture._id+'" checked="checked"  ></div><div class="large-2 columns"><span class="round warning label right btn-remove-img-mful" data-id="'+picture._id+'" data-tab="library" data-trash=no >Remove file</span></div></div></li>');
			}
		}

		function loadMorePicOnLibrary(){
		    btn_l = $('.btn-load-more-im-library');
		    if(btn_l.length>0 && btn_l.attr('data-status') != 'busy' ){
		        page = btn_l.attr('data-page');
		        if(typeof page == 'undefined' || page.length <=0)
		            page = 1;
		        btn_l.attr('data-status','busy');
		        $.ajax({
		            type: 'POST',
		            headers: { "cache-control": "no-cache" },
		            url: addRanParamToUrl(options.url_get),
		            async: true,
		            cache: false,
		            dataType : "json",
		            data: 'page='+page,
		            success: function(jsonData,textStatus,jqXHR)
		            {
		                if(jsonData.status == 'ok'){
		                    if(jsonData.done == 'done')
		                        btn_l.remove();
		                    else
		                        btn_l.attr('data-page', jsonData.page);
		                    for(i=0; i< jsonData.data.length; i++){
		                        picture = jsonData.data[i];
		                        if($('.list-img-on-library .mfpop-item-pic-'+picture._id).length<=0){
		                        	options.list_picture[picture._id] = {trash: 'no' ,picture : picture};
		                            $('.list-img-on-library').append('<li class="divider mfpop-item-pic-'+picture._id+'" ></li><li class="it-img mfpop-item-pic-'+picture._id+'" data-id="'+picture._id+'" data-ref="'+picture._id+'" data-trash="no" data-folder="'+picture.folder+'" data-name="'+picture.name+'" data-type="'+picture.type+'" ><div class="row" ><div class="large-2 columns" ><div class="th" ><img alt="Embedded Image" src="'+picture.folder+'/'+picture.name+'" ></div></div><div class="large-3 columns end" ><span class="lb-pic-ca">'+picture.name+'</span></div><div class="large-4 columns" >'+picture.caption+'</div><div class="large-1 columns" ><input type="checkbox" name="select_mf_library" data-id="'+picture._id+'" class="cbx-mf-slect-img cbx-'+picture._id+'" value="'+picture._id+'"  ></div><div class="large-2 columns"><span class="round warning label right btn-remove-img-mful" data-id="'+picture._id+'" data-tab="library" data-trash=no >Remove file</span></div></div></li>');
		                            $('.ct-library-zone').getNiceScroll().resize();
		                        }
		                    }
		                    btn_l.attr('data-status','ready');
		                }
		            },
		            error: function(XMLHttpRequest, textStatus, errorThrown)
		            {

		            }
		        });
		    }
		}

		function jsCommon(){
			$('#upload-oneshop-popup .nav-upload-pop .head-menu-conten>li>a').on('click',function(e){
				e.preventDefault();
        		e.stopPropagation();
				if(!$(this).parent().hasClass('active')){
					id_tab = $(this).attr('href');
					$(this).parent().addClass('active').siblings().removeClass('active');
					$(id_tab).addClass('active').siblings().removeClass('active');
					if(id_tab != '#cature-cam-ulo' && $('#cature-cam-ulo').hasClass('show-cam'))
			        	removeJsEventTabPopManificUploadCamera();

			        if(id_tab == '#choose-library-ulo'){
			            if(isElementInViewport($('.btn-load-more-im-library')))
			                loadMorePicOnLibrary();
			        }else if(id_tab == '#cature-cam-ulo'){
			            jsTabPopManificUploadCamera();
			        }
			        $(id_tab).addClass('loaded');
				}
			});
			$(document).on('click', '.btn-remove-img-mful', function(e){
			    e.preventDefault();
			    e.stopPropagation();
			    trash = $(this).attr('data-trash');
			    tab = $(this).attr('data-tab');
			    seft = $(this);
			    id = $(this).attr('data-id');
			    if(typeof trash != 'undefined' && $.trim(trash).length>0
			    	&& typeof id != 'undefined' && $.trim(id).length>0 ){
			        if(trash == 'no'){
			            if(typeof id != 'undefined' && $.trim(id).length>0 ){
			                $.ajax({
			                    type: 'POST',
			                    headers: { "cache-control": "no-cache" },
			                    url: addRanParamToUrl(options.url_delete),
			                    async: true,
			                    cache: false,
			                    dataType : "json",
			                    data: 'id='+id+'&trash='+trash,
			                    success: function(jsonData,textStatus,jqXHR)
			                    {
			                        if(jsonData.status == 'ok')
			                            updateViewPopUploadAfterRemove(seft, id, tab, trash);
			                        else
			                            alert(jsonData.msg);
			                    },
			                    error: function(XMLHttpRequest, textStatus, errorThrown)
			                    {
			                        alert('error');
			                    }
			                });
			            }
			        }else{
			            updateViewPopUploadAfterRemove(seft, id, tab, trash);
			        }
			    }else{
			    	$(this).parents('.preview-img-box').first().fadeIn(300,function(){
			    		$(this).remove();
			    	});
			    }
			});

		    $.magnificPopup.open({
			    items: {
			        src: '#upload-oneshop-popup',
			        type: 'inline',
			    },
			    showCloseBtn:true,
			    //modal:true,
			    fixedContentPos:true,
			    callbacks: {
			        open: function() {
			        	$("body").addClass("modal-open");
			        	options.list_picture_select_choose = {};
						options.list_picture_select_drop = {};
						options.list_picture_select_camera = {};
						options.list_picture_select_library = {};
						options.list_picture = {};
			            if($('#choose-library-ulo').hasClass('active')){
			                if(isElementInViewport($('.btn-load-more-im-library')))
			                    loadMorePicOnLibrary();
			            }
			        },
			        close: function() {
			            $("body").removeClass("modal-open");
			            removeJsCommon();
			            removeJsTabPopManificUploadChoose();
			            removeJsTabPopManificUploadDrop();
			            removeJsEventTabPopManificUploadCamera();
			            removeJsEventTabPopManificUploadLibrary();
			        },
			        beforeOpen: function() {
			        },
			        change: function() {
			        },
			        resize: function() {
			            wh = $(window).height();
			            $('.upload-oneshop-popup').height((wh-20));
			            if($('#cature-cam-ulo').hasClass('active')){
			            	$('#cam-mf-canvas').html('').attr('style','');
				            if(typeof set_time != 'undefined'){
					            clearTimeout(set_time);
					            clearInterval(set_time);
					        }
				            set_time = setTimeout(function(){
				            	jsTabPopManificUploadCamera();
				            },1000);
				        }
			        },
			        beforeClose: function() {
			        	if(Object.keys(options.list_picture_select_choose).length>0 
			        		|| Object.keys(options.list_picture_select_drop).length>0
			        		|| Object.keys(options.list_picture_select_camera).length>0
			        		|| Object.keys(options.list_picture_select_library).length>0){
			        		var r = confirm("Ban chac chan muon dong");
							if (r == true)
								return true;
							else
								throw "no close";
			        	}
			        },
			        afterClose: function() {
			        },
			    }
			}, 0);

		}

		function removeJsCommon(){
			$(document).off('click', '.btn-remove-img-mful');
			$('#upload-oneshop-popup .nav-upload-pop .head-menu-conten>li>a').off('click');
		}

		function createContenTabPopManificUploadChoose(){
			html = '';
			if(options.hasUploadChoose == true)
				html = '<section role="tabpanel" aria-hidden="true" class="content active tabs-item-mfu" id="choose-file-ulo" >'+
	                        '<div class="up-by-choofi" >'+
	                            '<div class="note-up-by-choo" >'+
	                                'I am creating a form within a reveal window but on submit i would like to keep the reveal ... I have attached the current code for the form within the modal window I would just .... To reply to this post, sign in or create an account.'+
	                            '</div>'+
	                            '<div class="progress-gb-upmf" >'+
	                                '<div class="progress large-12 success round">'+
	                                    '<span class="meter" style="width: 0%"></span>'+
	                                '</div>'+
	                            '</div>'+
	                            '<div class="control-up-by-choo" >'+
	                                '<span class="button tiny secondary round btn-upload-image btn-upload-imgmf" >Choose image'+
	                                  '<input type="file" multiple="multiple" name="fileuplad" >'+
	                                '</span>'+
	                            '</div>'+
	                            '<div class="list-image-gb-ulmf clearfix" >'+
	                            '</div>'+
	                        '</div>'+
	                        '<div class="btn-box-save-mf-upchoose" >'+
	                        	'<a href="javascript:;" class="button tiny btn-save-mf-upchoose" >ok</a>'+
	                        '</div>'+
	                    '</section>';
            return 	html;
		}

		function createTabPopManificUploadChoose(){
			html = '';
			if(options.hasUploadChoose == true)
				html = '<li class="active" role="presentation" >'+
	                        '<a href="#choose-file-ulo" role="tab" tabindex="0" aria-selected="true" aria-controls="choose-file-ulo" >choose file</a>'+
	                    '</li>';
            return 	html;
		}

		function jsTabPopManificUploadChoose(){
			if(options.hasUploadChoose == true){
				$(".btn-upload-imgmf input[type='file']").html5_upload({
				    url: function(number) {
				        return addRanParamToUrl(options.url_upload);
				    },
				    sendBoundary: window.FormData || $.browser.mozilla,
				    onStart: function(event, total) {
				        $('.progress-gb-upmf .meter').css({'width':'0%'});
				        return true;
				    },
				    onStartOne: function(event, name, number, total) {
				        options.createImagePreview($('.list-image-gb-ulmf'), options.template, event.target.files[number], options.width, options.height , 'small-2 columns end');
				        return true;
				    },
				    onProgress: function(event, progress, name, number, total) {
				        percent = parseInt((number+1)/total)*100;
				        if(isNaN(percent) || percent<0)
				            percent = 0;
				        if(percent > 100)
				            percent = 100;
				        $('.progress-gb-upmf .meter').css({'width':percent+'%'});
				    },
				    setName: function(text) {
				    },
				    setStatus: function(text) {
				    },
				    setProgress: function(val) {
				        percent = val*100;
				        index_box = $('.list-image-gb-ulmf .curent-preview-uplo');
				        index_box.css({'opacity':val});
				        index_box.find('.meter').css({'width':percent+'%'});
				    },
				    onFinishOne: function(event, response, name, number, total) {
				        if(response.constructor === String){
				            response = $.parseJSON(response);
				        }
				        index_box = $('.list-image-gb-ulmf .curent-preview-uplo');
				        if(response.status == 'ok'){
				            if(response.data.trash == false||response.data.trash == 'false'){
				                index_box.removeClass('curent-preview-uplo').addClass('mfpop-item-pic-'+response.data.picture._id).addClass('sucess-upmf').append('<span class="round alert label btn-remove-img-mful" data-id="'+response.data.picture._id+'" data-trash=no data-tab="choose" >remove</span>');
				            	synsUploadFileToLibrary(response.data.picture, 'no');
				            	options.list_picture_select_choose[response.data.picture._id] = {trash: 'no' ,picture : response.data.picture};
				            	options.list_picture[response.data.picture._id] = {trash: 'no' ,picture : response.data.picture};
				            }else{
				            	options.list_picture_select_choose[response.data.random_id+'_trash'] = {trash: 'ok' ,picture : response.data};
				                index_box.removeClass('curent-preview-uplo').addClass('mfpop-item-pic-'+response.data.random_id+'_trash').addClass('sucess-upmf').append('<span class="round alert label btn-remove-img-mful" data-id="'+response.data.random_id+'_trash" data-trash=ok data-tab="choose" >remove</span>');
				            }
				        }else{
				            index_box.removeClass('curent-preview-uplo').addClass('error-upmf').append('<div class="lb-error-img-uoup" ><span class="round alert label">upload error</span></div><span class="round alert label btn-remove-img-mful" data-trash=ok data-tab="choose" >remove</span>');
				        }
				        index_box.find('.progress-holder').remove();
				    },
				    onFinish: function(event, total) {
				    },
				    onError: function(event, name, error) {
				        index_box = $('.list-image-gb-ulmf .curent-preview-uplo');
				        index_box.removeClass('curent-preview-uplo').addClass('error-upmf').append('<div class="lb-error-img-uoup" ><span class="round alert label">upload error</span></div>');
				        index_box.find('.progress-holder').remove();
				    }
				});
				
				$(document).on('click', '.btn-save-mf-upchoose', function(e){
					e.preventDefault();
				    e.stopPropagation();
				    if(Object.keys(options.list_picture_select_choose).length>0){
				    	if(typeof options.callbacks == 'function' ){
				    		options.callbacks(options.list_picture_select_choose,{tab:'choose'});
				    		options.list_picture_select_choose ={};
				    		options.list_picture_select_library ={};
				    		removePopManificUpload();
				    	}
				    }else{
				    	alert('chua co hinh duoc upload');
				    }
				});
			}
		}

		function removeJsTabPopManificUploadChoose(){
			if(options.hasUploadChoose == true){
				$(document).off('click', '.btn-save-mf-upchoose');
			}
		}

		function createContenTabPopManificUploadDrop(){
			html = '';
			if(options.hasUploadDrop == true)
				html = '<section role="tabpanel" aria-hidden="true" class="content tabs-item-mfu" id="drop-file-ulo" >'+
	                        '<div class="drop-zone-mf" >'+
	                            '<div class="cell-drop-xone dropbox-canvas" id="dropbox" >'+
	                                '<div class="ct-drop-zone" >'+
	                                    '<span class="txt-drop-zone" >'+
	                                        'drop here'+
	                                    '</span>'+
	                                '</div>'+
	                            '</div>'+
	                            '<div class="cell-drop-xone dropbox-end" style="display:none" >'+
	                                '<div class="ct-drop-zone" id="img-drop-preview"  >'+
	                                '</div>'+
	                            '</div>'+
	                            '<div class="btn-box-save-mf-uldrop" >'+
		                        	'<a href="javascript:;" class="button tiny btn-save-mf-updrop" >ok</a>'+
		                        '</div>'+
	                        '</div>'+
	                    '</section>';
            return 	html;
		}

		function createTabPopManificUploadDrop(){
			html = '';
			if(options.hasUploadDrop == true)
				html = '<li role="presentation" >'+
	                        '<a href="#drop-file-ulo" role="tab" tabindex="0" aria-selected="true" aria-controls="drop-file-ulo" >Drop file</a>'+
	                    '</li>';
            return 	html;
		}

		function jsTabPopManificUploadDrop(){
			if(options.hasUploadDrop == true){
				$('#dropbox').filedrop({
				    paramname:'user_file[]',    
				    maxfiles: 5,
				    maxfilesize: 2,
				    url: addRanParamToUrl(options.url_upload),   
				    uploadFinished:function(i,file,response){
				        if(response.constructor === String){
				            response = $.parseJSON(response);
				        }
				        console.log(response);
				        index_box = $.data(file);
				        if(response.status == 'ok'){
				        	$('.btn-box-save-mf-uldrop').show();
				            if(response.data.trash == false||response.data.trash == 'false'){
				                index_box.removeClass('curent-preview-uplo').addClass('mfpop-item-pic-'+response.data.picture._id).addClass('sucess-upmf').append('<span class="round alert label btn-remove-img-mful" data-id="'+response.data.picture._id+'" data-trash=no data-tab="drop" >remove</span>');
				            	synsUploadFileToLibrary(response.data.picture , 'no');
				            	options.list_picture_select_drop[response.data.picture._id] = {trash: 'no' ,picture : response.data.picture};
				            	options.list_picture[response.data.picture._id] = {trash: 'no' ,picture : response.data.picture};
				            }else
				            	options.list_picture_select_drop[response.data.random_id+'_trash'] = {trash: 'ok' ,picture : response.data};
				                index_box.removeClass('curent-preview-uplo').addClass('mfpop-item-pic-'+response.data.random_id+'_trash').addClass('sucess-upmf').append('<span class="round alert label btn-remove-img-mful" data-id="'+response.data.random_id+'_trash" data-trash=ok data-tab="drop" >remove</span>');
				        }else{
				            index_box.removeClass('curent-preview-uplo').addClass('error-upmf').append('<div class="lb-error-img-uoup" ><span class="round alert label">upload error</span></div><span class="round alert label btn-remove-img-mful" data-trash=ok data-tab="drop" >remove</span>');
				        }
				        index_box.find('.progress-holder').remove();
				    },
				    error: function(err, file) {
				        switch(err) {
				            case 'BrowserNotSupported':
				                alert('Your browser does not support HTML5 file uploads!');
				                break;
				            case 'TooManyFiles':
				                alert('Too many files! Please select 5 at most! (configurable)');
				                break;
				            case 'FileTooLarge':
				                alert(file.name+' is too large! Please upload files up to 2mb (configurable).');
				                break;
				            default:
				                break;
				        }
				    },
				    beforeEach: function(file){
				        if(!file.type.match(/^image\//)){
				            alert('Only images are allowed!');
				            return false;
				        }
				    },
				    uploadStarted:function(i, file, len){
				        options.createImagePreview($('#img-drop-preview'), options.template, file , options.width , options.width , 'small-2 columns end');
				        $('.dropbox-canvas').hide();
				        $('.dropbox-end').show();
				    },
				    progressUpdated: function(i, file, progress) {
				        val_opac = progress/100;
				        $.data(file).css({'opacity':val_opac});
				        $.data(file).find('.meter').css({'width': progress+'%'});
				    } 
				});

				$(document).on('click', '.btn-save-mf-updrop', function(e){
					e.preventDefault();
				    e.stopPropagation();
					if(Object.keys(options.list_picture_select_drop).length>0){
				    	if(typeof options.callbacks == 'function' ){
				    		options.callbacks(options.list_picture_select_drop,{tab:'drop'});
				    		options.list_picture_select_drop ={};
				    		options.list_picture_select_library ={};
				    		removePopManificUpload();
				    	}
				    }else{
				    	alert('chua co hinh duoc upload');
				    }
				});
			}
		}

		function removeJsTabPopManificUploadDrop(){
			if(options.hasUploadDrop == true){
				$(document).off('click', '.btn-save-mf-updrop');
			}
		}

		function createContenTabPopManificUploadCamera(){
			html = '';
			if(options.hasUploadCapture == true)
				html = '<section role="tabpanel" aria-hidden="true" class="content tabs-item-mfu" id="cature-cam-ulo" >'+
	                        '<div class="cam-zone-mf" >'+
	                            '<div class="cell-cam-xone" >'+
	                                '<div class="ct-cam-zone" id="cam-mf-canvas" ></div>'+
	                            '</div>'+
	                            '<div class="control-cam-xone" >'+
	                            	'<div class="inner-control-cam-xone" >'+
	                            		'<div class="pre_take_buttons" >'+
	                            			'<a href="javascript:void(0);" class="button tiny btn-preview-snapshot" >Take Snapshot</a>'+
	                            		'</div>'+
	                            		'<div class="post_take_buttons" style="display:none" >'+
	                            			'<a href="javascript:void(0);" class="button tiny btn-cancel-snapshot">Take Another</a>'+
	                            			'<a href="javascript:void(0);" class="button tiny btn-save-snapshot">Save Photo</a>'+
	                            		'</div>'+
	                            	'</div>'+
	                            '</div>'+
	                        '</div>'+
	                    '</section>';
            return 	html;
		}

		function createTabPopManificUploadCamera(){
			html = '';
			if(options.hasUploadCapture == true)
				html = '<li role="presentation" >'+
	                        '<a href="#cature-cam-ulo" role="tab" tabindex="0" aria-selected="true" aria-controls="cature-cam-ulo" >Capture camera</a>'+
	                    '</li>';
            return 	html;
		}

		function jsTabPopManificUploadCamera(){
			if(options.hasUploadCapture == true){

				removeJsEventTabPopManificUploadCamera();
				url_shutter = '/styles/js/webcamjs/';
				var shutter = new Audio();
				shutter.autoplay = false;
				shutter.src = navigator.userAgent.match(/Firefox/) ? url_shutter+'shutter.ogg' : url_shutter+'shutter.mp3';

				wcam = $('#cam-mf-canvas').width();
				hcam = $('#cam-mf-canvas').height();
				Webcam.set({
					width: (wcam>640?640:wcam),
			        height: (hcam>480?480:hcam),
			        dest_width: (wcam>640?640:wcam),
			        dest_height: (hcam>480?480:hcam),
			        image_format: 'jpeg',
			        jpeg_quality: 90,
			        force_flash: false,
			        flip_horiz: true,
			        fps: 45
				});
				Webcam.attach( '#cam-mf-canvas' );
				Webcam.on( 'load', function() {
				});

				Webcam.on( 'live', function() {
					$('#cature-cam-ulo').addClass('show-cam');
				});

				Webcam.on( 'error', function(err) {
				    $('#cam-mf-canvas').attr('style','').html('<span class="txt-drop-zone" ><a href="javascript:void(0);" class="button tiny txt-ss-cam" >'+err+'</a></span>');
				});
				Webcam.on( 'uploadProgress', function(progress) {
				});

				Webcam.on( 'uploadComplete', function(code, text) {
				});
				
				$('.btn-preview-snapshot').off('click').on('click',function() {
					try { shutter.currentTime = 0; } catch(e) {}
					shutter.play();
					try {
						Webcam.freeze();
					} catch(e) {Webcam.reset();}
					$('.pre_take_buttons').hide();
					$('.post_take_buttons').show();
				});

				$('.btn-cancel-snapshot').off('click').on('click',function() {
					try {
						Webcam.freeze();
					} catch(e) {Webcam.reset();}
					$('.pre_take_buttons').show();
					$('.post_take_buttons').hide();
				});

				$('.btn-save-snapshot').off('click').on('click',function() {
					Webcam.snap( function(data_uri) {
						console.log(data_uri);
						$.ajax({
				            type: 'POST',
				            headers: { "cache-control": "no-cache" },
				            url: addRanParamToUrl(options.url_upload_base64),
				            async: true,
				            cache: false,
				            dataType : "json",
				            data: 'data='+data_uri,
				            success: function(jsonData,textStatus,jqXHR)
				            {
				            	console.log(jsonData);
				                if(jsonData.status == 'ok'){
				                    
				                }
				            },
				            error: function(XMLHttpRequest, textStatus, errorThrown)
				            {

				            }
				        });
					} );
				});
			}
		}

		function removeJsEventTabPopManificUploadCamera(){
			if(options.hasUploadCapture == true && $('#cature-cam-ulo').hasClass('show-cam')){
				$('#cam-mf-canvas').html('').attr('style','');
				Webcam.reset();
				$('#cature-cam-ulo').removeClass('show-cam');
				$('.btn-preview-snapshot').off('click');
				$('.btn-cancel-snapshot').off('click');
				$('.btn-save-snapshot').off('click');
			}
		}

		function createContenTabPopManificUploadLibrary(){
			html = '';
			if(options.hasLibrary == true)
				html = '<section role="tabpanel" aria-hidden="true" class="content tabs-item-mfu" id="choose-library-ulo" >'+
	                        '<div class="cell-library-zone" >'+
	                            '<div class="ct-library-zone" >'+
	                                '<div class="list-img-on-library side-nav" ></div>'+
	                                '<div class="loadmore-img-on-library" >'+
	                                    '<a href="javascript:;" class="button expand tiny secondary btn-load-more-im-library" data-status="ready" >Load more</a>'+
	                                '</div>'+
	                            '</div>'+
	                        '</div>'+
	                        '<div class="btn-box-save-mf-ullibrary" >'+
	                        	'<a href="javascript:;" class="button tiny btn-save-mf-ullibrary" >ok</a>'+
	                        '</div>'+
	                    '</section>';
            return 	html;
		}

		function createTabPopManificUploadLibrary(){
			html = '';
			if(options.hasLibrary == true)
				html = '<li role="presentation" >'+
	                        '<a href="#choose-library-ulo" role="tab" tabindex="0" aria-selected="true" aria-controls="choose-library-ulo" >library</a>'+
	                    '</li>';
            return 	html;
		}

		function jsTabPopManificUploadLibrary(){
			if(options.hasLibrary == true){
				$(document).on('click', '.btn-load-more-im-library', function(e){
				    e.preventDefault();
				    e.stopPropagation();
				    loadMorePicOnLibrary();
				});

				$(document).on('click', '.cbx-mf-slect-img', function(e){

				    id_ = $(this).val();
				    if(options.list_picture.hasOwnProperty(id_)){
				    	picture = options.list_picture[id_];
				    	if($(this).is(':checked')){
				    		if(picture)
				    			options.list_picture_select_library[id_] = picture;
				    	}else{
				    		if(options.list_picture_select_library.hasOwnProperty(id_))
				    			delete options.list_picture_select_library[id_];
				    	}
				    }

				});

				$(document).on('click', '.btn-save-mf-ullibrary', function(e){
					e.preventDefault();
				    e.stopPropagation();

				    if(Object.keys(options.list_picture_select_library).length>0){
				    	if(typeof options.callbacks == 'function' ){
				    		options.callbacks(options.list_picture_select_library,{tab:'library'});
				    		options.list_picture_select_drop ={};
				    		options.list_picture_select_choose ={};
				    		options.list_picture_select_library ={};
				    		removePopManificUpload();
				    	}
				    }else{
				    	alert('chua co hinh duoc chon');
				    }
				});

				$('.ct-library-zone').niceScroll({autohidemode: false, cursorcolor:"#00F"});
			    $('.ct-library-zone').niceScroll().scrollend(function(info){
			        if(isElementInViewport($('.btn-load-more-im-library')))
			            loadMorePicOnLibrary();
			    });
			}
		}

		function removeJsEventTabPopManificUploadLibrary(){
			if(options.hasLibrary == true){
				$(document).off('click', '.btn-load-more-im-library');
				$(document).off('click', '.cbx-mf-slect-img');
				$(document).off('click', '.btn-save-mf-ullibrary');
				$('.ct-library-zone').getNiceScroll().hide();
			}
		}

        function createPopManificUpload() {
        	removePopManificUpload();

        	templet = '<div id="upload-oneshop-popup" class="upload-oneshop-popup mfp-hide">'+
					    '<div class="up-mfct-popup" >'+
					        '<div class="upload-mfct-popup" >'+
					            '<div class="nav-upload-pop" >'+
					                '<ul class="head-menu-conten clearfix" data-tab role="tablist" >'+

					                    createTabPopManificUploadChoose()+

					                    createTabPopManificUploadDrop()+

					                    createTabPopManificUploadCamera()+

					                    createTabPopManificUploadLibrary()+

					                '</ul>'+
					            '</div>'+

					            '<div class="tabs-upload-pop" >'+
					                '<div class="tabs-content tab-con-mfu" >'+

					                    createContenTabPopManificUploadChoose()+

					                    createContenTabPopManificUploadDrop()+

					                    createContenTabPopManificUploadCamera()+

					                    createContenTabPopManificUploadLibrary()+

					                '</div>'+
					            '</div>'+
					        '</div>'+
					    '</div>'+
					'</div>';
			$('body').append(templet);

			jsCommon();
			jsTabPopManificUploadChoose();
			jsTabPopManificUploadDrop();
			/*jsTabPopManificUploadCamera();*/
			jsTabPopManificUploadLibrary();

            return true;
        }

        function removePopManificUpload() {
        	try {
	        	if($('#upload-oneshop-popup').length>0){
	        		$('#upload-oneshop-popup').remove();
	        		var magnificPopup = $.magnificPopup.instance;
					magnificPopup.close();
	        	}
        	}catch (ex) {console.log(ex);}
        }

        function showPopManificUpload(e) {
        	e.preventDefault();
    		e.stopPropagation();

        	createPopManificUpload();
            return true;
        }

        try {
            return this.each(function() {
				var obj = $(this);
				obj.off('click').on('click', showPopManificUpload);
            });
        }catch (ex) {
        	console.log(ex);
            return false;
        }
    };
})(jQuery);

var _checkInstanceModalUpload = function() {
	if(!$.modal_upload.instance) {
		$.modal_upload.instance = $('<a href="javascript:void(0);" class="modal_upload-instance-'+new Date().getTime()+'" ></a>');
		$.modal_upload.instance.modal_upload({
		    callbacks: function(response, exdata) {
		        $.modal_upload.callbacks(response, exdata);
		    },
		    error: function(error) {
		        $.modal_upload.error(error);
		    }
		});
	}
};

$.modal_upload = {
	instance: null,
	callbacks:function(response, exdata){
		console.log(response);
	},
	error:function(e){
		console.log(e);
	},
	open: function(options, index) {
		_checkInstanceModalUpload();
		return this.instance.trigger('click');
	},

	close: function() {
		return $.modal_upload.instance && $.modal_upload.instance.close();
	},
};
_checkInstanceModalUpload();