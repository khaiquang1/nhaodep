<?php
require_once PATH_BASE_ROOT . '/lang/vi_VN.php';
require_once PATH_BASE_ROOT . '/lang/en_US.php';
define ("LANGS_VN_ROOT", serialize ($LANGS_VN_ROOT));
define ("LANGS_EN_US", serialize ($LANGS_EN_US));

/*api for translate*/
if ( !function_exists('getArrayLangsRoot')) {
    function getArrayLangsRoot($type = 'vi_VN'){
        $my_fruits = array();
        if( $type = 'en_US'){
            $my_fruits = unserialize (LANGS_EN_US);
        }else{
            $my_fruits = unserialize (LANGS_VN_ROOT);
        }
        return $my_fruits;
    };
}
if (!function_exists('hasPermission')) {
    function hasPermission($module, $controller,$action){
        if( isset($_SESSION['CMSMEMBER']) ){
            if( $_SESSION['CMSMEMBER']['type'] == 'admin'){
                return TRUE;
            }
            if( isset($_SESSION['CMSMEMBER']['permissions']) ){
                if( isset($_SESSION['CMSMEMBER']['permissions'][$module][$controller][$action]) ){
                    return TRUE;
                }
            }
        }
        return FALSE;
    };
}
if (!function_exists('swapTranslateForAdmin')) {
    function swapTranslateForAdmin($langs, $type='vi_VN'){
        $results = $langs;
        try{
            $langsRoot = getArrayLangsRoot($type);
            foreach ($langsRoot as $key => $lang) {
                if( !isset($langs[$key]) ){
                    $langs[$key] = $lang;
                }
            }
            $results = $langs;
        }catch(\Exception $ex){}
        
        if( hasPermission('cms','language', 'manage-keywords')
            && !empty($_SESSION['CMSMEMBER']['translate']) 
            && $_SESSION['CMSMEMBER']['translate'] == 1 
            && empty($_SESSION['CMSMEMBER']['preview']) ){
            $wLangs = array();
            foreach ($results as $key => $lang) {
                $wLangs[$key] = '<lang class=\'editer-lang\' data-key=\''.$key.'\' >'.$lang.'</lang>';
            }
            $results = $wLangs;
        }
        return $results;
    };
}
if (!function_exists('mergeTranslateForRootAdmin')) {
    function mergeTranslateForRootAdmin($langs, $type='vi_VN'){
        $results = $langs;
        try{
            $langsRoot = getArrayLangsRoot($type);
            foreach ($langsRoot as $key => $lang) {
                if( !isset($langs[$key]) ){
                    $langs[$key] = $lang;
                }
            }
            $results = $langs;
        }catch(\Exception $ex){}

        return $results;
    };
}
if (!function_exists('megeTranslate')) {
    function megeTranslate($langs, $type){
        $results = $langs;
        try{
            if( !empty($_SESSION['websites_folder']) ){
                $name_folder = $_SESSION['websites_folder'];
                if( is_file(PATH_BASE_ROOT . '/templates/Websites/'.$name_folder.'/lang/'.$type.'.php') ){
                    require_once PATH_BASE_ROOT . '/templates/Websites/'.$name_folder.'/lang/'.$type.'.php';
                    $main_langs = ${$type};
                    foreach ($results as $key => $lang) {
                        if( !isset($main_langs[$key]) ){
                            $main_langs[$key] = $lang;
                        }
                    }
                    return $main_langs;
                }
            }
        }catch(\Exception $ex){}
        return $results;
    };
}

if (!function_exists('mergeTranslateForCms')) {
    function mergeTranslateForCms($langs, $type){
        $results = $langs;
        try{
            $langsRoot = getArrayLangsRoot($type);
            foreach ($langsRoot as $key => $lang) {
                if( !isset($langs[$key]) ){
                    $langs[$key] = $lang;
                }
            }
            $results = $langs;
        }catch(\Exception $ex){}
        
        try{
            $main_langs = $results;
            if( is_file(LANG_PATH . '/'.$type.'.php') ){
                require_once LANG_PATH . '/'.$type.'.php';
                if( isset(${$type}) ){
                    $main_langs = ${$type};
                    foreach ($results as $key => $lang) {
                        $main_langs[$key] = $lang;
                    }
                }
            }
            return $main_langs;
        }catch(\Exception $ex){}
        return $results;
    };
}

if (!function_exists('getDirWebsite')) {
    function getDirWebsite($website){
        $url_website = '';
        if(!empty($website)){
            if( empty($_SESSION['CMSMEMBER']['preview']) ){
                $websites_dir = $website['websites_dir'];
                $url_website = trim($websites_dir, '/');
            }else{
                $template = $_SESSION['CMSMEMBER']['preview'];
                $websites_dir = $template['template_dir'];
                $url_website = trim($websites_dir, '/');
            }
        }
        return $url_website;
    };
}

if (!function_exists('getFolderWebsite')) {
    function getFolderWebsite($website){
        $url_website = '';
        if(!empty($website)){
            if( empty($_SESSION['CMSMEMBER']['preview']) ){
                $websites_folder = $website['websites_folder'];
                $url_website = trim($websites_folder, '/');
            }else{
                $template = $_SESSION['CMSMEMBER']['preview'];
                $websites_folder = $template['template_folder'];
                $url_website = trim($websites_folder, '/');
            }
        }
        return $url_website;
    };
}

