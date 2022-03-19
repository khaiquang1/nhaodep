/*
Global Functions V2.1.2
*/
var sUTMSrc = '',sUTMCamp = '',sUTMMedium = '',sUTMTerm = '',sUTMContent = '',sGCLID = '',sDBMode = '',sVTok = '';
var cp_peid;
var cp_loopcnt = 0;
var cp_maxcnt = 3;
var cp_loopint = 3000;
var formAttrList = ["id","name","class"]; // Form form blacklist attribute
var fieldAttrList = ["id","name","type"] ;// Form form blacklist attribute
var formLists = [];
var formTypeCP = ""
var submitFormPattern = "";
var buttonActionCP = "submit";


var sAppHost = 'https://cdn.salesdy.com/';
var sAPIHost = 'https://cdn.salesdy.com/';
var cpSubmitUrl = sAPIHost + "SalesDy/v1.0/";


var $ = jQuery.noConflict();
// To get URL query
function getQS(key) {
    var cpQSUriEncode = encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&")
    var cpRegR = new RegExp("^(?:.*[&\\?]" + cpQSUriEncode + "(?:\\=([^&]*))?)?.*$", "i")
    return decodeURIComponent(window.location.search.replace(cpRegR, "$1"));
}
function delCookies(key) {
    document.cookie = key + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function clrCookies() {
    delCookies("cp_utm_s");
    delCookies("cp_utm_c");
    delCookies("cp_utm_m");
    delCookies("cp_utm_t");
    delCookies("cp_utm_o");
    delCookies("cp_gclid");
}

// To Generate cookie for TrackingId and Campaign Tracking
// To Generate cookie for TrackingId and Campaign Tracking

function cpCookieHolder(){
    if (typeof $.cookie === 'function')
    {
        console.log('$.cookie');
        if (sUTMSrc.length > 0) $.cookie('cp_utm_s', sUTMSrc, { path: '/' }); else sUTMSrc = $.cookie('cp_utm_s');
        if (sUTMCamp.length > 0) $.cookie('cp_utm_c', sUTMCamp, { path: '/' }); else sUTMCamp = $.cookie('cp_utm_c');
        if (sUTMMedium.length > 0) $.cookie('cp_utm_m', sUTMMedium, { path: '/' }); else sUTMMedium = $.cookie('cp_utm_m');
        if (sUTMTerm.length > 0) $.cookie('cp_utm_t', sUTMTerm, { path: '/' }); else sUTMTerm = $.cookie('cp_utm_t');
        if (sUTMContent.length > 0) $.cookie('cp_utm_o', sUTMContent, { path: '/' }); else sUTMContent = $.cookie('cp_utm_o');
        if (sGCLID.length > 0) $.cookie('cp_gclid', sGCLID, { path: '/' }); else sGCLID = $.cookie('cp_gclid');
    }
    else if (typeof Cookies === 'function') {
        console.log('Cookies');
        if (sUTMSrc.length > 0) Cookies.set ('cp_utm_s', sUTMSrc); else sUTMSrc = Cookies.get ('cp_utm_s');
        if (sUTMCamp.length > 0) Cookies.set ('cp_utm_c', sUTMCamp); else sUTMCamp = Cookies.get ('cp_utm_c');
        if (sUTMMedium.length > 0) Cookies.set ('cp_utm_m', sUTMMedium); else sUTMMedium = Cookies.get ('cp_utm_m');
        if (sUTMTerm.length > 0) Cookies.set ('cp_utm_t', sUTMTerm); else sUTMTerm = Cookies.get ('cp_utm_t');
        if (sUTMContent.length > 0) Cookies.set ('cp_utm_o', sUTMContent); else sUTMContent = Cookies.get ('cp_utm_o');
        if (sGCLID.length > 0) Cookies.set ('cp_gclid', sGCLID); else sGCLID = Cookies.get ('cp_gclid');
    }

    return true;
}

function initCP() {
    sUTMSrc = getQS("utm_source");
    sUTMCamp = getQS("utm_campaign");
    sUTMMedium = getQS("utm_medium");
    sUTMTerm = getQS("utm_term");
    sUTMContent = getQS("utm_content");
    sGCLID = getQS("gclid");
    sDBMode = getQS("dbmode");
    sVTok = getQS("tok");

    var ready = false;
    var clog = '';

    cpCookieHolder()

    var cpReady1 = 'a[id^=lp-pom-button-]'
    var cpReady2 = 'input[type="submit"][value="SUBMIT"],[type="submit"][value="submit"],[type="submit"][value="REGISTER NOW"]'
    var cpReady3 = 'input[type=submit][value=SUBMIT],[type=submit][value=submit],[type=submit][value=REGISTER NOW]'
    var cpReady4 = 'input[type="submit"][class="button"]'
    var cpReady5 = 'input[type=submit][class=button]'
    var cpReady6 = 'a[data-target="#"][class*="bg-red"]'
    var cpReady7 = 'a[data-target=#][class*=bg-red]'


    var formPattern = 'form:not([class*="search"],[name*="loan"]):first'
    var formPattern2 = 'form:not([class*=search],[name*=loan]):first'

    var cpChecker = [
        {"name" : "cpCheckr1","pattern" : cpReady1, "clog" : "$(cpReady1) > 0 \r\n"},
        {"name" : "cpCheckr2","pattern" : formPattern + ' input', "clog" : "$('" + formPattern2 + " input') > 0 \r\n"},
        {"name" : "cpCheckr3","pattern" : 'form:first button', "clog" : "$('form:first button') > 0 \r\n"},
        {"name" : "cpCheckr4","pattern" : formPattern + ' ' + cpReady2, "clog" : "$(" + formPattern2 + " " + cpReady3 + ") > 0 \r\n"},
        {"name" : "cpCheckr5","pattern" : formPattern + ' ' + cpReady4, "clog" : "$('" + formPattern2 +"  "  + cpReady5 + "') > 0 \r\n"},
        {"name" : "cpCheckr6","pattern" : formPattern + ' ' + cpReady6, "clog" : "$('" + formPattern2 + "  " + cpReady7 + "') > 0 \r\n"}
    ]

    for(var zcp = 0; zcp < cpChecker.length; zcp++){
        if($(cpChecker[zcp]["pattern"]).length > 0){
            ready = true
            clog += cpChecker[zcp]["name"] + " ==> "+ cpChecker[zcp]["pattern"]
        }
    }

    if (sDBMode == '1') {
        console.log("ready",ready)
        console.log("clog",clog);
        console.log('loop: ' + cp_loopcnt);
    }

    if (ready === true) {
        initCP_delay();
    } else {
        if (cp_loopcnt < cp_maxcnt) {
            setTimeout(function () { initCP(); }, cp_loopint);
            cp_loopcnt++;
        } else if (sDBMode == '1') {
            console.log('Stopped at loop count: ' + cp_loopcnt);
        }
    }

    // To check need to load adBlock js
    _CPABFlag()
}
function _CPABFlag(){
    var urlScript = sAppHost + 'SalesDy/Pixel/?PSID=' + cp_psid + '&URL=';
    console.log("urlScript",urlScript)

    if(window.XDomainRequest){
        // This code add for IE 8 & 9 compatibility.
        // Not able to handle JQuery AJAX cause of CORS- cross domain

        var xdr = new XDomainRequest();

        xdr.open("POST",urlScript);
        xdr.send();
        xdr.onerror = function(e) {
            console.log('Verify token failed. (svr bz) ');
            console.log('Err: ' + urlScript);
        };
        xdr.onload = function() {
            console.log(xdr.responseText);
            var data = JSON.parse(xdr.responseText)
            console.log(data)
            if (data['Ret'] == '0' && data['ABFlag'] == '1') {
                var script = new Array();
                script.push(sAPIHost + 'js/' + data['URL']);
                cp_loaddscript(script, null);
            }
        }

    }else{

        $.ajax({
            url: urlScript,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                console.log(data)
                if (data['Ret'] == '0' && data['ABFlag'] == '1') {
                    var script = new Array();
                    script.push(sAPIHost + 'js/' + data['URL']);
                    cp_loaddscript(script, null);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log('Err: '+urlScript);
            }
        });
    }
}

function initCP_delay() {


    if (typeof cpCustomFormPattern !== 'undefined' && (cpCustomFormPattern != '' || cpCustomFormPattern.length > 0)){
        console.log("CustomPattern",cpCustomFormPattern)
        detectPattern(cpCustomFormPattern , "cpCustomFormPattern")

        if (sDBMode == '1') {
            alert(cpCustomFormPattern + ' : ' + $(cpCustomFormPattern).length);
        }

    }else{

        var excludePattern = 'form:not([class*="earch"],[action*="earch"],[name*="loan"],[id="crForm"]):first';
        var excludePattern2 = 'form:not([class*="earch"],[action*="earch"],[name*="loan"]):first'
        var excludePatternBtn = 'button:not([id="btnSubmitCR"])'
        var inputPattern = 'input[type="submit"][value="SUBMIT"],[type="submit"][value="submit"],[type="submit"][value="REGISTER NOW"],[type="button"][value="Submit"],[type="submit"][value="Submit"],[type="submit"][name="submit"],[type="submit"][name="Submit"]'
        var inputPattern2 = 'input[type="submit"][class="button"]'
        var anchorPattern = 'a[data-target="#"][class*="bg-red"]'
        var anchorPattern2 = 'a[href="#"][class="button"]'
        var anchorPattern3 = 'a[id^=lp-pom-button-]'
        var catchAllForm = 'form:not([class*="earch"],[action*="earch"],[name*="loan"],[id="crForm"]) input[type=submit]'

        var patternRules = [
            {"name":"ptn1","pattern" : anchorPattern3},
            {"name":"ptn2", "pattern" : excludePattern + ' ' + excludePatternBtn},
            {"name":"ptn3", "pattern" : excludePattern + ' ' + inputPattern},
            {"name": "customPattern_OIB", "pattern" :excludePattern + ' ' + inputPattern2},
            {"name": "customPattern_HLB", "pattern" :excludePattern + ' ' + anchorPattern},
            {"name": "ptn4", "pattern" :'form ' + anchorPattern3},
            {"name": "ptnAll", "pattern" : catchAllForm }
        ]

        var cpAlert = ""
        var mLoCp = 0
        while(mLoCp < patternRules.length){
            cpAlert += patternRules[mLoCp].name
            cpAlert += patternRules[mLoCp].pattern + " : " + $(patternRules[mLoCp].pattern).length
            cpAlert += '\r\n'

            detectPattern(patternRules[mLoCp].pattern , patternRules[mLoCp].name)

            mLoCp++
        }

        if (sDBMode == '1') {
            alert(cpAlert);
        }

        $(excludePattern).submit(function() { console.log('submit');SendCP().then(function(e){})["catch"](function(e){}); });
    }

    if (sVTok.length > 0) {
        cpOff()
    }
}


// OFFLINE TEST BY PREVIOUS PROGRAMMER
function cpOff(){
    var urlCpOff = sAppHost + 'SalesDy/Verify/?Token=&PSID=' + cp_psid + '&URL=&VCode=' + sVTok

    if(window.XDomainRequest){
        // This code add for IE 8 & 9 compatibility.
        // Not able to handle JQuery AJAX cause of CORS- cross domain

        var xdr = new XDomainRequest();

        xdr.open("POST",urlCpOff);
        xdr.send();
        xdr.onerror = function(e) {
            console.log('Verify token failed. (svr bz) ');
            // console.log('in error',xdr.responseText);
        };
        xdr.onload = function() {
            console.log(xdr.responseText);
            var data = JSON.parse(xdr.responseText)
            console.log(data)
            if (data.Ret == '0')
                alert('SalesdyPixel installed sucessfully.');
            else
                console.log('Verify token failed: invalid token.');
        }

    }else{

        $.ajax({
            url: urlCpOff,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                if (data.Ret == '0')
                    alert('CandyPixel installed sucessfully.');
                else
                    console.log('Verify token failed: invalid token.');
            },
            error: function () {
                console.log('Verify token failed. (svr bz) ');
            }
        });
    }
}

