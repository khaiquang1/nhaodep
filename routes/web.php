<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
include_once('customer.php');

/****************   Model binding into route **************************/

Route::pattern('slug', '[a-z0-9-]+');
Route::pattern('slug2', '[a-z_]+');
Route::pattern('slug3', '[a-z0-9-_]+');
Route::pattern('id', '[0-9]+');

/******************   APP routes  ********************************/

Route::get('/', 'Users\DashboardController@index');
Route::get('/index1', 'Users\DashboardController@index1');
Route::get('/index2', 'Users\DashboardController@index2');


Route::get('home', 'Users\DashboardController@index');

Route::get('invite/{slug3}', 'AuthController@getSignup');
Route::post('invite/{slug3}', 'AuthController@postSignup');
//route after user login into system
Route::get('signin', 'AuthController@getSignin');
Route::post('signin', 'AuthController@postSignin');
Route::get('forgot', 'AuthController@getForgotPassword');
Route::post('password', 'AuthController@postForgotPassword');
Route::get('reset_password/{token}', 'AuthController@getReset');
Route::post('reset_password/{token}', 'AuthController@postReset');
Route::get('logout', 'AuthController@getLogout');
Route::get('logs', 'Users\DashboardController@logs');
Route::get('logsaccess', 'Users\DashboardController@logsaccess');


Route::get('passwordreset/{id}/{token}', ['as' => 'reminders.edit', 'uses' => 'AuthController@edit']);
Route::post('passwordreset/{id}/{token}', ['as' => 'reminders.update', 'uses' => 'AuthController@update']);
/**
 * Installation
 */
Route::group(['prefix' => 'install'], function () {
    Route::get('', 'InstallController@index');
    Route::get('requirements','InstallController@requirements');
    Route::get('permissions','InstallController@permissions');
    Route::get('database','InstallController@database');
    Route::get('start-installation','InstallController@installation');
    Route::post('start-installation','InstallController@installation');
    Route::get('install','InstallController@install');
    Route::post('install','InstallController@install');
    Route::get('settings','InstallController@settings');
    Route::post('settings','InstallController@settingsSave');
    Route::get('email_settings','InstallController@settingsEmail');
    Route::post('email_settings','InstallController@settingsEmailSave');
    Route::get('complete','InstallController@complete');
    Route::get('error','InstallController@error');
});

Route::group(array('middleware' => ['sentinel', 'xss_protection']), function () {
    Route::get('profile', 'AuthController@getProfile');
    Route::get('account', 'AuthController@getAccount');
    Route::put('account/{user}', 'AuthController@postAccount');
});
Route::group(array('middleware' => ['sentinel', 'admin', 'xss_protection'], 'namespace' => 'Users'), function () {

    Route::get('setting', 'SettingsController@index');
    Route::post('setting', 'SettingsController@update');

    Route::post('support/send_support', 'MailboxController@sendSupport');
    Route::post('support/send_support_replay', 'MailboxController@sendSupportReplay');
    Route::get('support', 'MailboxController@support');

    Route::group(['prefix' => 'option'], function () {
        Route::get('data/{slug2}', 'OptionController@data');
        Route::get('data', 'OptionController@data');
        Route::get('{option}/show', 'OptionController@show');
        Route::get('{option}/delete', 'OptionController@delete');
    });
    Route::resource('option', 'OptionController');
});

Route::group(array('middleware' => ['sentinel', 'admin'], 'namespace' => 'Users'), function () {

    Route::group(['prefix' => 'email_template'], function () {
        Route::get('data', 'EmailTemplateController@data');
        Route::get('{email_template}/show', 'EmailTemplateController@show');
        Route::get('{email_template}/delete', 'EmailTemplateController@delete');
    });
    Route::resource('email_template', 'EmailTemplateController');
});