if (!function_exists('getUrlFolderWebsite')) {
    function getUrlFolderWebsite($website){
        $url_website = '';
        if(!empty($website)){
            if( empty($_SESSION['CMSMEMBER']['preview']) ){
                $websites_dir = $website['websites_dir'];
                $websites_folder = $website['websites_folder'];
                $url_website = trim($websites_dir, '/').'/'.trim($websites_folder, '/');
            }else{
                $template = $_SESSION['CMSMEMBER']['preview'];
                $websites_dir = $template['template_dir'];
                $websites_folder = $template['template_folder'];
                $url_website = trim($websites_dir, '/').'/'.trim($websites_folder, '/');
            }
        }
        return $url_website;
    };
}
    function isHttps()
    {
        if (array_key_exists("HTTPS", $_SERVER) && 'on' === $_SERVER["HTTPS"]) {
            return true;
        }
        if (array_key_exists("SERVER_PORT", $_SERVER) && 443 === (int)$_SERVER["SERVER_PORT"]) {
            return true;
        }
        if (array_key_exists("HTTP_X_FORWARDED_SSL", $_SERVER) && 'on' === $_SERVER["HTTP_X_FORWARDED_SSL"]) {
            return true;
        }
        if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && 'https' === $_SERVER["HTTP_X_FORWARDED_PROTO"]) {
            return true;
        }
        return false;
    }
/**
 * debug tools
 */
define ( 'REQUEST_MICROTIME', microtime ( true ) );

/**
 * pagination
 */
define ( 'PAGE_LIST_COUNT', 15 );
define ( 'PAGE_LIST_RANGE', 5 );

/**
 * format date of mysql
 */
define ( 'DATETIME_FORMAT_DB', 'yyyy-MM-dd HH:mm:ss' );

/**
 * format view html
 */
define ( 'DATETIME_FORMAT_DISPLAY', 'dd-MM-yyyy HH:mm a' );

/**
 * upload ad categories image
 */
/**
 * status
 */
define ( 'STATUS_PENDING', 'PENDING' );
define ( 'STATUS_SENDING', 'SENDING' );
define ( 'STATUS_SENT', 'SENT' );
define ( 'STATUS_ERROR', 'ERROR' );

/**
 * info email
 */
define ( 'EMAIL_SENDER', '' );

/**
 * message
 */
define ( 'MSG_COMPLETED', 'Completed' );

/**
 * label
 */
define ( 'LAB_PLEASE_SELECT', '-- Vui lòng chọn --' );
define ( 'LAB_YES', 'Yes' );
define ( 'LAB_NO', 'No' );
define ( 'LAB_ALL', 'All' );
define ( 'LAB_FIELD', 'Lĩnh vực' );
define ( 'LAB_KEYWORD', 'Từ khóa' );
define ( 'LAB_RESET', 'Reset' );
define ( 'LAB_SEARCH', 'Search' );
define ( 'LAB_SORT_DOWN', 'Sort down' );
define ( 'LAB_SORT_UP', 'Sort up' );
define ( 'LAB_DELETE', 'Remove' );
define ( 'LAB_EDIT', 'Edit' );

define ( 'CKEDITOR_UPLOAD', '/upload/ck' );

define ( 'USER_ID_NELO', 1 );
$protocol = (!empty(isHttps()) && isHttps()== true) ? "https://" : "http://";
define ( 'FOLDERWEB',  $protocol.$_SERVER['SERVER_NAME']);
define ( 'PROTOCOL',  $protocol);
define ( 'MASTERPAGE', 'lataformen.com');
define ( 'ID_MASTERPAGE', 1);
define('VERSION_STYLE',326);
define ( 'PHOTOLINK', '//lataformen.com/custom/domain_1/' );
define ( 'NoPhotoImage', FOLDERWEB.'/styles/dataimages/no-photo-small.jpg' );
define ( 'ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);
define ( 'ROOT_DIR_FOLDER', $_SERVER['DOCUMENT_ROOT']."/custom/domain_1");
define ('link_redirect','');
define('STYLESTATIC', '//lataformen.com/styles');

define ( 'EMAIL_ADMIN_RECEIVE', 'info@lataformen.com' );
define ( 'EMAIL_ADMIN_SEND', 'info@lataformen.com' );
define ( 'HOST_MAIL', 'smtp.gmail.com' );
define ( 'NAME_HOST', 'smtp.gmail.com' );
define ( 'USERNAME_HOST_MAIL', 'marketingonline129@gmail.com' );
define ( 'PASSWORD_HOST_MAIL', 'Ziza@1234567891' );
define ( 'HOST_PORT', '587' );
define ( 'TLS', 'tls' );
    
define ( 'SYSMONEY', 'USD' );
define ( 'PAYPAL_SALER', '');
define ( 'hostsearch', '' );
define ( 'portsearch', '8983' );
define ( 'foldersearch', '/solr' );
define ( 'core', 'core');
define ( 'FACEBOOK_APP_ID', '960830494004687');