function submitForm(strLog) {
    SendCP().then(function(e){})["catch"](function(e){});

    if (sDBMode=='1') {
        // we are in debug mode
        console.log("submitForm",strLog);
    }
}

function detectPattern(pattern,message) {

    $(pattern).click(function(){
        console.log("click",message)
        submitForm(message)

    });

    $(pattern).on('touchstart',function(){
        console.log("touch",message)
        submitForm(message)

    });
}


function SendCP() {
    return new Promise(function(resolve,reject){
        if (bLock) { console.log('Lock'); return reject(); }
        var now = new Date();
        if (now.getTime() - dLast < 2000) { console.log('Dup'); return reject(); }
        var sFormData = '';
        var sPVal = '';

        if ($('#btnSubmitCR').length > 0 && $('#txtCRPhone').val() == "") return reject();

        var curl = window.location.href.toLowerCase();
        if (curl.indexOf("ioiproperties.com.my") >= 0) {
            if ($('form[id=inlineEnquiry]').length > 0) {
                var fa = $('form[id=inlineEnquiry]').attr('action');
                if (typeof fa !== 'undefined' && fa != null) {
                    var q = fa.substring(fa.indexOf('?'), fa.length);
                    var key = 'pid';
                    sPVal = q.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1");
                    sFormData = '"' + cp_peid + '":"' + sPVal + '"';
                }
            }
        }

        $.each($('form input:text,input[type="tel"],input[type="email"],input[type="number"],textarea'), function(index, formField) {
            if ($.trim($(formField).val()).length > 0) {
                var sFldName = $(formField).attr('name');
                var sFldID = $(formField).attr('id');
                var sV = $.trim($(formField).val());
                try { if (typeof $.parseJSON(sV) == 'number') {sV = '"'+sV+'"';}} catch(err) { sV = '"'+sV+'"';}
                sFormData += ((sFormData.length > 0) ? ', ' : '') + '"' + ((typeof sFldName === "undefined") ? sFldID : sFldName) + '":' + sV;
            }
        });

        $.each($('form select'), function(index, formField) {
            if ($.trim($('option:selected', formField).val()).length > 0) {
                var sFldName = $(formField).attr('name');
                var sFldID = $(formField).attr('id');
                var sV = $.trim($('option:selected', formField).val());
                var sT = $.trim($('option:selected', formField).text());
                if (sFldID == cp_peid || sFldName == cp_peid) sPVal = sV;
                try { if (typeof $.parseJSON(sV) == 'number') {sV = '"'+sV+'"';}} catch(err) { sV = '"'+sV+'"';}
                if (sFldID == cp_peid)
                    sFormData += ((sFormData.length > 0) ? ', ' : '') + '"' + sFldID + '":' + sV;
                else
                    sFormData += ((sFormData.length > 0) ? ', ' : '') + '"' + ((typeof sFldName === "undefined") ? sFldID : sFldName) + '":' + sV;
            }
        });

        $.each($('form input:checkbox'), function(index, formField) {
            if ($(formField).is(':checked') == true) {
                var sFldName = $(formField).attr('name');
                var sFldID = $(formField).attr('id');
                var sV = true;
                try { if (typeof $.parseJSON(sV) == 'number') {sV = '"'+sV+'"';}} catch(err) { sV = '"'+sV+'"';}
                sFormData += ((sFormData.length > 0) ? ', ' : '') + '"' + ((typeof sFldName === "undefined") ? sFldID : sFldName) + '":' + sV;
            }
        });

        if ('' == sFormData) { console.log('No Data'); return reject();}
        sFormData = '{' + sFormData + '}';

        var Cp_PRM = cp_token + '&UTM_Source=' + ((typeof sUTMSrc === "undefined") ? "" : sUTMSrc) + '&UTM_Campaign=' + ((typeof sUTMCamp === "undefined") ? "" : sUTMCamp) + '&UTM_Medium=' + ((typeof sUTMMedium === "undefined") ? "" : sUTMMedium) + '&UTM_Term=' + ((typeof sUTMTerm === "undefined") ? "" : sUTMTerm) + '&UTM_Content=' + ((typeof sUTMContent === "undefined") ? "" : sUTMContent) + '&GCLID=' + ((typeof sGCLID === "undefined") ? "" : sGCLID) + '&PID=' + cp_pid + '&PSID=' + cp_psid + '&PVal=' + sPVal + '&URL=' + encodeURIComponent(window.location.href.toLowerCase());

        bLock = true;

        if(window.XDomainRequest){
            // Using Proxy
            var xDurl = sAPIHost + 'Pixel/?Token=' + Cp_PRM

            XDdataUri = encodeURIComponent(sFormData)
            XDdataUri = XDdataUri.replace(/%20/g,"+")

            XDdata = "data=" + XDdataUri

            var xdr = new XDomainRequest();
            xdr.open("POST",xDurl);
            xdr.send(XDdata);
            xdr.onerror = function(e) {
                console.log('in error',xdr.responseText);
                console.log('Err: '+url); console.log(sFormData);
                now = new Date(); dLast = now.getTime(); bLock = false; clrCookies(); return reject();
            };
            xdr.onload = function() {
                console.log(xdr.responseText);
                now = new Date(); dLast = now.getTime(); bLock = false; return resolve();
            }

        } else {

            var url = cpSubmitUrl + '?Token=' + Cp_PRM

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: { data: sFormData },
                success: function (data) { console.log(data); now = new Date(); dLast = now.getTime(); bLock = false; clrCookies(); return resolve(); },
                error: function (xhr, ajaxOptions, thrownError) { console.log('Err: '+url); console.log(sFormData); now = new Date(); dLast = now.getTime(); bLock = false; return reject(); }
            });
        }
    })
}
function cleanFromBlackListForm() {
    var cleanForm = []
    
    $("form").each(function(e) {
        var flag = true;
        var captureAttr = "form";
        $.each(this.attributes, function() {
            if (this.specified) {
                if(formAttrList.indexOf(this.name) != -1){
                    captureAttr += "[" + this.name + "=\"" + this.value + "\"]";
                }
                
                if(!filterBlackLists(this.value, blackListForm)){
                    flag = false;
                }
            }
        });
        if(flag){
            cleanForm.push(captureAttr)
        }
    });

    return cleanForm;
}