Route::group(array('middleware' => ['sentinel', 'authorized', 'xss_protection'], 'namespace' => 'Users'), function () {

    Route::group(['prefix' => 'salesteam'], function () {
        Route::get('data', 'SalesteamController@data');
        Route::get('import', 'SalesteamController@getImport');
        Route::post('import', 'SalesteamController@postImport');
        Route::post('ajax-store', 'SalesteamController@postAjaxStore');
        Route::get('download-template', 'SalesteamController@downloadExcelTemplate');
        Route::get('{salesteam}/delete', 'SalesteamController@delete');
        Route::get('{salesteam}/show', 'SalesteamController@show');
    });
    Route::resource('salesteam', 'SalesteamController');

    Route::group(['prefix' => 'groupclient'], function () {
        Route::get('data', 'GroupclientController@data');
        Route::get('{groupclient}/delete', 'GroupclientController@delete');
        Route::get('{groupclient}/show', 'GroupclientController@show');
    });
    Route::resource('groupclient', 'GroupclientController');


    Route::group(['prefix' => 'tags'], function () {
        Route::get('data', 'TagsController@data');
        Route::get('{tags}/delete', 'TagsController@delete');
        Route::get('{tags}/show', 'TagsController@show');
    });
    Route::resource('tags', 'TagsController');

    Route::group(['prefix' => 'libcontent'], function () {
        Route::get('data', 'LibcontentController@data');
        Route::get('{libcontent}/delete', 'LibcontentController@delete');
        Route::get('searchcontent', 'LibcontentController@searchcontent');

        Route::get('{libcontent}/show', 'LibcontentController@show');
    });
    Route::resource('libcontent', 'LibcontentController');

    Route::group(['prefix' => 'contentautomation'], function () {
        Route::get('getcontentchild', 'ContentAutomationController@getcontentchirldren');
        Route::post('getcontentchild', 'ContentAutomationController@getcontentchirldren');

        Route::get('data', 'ContentAutomationController@data');
        Route::get('{contentautomation}/delete', 'ContentAutomationController@delete');
        Route::get('{contentautomation}/show', 'ContentAutomationController@show');
    });
    Route::resource('contentautomation', 'ContentAutomationController');


    Route::group(['prefix' => 'source'], function () {
        Route::get('data', 'SourceController@data');
        Route::get('{source}/delete', 'SourceController@delete');
        Route::get('{source}/show', 'SourceController@show');
    });
    Route::resource('source', 'SourceController');


    Route::group(['prefix' => 'branch'], function () {
        Route::get('data', 'BranchController@data');
        Route::get('{branch}/delete', 'BranchController@delete');
        Route::get('{branch}/show', 'BranchController@show');
    });
    Route::resource('branch', 'BranchController');

    Route::group(['prefix' => 'smsconfig'], function () {
        Route::get('data', 'SmsconfigController@data');
        Route::get('{smsconfig}/delete', 'SmsconfigController@delete');
        Route::get('{smsconfig}/show', 'SmsconfigController@show');
    });
    Route::resource('smsconfig', 'SmsconfigController');

    Route::group(['prefix' => 'groupuser'], function () {
        Route::get('data', 'GroupuserController@data');
        Route::get('{groupuser}/delete', 'GroupuserController@delete');
        Route::get('{groupuser}/show', 'GroupuserController@show');
        Route::get('user_group', 'GroupuserController@userGroup');
    });
    Route::resource('groupuser', 'GroupuserController');
    /* Report*/
    Route::group(['prefix' => 'report'], function () {
        Route::get('data', 'ReportController@data');
        Route::get('staff', 'ReportController@staff');
        Route::get('clients', 'ReportController@clients');
        Route::get('tags', 'ReportController@tags');
        Route::get('inbox', 'ReportController@inbox');
        Route::get('summary', 'ReportController@summary');
        Route::get('dashboard2', 'ReportController@summarynew');

    });
    Route::resource('report', 'ReportController');
    /* End report */

    Route::group(['prefix' => 'getdata'], function () {
        Route::get('data', 'GetdataController@data');
        Route::get('pageupdate', 'GetdataController@updatestatus');
        Route::get('{getdata}/delete', 'GetdataController@delete');
    });
    Route::resource('getdata', 'GetdataController');

    //* manager content
    Route::group(['prefix' => 'botcontent'], function () {
        Route::get('data', 'BotcontentController@data');
        Route::get('pageupdate', 'BotcontentController@updatestatus');
        Route::get('{botcontent}/delete', 'BotcontentController@delete');
    });
    Route::resource('botcontent', 'BotcontentController');
    ///

    Route::group(['prefix' => 'clientstatus'], function () {
        Route::post('updateposition', 'ClientstatusController@updateposition');
        Route::get('data', 'ClientstatusController@data');
        Route::get('{clientstatus}/delete', 'ClientstatusController@delete');
        Route::get('{clientstatus}/show', 'ClientstatusController@show');

    });
    Route::resource('clientstatus', 'ClientstatusController');
    
    //Partner
    Route::group(['prefix' => 'partner'], function () {
        Route::get('data', 'PartnerController@data');
        Route::post('ajax-store', 'PartnerController@postAjaxStore');
        Route::get('{partner}/delete', 'PartnerController@delete');
        Route::get('{partner}/show', 'PartnerController@show');
    });
    Route::resource('partner', 'PartnerController');
    //End partner

    Route::group(['prefix' => 'category'], function () {
        Route::get('data', 'CategoryController@data');
        Route::get('{category}/delete', 'CategoryController@delete');

        Route::get('import', 'CategoryController@getImport');
        Route::post('import', 'CategoryController@postImport');
        Route::post('ajax-store', 'CategoryController@postAjaxStore');

        Route::get('download-template', 'CategoryController@downloadExcelTemplate');

    });
    Route::resource('category', 'CategoryController');

    Route::group(['prefix' => 'lead'], function () {
        Route::get('data', 'LeadController@data');
        Route::get('historypageaccess', 'LeadController@historypageaccess');
        Route::get('historychat', 'LeadController@historyChat');
        Route::get('historysms', 'LeadController@historySms');

        Route::get('ajax_state_list', 'LeadController@ajaxStateList');
        Route::get('ajax_city_list', 'LeadController@ajaxCityList');
        Route::get('ajax_district_list', 'LeadController@ajaxDistrictList');
        Route::get('ajax_ward_list', 'LeadController@ajaxWardList');
        Route::get('statusgroup', 'LeadController@ajaxStausGroup');

        Route::get('{lead}/delete', 'LeadController@delete');
        Route::get('{lead}/show', 'LeadController@show');
        Route::get('{lead}/lead-win', 'LeadController@leadWin');
        Route::get('{lead}/lead-lost', 'LeadController@leadLost');
        Route::get('lead-export', 'LeadController@leadExport');
        Route::get('history', 'LeadController@history');
        Route::get('kanban', 'LeadController@kanban');
        Route::get('kanban_data', 'LeadController@kanbanData');
        Route::get('home', 'LeadController@index');
        Route::get('chat', 'LeadController@chat');
        Route::get('messenger', 'LeadController@chat2');

        Route::get('detaillead', 'LeadController@detailLead');
        Route::get('detailcomment', 'LeadController@detailComment');

        Route::get('pageloading', 'LeadController@pageloading');
        Route::get('messengerloading', 'LeadController@messengerloading');


        Route::get('comment', 'LeadController@comment');
        Route::get('page_messenger_loading', 'LeadController@pageloadingComment');
        Route::get('comment_loading', 'LeadController@messengerloading');
        Route::get('history_comment', 'LeadController@historyComment');

        
        Route::get('editassign', 'LeadController@editassign');
        

        Route::get('products_order_history', 'LeadController@historyProductOfLead');
        Route::get('products_interest', 'LeadController@interestProductOfLead');
        Route::get('ajax_leads_list', 'LeadController@searchLead');
        Route::get('ajax_lead_list', 'LeadController@ajaxLeadList');
        Route::get('assign', 'LeadController@assign');
        Route::get('assignto', 'LeadController@assignto');
        Route::get('updateauto', 'LeadController@updateLeadImport');
        //Excel Import leadLost
        Route::get('import', 'LeadController@getImport');

        Route::post('import-lead', 'LeadController@postImport');
        Route::get('importleadfromtemp', 'LeadController@postImportTempToMain');
        Route::post('updateclientauto', 'LeadController@updateclientauto');
        Route::post('updatephone', 'LeadController@updatephonemain');
        Route::post('updateemail', 'LeadController@updateemail');
        Route::post('acceptlead', 'LeadController@updateacceptlead');
        Route::post('assignlead', 'LeadController@assignlead');
        Route::post('updateassignlead', 'LeadController@updateassignlead');

        Route::post('receivelead', 'LeadController@receivelead');
        Route::post('checksattusassign', 'LeadController@checksattusassign');
        Route::post('addtags', 'LeadController@addtags');

        Route::post('chatwidthuser', 'LeadController@chatWithUser');
        Route::get('user_online', 'LeadController@onlineUsers');
        Route::post('ghimlead', 'LeadController@gimlead');
        Route::post('reporttags', 'LeadController@reporttags');
        Route::post('updatecommentread', 'LeadController@updatecommentread');

        
        
        
         //Excel Import leadLost
         Route::post('lockedupdate', 'LeadController@lockeduser');

         Route::get('importemail', 'LeadController@getImportEmail');

         Route::post('import-email', 'LeadController@postImportEmail');
         Route::post('add_call_log', 'LeadController@addCallLog');

         
        Route::post('ajax-store', 'LeadController@postAjaxStore');

        Route::get('download-template', 'LeadController@downloadExcelTemplate');



    });
    Route::resource('lead', 'LeadController');

    Route::group(['prefix' => 'deal'], function () {
        Route::get('data', 'DealController@data');
        Route::get('ajax_state_list', 'DealController@ajaxStateList');
        Route::get('ajax_city_list', 'DealController@ajaxCityList');
        Route::get('ajax_district_list', 'DealController@ajaxDistrictList');
        Route::get('ajax_ward_list', 'DealController@ajaxWardList');
        Route::get('statusgroup', 'DealController@ajaxStausGroup');

        Route::get('{lead}/delete', 'DealController@delete');
        Route::get('{lead}/show', 'DealController@show');
        Route::get('{lead}/lead-win', 'DealController@leadWin');
        Route::get('{lead}/lead-lost', 'DealController@leadLost');
        Route::get('lead-export', 'DealController@leadExport');
        Route::get('history', 'DealController@history');
        Route::get('kanban', 'DealController@kanban');
        Route::get('home', 'DealController@index');

        Route::get('products_order_history', 'DealController@historyProductOfLead');
        Route::get('products_interest', 'DealController@interestProductOfLead');
        Route::get('ajax_leads_list', 'DealController@searchLead');
        Route::get('ajax_lead_list', 'DealController@ajaxLeadList');
        
        //Excel Import leadLost
        Route::get('import', 'DealController@getImport');

        Route::post('import-lead', 'DealController@postImport');
        Route::get('importleadfromtemp', 'DealController@postImportTempToMain');

        
         //Excel Import leadLost
         Route::post('lockedupdate', 'DealController@lockeduser');

         Route::get('importemail', 'DealController@getImportEmail');
         Route::get('plan', 'DealController@plan');

         Route::get('history', 'DealController@history');

         Route::post('import-email', 'DealController@postImportEmail');
         Route::post('add_call_log', 'DealController@addCallLog');
         Route::post('update_lead_status', 'LeadController@updateLeadStatus');


        Route::post('ajax-store', 'DealController@postAjaxStore');

        Route::get('download-template', 'DealController@downloadExcelTemplate');

    });
    Route::resource('deal', 'DealController');


    Route::group(['prefix' => 'opportunity'], function () {
        Route::get('data', 'OpportunityController@data');
        Route::get('{opportunity}/delete', 'OpportunityController@delete');
        Route::get('{opportunity}/show', 'OpportunityController@show');
        Route::get('{opportunity}/lost', 'OpportunityController@lost');
        Route::post('{opportunity}/update_lost', 'OpportunityController@updateLost');
        Route::get('{opportunity}/won', 'OpportunityController@won');
        Route::get('{opportunity}/convert_to_quotation', 'OpportunityController@convertToQuotation');
        Route::post('{opportunity}/opportunity_archive', 'OpportunityController@convertToArchive');
        Route::get('{opportunity}/opportunity_delete_list', 'OpportunityController@convertToDeleteList');
        Route::post('{opportunity}/customerData', 'OpportunityController@customerData');
        Route::get('customerData', 'OpportunityController@customerData');
        Route::get('ajax_agent_list', 'OpportunityController@ajaxAgentList');
        Route::get('ajax_main_staff_list', 'OpportunityController@ajaxMainStaffList');

    });
    Route::resource('opportunity', 'OpportunityController');

    Route::get('convertedlist_view/{id}/show', 'OpportunityConvertedListController@quatationList');

    Route::group(['prefix' => 'opportunity_archive'], function () {
        Route::get('{opportunity_archive}/show', 'OpportunityArchiveController@show');
        Route::get('data', 'OpportunityArchiveController@data');
    });
    Route::resource('opportunity_archive', 'OpportunityArchiveController');

    Route::group(['prefix' => 'opportunity_delete_list'], function () {
        Route::get('data', 'OpportunityDeleteListController@data');
        Route::get('{opportunity_delete_list}/show', 'OpportunityDeleteListController@show');
        Route::get('{opportunity_delete_list}/restore', 'OpportunityDeleteListController@delete');
        Route::delete('{opportunity_delete_list}', 'OpportunityDeleteListController@restoreOpportunity');
        Route::get('/', 'OpportunityDeleteListController@index');
    });

    Route::group(['prefix' => 'opportunity_converted_list'], function () {
        Route::get('data', 'OpportunityConvertedListController@data');
    });
    Route::resource('opportunity_converted_list','OpportunityConvertedListController');

    Route::group(['prefix' => 'company'], function () {
        Route::get('data', 'CompanyController@data');
        Route::get('{company}/show', 'CompanyController@show');
        Route::get('{company}/delete', 'CompanyController@delete');
    });
    Route::resource('company', 'CompanyController');

    Route::group(['prefix' => 'customer'], function () {
        Route::get('data', 'CustomerController@data');
        Route::put('{user}/ajax', 'CustomerController@ajaxUpdate');
        Route::get('{customer}/show', 'CustomerController@show');
        Route::get('{customer}/delete', 'CustomerController@delete');
        //Excel Import
        Route::get('import', 'CustomerController@getImport');
        Route::post('import', 'CustomerController@postImport');
        Route::post('ajax-store', 'CustomerController@postAjaxStore');

        Route::post('import-excel-data', 'CustomerController@importExcelData');
        Route::get('download-template', 'CustomerController@downloadExcelTemplate');
    });
    Route::resource('customer', 'CustomerController');

    Route::group(['prefix' => 'call'], function () {
        Route::get('data', 'CallController@data');
        Route::get('{call}/data_user', 'CallController@dataUser');
        Route::get('{call}/edit', 'CallController@edit');
        Route::get('{call}/show', 'CallController@show');
        Route::get('{call}/user', 'CallController@user');
        Route::get('{call}/delete', 'CallController@delete');
        Route::delete('{call}', 'CallController@destroy');
        Route::put('{call}', 'CallController@update');
        Route::post('', 'CallController@store');
        Route::get('', 'CallController@index');
        
    });
    Route::resource('call', 'CallController');

    Route::group(['prefix' => 'sms'], function () {
        Route::get('data', 'SmsController@data');
        Route::get('/marketing', 'SmsController@index');
        Route::get('/reply', 'SmsController@reply');
        Route::get('/reply_sms', 'SmsController@replySMS');
        Route::get('{sms}/edit', 'SmsController@edit');
        Route::get('{sms}/show', 'SmsController@show');
        Route::get('{sms}/user', 'SmsController@user');
        Route::get('{sms}/delete', 'SmsController@delete');
        Route::delete('{sms}', 'SmsController@destroy');
        Route::put('{sms}', 'SmsController@update');
        Route::post('', 'SmsController@store');
        Route::get('', 'SmsController@index');
        
    });
    Route::resource('sms', 'SmsController');

    Route::group(['prefix' => 'leadcall'], function () {
        Route::get('{lead}', 'LeadCallController@index');
        Route::get('{lead}/data', 'LeadCallController@data');
        Route::get('{lead}/create', 'LeadCallController@create');
        Route::post('{lead}', 'LeadCallController@store');
        Route::get('{lead}/{call}/edit', 'LeadCallController@edit');
        Route::put('{lead}/{call}', 'LeadCallController@update');
        Route::get('{lead}/{call}/delete', 'LeadCallController@delete');
        Route::delete('{lead}/{call}', 'LeadCallController@destroy');
    });
    Route::resource('leadcall', 'LeadCallController');

    Route::group(['prefix' => 'opportunitycall'], function () {
        Route::get('{opportunity}', 'OpportunityCallController@index');
        Route::get('{opportunity}/data', 'OpportunityCallController@data');
        Route::get('{opportunity}/create', 'OpportunityCallController@create');
        Route::post('{opportunity}', 'OpportunityCallController@store');
        Route::get('{opportunity}/{call}/edit', 'OpportunityCallController@edit');
        Route::put('{opportunity}/{call}', 'OpportunityCallController@update');
        Route::get('{opportunity}/{call}/delete', 'OpportunityCallController@delete');
        Route::delete('{opportunity}/{call}', 'OpportunityCallController@destroy');
    });
    Route::resource('opportunitycall', 'OpportunityCallController');





    Route::group(['prefix' => 'opportunitymeeting'], function () {
        Route::get('{opportunity}', 'OpportunityMeetingController@index');
        Route::get('{opportunity}/data', 'OpportunityMeetingController@data');
        Route::get('{opportunity}/create', 'OpportunityMeetingController@create');
        Route::post('{opportunity}', 'OpportunityMeetingController@store');
        Route::get('{opportunity}/calendar', 'OpportunityMeetingController@calendar');
        Route::post('{opportunity}/calendarData', 'OpportunityMeetingController@calendar_data');
        Route::get('{opportunity}/{meeting}/edit', 'OpportunityMeetingController@edit');
        Route::put('{opportunity}/{meeting}', 'OpportunityMeetingController@update');
        Route::get('{opportunity}/{meeting}/delete', 'OpportunityMeetingController@delete');
        Route::delete('{opportunity}/{meeting}', 'OpportunityMeetingController@destroy');
    });
    Route::resource('opportunitymeeting', 'OpportunityMeetingController');

    Route::group(['prefix' => 'meeting'], function () {
        Route::get('calendar', 'MeetingController@calendar');
        Route::post('calendarData', 'MeetingController@calendar_data');
        Route::get('data', 'MeetingController@data');
        Route::get('{meeting}/delete', 'MeetingController@delete');
    });
    Route::resource('meeting', 'MeetingController');

    Route::group(['prefix' => 'product'], function () {
        Route::get('data', 'ProductController@data');
        Route::get('import', 'ProductController@getImport');
        Route::post('import', 'ProductController@postImport');
        Route::get('importproductedu', 'ProductController@postProductEdu');
        Route::post('importproductedu', 'ProductController@postProductEdu');

        Route::post('ajax-store', 'ProductController@postAjaxStore');
        Route::get('download-template', 'ProductController@downloadExcelTemplate');
        Route::get('{product}/delete', 'ProductController@delete');
        Route::get('{product}/show', 'ProductController@show');
        Route::get('{product}/export-code', 'ProductController@exportCode');

    });
    Route::resource('product', 'ProductController');

    Route::group(['prefix' => 'staff'], function () {
        Route::get('data', 'StaffController@data');
        Route::get('{staff}/show', 'StaffController@show');
        Route::get('{staff}/dashboard', 'StaffController@dashboard');
        Route::get('dashboard', 'StaffController@dashboard');
        Route::get('user_list', 'StaffController@user_list');

        
        Route::get('{staff}/delete', 'StaffController@delete');
        Route::get('invite', 'StaffController@invite');
        Route::post('invite', 'StaffController@inviteSave');
        Route::get('invite/{id}/cancel', 'StaffController@inviteCancel');
        Route::post('invite/{id}/cancel-invite', 'StaffController@inviteCancelConfirm');
        Route::get('{staff}/task', 'StaffController@taskuser');
        Route::get('{staff}/delete-partner', 'StaffController@deletepartner');
    });
    Route::resource('staff', 'StaffController');

    Route::group(['prefix' => 'qtemplate'], function () {
        Route::get('data', 'QtemplateController@data');
        Route::get('{qtemplate}/delete', 'QtemplateController@delete');
    });
    Route::resource('qtemplate', 'QtemplateController');

    Route::group(['prefix' => 'quotation'], function () {
        Route::get('data', 'QuotationController@data');
        Route::post('send_quotation', 'QuotationController@sendQuotation');
        Route::get('{quotation}/show', 'QuotationController@show');
        Route::get('{quotation}/edit', 'QuotationController@edit');
        Route::get('{quotation}/delete', 'QuotationController@delete');
        Route::get('{quotation}/ajax_create_pdf', 'QuotationController@ajaxCreatePdf');
        Route::get('{quotation}/print_quot', 'QuotationController@printQuot');
        Route::get('{quotation}/make_invoice', 'QuotationController@makeInvoice');
        Route::get('{quotation}/confirm_sales_order', 'QuotationController@confirmSalesOrder');
        Route::put('{quotation}', 'QuotationController@update');
        Route::delete('{quotation}', 'QuotationController@destroy');
        Route::get('ajax_qtemplates_products/{qtemplate}', 'QuotationController@ajaxQtemplatesProducts');
        Route::get('ajax_sales_team_list', 'QuotationController@ajaxSalesTeamList');

        Route::get('draft_quotations_list/data', 'QuotationController@draftQuotations');
        Route::get('draft_quotations', 'QuotationController@draftIndex');
    });
    Route::resource('quotation', 'QuotationController');

    Route::group(['prefix' => 'quotation_converted_list'], function () {
        Route::get('data', 'QuotationConvertedListController@data');
    });
    Route::resource('quotation_converted_list','QuotationConvertedListController');
    Route::get('quotation_converted_list/{id}/show', 'QuotationConvertedListController@salesOrderList');

    Route::group(['prefix' => 'quotation_invoice_list'], function () {
        Route::get('data', 'QuotationInvoiceListController@data');
    });
    Route::resource('quotation_invoice_list','QuotationInvoiceListController');
    Route::get('quotation_invoice_list/{id}/show', 'QuotationInvoiceListController@invoiceList');

    Route::group(['prefix' => 'quotation_delete_list'], function () {
        Route::get('data', 'QuotationDeleteListController@data');
        Route::get('{quotation_delete_list}/show', 'QuotationDeleteListController@show');
        Route::get('{quotation_delete_list}/restore', 'QuotationDeleteListController@delete');
        Route::delete('{quotation_delete_list}', 'QuotationDeleteListController@restoreQuotation');
        Route::get('/', 'QuotationDeleteListController@index');
    });
    Route::get('calendar/events', 'CalendarController@events');
    Route::post('calendar/events', 'CalendarController@events');
    Route::resource('calendar', 'CalendarController');

    Route::group(['prefix' => 'contract'], function () {
        Route::get('data', 'ContractController@data');
        Route::get('{contract}/delete', 'ContractController@delete');
        Route::get('{contract}/show', 'ContractController@show');
    });
    Route::resource('contract', 'ContractController');

    Route::group(['prefix' => 'sales_order'], function () {
        Route::get('data', 'SalesorderController@data');
        Route::post('send_saleorder', 'SalesorderController@sendSaleorder');
        Route::post('update_order_status', 'SalesorderController@updateOrderStatus');
        Route::get('kanban', 'SalesorderController@kanban');
        Route::post('add_sales_order_log', 'SalesorderController@addOrderLog');

        Route::get('{sales_order}/show', 'SalesorderController@show');
        Route::get('{sales_order}/edit', 'SalesorderController@edit');
        Route::get('{sales_order}/delete', 'SalesorderController@delete');
        Route::get('{sales_order}/ajax_create_pdf', 'SalesorderController@ajaxCreatePdf');
        Route::get('{sales_order}/print_quot', 'SalesorderController@printQuot');
        Route::get('{sales_order}/make_invoice', 'SalesorderController@makeInvoice');
        Route::get('{sales_order}/confirm_sales_order', 'SalesorderController@confirmSalesOrder');
        Route::put('{sales_order}', 'SalesorderController@update');
        Route::delete('{sales_order}', 'SalesorderController@destroy');
        Route::get('ajax_qtemplates_products/{qtemplate}', 'SalesorderController@ajaxQtemplatesProducts');
        Route::get('draft_salesorder_list/data', 'SalesorderController@draftSalesOrders');
        Route::get('draft_salesorders', 'SalesorderController@draftIndex');
        Route::get('history', 'SalesorderController@history');

    });
    Route::resource('sales_order', 'SalesorderController');

    Route::group(['prefix' => 'salesorder_delete_list'], function () {
        Route::get('data', 'SalesorderDeleteListController@data');
        Route::get('{salesorder_delete_list}/show', 'SalesorderDeleteListController@show');
        Route::get('{salesorder_delete_list}/restore', 'SalesorderDeleteListController@delete');
        Route::delete('{salesorder_delete_list}', 'SalesorderDeleteListController@restoreSalesorder');
        Route::get('/', 'SalesorderDeleteListController@index');
    });

    Route::group(['prefix' => 'salesorder_invoice_list'], function () {
        Route::get('data', 'SalesorderInvoiceListController@data');
    });
    Route::resource('salesorder_invoice_list','SalesorderInvoiceListController');
    Route::get('salesorder_invoice_list/{id}/show', 'SalesorderInvoiceListController@invoiceList');

    Route::group(['prefix' => 'invoices_payment_log'], function () {
        Route::get('data', 'InvoicesPaymentController@data');
        Route::get('{invoiceReceivePayment}/show', 'InvoicesPaymentController@show');
        Route::get('{invoiceReceivePayment}/delete', 'InvoicesPaymentController@delete');
        Route::get('payment_logs','InvoicesPaymentController@paymentLog');
    });
    Route::resource('invoices_payment_log', 'InvoicesPaymentController');

    Route::get('mailbox', 'MailboxController@index');
    Route::get('mailbox/all', 'MailboxController@getData');
    Route::get('mailbox/mail-template/{id}', 'MailboxController@getMailTemplate');
    Route::get('mailbox/{id}/get', 'MailboxController@getMail');
    Route::get('mailbox/{id}/getSent', 'MailboxController@getSentMail');
    Route::post('mailbox/{id}/reply', 'MailboxController@postReply');
    Route::get('mailbox/data', 'MailboxController@getAllData');
    Route::get('mailbox/received', 'MailboxController@getReceived');
    Route::post('mailbox/send', 'MailboxController@sendEmail');
    Route::get('mailbox/sent', 'MailboxController@getSent');
    Route::post('mailbox/mark-as-read', 'MailboxController@postMarkAsRead');
    Route::post('mailbox/delete', 'MailboxController@postDelete');

    Route::group(['prefix' => 'invoice'], function () {
        Route::get('data', 'InvoiceController@data');
        Route::post('send_invoice', 'InvoiceController@sendInvoice');
        Route::get('{invoice}/show', 'InvoiceController@show');
        Route::get('{invoice}/edit', 'InvoiceController@edit');
        Route::get('{invoice}/delete', 'InvoiceController@delete');
        Route::post('{user}/ajax_customer_details', 'InvoiceController@ajaxCustomerDetails');
        Route::get('{invoice}/ajax_create_pdf', 'InvoiceController@ajaxCreatePdf');
        Route::get('{invoice}/print_quot', 'InvoiceController@printQuot');
    });
    Route::resource('invoice', 'InvoiceController');


    Route::group(['prefix' => 'invoice_delete_list'], function () {
        Route::get('data', 'InvoiceDeleteListController@data');
        Route::get('{invoice_delete_list}/show', 'InvoiceDeleteListController@show');
        Route::get('{invoice_delete_list}/restore', 'InvoiceDeleteListController@delete');
        Route::delete('{invoice_delete_list}', 'InvoiceDeleteListController@restoreInvoice');
        Route::get('/', 'InvoiceDeleteListController@index');
    });

    Route::group(['prefix' => 'paid_invoice'], function () {
        Route::get('data', 'InvoicePaidListController@data');
    });
    Route::resource('paid_invoice','InvoicePaidListController');


    Route::group(['prefix' => 'notifications'], function () {
        Route::get('all', 'NotificationController@getAllData');
        Route::post('read', 'NotificationController@postRead');
    });
    Route::post('sales_order/update_order_status', 'SalesorderController@updateOrderStatus');
    Route::post('lead/add_products_interest', 'LeadController@addProductInterate');
    Route::post('lead/update_lead_status', 'LeadController@updateLeadStatus');
    Route::get('task', 'TaskController@index');
    Route::post('task/create', 'TaskController@store');
    Route::post('task/addtasktolead', 'TaskController@addtasktolead');
    
    Route::get('task/history_task_report', 'TaskController@historyReport');

    Route::post('task/addreporttask', 'TaskController@addReportTask');
    Route::get('task/reporttask', 'TaskController@reportTask');

    Route::get('task/editag', 'TaskController@editag');


    
    Route::get('task/data', 'TaskController@data');
    Route::get('task/history', 'TaskController@history');
    
    Route::post('task/{task}/edit', 'TaskController@update');
    Route::post('task/{task}/delete', 'TaskController@delete');

    Route::group(['prefix' => 'backup'], function () {
        Route::get('/','BackupController@index');
        Route::get('store','BackupController@store');
        Route::get('clean','BackupController@clean');
    });

    Route::group(['prefix' => 'projects'], function () {
        Route::get('data', 'ProjectsController@getAllData');
        Route::post('ajax-store', 'ProjectsController@postAjaxStore');
        Route::get('{projects}/create', 'ProductController@create');
        Route::get('{projects}/delete', 'ProductController@delete');
        Route::get('{projects}/show', 'ProductController@show');
    });
    Route::resource('projects', 'ProjectsController');


    Route::get('attendance/listdata', 'AttendanceController@customerlog');
    Route::get('attendance/customervistor', 'AttendanceController@customervistor')->name('customer.customervistor');
    Route::post('attendance/customervistor-syndata', 'AttendanceController@customerSyndata')->name('customer.customersyndata');
    Route::post('attendance/update-person-customer', 'AttendanceController@updatePersonCustomer')->name('customer.customerupdateperson');

    Route::group(['prefix' => 'attendance'], function () {
        Route::post('{attendance}/deletebyselection', 'AttendanceController@deleteBySelection'); 
        Route::post('{attendance}/updatelatter', 'AttendanceController@updateLater')->name('attendance.updatelatter');
        Route::get('{attendance}/detail', 'AttendanceController@detail');
    });
    Route::resource('attendance', 'AttendanceController');

  

});