/*!
 * fileDropUpload.js v0.4.5
 */

var fileDropUpload = (function(global) {
	"use strict";
	var doc = document;
	var fileDropUpload = function(options) {
		var self = this,
		i,     
		options_to_be_deleted = ['el', 'maxfiles', 'maxfilesize', 'paramname', 'url', 'uploadFinished', 'error', 'beforeEach', 'uploadStarted', 'progressUpdated'],
		container_ = options.el;
		this.version = '1.0';
		this.loaded = false; 
		this.el = null;
		this.maxfiles = options.el || 1 ;
		this.maxfilesize = options.el || 1 ;//2 MBs
		this.paramname = options.paramname || null;
		this.url = options.url || null;
		this.uploadFinished = options.uploadFinished || function(i,file,response){};
		this.error = options.error || function(err, file) {
			switch(err) {
				case 'BrowserNotSupported':
					showMessage('Your browser does not support HTML5 file uploads!');
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
		};
		this.beforeEach = options.beforeEach || function(file){
			if(!file.type.match(/^image\//)){
				alert('Only images are allowed!');
				return false;
			}
		};
		this.uploadStarted = options.uploadStarted || function(i, file, len){};
		this.progressUpdated = options.progressUpdated || function(i, file, progress) {};

		if (typeof(options.el) === 'string') {
			this.el = $(container_, options.context);
		} else {
			this.el = container_;
		}
		if (typeof(this.el) === 'undefined' || this.el === null) {
		  throw 'No element defined.';
		}
		
		for (i = 0; i < options_to_be_deleted.length; i++) {
			delete options[options_to_be_deleted[i]];
		}
			
		//this.configure();
	}

	return fileDropUpload;
})(this);

fileDropUpload.prototype.getVersion = function() {
	return this.version;
};

fileDropUpload.prototype.getLoaded= function() {
	return this.loaded;
};

fileDropUpload.prototype.setContainer= function(el) {
	if (typeof(el) === 'string') {
		this.el = $(el);
	} else {
		this.el = el;
	}
	if (typeof(this.el) === 'undefined' || this.el === null) {
	  throw 'No element defined.';
	}
	return this.el;
};

fileDropUpload.prototype.getContainer= function() {
	return this.el;
};

fileDropUpload.prototype.setMaxFiles= function(maxfiles) {
	this.maxfiles = maxfiles;
	return this.maxfiles;
};

fileDropUpload.prototype.getMaxFiles= function() {
	return this.maxfiles;
};

fileDropUpload.prototype.setMaxFileSize= function(maxfilesize) {
	this.maxfilesize = maxfilesize;
	return this.maxfilesize;
};

fileDropUpload.prototype.getMaxFileSize= function() {
	return this.maxfilesize;
};

fileDropUpload.prototype.setParamName= function(paramname) {
	this.paramname = paramname;
	return this.paramname;
};

fileDropUpload.prototype.getParamName= function() {
	return this.paramname;
};

fileDropUpload.prototype.setUrl= function(url) {
	this.url = url;
	return this.url;
};

fileDropUpload.prototype.getUrl= function() {
	return this.url;
};

fileDropUpload.prototype.setUploadFinished= function(uploadFinished) {
	if (typeof(uploadFinished) === 'function') {
		this.uploadFinished = uploadFinished;
    } else {
		this.uploadFinished = '';
    }
	return this.uploadFinished;
};

fileDropUpload.prototype.getUploadFinished= function() {
	return this.uploadFinished;
};

fileDropUpload.prototype.setError= function(error) {
	if (typeof(error) === 'function') {
		this.error = error;
    } else {
		this.error = '';
    }
	return this.error;
};

fileDropUpload.prototype.getError= function() {
	return this.error;
};

fileDropUpload.prototype.setBeforeEach= function(beforeEach) {
	if (typeof(beforeEach) === 'function') {
		this.beforeEach = beforeEach;
    } else {
		this.beforeEach = '';
    }
	return this.beforeEach;
};

fileDropUpload.prototype.getBeforeEach= function() {
	return this.beforeEach;
};

fileDropUpload.prototype.setUploadStarted= function(uploadStarted) {
	if (typeof(uploadStarted) === 'function') {
		this.uploadStarted = uploadStarted;
    } else {
		this.uploadStarted = '';
    }
	return this.uploadStarted;
};

fileDropUpload.prototype.getUploadStarted= function() {
	return this.uploadStarted;
};

fileDropUpload.prototype.setProgressUpdated = function(progressUpdated) {
	if (typeof(progressUpdated) === 'function') {
		this.progressUpdated = progressUpdated;
    } else {
		this.progressUpdated = '';
    }
	return this.progressUpdated;
};

fileDropUpload.prototype.getProgressUpdated = function() {
	return this.progressUpdated;
};


fileDropUpload.prototype.configure = function() {
	var self = this;
	this.getContainer().filedrop({
		paramname:self.getParamName(),	
		maxfiles: self.getMaxFiles(),
		maxfilesize: self.getMaxFileSize(),
		url: self.getUrl(),	
		uploadFinished:function(i,file,response){console.log(response);
			var data = response;
			if(data.constructor === String){
				data = $.parseJSON(data);
			}
			self.getUploadFinished()(i,file,response);
		},
		error: function(err, file) {console.log(err);
			self.getError()(err, file);
		},
		beforeEach: function(file){console.log(file);
			self.getBeforeEach()(file);
		},
		uploadStarted:function(i, file, len){console.log(file);
			self.getUploadStarted()(i, file, len);
		},
		progressUpdated: function(i, file, progress) {console.log(progress);
			self.getProgressUpdated()(i, file, progress);
		} 
	});
};

//==========================
// Array indexOf
// https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/indexOf
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
      "use strict";
      if (this == null) {
          throw new TypeError();
      }
      var t = Object(this);
      var len = t.length >>> 0;
      if (len === 0) {
          return -1;
      }
      var n = 0;
      if (arguments.length > 1) {
          n = Number(arguments[1]);
          if (n != n) { // shortcut for verifying if it's NaN
              n = 0;
          } else if (n != 0 && n != Infinity && n != -Infinity) {
              n = (n > 0 || -1) * Math.floor(Math.abs(n));
          }
      }
      if (n >= len) {
          return -1;
      }
      var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
      for (; k < len; k++) {
          if (k in t && t[k] === searchElement) {
              return k;
          }
      }
      return -1;
  }
}