function filterBlackLists(_attr, _blacklist){
    var flag = true 
    $.each(_blacklist, function(e, v){
        var check = new RegExp(v,"gi")
        var matcher = _attr.match(check)

        if(matcher !==  null){
            // console.log("CPdebug: match?",_attr.match(check))
            flag = false
        }
        
    })
    return flag;
}

function filterBlackListsField(_attr,_blacklist){
    var flag = true 
    $.each(_attr, function() {
        if(this.specified && fieldAttrList.indexOf(this.name) != -1){
            
            if(!filterBlackLists(this.value,blackListField)){
                flag = false;
            }
        }
    });

   return flag;
}

function inputTextCollector(){
    var sFormData = {}
    $.each(
        $(
            submitFormPattern + ' input:text,input[type="tel"],input[type="email"],input[type="number"],textarea'
        ),
        function(index, formField) {
            var blackListStatus = filterBlackListsField(this.attributes,blackListField)

            if(blackListStatus){
                if ($.trim($(formField).val()).length > 0) {
                    var sFldName = $(formField).attr("name");
                    var sFldID = $(formField).attr("id");
                    var sV = $.trim($(formField).val());
                    
                    sFormData[(typeof sFldName === "undefined" ? sFldID : sFldName) ] = sV
                    
                }
            }
            
        }
    );
    // console.log("CPdebug: sFormData input",sFormData)
    return sFormData;
}

function inputSelectCollector(){
    var sFormData = {};
    var sPVal = "";
    $.each($(submitFormPattern + " select"), function(index, formField) {
        var blackListStatus = filterBlackListsField(this.attributes,blackListField)
        if(blackListStatus){
            if ($.trim($("option:selected", formField).val()).length > 0) {
                var sFldName = $(formField).attr("name");
                var sFldID = $(formField).attr("id");
                var sV = $.trim($("option:selected", formField).val());
                
                if (sFldID == cp_peid || sFldName == cp_peid) sPVal = sV;
                
                if (sFldID == cp_peid){
                    sFormData[sFldID] = sV;
                } 
                else{
                    sFormData[(typeof sFldName === "undefined" ? sFldID : sFldName)] = sV;
                }
            }
        }
    });
    var data = {"sFormData": sFormData, "sPVal": sPVal}
    // console.log("CPdebug: sFormData input",sFormData)
    return data;
}

function inputCheckboxCollector(){
    var sFormData = {}
    $.each($(submitFormPattern + " input:checkbox"), function(index, formField) {
        var blackListStatus = filterBlackListsField(this.attributes,blackListField)
        if(blackListStatus){
            if ($(formField).is(":checked") == true) {
                var sFldName = $(formField).attr("name");
                var sFldID = $(formField).attr("id");
                var sV = true;
                sFormData[(typeof sFldName === "undefined" ? sFldID : sFldName)] = sV;
            }
        }
    });

    return sFormData;
}

// This is for manual trigger. User can submit form with this function when they ready to submit
function cpReadySubmit(_formId, _cp_token, _cp_pid, _cp_psid, _cp_peid){
    if(_formId !== undefined){
        submitFormPattern = _formId;
    }

    if(_cp_token !== undefined){
        cp_token = _cp_token;
    }

    if(_cp_pid !== undefined){
        cp_pid = _cp_pid;
    }

    if(_cp_psid !== undefined){
        cp_psid = _cp_psid;
    }

    if(_cp_psid !== undefined){
        cp_peid = _cp_peid;
    }

    SendCP()
        .then(function(e) {})
        ["catch"](function(e) { console.log("CPdebug: ",e) });
    return false;
}
function specialHandle() {
    var url = window.location.href.toLowerCase();

    if (url.indexOf("ioiproperties.com.my") >= 0 || url.indexOf("oojibo.hopto.org") >= 0) {
        console.log('SH: ' + url);
        $('section.general a[data-osp="114"]').on('touchstart click', function (e) {
            setTimeout(function () {
                console.log('section.general a[data-osp="114"]');
                initCP();
            }, 3000);
        });
        $('div.social-holder>a[data-itemid=1448], div.btn-holder.visible-xs a[data-itemid=1448]').on('click touchstart', function (e) {
            setTimeout(function () {
                console.log('$(\'div.social-holder>a[data-itemid=1448]\').bind(\'click\')');
                initCP();
            }, 3000);
        });
    }
    if (url.indexOf("senadakl") >= 0) {
        $("#gform_1").on('submit', function(event) {
            SendCP().then(function(e){})["catch"](function(e){});
        });
    }
}

$(document).ready(function() {
    specialHandle();
});
