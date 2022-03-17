<?php
/**
 * Copyright (c) 2014-2018, www.kuacg.com
 * All right reserved.
 *
 * @since LTS-181021
 * @package Cute
 * @author 酷ACG资源网
 * @date 2018/10/21 10:00
 * @link https://www.kuacg.com/23856.html
 */
?>
<?php
define( 'WP_CACHE_KEY_SALT', 'tt_object_cache'.short_md5(home_url()) );
/* 引入常量 */
require_once 'Constants.php';

/* 设置默认时区 */
date_default_timezone_set('PRC');

if (!function_exists('load_dash')) {
    function load_dash($path)
    {
        load_template(CUTE_THEME_DIR.'/dash/options/'.$path.'.php');
    }
}

if (!function_exists('load_api')) {
    function load_api($path)
    {
        load_template(CUTE_THEME_DIR.'/core/api/'.$path.'.php');
    }
}

if (!function_exists('load_class')) {
    function load_class($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/classes/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/classes/'.$path.'.php');
        }
    }
}

if (!function_exists('load_func')) {
    function load_func($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/functions/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/functions/'.$path.'.php');
        }
    }
}

if (!function_exists('load_mod')) {
    function load_mod($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/modules/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/modules/'.$path.'.php');
        }
    }
}

if (!function_exists('load_tpl')) {
    function load_tpl($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/templates/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/templates/'.$path.'.php');
        }
    }
}

if (!function_exists('load_widget')) {
    function load_widget($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/modules/widgets/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/modules/widgets/'.$path.'.php');
        }
    }
}

if (!function_exists('load_vm')) {
    function load_vm($path, $safe = false)
    {
        if ($safe) {
            @include_once CUTE_THEME_DIR.'/core/viewModels/'.$path.'.php';
        } else {
            load_template(CUTE_THEME_DIR.'/core/viewModels/'.$path.'.php');
        }
    }
}

/**
 * 载入语言包.
 *
 * @since 2.0.0
 */
function ct_load_languages()
{
    load_theme_textdomain('tt', CUTE_THEME_DIR.'/core/languages');
}
add_action('after_setup_theme', 'ct_load_languages');
function of_get_option($value,$default='') {
$cute_options= get_option( 'cute_options', array() );

  if(isset($cute_options[$value])){
   return $cute_options[$value];
  }
  return $default;
}
/* 载入option_framework */
load_dash('framework');

/* 载入主题选项 */
load_dash('config');

defined('CUTE_THEME_CDN_ASSETS') || define('CUTE_THEME_CDN_ASSETS', of_get_option('tt_cute_static_cdn_path', CUTE_THEME_ASSET));

/* 调试模式选项保存为全局变量 */
defined('CT_DEBUG') || define('CT_DEBUG', of_get_option('tt_theme_debug', false));
if (CT_DEBUG) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 'Off');
}

/* 载入后台框架 */
load_dash('dash');

/* 载入REST API功能控制函数 */
load_api('api.Config');

/* 载入功能函数 */

/**
 * 选择本地化语言
 *
 * @since 2.0.0
 */
function ct_theme_l10n()
{
    return ct_get_option('tt_l10n', 'zh_CN');
}
add_filter('locale', 'ct_theme_l10n');

/**
 * 生成密码重置链接.
 *
 * @since   2.0.0
 *
 * @param string $email
 * @param int    $user_id
 *
 * @return string
 */
function reset_password_link($email, $user_id = 0)
{
    $url = ct_url_for('resetpass');

    if (!$user_id) {
        $user_id = get_user_by('email', $email)->ID;
    }

    $data = array(
        'id' => $user_id,
        'email' => $email,
    );

    $base = base64_encode(ct_authdata($data, 'ENCODE', ct_get_option('tt_private_token'), 60 * 10)); // 10分钟有效期

    return add_query_arg('key', $base, $url);
}

/**
 * 验证密码重置链接包含的key.
 *
 * @since   2.0.0
 *
 * @param string $key
 *
 * @return bool
 */
function ct_verify_reset_password_link($base)
{
    if (empty($base)) {
        return false;
    }
	$token = ct_get_option('tt_private_token');
    $data = ct_authdata(base64_decode($base), 'DECODE', $token);
    if (!$data || !is_array($data) || !isset($data['id']) || !isset($data['email'])) {
        return false;
    }

    return true;
}

/**
 * 通过key进行密码重置.
 *
 * @since   2.0.0
 *
 * @param string $key
 * @param string $new_pass
 *
 * @return WP_User | WP_Error
 */
function ct_reset_password_by_key($base, $new_password)
{
	$token = ct_get_option('tt_private_token');
    $data = ct_authdata(base64_decode($base), 'DECODE', $token);
    if (!$data || !is_array($data) || !isset($data['id']) || !isset($data['email'])) {
        return new WP_Error('invalid_key', __('The key is invalid.', 'tt'), array('status' => 400));
    }

    $user = get_user_by('id', (int) $data['id']);
    if (!$user) {
        return new WP_Error('user_not_found', __('Sorry, the user was not found.', 'tt'), array('status' => 404));
    }else{
      reset_password($user, $new_password);
    }
    return $user;
}

/**
 * 生成包含注册信息的激活链接
 *
 * @since   2.0.0
 * @param   string  $username
 * @param   string  $email
 * @param   string  $password
 * @return  string
 */
function ct_generate_registration_activation_link ($username, $email, $password, $oauth='', $open_data_key='') {
    $base_url = ct_url_for('activate');

    $data = array(
        'oauth' => $oauth,
        'open_data_key' => $open_data_key,
        'username' => $username,
        'email' =>  $email,
        'password' => $password
    );

    $key = base64_encode(ct_authdata($data, 'ENCODE', ct_get_option('tt_private_token'), 60*10)); // 10分钟有效期

    return add_query_arg('key', $key, $base_url);
}


/**
 * 验证并激活注册信息的链接中包含的key
 *
 * @since   2.0.0
 *
 * @param   string  $key
 * @return  array | WP_Error
 */
function ct_activate_registration_from_link($key) {
    $reg_ver_option = ct_get_option('tt_enable_k_reg_ver', false);
    if(empty($key)) {
        return new WP_Error( 'invalid_key', __( 'The registration activation key is invalid.', 'tt' ), array( 'status' => 400 ) );
    }
    $data = ct_authdata(base64_decode($key), 'DECODE', ct_get_option('tt_private_token'));
    if(!$data || !is_array($data) || !isset($data['username']) || !isset($data['email']) || !isset($data['password'])){
        return new WP_Error( 'invalid_key', __( 'The registration activation key is invalid.', 'tt' ), array( 'status' => 400 ) );
    }

    // 开始激活
    $userdata = array(
        'user_login' => $data['username'],
        'user_email' => $data['email'],
        'user_pass' => $data['password']
    );
    switch($data['oauth']) {
                case 'qq':
                    $openid_meta_key = 'tt_qq_openid';
                    $_access_token_meta_key = 'tt_qq_access_token';
                    $_refresh_token_meta_key = 'tt_qq_refresh_token';
                    $_token_expiration_meta_key = 'tt_qq_token_expiration';
                    break;
                case 'weibo':
                    $openid_meta_key = 'tt_weibo_openid';
                    $_access_token_meta_key = 'tt_weibo_access_token';
                    $_refresh_token_meta_key = 'tt_weibo_refresh_token';
                    $_token_expiration_meta_key = 'tt_weibo_token_expiration';
                    break;
                case 'weixin':
                    $openid_meta_key = 'tt_weixin_openid';
                    $_access_token_meta_key = 'tt_weixin_access_token';
                    $_refresh_token_meta_key = 'tt_weixin_refresh_token';
                    $_token_expiration_meta_key = 'tt_weixin_token_expiration';
                    break;
            }
    $user_id = wp_insert_user($userdata);
    $_data_transient_key = $data['open_data_key'];
    $oauth_data_cache = get_transient($_data_transient_key);
    $oauth_data = (array)maybe_unserialize($oauth_data_cache);
    if($oauth_data['type']==='key'){
      $oauth_data = $oauth_data['data'];
    }
    if(is_wp_error($user_id)) {
        return $user_id;
    }elseif(!empty($data['oauth']) && $reg_ver_option){
        update_user_meta($user_id, $openid_meta_key, $oauth_data['openid']);
        update_user_meta($user_id, $_access_token_meta_key, $oauth_data['access_token']);
        update_user_meta($user_id, $_refresh_token_meta_key, $oauth_data['refresh_token']);
        update_user_meta($user_id, $_token_expiration_meta_key, $oauth_data['expiration']);

        if($data['oauth'] === 'weixin'){
            update_user_meta($user_id, 'tt_weixin_avatar', set_url_scheme($oauth_data['headimgurl'], 'https'));
            update_user_meta($user_id, 'tt_weixin_unionid', $oauth_data['unionid']);
            update_user_meta($user_id, 'tt_user_country', $oauth_data['country']); // 国家，如中国为CN
            update_user_meta($user_id, 'tt_user_province', $oauth_data['province']); // 普通用户个人资料填写的省份
            update_user_meta($user_id, 'tt_user_city', $oauth_data['city']); // 普通用户个人资料填写的城市
            update_user_meta($user_id, 'tt_user_sex', $oauth_data['sex']==2 ? 'female' : 'male'); // 普通用户性别，1为男性，2为女性
        }

        if($data['oauth'] === 'weibo'){
            update_user_meta($user_id, 'tt_weibo_avatar', $oauth_data['avatar_large']);
            update_user_meta($user_id, 'tt_weibo_profile_img', $oauth_data['profile_image_url']);
            update_user_meta($user_id, 'tt_weibo_id', $oauth_data['id']);
            update_user_meta($user_id, 'tt_user_description', $oauth_data['description']);
            update_user_meta($user_id, 'tt_user_location', $oauth_data['location']);
            update_user_meta($user_id, 'tt_user_sex', $oauth_data['sex']!='m' ? 'female' : 'male'); // 普通用户性别，m为男性，f为女性
        }

        // 使用开放平台头像
        update_user_meta($user_id, 'tt_avatar_type', $data['oauth']);
        delete_transient($_data_transient_key);
    }

    $result = array(
        'success' => 1,
        'message' => __('Activate the registration successfully', 'tt'),
        'data' => array(
            'username' => $data['username'],
            'email' => $data['email'],
            'id' => $user_id
        )
    );

    // 发送激活成功与注册欢迎信
    $blogname = get_bloginfo('name');
    // 给注册用户
    //cute_mail('', $data['email'], sprintf(__('欢迎加入[%s]', 'tt'), $blogname), array('loginName' => $data['username'], 'password' => '******', 'loginLink' => ct_url_for('signin')), 'register');
    //cute_async_mail('', $data['email'], sprintf(__('欢迎加入[%s]', 'tt'), $blogname), array('loginName' => $data['username'], 'password' => $data['password'], 'loginLink' => ct_url_for('signin')), 'register');
    // 给管理员
    cute_async_mail('', get_option('admin_email'), sprintf(__('您的站点「%s」有新用户注册 :', 'tt'), $blogname), array('loginName' => $data['username'], 'email' => $data['email'], 'ip' => $_SERVER['REMOTE_ADDR']), 'register-admin');
    wp_set_current_user( $user_id, $data['username'] );
    wp_set_auth_cookie( $user_id );
    return $result;
}

/**
 * 设置默认的登录链接.
 *
 * @since   2.0.0
 *
 * @param string $login_url
 * @param string $redirect
 *
 * @return string
 */
function ct_set_default_login_url($login_url, $redirect)
{
    $login_url = ct_url_for('signin');

    if (!empty($redirect)) {
        $login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
    }

    return $login_url;
}
add_filter('login_url', 'ct_set_default_login_url', 10, 2);

/**
 * 设置默认的注销链接.
 *
 * @since   2.0.0
 *
 * @param string $logout_url
 * @param string $redirect
 *
 * @return string
 */
function ct_set_default_logout_url($logout_url, $redirect)
{
    $logout_url = ct_url_for('signout');

    if (!empty($redirect)) {
        $logout_url = add_query_arg('redirect_to', urlencode($redirect), $logout_url);
    }

    return $logout_url;
}
add_filter('logout_url', 'ct_set_default_logout_url', 10, 2);

/**
 * 更改默认的注册链接.
 *
 * @since   2.0.0
 *
 * @return string
 */
function ct_set_default_register_url()
{
    return ct_url_for('signup');
}
add_filter('register_url', 'ct_set_default_register_url');

/**
 * 更改默认的忘记密码链接.
 *
 * @since   2.0.0
 *
 * @return string
 */
function ct_set_default_lostpassword_url()
{
    return ct_url_for('findpass');
}
add_filter('lostpassword_url', 'ct_set_default_lostpassword_url');

/**
 * 默认登录页,忘记密码页跳转.
 *
 * @since   2.0.0
 *
 * @return string
 */
function lost_password_redirect() {
   if ( isset( $_GET[ 'action' ] ) ){
      if ( in_array( $_GET[ 'action' ], array( 'lostpassword', 'retrievepassword' ) ) ) {
        wp_redirect( ct_url_for('findpass'), 301 );
        exit;
    }
     if ( in_array( $_GET[ 'action' ], array( 'register', 'registered' ) ) ) {
        wp_redirect( ct_url_for('signup'), 301 );
        exit;
    }
     if ( in_array( $_GET[ 'action' ], array( 'login' ) ) ) {
        wp_redirect( ct_url_for('signin'), 301 );
        exit;
    }
  }
  if(preg_match('/^\/wp-login.php([^\/]*)$/i', $_SERVER['REQUEST_URI'])){
        wp_redirect( ct_url_for('signin'), 301 );
        exit;
    }
}
add_action( 'init','lost_password_redirect' );

/**
 * 更改找回密码邮件中的内容.
 *
 * @since 2.0.0
 *
 * @param $message
 * @param $key
 *
 * @return string
 */
function ct_reset_password_message($message, $key)
{
    if (strpos($_POST['user_login'], '@')) {
        $user_data = get_user_by('email', trim($_POST['user_login']));
    } else {
        $login = trim($_POST['user_login']);
        $user_data = get_user_by('login', $login);
    }
    $user_login = $user_data->user_login;
    $reset_link = network_site_url('wp-login.php?action=rp&key='.$key.'&login='.rawurlencode($user_login), 'login');

    $templates = new League\Plates\Engine(CUTE_THEME_TPL.'/plates/emails');

    return $templates->render('findpass', array('home' => home_url(), 'userLogin' => $user_login, 'resetPassLink' => $reset_link));
}
add_filter('retrieve_password_message', 'ct_reset_password_message', null, 2);

/**
 * 更新基本资料(个人设置).
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $avatar_type
 * @param $nickname
 * @param $site
 * @param $description
 *
 * @return array|WP_Error
 */
function ct_update_basic_profiles($user_id, $type, $nickname, $site, $des)
{
    $data = array(
        'ID' => $user_id,
        'user_url' => $site,
        'description' => $des,
    );
    if (!empty($nickname)) {
        $data['nickname'] = $nickname;
        $data['display_name'] = $nickname;
    }
    $update = wp_update_user($data);

    if ($update instanceof WP_Error) {
        return $update;
    }

    if (!in_array($type, Avatar::$_avatarTypes)) {
        $type = Avatar::LETTER_AVATAR;
    }
    update_user_meta($user_id, 'tt_avatar_type', $type);

    //删除缓存
    ct_clear_avatar_related_cache($user_id);

    return array(
        'success' => true,
        'message' => __('Update basic profiles successfully', 'tt'),
    );
}

/**
 * 更新扩展资料.
 *
 * @since 2.0.0
 *
 * @param $data
 *
 * @return array|int|WP_Error
 */
function ct_update_extended_profiles($data)
{
    $update = wp_update_user($data);

    if ($update instanceof WP_Error) {
        return $update;
    }

    //删除VM缓存
    ct_clear_cache_key_like('ct_cache_daily_vm_MeSettingsVM_user'.$data['ID']);
    ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM_author_'.$data['ID']);

    return array(
        'success' => true,
        'message' => __('Update extended profiles successfully', 'tt'),
    );
}

/**
 * 更新安全资料.
 *
 * @since 2.0.0
 *
 * @param $data
 *
 * @return array|int|WP_Error
 */
function ct_update_security_profiles($data)
{
    $update = wp_update_user($data);

    if ($update instanceof WP_Error) {
        return $update;
    }

    //删除VM缓存
    ct_clear_cache_key_like('ct_cache_daily_vm_MeSettingsVM_user'.$data['ID']);
    ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM_author_'.$data['ID']);

    return array(
        'success' => true,
        'message' => __('Update security profiles successfully', 'tt'),
    );
}

require_once CUTE_THEME_CLASS.'/class.Avatar.php';

/**
 * 获取头像.
 *
 * @since   2.0.0
 *
 * @param int | string | object $id_or_email 用户ID或Email或用户实例对象
 * @param int | string          $size        头像尺寸
 *
 * @return string
 */
function ct_get_avatar($id_or_email, $size = 'medium')
{
    $avatar = new Avatar($id_or_email, $size);
    if ($cache = get_transient($avatar->cache_key)) {
        return $cache;
    }

    return $avatar->getAvatar();
}

/**
 * 清理Avatar transient缓存.
 *
 * @since   2.0.0
 */
//function ct_daily_clear_avatar_cache(){
//    global $wpdb;
//    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_daily_avatar_%' OR `option_name` LIKE '_transient_timeout_ct_cache_daily_avatar_%'" );
//}
//add_action('ct_setup_common_daily_event', 'ct_daily_clear_avatar_cache');

/**
 * 删除头像缓存以及包含头像的多处缓存数据.
 *
 * @since 2.0.0
 *
 * @param $user_id
 */
function ct_clear_avatar_related_cache($user_id)
{
    //删除VM缓存
    delete_transient('ct_cache_daily_vm_MeSettingsVM_user'.$user_id);
    delete_transient('ct_cache_daily_vm_UCProfileVM_author'.$user_id);
    //删除头像缓存
	delete_transient('tt_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'small'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('tt_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'medium'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('tt_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'large'.Utils::getCurrentDateTimeStr('day')));
}

/**
 * Cache 封装.
 *
 * @since   2.0.0
 *
 * @param string   $key     缓存键
 * @param callable $miss_cb 未命中缓存时的回调函数
 * @param string   $group   缓存数据分组
 * @param int      $expire  缓存时间，单位为秒
 *
 * @return mixed
 */
function ct_cached($key, $miss_cb, $group, $expire)
{
    if (ct_get_option('tt_object_cache', 'none') == 'none' && !CT_DEBUG) {
        $data = get_transient($key);
        if ($data !== false) {
            return $data;
        }
        if (is_callable($miss_cb)) {
            $data = call_user_func($miss_cb);
            if (is_string($data) || is_int($data)) {
                set_transient($key, $data, $expire);
            }

            return $data;
        }

        return false;
    }elseif (in_array(ct_get_option('tt_object_cache', 'none'), array('memcached','memcache', 'redis')) && !CT_DEBUG) {
        $data = wp_cache_get($key, $group);
        if ($data !== false) {
            return $data;
        }
        if (is_callable($miss_cb)) {
            $data = call_user_func($miss_cb);
            wp_cache_set($key, $data, $group, $expire);

            return $data;
        }

        return false;
    }

    return is_callable($miss_cb) ? call_user_func($miss_cb) : false;
}

/**
 * 定时清理大部分缓存(每小时).
 *
 * @since   2.0.0
 */
function ct_cache_flush_hourly()
{
    // Object Cache
    wp_cache_flush();

    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_hourly_%' OR `option_name` LIKE '_transient_timeout_ct_cache_hourly%'");
}
add_action('ct_setup_common_hourly_event', 'ct_cache_flush_hourly');

/**
 * 定时清理大部分缓存(每天执行).
 *
 * @since   2.0.0
 */
function ct_cache_flush_daily()
{
    // Rewrite rules Cache
    global $wp_rewrite;
    $wp_rewrite->flush_rules();

    // Transient cache
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_daily_%' OR `option_name` LIKE '_transient_timeout_ct_cache_daily_%'");
}
add_action('ct_setup_common_daily_event', 'ct_cache_flush_daily');

/**
 * 定时清理大部分缓存(每周).
 *
 * @since   2.0.0
 */
function ct_cache_flush_weekly()
{
    // Object Cache
    wp_cache_flush();

    // Transient cache
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_weekly_%' OR `option_name` LIKE '_transient_timeout_ct_cache_weekly%'");
}
add_action('ct_setup_common_weekly_event', 'ct_cache_flush_weekly');

/**
 * 清除所有缓存.
 *
 * @since   2.0.0
 */
function ct_clear_all_cache()
{
    // Object Cache
    wp_cache_flush();

    // Rewrite rules Cache
    global $wp_rewrite;
    $wp_rewrite->flush_rules();

    // Transient cache
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_%' OR `option_name` LIKE '_transient_timeout_ct_cache_%'");
}

/**
 * 模糊匹配键值删除transient的缓存.
 *
 * @since 2.0.0
 *
 * @param $key
 */
function ct_clear_cache_key_like($key)
{
    if (wp_using_ext_object_cache()) {
        return; //object cache无法模糊匹配key
    }
    global $wpdb;
    $key1 = '_transient_'.$key.'%';
    $key2 = '_transient_timeout_'.$key.'%';
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` LIKE %s OR `option_name` LIKE %s", $key1, $key2));
}

/**
 * 精确匹配键值删除transient的缓存(包括Object Cache).
 *
 * @since 2.0.0
 *
 * @param $key
 */
function ct_clear_cache_by_key($key)
{
    if (wp_using_ext_object_cache()) {
        wp_cache_delete($key, 'transient');
    } else {
        global $wpdb;
        $key1 = '_transient_'.$key;
        $key2 = '_transient_timeout_'.$key;
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` IN ('%s','%s')", $key1, $key2));
    }
}

/**
 * 预读取菜单时寻找缓存.
 *
 * @since   2.0.0
 *
 * @param string $menu 导航菜单
 * @param array  $args 菜单参数
 *
 * @return string
 */
function ct_cached_menu($menu, $args)
{
    if (CT_DEBUG) {
        return $menu;
    }

    global $wp_query;
    $cache_key = CACHE_PREFIX.'_hourly_nav_'.md5($args->theme_location.'_'.$wp_query->query_vars_hash);
    $cached_menu = get_transient($cache_key); //TODO： 尝试Object cache
    if ($cached_menu !== false) {
        return $cached_menu;
    }

    return $menu;
}
add_filter('pre_wp_nav_menu', 'ct_cached_menu', 10, 2);

/**
 * 读取菜单完成后设置缓存(缓存命中的菜单读取不会触发该动作).
 *
 * @since   2.0.0
 *
 * @param string $menu 导航菜单
 * @param array  $args 菜单参数
 *
 * @return string
 */
function ct_cache_menu($menu, $args)
{
    if (CT_DEBUG) {
        return $menu;
    }

    global $wp_query;
    $cache_key = CACHE_PREFIX.'_hourly_nav_'.md5($args->theme_location.'_'.$wp_query->query_vars_hash);
    set_transient($cache_key, sprintf(__('<!-- Nav cached %s -->', 'tt'), current_time('mysql')).$menu.__('<!-- Nav cache end -->', 'tt'), 3600);

    return $menu;
}
add_filter('wp_nav_menu', 'ct_cache_menu', 10, 2);

/**
 * 设置更新菜单时主动删除缓存.
 */
function ct_delete_menu_cache()
{
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE '_transient_ct_cache_hourly_nav_%' OR `option_name` LIKE '_transient_timeout_ct_cache_hourly_nav_%'");

    if (wp_using_ext_object_cache()) {
        wp_cache_flush();
    }
}
add_action('wp_update_nav_menu', 'ct_delete_menu_cache');


/**
 * 文章点赞或取消赞时删除对应缓存.
 *
 * @since   2.0.0
 *
 * @param int $post_ID
 */
function ct_clear_cache_for_stared_or_unstar_post($post_ID) {
    $cache_key = 'ct_cache_daily_vm_SinglePostVM_post' . $post_ID;
    $cache_key2 = 'ct_cache_daily_vm_SinglePostVM_post' . $post_ID . '_u' .get_current_user_id().'_not_reviewed_not_sale';
    $cache_key3 = 'ct_cache_daily_vm_SinglePostVM_post' . $post_ID . '_u' .get_current_user_id().'_reviewed_not_sale';
    $cache_key4 = 'ct_cache_daily_vm_SinglePostVM_post' . $post_ID . '_u' .get_current_user_id().'_not_reviewed_sale';
    $cache_key5 = 'ct_cache_daily_vm_SinglePostVM_post' . $post_ID . '_u' .get_current_user_id().'_reviewed_sale';
    delete_transient($cache_key);
    delete_transient($cache_key2);
    delete_transient($cache_key3);
    delete_transient($cache_key4);
    delete_transient($cache_key5);
}
add_action('ct_stared_post', 'ct_clear_cache_for_stared_or_unstar_post', 10 , 1);
add_action('ct_unstared_post', 'ct_clear_cache_for_stared_or_unstar_post', 10, 1);

/**
 * 文章点赞或取消赞时删除对应用户的UC Stars缓存.
 *
 * @since   2.0.0
 *
 * @param int $post_ID
 * @param int $author_id
 */
function ct_clear_cache_for_uc_stars($post_ID, $author_id) {
    $cache_key = 'ct_cache_daily_vm_UCStarsVM_author' . $author_id . '_page'; //模糊键值
    //delete_transient($cache_key);
    ct_clear_cache_key_like($cache_key);
    ct_clear_cache_by_key($cache_key . '1');
}
add_action('ct_stared_post', 'ct_clear_cache_for_uc_stars', 10 , 2);
add_action('ct_unstared_post', 'ct_clear_cache_for_uc_stars', 10, 2);

/**
 * 文章点赞/收藏创建通知
 *
 * @since   2.0.0
 * @param   int $post_ID
 * @param   int $author_id
 * @return  void
 */
function ct_stars_create_notification($post_ID) {
  global $current_user;
  get_currentuserinfo();
  $post = get_post($post_ID);
  ct_create_message($current_user->ID, 0, 'System', 'star', sprintf( __('您在>>%1$s<<中点赞并且收藏成功', 'tt'), $post->post_title ), '');
}
add_action('ct_stared_post', 'ct_stars_create_notification', 10 , 2);

/**
 * 订单状态变更时删除相关缓存.
 *
 * @since   2.0.0
 *
 * @param int $order_id
 */
function ct_clear_cache_for_order_relates($order_id)
{
    $order = ct_get_order($order_id);
    if (!$order) {
        return;
    }

    //Product VM
    delete_transient(sprintf('ct_cache_daily_vm_ShopProductVM_product%1$s_user%2$s', $order->product_id, $order->user_id));
    //Order Detail VM
    delete_transient(sprintf('ct_cache_daily_vm_MeOrderVM_user%1$s_seq%2$s', $order->user_id, $order->id));
    //Orders VM
    delete_transient(sprintf('ct_cache_daily_vm_MeOrdersVM_user%1$s_typeall', $order->user_id));
    delete_transient(sprintf('ct_cache_daily_vm_MeOrdersVM_user%1$s_typecash', $order->user_id));
    delete_transient(sprintf('ct_cache_daily_vm_MeOrdersVM_user%1$s_typecredit', $order->user_id));
}
add_action('ct_order_status_change', 'ct_clear_cache_for_order_relates');

/**
 * 保存文章时清理相关缓存.
 *
 * @since 2.0.0
 *
 * @param $post_id
 */
function ct_clear_cache_for_post_relates($post_id)
{
    $post_type = get_post_type($post_id);
    $user_id = get_current_user_id();
    if ($post_type == 'post') {
        // 文章本身
        delete_transient(sprintf('ct_cache_daily_vm_SinglePostVM_post%1$s_page_1_u%2$s_reviewed_not_sale_not_star', $post_id, $user_id));
        // 文章列表
        delete_transient('ct_cache_daily_vm_HomeLatestVM_page1');
        // 分类列表
        // TODO
    } elseif ($post_type == 'page') {
        // 页面本身
        delete_transient(sprintf('ct_cache_daily_vm_SinglePageVM_page%1$s', $post_id));
    } elseif ($post_type == 'product') {
        // 商品本身
        delete_transient(sprintf('ct_cache_daily_vm_ShopProductVM_product%1$s_user%2$s', $post_id, $user_id));
        // 商品列表
        delete_transient('ct_cache_daily_vm_ShopHomeVM_page1_sort_latest');
        delete_transient('ct_cache_daily_vm_ShopHomeVM_page1_sort_popular');
    }
}
add_action('save_post', 'ct_clear_cache_for_post_relates');

/**
 * 输出小工具前尝试检索缓存.
 *
 * @since 2.0.0
 *
 * @param $value
 * @param $type
 *
 * @return string|bool
 */
function ct_retrieve_widget_cache($value, $type)
{
    if (ct_get_option('tt_theme_debug', false)) {
        return false;
    }

    $cache_key = CACHE_PREFIX.'_daily_widget_'.$type;
    $cache = get_transient($cache_key);

    return $cache;
}
add_filter('ct_widget_retrieve_cache', 'ct_retrieve_widget_cache', 10, 2);

/**
 * 将查询获得的小工具的结果缓存.
 *
 * @since 2.0.0
 *
 * @param $value
 * @param $type
 * @param $expiration
 */
function ct_create_widget_cache($value, $type, $expiration = 21600)
{
    $cache_key = CACHE_PREFIX.'_daily_widget_'.$type;
    $value = '<!-- Widget cached '.current_time('mysql').' -->'.$value;
    set_transient($cache_key, $value, $expiration);
}
add_action('ct_widget_create_cache', 'ct_create_widget_cache', 10, 2);

/**
 * 配置Object Cache服务器.
 *
 * @since 2.0.0
 */
function ct_init_object_cache_server()
{
    if (in_array(of_get_option('tt_object_cache', 'none'), array('memcached','memcache', 'redis'))) {
        global $memcached_servers;
        $host = of_get_option('tt_memcache_host', '127.0.0.1');
        $port = of_get_option('tt_memcache_port', 11211);
        $memcached_servers[] = $host.':'.$port;
    } elseif (of_get_option('tt_object_cache', 'none') == 'redis') {
        global $redis_server;
        $redis_server['host'] = of_get_option('tt_redis_host', '127.0.0.1');
        $redis_server['port'] = of_get_option('tt_redis_port', 6379);
    }
}
ct_init_object_cache_server();

/**
 * 插入新评论时清理对应文章评论的缓存.
 *
 * @since 2.0.0
 *
 * @param int   $comment_ID
 * @param int   $comment_approved
 * @param array $commentdata
 */
function ct_clear_post_comments_cache ($comment_ID, $comment_approved, $commentdata) {
    if(!$comment_approved) return;

    $comment_post_ID = $commentdata['comment_post_ID'];
    $user_id = get_current_user_id();
    $cache_key1 = 'ct_cache_hourly_vm_PostCommentsVM_post' . $comment_post_ID . '_comments';
    $cache_key2 = 'ct_cache_hourly_vm_ProductCommentsVM_product' . $comment_post_ID . '_comments';
    $cache_key3 = 'ct_cache_hourly_vm_RecentCommentsVM_count5';
    $cache_key4 = 'ct_cache_hourly_vm_RecentCommentsVM_count6';
    $cache_key5 = 'ct_cache_hourly_vm_RecentCommentsVM_count8';
    $cache_key6 = 'ct_cache_hourly_vm_RecentCommentsVM_count10';
    delete_transient($cache_key1);
    delete_transient($cache_key2);
    delete_transient($cache_key3);
    delete_transient($cache_key4);
    delete_transient($cache_key5);
    delete_transient($cache_key6);
}
add_action('comment_post', 'ct_clear_post_comments_cache', 10, 3);

/**
 * 评论添加评论时间字段.
 *
 * @since   2.0.0
 *
 * @param   $comment_ID
 * @param   $comment_approved
 * @param   $commentdata
 */
function ct_update_post_latest_reviewed_meta($comment_ID, $comment_approved, $commentdata)
{
    if (!$comment_approved) {
        return;
    }
    $post_id = (int) $commentdata['comment_post_ID'];
    update_post_meta($post_id, 'tt_latest_reviewed', time());
}
add_action('comment_post', 'ct_update_post_latest_reviewed_meta', 10, 3);

/**
 * 评论列表输出callback.
 *
 * @since   2.0.0
 *
 * @param   $comment
 * @param   $args
 * @param   $depth
 */
function tt_comment($comment, $args, $depth) {
    global $postdata;
    if($postdata && property_exists($postdata, 'comment_status')) {
        $comment_open = $postdata->comment_status;
    }else{
        $comment_open = comments_open($comment->comment_post_ID);
    }
    $GLOBALS['comment'] = $comment;
    $author_user = get_user_by('ID', $comment->user_id);
    $author_name = $comment->comment_author;
    if($author_user) {
    $author_name = $author_user->nickname ? $author_user->nickname : $author_user->display_name;
    }
    global $wpdb;
    $cntt = get_comments('post_id='.$comment->comment_post_ID.'&parent=0&status=approve');//获取主评论总数量
    $commentcount = count($cntt) - array_search(get_comments('post_id='.$comment->comment_post_ID.'&comment__in='.$comment->comment_ID)[0],$cntt);
		if ( !$comment->comment_parent) {
			$commentcountText = '<div class="commentcountText">';
			if ( get_option('comment_order') != 'desc' ) { //倒序
				$commentcountText .= $commentcount . '楼';
			} else {

				switch ($commentcount) {
					case 2:
						$commentcountText .= '<span class="commentcountText3">沙发</span>';
						break;
					case 3:
						$commentcountText .= '<span class="commentcountText2">板凳</span>';
						break;
					case 4:
						$commentcountText .= '<span class="commentcountText1">地板</span>';
						break;
					default:
						$commentcountText .= $commentcount . '楼';
						break;
				}
			}
			$commentcountText .= '</div>';
		}
    ?>
    <li <?php comment_class(); ?> id="comment-<?php echo $comment->comment_ID;//comment_ID() ?>" data-current-comment-id="<?php echo $comment->comment_ID; ?>" data-parent-comment-id="<?php echo $comment->comment_parent; ?>" data-member-id="<?php echo $comment->user_id; ?>">

    <div class="comment-left pull-left">
        <?php if($author_user) { ?>
            <a rel="nofollow" href="<?php echo get_author_posts_url($comment->user_id); ?>">
                <img class="avatar lazy" src="<?php echo LAZY_PENDING_AVATAR; ?>" data-original="<?php echo ct_get_avatar( $author_user, 50 ); ?>">
            </a>
        <?php }else{ ?>
            <a rel="nofollow" href="javascript: void(0)">
                <img class="avatar lazy" src="<?php echo LAZY_PENDING_AVATAR; ?>" data-original="<?php echo ct_get_avatar( $comment->comment_author, 50 ); ?>">
            </a>
        <?php } ?>
    </div>

    <div class="comment-body">
        <div class="comment-content">
            <?php if($author_user) { ?>
                <a <?php if(!empty($author_user->user_url)){ echo 'rel="nofollow" href="'.ct_links_to_internal_links($author_user->user_url).'"';}else{ echo 'href="'.get_author_posts_url($comment->user_id).'"';}; ?> class="name replyName" target="_blank"><?php echo $author_name; ?><?php echo ct_get_user_comment_cap($comment->user_id); ?><?php echo ct_get_member_icon($comment->user_id); ?></a>
            <?php }else{ ?>
                <a rel="nofollow" href="<?php if(!empty($comment->comment_author_url)){ echo ct_links_to_internal_links($comment->comment_author_url);}else{ echo 'javascript: void(0)';}; ?>" class="name replyName" target="_blank"><?php echo $author_name; ?></a><span class="user_level">游客</span>
            <?php } ?>
            <?php if ( $comment->comment_approved == '0' ) : ?>
                <span class="pending-comment;"><?php $parent = $comment->comment_parent; if($parent != 0)echo '@'; comment_author_link($parent) ?><?php _e('Your comment is under review...','tt'); ?></span>
                <br />
            <?php endif; ?>
            <?php if ( $comment->comment_approved == '1' ) : ?>
                <div><?php echo get_comment_text($comment->comment_ID) ?></div>
            <?php endif; ?>
        </div>

        <span class="comment-time"><?php echo Utils::getTimeDiffString(get_comment_time('Y-m-d G:i:s', true)); ?></span>
        <?php echo $commentcountText; ?>
        <div class="comment-meta">
            <?php if($comment_open) { ?><a href="javascript:;" onclick="moveForm(&quot;<?php echo $comment->comment_ID; ?>&quot;, &quot;<?php echo $author_name; ?>&quot;)" class="respond-coin mr5" title="<?php _e('Reply', 'tt'); ?>"><i class="msg"></i><em><?php _e('Reply', 'tt'); ?></em></a><?php } ?>
            <span class="like"><i class="zan"></i><em class="like-count">(<?php echo (int)get_comment_meta($comment->comment_ID, 'tt_comment_likes', true); ?>)</em></span>
        </div>
    </div>
    <?php
}


/**
 * 评论列表输出callback(商店使用)
 *
 * @since   2.0.0
 * @param   $comment
 * @param   $args
 * @param   $depth
 */
function tt_shop_comment($comment, $args, $depth) {
    global $productdata;
    if($productdata && property_exists($productdata, 'comment_status')) {
        $comment_open = $productdata->comment_status;
    }else{
        $comment_open = comments_open($comment->comment_ID);
    }

    $GLOBALS['comment'] = $comment;
    $rating = (int)get_comment_meta($comment->comment_ID, 'tt_rating_product', true);
    $author_user = get_user_by('ID', $comment->user_id);
    ?>
<li <?php comment_class(); ?> id="comment-<?php echo $comment->comment_ID;//comment_ID() ?>" data-current-comment-id="<?php echo $comment->comment_ID; ?>" data-parent-comment-id="<?php echo $comment->comment_parent; ?>" data-member-id="<?php echo $comment->user_id; ?>">
    <div class="comment-left pull-left">
        <?php if($author_user) { ?>
            <a rel="nofollow" href="<?php echo get_author_posts_url($comment->user_id); ?>">
                <img class="avatar lazy" src="<?php echo LAZY_PENDING_AVATAR; ?>" data-original="<?php echo ct_get_avatar( $author_user, 50 ); ?>">
            </a>
        <?php }else{ ?>
            <a rel="nofollow" href="javascript: void(0)">
                <img class="avatar lazy" src="<?php echo LAZY_PENDING_AVATAR; ?>" data-original="<?php echo ct_get_avatar( $comment->comment_author, 50 ); ?>">
            </a>
        <?php } ?>
    </div>
    <div class="comment-body">
        <div class="comment-content">
            <?php if($author_user) { ?>
                <a rel="nofollow" href="<?php echo get_author_posts_url($comment->user_id); ?>" class="name replyName"><?php echo $comment->comment_author; ?><?php echo ct_get_member_icon($comment->user_id); ?></a>
            <?php }else{ ?>
                <a rel="nofollow" href="javascript: void(0)" class="name replyName"><?php echo $comment->comment_author; ?></a>
            <?php } ?>
            <span class="comment-time"><?php echo ' - ' . Utils::getTimeDiffString(get_comment_time('Y-m-d G:i:s', true)); ?></span>
            <?php if ( $comment->comment_approved == '0' ) : ?>
                <span class="pending-comment;"><?php $parent = $comment->comment_parent; if($parent != 0)echo '@'; comment_author_link($parent) ?><?php _e('Your comment is under review...','tt'); ?></span>
                <br />
            <?php endif; ?>
            <?php if ( $comment->comment_approved == '1' ) : ?>
                <div><?php echo get_comment_text($comment->comment_ID) ?></div>
            <?php endif; ?>
        </div>
        <?php if($rating) { ?>
            <span itemprop="reviewRating" itemscope="" itemtype="http://schema.org/Rating" class="star-rating tico-star-o" title="<?php printf(__('Rated %d out of 5', 'tt'), $rating); ?>">
            <span class="tico-star" style="<?php echo sprintf('width:%d', intval($rating*100/5)) . '%;'; ?>"></span>
        </span>
        <?php } ?>
        <div class="comment-meta">
            <?php if($comment_open) { ?><a href="javascript:;" class="respond-coin mr5" title="<?php _e('Reply', 'tt'); ?>"><i class="msg"></i><em><?php _e('Reply', 'tt'); ?></em></a><?php } ?>
        </div>

        <div class="respond-submit reply-form">
            <div class="text"><input id="<?php echo 'comment-replytext' . $comment->comment_ID; ?>" type="text"><div class="tip"><?php _e('Reply', 'tt'); ?><a><?php echo $comment->comment_author; ?></a>：</div></div>
            <div class="err text-danger"></div>
            <div class="submit-box clearfix">
                <button class="btn btn-danger pull-right reply-submit" type="submit" title="<?php _e('Reply', 'tt'); ?>" ><?php _e('Reply', 'tt'); ?></button>
            </div>
        </div>
    </div>
    <?php
}


function tt_end_comment() {
    echo '</li>';
}

/**
 * 主题扩展.
 *
 * @since   2.0.0
 */
function cute_setup()
{
    // 开启自动feed地址
    add_theme_support('automatic-feed-links');

    // 开启缩略图
    add_theme_support('post-thumbnails');

    // 增加文章形式
    add_theme_support('post-formats', array('audio', 'aside', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video'));

    // 菜单区域
    $menus = array(
        'header-menu' => __('Top Menu', 'tt'), //顶部菜单
        'footer-menu' => __('Foot Menu', 'tt'), //底部菜单
    );
    if (CUTE_PRO && ct_get_option('tt_enable_shop', false)) {
        $menus['shop-menu'] =  '右侧快速导航';
    }
    register_nav_menus($menus);

    // 必须和推荐插件安装提醒
    function ct_register_required_plugins()
    {
        $plugins = array(
            // 浏览数统计必装
            array(
                'name' => 'WP-PostViews',
                'slug' => 'wp-postviews',
                'source' => 'https://downloads.wordpress.org/plugin/wp-postviews.1.75.zip',
                'required' => true,
                'version' => '1.75',
                'force_activation' => true,
                'force_deactivation' => false,
            ),


        );
        $config = array(
            'domain' => 'tt',         	// Text domain - likely want to be the same as your theme.
            'default_path' => CUTE_THEME_DIR.'/dash/plugins',                         	// Default absolute path to pre-packaged plugins
            'menu' => 'install-required-plugins', 	// Menu slug
            'has_notices' => true,                       	// Show admin notices or not
            'is_automatic' => false,					   	// Automatically activate plugins after installation or not
            'message' => '',							// Message to output right before the plugins table
            'strings' => array(
                'page_title' => __('Install Required Plugins', 'tt'),
                'menu_title' => __('Install Plugins', 'tt'),
                'installing' => __('Installing: %s', 'tt'), // %1$s = plugin name
                'oops' => __('There is a problem with the plugin API', 'tt'),
                'notice_can_install_required' => _n_noop('Cute 需要下列插件: %1$s', 'Cute 需要下列插件: %1$s', 'tt'), // %1$s = plugin name(s)
                'notice_can_install_recommended' => _n_noop('Cute 推荐下列插件: %1$s', 'Cute 推荐下列插件: %1$s', 'tt'), // %1$s = plugin name(s)
                'notice_cannot_install' => _n_noop('Permission denied while installing %s plugin.', 'Permission denied while installing %s plugins.', 'tt'),
                'notice_can_activate_required' => _n_noop('The required plugin are not activated yet: %1$s', 'These required plugins are not activated yet: %1$s', 'tt'),
                'notice_can_activate_recommended' => _n_noop('The recommended plugin are not activated yet: %1$s', 'These recommended plugins are not activated yet: %1$s', 'tt'),
                'notice_cannot_activate' => _n_noop('Permission denied while activating the %s plugin.', 'Permission denied while activating the %s plugins.', 'tt'),
                'notice_ask_to_update' => _n_noop('The plugin need update: %1$s.', 'These plugins need update: %1$s.', 'tt'), // %1$s = plugin name(s)
                'notice_cannot_update' => _n_noop('Permission denied while updating the %s plugin.', 'Permission denied while updating %s plugins.', 'tt'),
                'install_link' => _n_noop('Install the plugin', 'Install the plugins', 'tt'),
                'activate_link' => _n_noop('Activate the installed plugin', 'Activate the installed plugins', 'tt'),
                'return' => __('return back', 'tt'),
                'plugin_activated' => __('Plugin activated', 'tt'),
                'complete' => __('All plugins are installed and activated %s', 'tt'), // %1$s = dashboard link
                'nag_type' => 'updated', // Determines admin notice type - can only be 'updated' or 'error'
            ),
        );
        tgmpa($plugins, $config);
    }
    add_action('tgmpa_register', 'ct_register_required_plugins');
}
add_action('after_setup_theme', 'cute_setup');

/**
 * 建立Avatar文件夹.
 *
 * @since   2.0.0
 */
function ct_add_avatar_folder()
{
    $upload_dir = WP_CONTENT_DIR.'/uploads';
    $avatar_dir = WP_CONTENT_DIR.'/uploads/avatars';
    if (!is_dir($avatar_dir)) {
        try {
            mkdir($upload_dir, 0755);
            mkdir($avatar_dir, 0755);
        } catch (Exception $e) {
            if (ct_get_option('tt_theme_debug', false)) {
                $message = __('Create avatar upload folder failed, maybe check your php.ini to enable `mkdir` function.\n', 'tt').__('Caught exception: ', 'tt').$e->getMessage().'\n';
                $title = __('WordPress internal error', 'tt');
                wp_die($message, $title);
            }
        }
    }
}
add_action('load-themes.php', 'ct_add_avatar_folder');

/**
 * 建立上传图片的临时文件夹.
 *
 * @since   2.0.0
 */
function ct_add_upload_tmp_folder()
{
    $tmp_dir = WP_CONTENT_DIR.'/uploads/tmp';
    if (!is_dir($tmp_dir)) {
        try {
            mkdir($tmp_dir, 0755);
        } catch (Exception $e) {
            if (ct_get_option('tt_theme_debug', false)) {
                $message = __('Create tmp upload folder failed, maybe check your php.ini to enable `mkdir` function.\n', 'tt').__('Caught exception: ', 'tt').$e->getMessage().'\n';
                $title = __('WordPress internal error', 'tt');
                wp_die($message, $title);
            }
        }
    }
}
add_action('load-themes.php', 'ct_add_upload_tmp_folder');

/**
 * 复制Object-cache.php到wp-content目录.
 *
 * @since   2.0.0
 */
function shapeSpace_custom_admin_notice($type) { ?>
<div class="notice notice-error is-dismissible">
    <p>你开启了对象缓存，但服务器未安装对象缓存扩展，所以对象缓存并未生效，请查看主题使用手册安装！</p>
</div>

<?php }
function ct_copy_object_cache_plugin()
{
    $object_cache_type = ct_get_option('tt_object_cache', 'none');
    if ($object_cache_type == 'memcached' && !class_exists('Memcached')) {
        add_action('admin_notices', 'shapeSpace_custom_admin_notice');
    }
    if ($object_cache_type == 'memcache' && !class_exists('Memcache')) {
        add_action('admin_notices', 'shapeSpace_custom_admin_notice');
    }
    if ($object_cache_type == 'redis' && !class_exists('Redis')) {
        add_action('admin_notices', 'shapeSpace_custom_admin_notice');
    }

    $last_use_cache_type = get_option('tt_object_cache_type');
    if (in_array($object_cache_type, array('memcached', 'memcache', 'redis')) && $last_use_cache_type != $object_cache_type && file_exists(CUTE_THEME_DIR.'/dash/plugins/'.$object_cache_type.'/object-cache.php')) {
        try {
            copy(CUTE_THEME_DIR.'/dash/plugins/'.$object_cache_type.'/object-cache.php', WP_CONTENT_DIR.'/object-cache.php');
            update_option('tt_object_cache_type', $object_cache_type);
        } catch (Exception $e) {
            if (ct_get_option('tt_theme_debug', false)) {
                $message = __('Can not copy `object-cache.php` to `wp-content` dir.\n', 'tt').__('Caught exception: ', 'tt').$e->getMessage().'\n';
                $title = __('Copy plugin error', 'tt');
                wp_die($message, $title);
            }
        }
    }
}
add_action('admin_menu', 'ct_copy_object_cache_plugin');

/**
 * 复制Timthumb图片裁剪插件必须的缓存引导文件至指定目录.
 *
 * @since   2.0.0
 */
function ct_copy_timthumb_cache_base()
{
    $cache_dir = WP_CONTENT_DIR.'/cache';
    if (!is_dir($cache_dir)) {
        try {
            mkdir($cache_dir, 0755);
            mkdir($cache_dir.'/timthumb', 0755);
        } catch (Exception $e) {
            if (ct_get_option('tt_theme_debug', false)) {
                $message = __('Create timthumb cache folder failed, maybe check your php.ini to enable `mkdir` function.\n', 'tt').__('Caught exception: ', 'tt').$e->getMessage().'\n';
                $title = __('Create folder error', 'tt');
                wp_die($message, $title);
            }
        }
    }

    if (is_dir($cache_dir)) {
        try {
            copy(CUTE_THEME_DIR.'/dash/plugins/timthumb/index.html', WP_CONTENT_DIR.'/cache/timthumb/index.html');
            copy(CUTE_THEME_DIR.'/dash/plugins/timthumb/timthumb_cacheLastCleanTime.touch', WP_CONTENT_DIR.'/cache/timthumb/timthumb_cacheLastCleanTime.touch');
        } catch (Exception $e) {
            if (ct_get_option('tt_theme_debug', false)) {
                $message = __('Can not copy `memcache object-cache.php` to `wp-content` dir.\n', 'tt').__('Caught exception: ', 'tt').$e->getMessage().'\n';
                $title = __('WordPress internal error', 'tt');
                wp_die($message, $title);
            }
        }
    }
}
add_action('load-themes.php', 'ct_copy_timthumb_cache_base');

/**
 * 重置缩略图的默认尺寸.
 *
 * @since   2.0.0
 */
function ct_reset_image_size()
{
    $enable = of_get_option('tt_enable_wp_crop', false);
    update_option('thumbnail_size_w', $enable ? 225 : 0);
    update_option('thumbnail_size_h', $enable ? 150 : 0);
    update_option('thumbnail_crop', 1);
    update_option('medium_size_w', $enable ? 375 : 0);
    update_option('medium_size_h', $enable ? 250 : 0);
    update_option('large_size_w', $enable ? 960 : 0);
    update_option('large_size_h', $enable ? 640 : 0);
    update_option( 'medium_large_size_w', $enable ? 768 : 0 );
}
add_action('load-themes.php', 'ct_reset_image_size');

/* 建立数据表 */
//TODO: add tables

/**
 * 新建粉丝和关注所用的数据表.
 *
 * @since 2.0.0
 */
function ct_install_follow_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $table = $prefix.'tt_follow';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $table_charset .= " COLLATE $wpdb->collate";
        }
    }
    $sql = "CREATE TABLE $table (
        `id` int NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(id),
        INDEX uid_index(user_id),
        INDEX fuid_index(follow_user_id),
        `user_id` int,
        `follow_user_id` int,
        `follow_status` int,
        `follow_time` datetime
    ) ENGINE = MyISAM $table_charset;";
    maybe_create_table($table, $sql);
}
add_action('load-themes.php', 'ct_install_follow_table');

/**
 * 新建消息所用的数据表.
 *
 * @since 2.0.0
 */
function ct_install_message_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $table = $prefix.'tt_messages';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $table_charset .= " COLLATE $wpdb->collate";
        }
    }
    $sql = "CREATE TABLE $table (
        `msg_id` int NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(msg_id),
        INDEX uid_index(user_id),
        INDEX sid_index(sender_id),
        INDEX mtype_index(msg_type),
        INDEX mdate_index(msg_date),
        INDEX mstatus_index(msg_read),
        `user_id` int,
        `sender_id` int,
        `sender`  varchar(50),
        `msg_type` varchar(20),
        `msg_date` datetime,
        `msg_title` text,
        `msg_content` text,
        `msg_read`  boolean DEFAULT 0,
        `msg_status`  varchar(20)
    ) ENGINE = MyISAM $table_charset;";
    maybe_create_table($table, $sql);
}
add_action('load-themes.php', 'ct_install_message_table');

/**
 * 新建会员所用的数据表.
 *
 * @since 2.0.0
 */
function ct_install_membership_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $users_table = $prefix.'tt_members';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $table_charset .= " COLLATE $wpdb->collate";
        }
    }
    $create_vip_users_sql = "CREATE TABLE $users_table (id int(11) NOT NULL auto_increment,user_id int(11) NOT NULL,user_type tinyint(4) NOT NULL default 0,user_level tinyint(4) NOT NULL default 1,startTime datetime NOT NULL default '0000-00-00 00:00:00',endTime datetime NOT NULL default '0000-00-00 00:00:00',endTimeStamp int NOT NULL default 0,PRIMARY KEY (id),INDEX uid_index(user_id),INDEX utype_index(user_type),INDEX endTime_index(user_id)) ENGINE = MyISAM $table_charset;";
    maybe_create_table($users_table, $create_vip_users_sql);
}
add_action('load-themes.php', 'ct_install_membership_table');
//add_action('admin_menu', 'ct_install_membership_table');
function ct_add_membership_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_members';
    $column = $wpdb->query("SELECT * FROM information_schema.columns WHERE table_name = '".$orders_table."' AND column_name = 'user_level'");
    if(!$column){
    $wpdb->query("ALTER TABLE {$orders_table} ADD COLUMN user_level tinyint(4) NOT NULL default 1;");
    }
}
add_action('admin_init', 'ct_add_membership_table');

/**
 * 新建现金充值券所用的数据表.
 *
 * @since 2.2.0
 */
function ct_install_card_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $table_charset .= " COLLATE $wpdb->collate";
        }
    }
    $create_cards_sql = "CREATE TABLE $cards_table (
        id int(11) NOT NULL auto_increment,
        denomination int NOT NULL DEFAULT 100,
        card_id VARCHAR(20) NOT NULL,
        card_secret VARCHAR(20) NOT NULL,
        create_time datetime NOT NULL default '0000-00-00 00:00:00',
        status SMALLINT NOT NULL DEFAULT 1,
        type SMALLINT NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        INDEX status_index(status),
        INDEX denomination_index(denomination)) ENGINE = MyISAM $table_charset;";
    maybe_create_table($cards_table, $create_cards_sql);
}
add_action('load-themes.php', 'ct_install_card_table');

/**
 * 不可归类工具.
 */

/**
 * 根据name获取主题设置(of_get_option别名函数).
 *
 * @since   2.0.0
 *
 * @param string $name    设置ID
 * @param mixed  $default 默认值
 *
 * @return mixed 具体设置值
 */
function ct_get_option($name, $default = '')
{
    return of_get_option($name, $default);
}
// TODO: Utils::function_name -> tt_function_name

// TODO: ct_url_for
/**
 * 获取各种Url.
 *
 * @since   2.0.0
 *
 * @param string $key      待查找路径的关键字
 * @param mixed  $arg      接受一个参数，用于动态链接(如一个订单号，一个用户昵称，一个用户id或者一个用户对象)
 * @param bool   $relative 是否使用相对路径
 *
 * @return string | false
 */
function ct_url_for($key, $arg = null, $relative = false)
{
    $routes = (array) json_decode(SITE_ROUTES);
    if (array_key_exists($key, $routes)) {
        return $relative ? '/'.$routes[$key] : home_url('/'.$routes[$key]);
    }

    // 输入参数$arg为user时获取其ID使用
    $get_uid = function ($var) {
        if ($var instanceof WP_User) {
            return $var->ID;
        } else {
            return intval($var);
        }
    };

    $endpoint = null;
    switch ($key) {
        case 'my_order':
            $endpoint = 'me/order/'.(int) $arg;
            break;
        case 'uc_comments':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/comments';
            break;
        case 'uc_profile':
            $endpoint = 'u/'.call_user_func($get_uid, $arg);
            break;
        case 'uc_me':
            $endpoint = 'u/'.get_current_user_id();
            break;
        case 'uc_latest':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/latest';
            break;
        case 'uc_product':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/product';
            break;
        case 'uc_stars':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/stars';
            break;
        case 'uc_followers':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/followers';
            break;
        case 'uc_following':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/following';
            break;
        case 'uc_activities':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/activities';
            break;
        case 'uc_chat':
            $endpoint = 'u/'.call_user_func($get_uid, $arg).'/chat';
            break;
        case 'manage_user':
            $endpoint = 'management/users/'.intval($arg);
            break;
        case 'manage_order':
            $endpoint = 'management/orders/'.intval($arg);
            break;
        case 'manage_apply':
            $endpoint = 'management/applys/'.intval($arg);
            break;
        case 'shop_archive':
            $endpoint = ct_get_option('tt_product_archives_slug', 'shop');
            break;
        case 'edit_post':
            $endpoint = 'me/editpost/'.absint($arg);
            break;
        case 'download':
            $endpoint = 'site/download?_='.urlencode(rtrim(ct_encrypt($arg, ct_get_option('tt_private_token')), '='));
            break;
    }
    if ($endpoint) {
        return $relative ? '/'.$endpoint : home_url('/'.$endpoint);
    }

    return false;
}

/**
 * 获取当前页面url.
 *
 * @since   2.0.0
 *
 * @param string $method 获取方法，分别为PHP的$_SERVER对象获取(php)和WordPress的全局wp_query对象获取(wp)
 *
 * @return string
 */
function ct_get_current_url($method = 'php')
{
    if ($method === 'wp') {
        return Utils::getCurrentUrl();
    }

    return Utils::getPHPCurrentUrl();
}

/**
 * 登录的url.
 *
 * @since   2.0.0
 *
 * @param string $redirect 重定向链接，未url encode
 *
 * @return string
 */
function ct_signin_url($redirect)
{
    return ct_set_default_login_url('', $redirect);
}

/**
 * 注册的url.
 *
 * @since   2.0.0
 *
 * @param string $redirect 重定向链接，未url encode
 *
 * @return string
 */
function ct_signup_url($redirect)
{
    $signup_url = ct_url_for('signup');

    if (!empty($redirect)) {
        $signup_url = add_query_arg('redirect_to', urlencode($redirect), $signup_url);
    }

    return $signup_url;
}

/**
 * 注销的url.
 *
 * @since   2.0.0
 *
 * @param string $redirect 重定向链接，未url encode
 *
 * @return string
 */
function ct_signout_url($redirect = '')
{
    if (empty($redirect)) {
        $redirect = home_url();
    }

    return ct_set_default_logout_url('', $redirect);
}

/**
 * 为链接添加重定向链接.
 *
 * @since   2.0.0
 *
 * @param string $url
 * @param string $redirect
 *
 * @return string
 */
function ct_add_redirect($url, $redirect = '')
{
    if ($redirect) {
        return add_query_arg('redirect_to', urlencode($redirect), $url);
    } elseif (isset($_GET['redirect_to'])) {
        return add_query_arg('redirect_to', urlencode(esc_url_raw($_GET['redirect_to'])), $url);
    } elseif (isset($_GET['redirect'])) {
        return add_query_arg('redirect_to', urlencode(esc_url_raw($_GET['redirect'])), $url);
    }

    return add_query_arg('redirect_to', urlencode(home_url()), $url);
}

/**
 * 可逆加密.
 *
 * @since   2.0.0
 *
 * @param mixed  $data 待加密数据
 * @param string $key  加密密钥
 *
 * @return string
 */
function ct_encrypt($data, $key)
{
    if (is_numeric($data)) {
        $data = strval($data);
    } else {
        $data = maybe_serialize($data);
    }
    $key = md5($key);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; ++$i) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key[$x];
        ++$x;
    }
    for ($i = 0; $i < $len; ++$i) {
        $str .= chr(ord($data[$i]) + (ord($char[$i])) % 256);
    }

    return base64_encode($str);
}

/**
 * 解密.
 *
 * @since   2.0.0
 *
 * @param string $data 待解密数据
 * @param string $key  密钥
 *
 * @return mixed
 */
function ct_decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; ++$i) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        ++$x;
    }
    for ($i = 0; $i < $len; ++$i) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }

    return maybe_unserialize($str);
}

/**
 * 加密解密数据.
 *
 * @since   2.0.0
 *
 * @param mixed  $data      待加密数据
 * @param string $operation 操作(加密|解密)
 * @param string $key       密钥
 * @param int    $expire    过期时间
 *
 * @return string
 */
function ct_authdata($data, $operation = 'DECODE', $key = '', $expire = 0)
{
    if ($operation != 'DECODE') {
        $data = maybe_serialize($data);
    }
    $ckey_length = 4;
    $key = md5($key ? $key : 'null');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($data, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $data = $operation == 'DECODE' ? base64_decode(substr($data, $ckey_length)) : sprintf('%010d', $expire ? $expire + time() : 0).substr(md5($data.$keyb), 0, 16).$data;
    $string_length = strlen($data);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; ++$i) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; ++$i) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; ++$i) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($data[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return maybe_unserialize(substr($result, 26));
        } else {
            return false;
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

/**
 * 替换默认的wp_die处理函数.
 *
 * @since   2.0.0
 *
 * @param string | WP_Error $message 错误消息
 * @param string            $title   错误标题
 * @param array             $args    其他参数
 */
function ct_wp_die_handler($message, $title = '', $args = array())
{
    $defaults = array('response' => 403);
    $r = wp_parse_args($args, $defaults);

    if (function_exists('is_wp_error') && is_wp_error($message)) {
        if (empty($title)) {
            $error_data = $message->get_error_data();
            if (is_array($error_data) && isset($error_data['title'])) {
                $title = $error_data['title'];
            }
        }
        $errors = $message->get_error_messages();
        switch (count($errors)) {
            case 0:
                $message = '';
                break;
            case 1:
                $message = "{$errors[0]}";
                break;
            default:
                $message = "<ul>\n\t\t<li>".join("</li>\n\t\t<li>", $errors)."</li>\n\t</ul>";
                break;
        }
    }

    if (!did_action('admin_head')) :
        if (!headers_sent()) {
            status_header($r['response']);
            nocache_headers();
            header('Content-Type: text/html; charset=utf-8');
        }

    if (empty($title)) {
        $title = __('WordPress &rsaquo; Error');
    }

    $text_direction = 'ltr';
    if (isset($r['text_direction']) && 'rtl' == $r['text_direction']) {
        $text_direction = 'rtl';
    } elseif (function_exists('is_rtl') && is_rtl()) {
        $text_direction = 'rtl';
    }

    // 引入自定义模板
    global $wp_query;
    $wp_query->query_vars['die_title'] = $title;
    $wp_query->query_vars['die_msg'] = $message;
    include_once CUTE_THEME_TPL.'/tpl.Error.php';
    endif;

    die();
}
function ct_wp_die_handler_switch()
{
    return 'ct_wp_die_handler';
}
add_filter('wp_die_handler', 'ct_wp_die_handler_switch');

/**
 * 获取当前页面需要应用的样式链接.
 *
 * @since   2.0.0
 *
 * @param string $filename 文件名
 *
 * @return string
 */

function ct_get_css($filename = '')
{
    if ($filename) {
        return CUTE_THEME_CDN_ASSETS.'/css/'.$filename;
    }

    $post_type = get_post_type();

    if (is_home()) {
        $filename = CSS_HOME;
    } elseif (is_single()) {
        if ($post_type === 'thread') {
            $filename = CSS_THREAD;
        } else if ($post_type === 'product') {
            $filename = CSS_PRODUCT;
        } else if ($post_type === 'bulletin') {
            $filename = CSS_PAGE;
        } else {
            $filename = CSS_SINGLE;
        }
    } elseif($post_type == 'thread' || get_query_var('is_thread_route')) {
        $filename = CSS_THREAD;
    } elseif ((is_archive() && !is_author()) || (is_search() && isset($_GET['in_shop']) && $_GET['in_shop'] == 1)) {
        $filename = get_post_type() === 'product' || (is_search() && isset($_GET['in_shop']) && $_GET['in_shop'] == 1) ? CSS_PRODUCT_ARCHIVE : CSS_ARCHIVE;
    } elseif (is_author()) {
        $filename = CSS_UC;
    } elseif (is_404()) {
        $filename = CSS_404;
    } elseif (get_query_var('is_me_route')) {
        $filename = CSS_ME;
    } elseif (get_query_var('action')) {
        $filename = CSS_ACTION;
    } elseif (is_front_page()) {
        $filename = CSS_FRONT_PAGE;
    } elseif (get_query_var('site_util')) {
        $filename = CSS_SITE_UTILS;
    } elseif (get_query_var('oauth')) {
        $filename = CSS_OAUTH;
    } elseif (get_query_var('is_manage_route')) {
        $filename = CSS_MANAGE;
    } elseif (is_search()) {
        $filename = CSS_ARCHIVE;
    } else {
        $filename = CSS_PAGE;
    }

    return CUTE_THEME_CDN_ASSETS.'/css/'.$filename;
}

function ct_get_custom_css()
{
    $ver = ct_get_option('tt_custom_css_cache_suffix');

    return add_query_arg('ver', $ver, ct_url_for('custom_css'));
}

/**
 * 条件判断类名.
 *
 * @param $base_class
 * @param $condition
 * @param string $active_class
 *
 * @return string
 */
function ct_conditional_class($base_class, $condition, $active_class = 'active')
{
    if ($condition) {
        return $base_class.' '.$active_class;
    }

    return $base_class;
}

/**
 * 二维码API.
 *
 * @since 2.0.0
 *
 * @param $text
 * @param $size
 *
 * @return string
 */
function ct_qrcode($text, $size)
{
    //TODO size
    return ct_url_for('qr').'?text='.$text;
}

/**
 * 页脚年份.
 *
 * @since 2.0.0
 *
 * @return string
 */
function ct_copyright_year()
{
    $now_year = date('Y');
    $open_date = ct_get_option('tt_site_open_date', $now_year);
    $open_year = substr($open_date, 0, 4);

    return $open_year.'-'.$now_year.'&nbsp;&nbsp;';
}

/**
 * 生成推广链接.
 *
 * @param int    $user_id
 * @param string $base_link
 *
 * @return string
 */
function ct_get_referral_link($user_id = 0, $base_link = '')
{
    if (!$base_link) {
        $base_link = home_url();
    }
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    return add_query_arg(array('ref' => $user_id), $base_link);
}

/**
 * 获取GET方法http响应状态代码
 *
 * @since 2.0.0
 *
 * @param $theURL
 *
 * @return string
 */
function ct_get_http_response_code($theURL)
{
    @$headers = get_headers($theURL);

    return substr($headers[0], 9, 3);
}

/**
 * Curl GET方式获取url响应文档.
 *
 * @param $url
 *
 * @return mixed
 */
function ct_curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * Curl POST方式获取url响应文档.
 *
 * @param $url
 * @param $data
 *
 * @return mixed
 */
function ct_curl_post($url, $data)
{
    $post_data = http_build_query($data);
    $post_url = $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $return = curl_exec($ch);
    if (curl_errno($ch)) {
        return '';
    }
    curl_close($ch);

    return $return;
}

/**
 * 过滤multicheck选项的设置值
 *
 * @since 2.0.5
 *
 * @param $option
 *
 * @return array
 */
function ct_filter_of_multicheck_option($option)
{
    // 主题选项框架获得multicheck类型选项的值为 array(id => bool), 而我们需要的是bool为true的array(id)
    if (!is_array($option)) {
        return $option;
    }

    $new_option = array();
    foreach ($option as $key => $value) {
        if ($value) {
            $new_option[] = $key;
        }
    }

    return $new_option;
}

/**
 * 分页.
 *
 * @param $base
 * @param $current
 * @param $max
 */
function ct_default_pagination($base, $current, $max)
{
    ?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php $pagination = paginate_links(array(
                'base' => $base,
                'format' => '?paged=%#%',
                'current' => $current,
                'total' => $max,
                'type' => 'array',
                'prev_next' => true,
                'prev_text' => '<i class="tico tico-angle-left"></i>',
                'next_text' => '<i class="tico tico-angle-right"></i>',
            )); ?>
            <?php foreach ($pagination as $page_item) {
                echo '<li class="page-item">'.$page_item.'</li>';
            } ?>
        </ul>
        <div class="page-nums">
            <span class="current-page"><?php printf(__('Current Page %d', 'tt'), $current); ?></span>
            <span class="separator">/</span>
            <span class="max-page"><?php printf(__('Total %d Pages', 'tt'), $max); ?></span>
        </div>
    </nav>
    <?php
}

/**
 * 分页.
 *
 * @param $base
 * @param $current
 * @param $max
 */
function ct_pagination($base, $current, $max)
{
    ?>
    <nav class="pagination-new">
        <ul>
            <?php $pagination = paginate_links(array(
                'base' => $base,
                'format' => '?paged=%#%',
                'current' => $current,
                'total' => $max,
                'type' => 'array',
                'prev_next' => true,
                'prev_text' => '<span class="prev">'.__('PREV PAGE', 'tt').'</span>',
                'next_text' => '<span class="next">'.__('NEXT PAGE', 'tt').'</span>',
            )); ?>
            <?php foreach ($pagination as $page_item) {
                   $page_item = str_replace('_1.html', '.html',$page_item);
                   $page_item = str_replace("'", '"',$page_item);
                  echo '<li class="page-item">'.$page_item.'</li>';
            } ?>
            <li class="page-item"><span class="max-page"><?php printf(__('Total %d Pages', 'tt'), $max); ?></span></li>
        </ul>
    </nav>
    <?php
}

/**
 * 获取文章总分页数量.
 *
 */
function ct_get_pagination_count($posts)
{
    $counts = substr_count($posts[0]->post_content, '<!--nextpage-->');
    $count = $counts + 1;

    return $count;
}

//load_func('base/func.Mail');
/**
 * 根据用户设置选择邮件发送方式.
 *
 * @since   2.0.0
 *
 * @param object $phpmailer PHPMailer对象
 */
function ct_switch_mailer($phpmailer)
{
    $mailer = ct_get_option('tt_default_mailer');
    if ($mailer === 'smtp') {
        //$phpmailer->isSMTP();
        $phpmailer->Mailer = 'smtp';
        $phpmailer->Host = ct_get_option('tt_smtp_host');
        $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
        $phpmailer->Port = ct_get_option('tt_smtp_port');
        $phpmailer->Username = ct_get_option('tt_smtp_username');
        $phpmailer->Password = ct_get_option('tt_smtp_password');

        // Additional settings…
        $phpmailer->SMTPSecure = ct_get_option('tt_smtp_secure');
        $phpmailer->FromName = ct_get_option('tt_smtp_name');
        $phpmailer->From = $phpmailer->Username; // ct_get_option('tt_mail_custom_address'); // 多数SMTP提供商要求发信人与SMTP服务器匹配，自定义发件人地址可能无效
        $phpmailer->Sender = $phpmailer->From; //Return-Path
        $phpmailer->AddReplyTo($phpmailer->From, $phpmailer->FromName); //Reply-To
    } else {
        // when use php mail
        $phpmailer->FromName = ct_get_option('tt_mail_custom_sender');
        $phpmailer->From = ct_get_option('tt_mail_custom_address');
    }
}
add_action('phpmailer_init', 'ct_switch_mailer');

/**
 * 发送邮件.
 *
 * @since 2.0.0
 *
 * @param string       $from     发件人
 * @param string       $to       收件人
 * @param string       $title    主题
 * @param string|array $args     渲染内容所需的变量对象
 * @param string       $template 模板，例如评论回复邮件模板、新用户、找回密码、订阅信等模板
 */
function cute_mail($from, $to, $title = '', $args = array(), $template = 'comment')
{
    $title = $title ? trim($title) : ct_get_mail_title($template);
    $content = cute_mail_render($args, $template);
    $blog_name = get_bloginfo('name');
    $sender_name = ct_get_option('tt_mail_custom_sender') || ct_get_option('tt_smtp_name', $blog_name);
    if (empty($from)) {
        $from = ct_get_option('tt_mail_custom_address', ct_get_option('tt_smtp_username')); //TODO: case e.g subdomain.domain.com
    }

    $fr = 'From: "'.$sender_name."\" <$from>";
    $headers = "$fr\nContent-Type: text/html; charset=".get_option('blog_charset')."\n";
    wp_mail($to, $title, $content, $headers);
}
add_action('ct_async_send_mail', 'cute_mail', 10, 5);

/**
 * 异步发送邮件.
 *
 * @since 2.0.0
 *
 * @param $from
 * @param $to
 * @param string $title
 * @param array  $args
 * @param string $template
 */
function cute_async_mail($from, $to, $title = '', $args = array(), $template = 'comment'){
    if(!current_user_can('edit_users')) {
            return cute_mail($from, $to, $title, $args, $template);
    }
    if(is_array($args)) {
        $args = base64_encode(json_encode($args));
    }
    do_action('send_mail', $from, $to, $title, $args, $template);
}

/**
 * 邮件内容的模板选择处理.
 *
 * @since   2.0.0
 *
 * @param string $content  未处理的邮件内容或者内容必要参数数组
 * @param string $template 渲染模板选择(reset_pass|..)
 *
 * @return string
 */
function cute_mail_render($content, $template = 'comment')
{
    // 使用Plates模板渲染引擎
    $templates = new League\Plates\Engine(CUTE_THEME_TPL.'/plates/emails');
    if (is_string($content)) {
        return $templates->render('pure', array('content' => $content));
    } elseif (is_array($content)) {
        return $templates->render($template, $content); // TODO confirm template exist
    }

    return '';
}

/**
 * 不同模板的邮件标题.
 *
 * @since   2.0.0
 *
 * @param string $template 邮件模板
 *
 * @return string
 */
function ct_get_mail_title($template = 'comment')
{
    $blog_name = get_bloginfo('name');
    switch ($template) {
        case 'comment':
            return sprintf(__('New Comment Notification - %s', 'tt'), $blog_name);
            break;
        case 'comment-admin':
            return sprintf(__('New Comment In Your Blog - %s', 'tt'), $blog_name);
            break;
        case 'contribute-post':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        case 'download':
            return sprintf(__('The Files You Asking For In %s', 'tt'), $blog_name);
            break;
        case 'download-admin':
            return sprintf(__('New Download Request Handled In Your Blog %s', 'tt'), $blog_name);
            break;
        case 'findpass':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        case 'login':
            return sprintf(__('New Login Event Notification - %s', 'tt'), $blog_name);
            break;
        case 'login-fail':
            return sprintf(__('New Login Fail Event Notification - %s', 'tt'), $blog_name);
            break;
        case 'reply':
            return sprintf(__('New Comment Reply Notification - %s', 'tt'), $blog_name);
            break;
        //TODO more
        default:
            return sprintf(__('Site Internal Notification - %s', 'tt'), $blog_name);
    }
}

/**
 * 评论回复邮件.
 *
 * @since 2.0.0
 *
 * @param $comment_id
 * @param $comment_object
 */
function ct_comment_mail_notify($comment_id, $comment_object)
{
    if (!ct_get_option('tt_comment_events_notify', false) || $comment_object->comment_approved != 1 || !empty($comment_object->comment_type)) {
        return;
    }
    date_default_timezone_set('Asia/Shanghai');
    $admin_notify = '1'; // admin 要不要收回复通知 ( '1'=要 ; '0'=不要 )
    $admin_email = get_bloginfo('admin_email'); // $admin_email 可改为你指定的 e-mail.
    $comment = get_comment($comment_id);
    $comment_author = trim($comment->comment_author);
    $comment_date = trim($comment->comment_date);
    $comment_link = htmlspecialchars(get_comment_link($comment_id));
    $comment_content = nl2br($comment->comment_content);
    $comment_author_email = trim($comment->comment_author_email);
    $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
    $parent_comment = !empty($parent_id) ? get_comment($parent_id) : null;
    $parent_email = $parent_comment ? trim($parent_comment->comment_author_email) : '';
    $post = get_post($comment_object->comment_post_ID);
    $post_author_email = get_user_by('id', $post->post_author)->user_email;

//    global $wpdb;
//    if ($wpdb->query("Describe {$wpdb->comments} comment_mail_notify") == '')
//        $wpdb->query("ALTER TABLE {$wpdb->comments} ADD COLUMN comment_mail_notify TINYINT NOT NULL DEFAULT 0;");
//    if (isset($_POST['comment_mail_notify']))
//        $wpdb->query("UPDATE {$wpdb->comments} SET comment_mail_notify='1' WHERE comment_ID='$comment_id'");
    //$notify = $parent_id ? $parent_comment->comment_mail_notify : '0';
    $notify = 1; // 默认全部提醒
    $spam_confirmed = $comment->comment_approved;
    //给父级评论提醒
    if ($parent_id != '' && $spam_confirmed != 'spam' && $notify == '1' && $parent_email != $comment_author_email) {
        $parent_author = trim($parent_comment->comment_author);
        $parent_comment_date = trim($parent_comment->comment_date);
        $parent_comment_content = nl2br($parent_comment->comment_content);
        $args = array(
            'parentAuthor' => $parent_author,
            'parentCommentDate' => $parent_comment_date,
            'parentCommentContent' => $parent_comment_content,
            'postTitle' => $post->post_title,
            'commentAuthor' => $comment_author,
            'commentDate' => $comment_date,
            'commentContent' => $comment_content,
            'commentLink' => $comment_link,
        );
        if (filter_var($post_author_email, FILTER_VALIDATE_EMAIL)) {
            cute_mail('', $parent_email, sprintf(__('%1$s在%2$s中回复你', 'tt'), $comment_object->comment_author, $post->post_title), $args, 'reply');
        }
        if ($parent_comment->user_id) {
            ct_create_message($parent_comment->user_id, $comment->user_id, $comment_author, 'comment', sprintf(__('我在%1$s中回复了你', 'tt'), $post->post_title), $comment_content);
        }
    }

    //给文章作者的通知
    if ($post_author_email != $comment_author_email && $post_author_email != $parent_email) {
        $args = array(
            'postTitle' => $post->post_title,
            'commentAuthor' => $comment_author,
            'commentContent' => $comment_content,
            'commentLink' => $comment_link,
        );
        if (filter_var($post_author_email, FILTER_VALIDATE_EMAIL)) {
            cute_mail('', $post_author_email, sprintf(__('%1$s在%2$s中回复你', 'tt'), $comment_author, $post->post_title), $args, 'comment');
        }
        ct_create_message($post->post_author, 0, 'System', 'notification', sprintf(__('%1$s在%2$s中回复你', 'tt'), $comment_author, $post->post_title), $comment_content);
    }

    //给管理员通知
    if ($post_author_email != $admin_email && $parent_id != $admin_email && $admin_notify == '1') {
        $args = array(
            'postTitle' => $post->post_title,
            'commentAuthor' => $comment_author,
            'commentContent' => $comment_content,
            'commentLink' => $comment_link,
        );
        cute_mail('', $admin_email, sprintf(__('%1$s上的文章有了新的回复', 'tt'), get_bloginfo('name')), $args, 'comment-admin');
        //ct_create_message() //TODO
    }
}
//add_action('comment_post', 'ct_comment_mail_notify');
add_action('wp_insert_comment', 'ct_comment_mail_notify', 99, 2);

/**
 * WP登录提醒.
 *
 * @since 2.0.0
 *
 * @param string $user_login
 */
function ct_wp_login_notify($user_login)
{
    if (!ct_get_option('tt_login_success_notify')) {
        return;
    }
    date_default_timezone_set('Asia/Shanghai');
    $admin_email = get_bloginfo('admin_email');
    $subject = __('你的博客空间登录提醒', 'tt');
    $args = array(
        'loginName' => $user_login,
        'ip' => $_SERVER['REMOTE_ADDR'],
    );
    cute_async_mail('', $admin_email, $subject, $args, 'login');
    //cute_mail('', $admin_email, $subject, $args, 'login');
}
add_action('wp_login', 'ct_wp_login_notify', 10, 1);

/**
 * WP登录错误提醒.
 *
 * @since 2.0.0
 *
 * @param string $login_name
 */
function ct_wp_login_failure_notify($login_name)
{
    if (!ct_get_option('tt_login_failure_notify')) {
        return;
    }
    date_default_timezone_set('Asia/Shanghai');
    $admin_email = get_bloginfo('admin_email');
    $subject = __('你的博客空间登录错误警告', 'tt');
    $args = array(
        'loginName' => $login_name,
        'ip' => $_SERVER['REMOTE_ADDR'],
    );
    cute_async_mail('', $admin_email, $subject, $args, 'login-fail');
}
add_action('wp_login_failed', 'ct_wp_login_failure_notify', 10, 1);

/**
 * 投稿文章发表时给作者添加积分和发送邮件通知.
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_pending_to_publish($post)
{
    $rec_post_num = (int) ct_get_option('tt_rec_post_num', '5');
    $rec_post_credit = (int) ct_get_option('tt_rec_post_credit', '50');
    $rec_post = (int) get_user_meta($post->post_author, 'tt_rec_post', true);
    if ($rec_post < $rec_post_num && $rec_post_credit) {
        //添加积分
        ct_update_user_credit($post->post_author, $rec_post_credit, sprintf(__('获得文章投稿奖励%1$s%2$s', 'tt'), $rec_post_credit,CREDIT_NAME), false);
        //发送邮件
        $user = get_user_by('id', $post->post_author);
        $user_email = $user->user_email;
        if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $subject = sprintf(__('你在%1$s上有新的文章发表', 'tt'), get_bloginfo('name'));
            $args = array(
                'postAuthor' => $user->display_name,
                'postLink' => get_permalink($post->ID),
                'postTitle' => $post->post_title,
            );
            cute_async_mail('', $user_email, $subject, $args, 'contribute-post');
        }
    }
    update_user_meta($post->post_author, 'tt_rec_post', $rec_post + 1);
}
add_action('pending_to_publish', 'ct_pending_to_publish', 10, 1);
add_action('ct_immediate_to_publish', 'ct_pending_to_publish', 10, 1);

/**
 * 开通或续费会员后发送邮件.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $type
 * @param $start_time
 * @param $end_time
 */
function ct_open_vip_email($user_id, $type, $start_time, $end_time)
{
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return;
    }
    $user_email = $user->user_email;
    $subject = __('会员状态变更提醒', 'tt');
    $vip_type_des = ct_get_member_type_string($type);
    $args = array(
        'adminEmail' => get_option('admin_email'),
        'vipType' => $vip_type_des,
        'startTime' => $start_time,
        'endTime' => $end_time,
    );
    cute_async_mail('', $user_email, $subject, $args, 'open-vip');
}

/**
 * 管理员手动提升会员后发送邮件.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $type
 * @param $start_time
 * @param $end_time
 */
function ct_promote_vip_email($user_id, $type, $start_time, $end_time)
{
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return;
    }
    $user_email = $user->user_email;
    $subject = __('会员状态变更提醒', 'tt');
    $vip_type_des = ct_get_member_type_string($type);
    $args = array(
        'adminEmail' => get_option('admin_email'),
        'vipType' => $vip_type_des,
        'startTime' => $start_time,
        'endTime' => $end_time,
    );
    cute_async_mail('', $user_email, $subject, $args, 'promote-vip');
}

//load_func('base/func.Metabox');
function ct_add_metaboxes()
{
    // 嵌入商品
    add_meta_box(
        'ct_post_embed_product',
        __('Post Embed Product', 'tt'),
        'ct_post_embed_product_callback',
        'post',
        'normal', 'high'
    );
     // 文章右侧下载小工具
    add_meta_box(
        'ct_post_embed_down_info',
        __('文章右侧下载小工具', 'tt'),
        'ct_post_embed_down_info_widget',
        'post',
        'normal', 'high'
    );
    // 转载信息
    add_meta_box(
        'ct_copyright_content',
        __('Post Copyright Info', 'tt'),
        'ct_post_copyright_callback',
        'post',
        'normal', 'high'
    );
    // 自定义SEO数据
    add_meta_box(
        'ct_post_seo_metabox',
        __('自定义SEO数据', 'tt'),
        'ct_post_seo_metabox_callback',
        'post',
        'normal', 'high'
    );
    // 文章内嵌下载资源
    add_meta_box(
        'ct_dload_metabox',
        '普通与'.CREDIT_NAME.'收费下载',
        'ct_download_metabox_callback',
        'post',
        'normal', 'high'
    );
    // 页面关键词与描述
    add_meta_box(
        'ct_keywords_description',
        __('页面关键词与描述', 'tt'),
        'ct_keywords_description_callback',
        'page',
        'normal', 'high'
    );
    // 商品信息输入框
    add_meta_box(
        'ct_product_info',
        __('商品信息', 'tt'),
        'ct_product_info_callback',
        'product',
        'normal', 'high'
    );
    // 聚合图床自动替换
    add_meta_box(
			'post_juhe_image',
			 '聚合图床自动替换',
			'ct_post_juhe_image_metabox_callback',
			Array('post','page','product'),
			'side','low'
		);
  // 文章筛选字段
    add_meta_box(
			'post_filter',
			 '文章筛选字段',
			'ct_post_filter_metabox_callback',
			Array('post'),
			'side','low'
		);
}
add_action('add_meta_boxes', 'ct_add_metaboxes');

/**
 * 文章内嵌入商品
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_post_embed_product_callback($post)
{
    $embed_product = (int) get_post_meta($post->ID, 'tt_embed_product', true); ?>
    <p style="width:100%;">
        <?php _e('Embed Product ID', 'tt'); ?>
        <input name="tt_embed_product" class="small-text code" value="<?php echo $embed_product;?>" style="width:80px;height: 28px;">
        <?php _e('(Leave empty or zero to disable)', 'tt'); ?>
    </p>
    <?php
}

/**
 * 文章右侧下载小工具
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_post_embed_down_info_widget($post)
{

    $post_embed_down_info = maybe_unserialize(get_post_meta($post->ID, 'tt_embed_down_info', true));
    $option = $post_embed_down_info[0];
    $demo_url = $post_embed_down_info[1];
    $file_version = $post_embed_down_info[2];
    $file_format = $post_embed_down_info[3];
    $file_size = $post_embed_down_info[4];
    $file_require = $post_embed_down_info[5];
    ?>
    <p>请选择开启或关闭
      <select name="tt_embed_down_info_option">
            <option value="0" <?php if( $option!=1) echo 'selected="selected"';?>>关闭</option>
            <option value="1" <?php if( $option==1) echo 'selected="selected"';?>>启用</option>
        </select>
    </p>
    <p>演示地址（留空则不显示）</p>
        <input type="text" name="tt_embed_down_info_demo_url" class="large-text code" value="<?php echo $demo_url;?>">
    <p>当前版本（留空则不显示）</p>
        <input type="text" name="tt_embed_down_info_file_version" class="large-text code" value="<?php echo $file_version;?>">
    <p>文件格式（留空则不显示）</p>
        <input type="text" name="tt_embed_down_info_file_format" class="large-text code" value="<?php echo $file_format;?>">
    <p>文件大小（留空则不显示）</p>
        <input type="text" name="tt_embed_down_info_file_size" class="large-text code" value="<?php echo $file_size;?>">
    <p>安装要求（留空则不显示）</p>
        <input type="text" name="tt_embed_down_info_file_require" class="large-text code" value="<?php echo $file_require;?>">
    <?php
}

/**
 * 文章转载信息.
 *
 * @since   2.0.0
 *
 * @param WP_Post $post
 */
function ct_post_copyright_callback($post)
{
    $cc = get_post_meta($post->ID, 'tt_post_copyright', true);
    $cc = $cc ? maybe_unserialize($cc) : array('source_title' => '', 'source_link' => ''); ?>
    <p><?php _e('Post Source Title', 'tt'); ?></p>
    <textarea name="tt_source_title" rows="1" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($cc['source_title'])); ?></textarea>
    <p><?php _e('Post Source Link, leaving empty means the post is original article', 'tt'); ?></p>
    <textarea name="tt_source_link" rows="1" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($cc['source_link'])); ?></textarea>
    <?php
}

/**
 * 普通与积分下载Metabox.
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_download_metabox_callback( $post ) {

    //付费显示内容
    $currency = get_post_meta($post->ID, 'tt_sale_content_currency', true); // 0 - credit 1 - cash
    $price = get_post_meta($post->ID, 'tt_sale_content_price', true);
    //免费下载资源
    $free_dl = get_post_meta( $post->ID, 'tt_free_dl') ? : '';
    //付费下载资源
    $sale_dl2 = get_post_meta( $post-> ID, 'tt_sale_dl2') ? : '';
    ?>
    <script type='text/javascript' src='<?php echo get_stylesheet_directory_uri(); ?>/assets/js/vue.js'></script>
    <style>#pan .box{overflow:hidden;margin-bottom:10px;position:relative;}#pan .box input{width:20%;height: 42px;}#pan .box input+input{margin-left:2%;}#add_pan{width:100%;height:45px;background-color:#2086bf;color:#fff;cursor:pointer;}#pan .box .icon-times{color:#faa19b;font-size:20px;position:absolute;right:0;top:22.5px;margin-top:-15px;cursor:pointer;}#payPan .box{overflow:hidden;margin-bottom:10px;position:relative;}#payPan .box input{width:12%;float:left;height: 42px;}#payPan .box select{width:17%;height:42px;margin-left:2%;}#payPan .box input+input{margin-left:2%;}#payPan .box .icon-times{color:#faa19b;font-size:20px;position:absolute;right:0;top:22.5px;margin-top:-15px;cursor:pointer;}#pan .box .tico-trash,#payPan .box .tico-trash{font-size:20px;padding:10px;}</style>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('付费显示内容，使用付费可见短代码后必须设置价格和支付币种', 'tt'); ?>
    </p>
    <p style="width:50%;float:left;"><?php _e( '选择支付币种', 'tt' );?>
        <select name="tt_sale_content_currency">
            <option value="0" <?php if( $currency!=1) echo 'selected="selected"';?>><?php echo CREDIT_NAME;?></option>
            <option value="1" <?php if( $currency==1) echo 'selected="selected"';?>><?php _e( '人民币', 'tt' );?></option>
        </select>
    </p>
    <p style="width:50%;float:left;"><?php _e( '商品售价 ', 'tt' );?>
        <input name="tt_sale_content_price" class="small-text code" value="<?php echo sprintf('%0.2f', $price);?>" style="width:80px;height: 28px;">
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;"></p>
    <div class="form-group" id="pan">
                            <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">免费资源</p>
                            <div class="box" v-for="(item,index) in list">
                                <input type="text" class="form-control"  placeholder="名称（可选）" value="" v-model="item.name">
                                <input type="text" class="form-control"  placeholder="页面链接（包括http://）" value="" v-model="item.url">
                                <input type="text" class="form-control"  placeholder="提取码" value="" v-model="item.pwd1">
                                <input type="text" class="form-control"  placeholder="解压密码" value="" v-model="item.pwd2">
                                <span class="tico tico-trash" v-bind:data-id=index @click="del_pan" style="color:red;font-size: 15px;">删除</span>
                            </div>
                            <input id="add_pan" type="button" value="添加新链接" @click="add_pan">
                            <textarea type="text" style="display: none;" class="form-control" id="free-downloads" name="tt_free_dl" rows="5" placeholder="" v-model="freeDownloads"></textarea>
                            <p><?php $meta_values = $free_dl;
                                if ($meta_values[0]=="") {
                                    $panList="[]";
                                }
                                else{
                                    $valList=explode(PHP_EOL,$meta_values[0]);
                                    $i=0;
                                    $panList="[";
                                    foreach ($valList as $key => $value) {
                                        $panList=$panList."{";
                                        $valArr=explode("|",$value);
                                        $panList=$panList."name:'".trim($valArr[0])."',";
                                        $panList=$panList."url:'".trim($valArr[1])."',";
                                        $panList=$panList."pwd1:'".trim($valArr[2])."',";
                                        $panList=$panList."pwd2:'".trim($valArr[3])."'";
                                        $panList=$panList."},";
                                        $i++;
                                    }
                                    $panList=$panList."]";
                                }
                              ?></p>
                        </div>
                        <script type="text/javascript">
                            var pan=new Vue({
                                el:"#pan",
                                data:{
                                   list:<?php echo $panList; ?>,

                                },
                                computed:{
                                    freeDownloads:function() {
                                        var listArr=this.list.slice(0);
                                        var str="";
                                        for (var i = 0; i < listArr.length; i++) {
                                            if (listArr[i].url=="") {
                                                listArr.splice(i,1);
                                            }
                                        }
                                        for (var i = 0; i < listArr.length; i++) {
                                            if (i!=0) { str=str+'\n';}
                                            str=str+listArr[i].name+"|"+listArr[i].url+"|"+listArr[i].pwd1+"|"+listArr[i].pwd2;
                                        }
                                        return str;
                                    },
                                },
                                methods:{
                                    add_pan:function() {
                                        pan.list.push({name:"",url:"",pwd1:"",pwd2:"",});
                                    },
                                    del_pan:function(e) {
                                        var index=e.target.dataset.id;
                                        pan.list.splice(index,1);
                                    }
                                }
                            });
                        </script>
    <div class="form-group" id="payPan">
                        <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">付费资源</p>
                        <div class="box" v-for="(item,index) in list">
                            <input type="text" class="form-control"  placeholder="名称（可选）" value="" v-model="item.name">
                            <input type="text" class="form-control"  placeholder="页面链接（包括http://）" value="" v-model="item.url">
                            <input type="text" class="form-control"  placeholder="提取码" value="" v-model="item.pwd1">
                            <input type="text" class="form-control"  placeholder="解压密码" value="" v-model="item.pwd2">
                            <input type="text" class="form-control"  placeholder="价格" value="" v-model="item.payNum">
                            <select v-model="item.payMode">
                                <option  value="cash">现金</option>
                                <option value="credit"><?php echo CREDIT_NAME; ?></option>
                                <option value="vip1">月费会员</option>
                                <option value="vip2">年费会员</option>
                                <option value="vip3">永久会员</option>
                            </select>
                            <span class="tico tico-trash" v-bind:data-id=index @click="del_pan" style="color:red;font-size: 15px;">删除</span>
                        </div>
                        <textarea type="text" style="display: none;" class="form-control" id="sale-downloads" name="tt_sale_dl2" rows="5" placeholder="" v-model="safeDownloads"></textarea>
                        <input id="add_pan" type="button" value="添加新链接" @click="add_pan">
                        <p><?php $meta_values = $sale_dl2;
                            $str=$meta_values[0];

                            if ($meta_values[0]=="") {
                                $panList="[]";
                            }
                            else{
                                $list=explode(PHP_EOL,$meta_values[0]);
                                $i=0;
                                $panList="[";
                                foreach ($list as $key => $value) {
                                    $panList=$panList."{";
                                    $valArr=explode("|",$value);
                                    $panList=$panList."name:'".trim($valArr[0])."',";
                                    $valArrUrl=explode("__",$valArr[1]);
                                    $panList=$panList."url:'".trim($valArrUrl[0])."',";
                                    $panList=$panList."pwd1:'".trim($valArrUrl[1])."',";
                                    $panList=$panList."pwd2:'".trim($valArr[4])."',";
                                    $panList=$panList."payNum:'".trim($valArr[2])."',";
                                    $panList=$panList."payMode:'".trim($valArr[3])."',";
                                    $panList=$panList."},";
                                    $i++;
                                }
                                $panList=$panList."]";
                            }
                          ?></p>
                    </div>
                    <script type="text/javascript">
                        var salePan=new Vue({
                            el:"#payPan",
                            data:{
                                list:<?php echo $panList; ?>,
                            },
                            computed:{
                                safeDownloads:function() {
                                    var listArr=this.list.slice(0);
                                    var str="";
                                    for (var i = 0; i < listArr.length; i++) {
                                        if (listArr[i].url=="") {
                                            listArr.splice(i,1);
                                        }
                                    }
                                    for (var i = 0; i < listArr.length; i++) {
                                        var fuhao='';
                                        if (i!=0) { str=str+'\n';}
                                        if (listArr[i].pwd1){fuhao='__'}
                                        str=str+listArr[i].name+"|"+listArr[i].url+fuhao+listArr[i].pwd1+"|"+listArr[i].payNum+"|"+listArr[i].payMode+"|"+listArr[i].pwd2;
                                    }
                                    return str;

                                },
                            },
                            methods:{
                                add_pan:function() {
                                    salePan.list.push({name:"",url:"",pwd1:"",payNum:"",payMode:"credit",pwd2:""});
                                },
                                del_pan:function(e) {
                                    var index=e.target.dataset.id;
                                    salePan.list.splice(index,1);
                                }
                            }
                        });
                    </script>
    <?php
}

/**
 * 文章SEO metabox.
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_post_seo_metabox_callback( $post ) {
    $tkd = get_post_meta($post->ID, 'tt_post_seo', true);
    $tkd = $tkd ? maybe_unserialize($tkd) : array('tt_post_title' => '', 'tt_post_keywords' => '', 'tt_post_description' => '');
    //文章SEO标题
    $title = $tkd['tt_post_title'];
    //文章页keywords
    $keywords = $tkd['tt_post_keywords'];
    //文章页description
    $description = $tkd['tt_post_description'];
     ?>
    <p><?php _e( '自定义文章页SEO标题，留空将按默认显示', 'tt' );?></p>
    <input type="text" name="tt_post_title" class="large-text code" value="<?php echo $title;?>">
    <p><?php _e( '自定义文章页keywords，留空将按默认显示', 'tt' );?></p>
    <input type="text" name="tt_post_keywords" class="large-text code" value="<?php echo $keywords;?>">
    <p><?php _e( '自定义文章页description，留空将按默认显示', 'tt' );?></p>
    <input type="text" name="tt_post_description" class="large-text code" value="<?php echo $description;?>">
    <?php
}

/**
 * 文章编辑页微博图片按钮.
 *
 * @since 2.0.0
 *
 * @param $post
 */

function ct_post_juhe_image_metabox_callback($post){
	$post_disable_juhe_image = get_post_meta( $post->ID, 'ct_post_enable_juhe_image', true );
?>
<p><?php _e( '如果主题选项中开启了聚合图床功能，则此设置有效。', 'tinection' );?></p>
<label for="ct_post_enable_juhe_image">
	<input name="ct_post_enable_juhe_image" id="ct_post_enable_juhe_image" value="enable" type="checkbox" <?php if($post_disable_juhe_image!='disabled') echo 'checked="checked"'; ?>>启用本文聚合图床功能。
</label>
<?php
}

/**
 * 文章编辑页微博图片按钮.
 *
 * @since 2.0.0
 *
 * @param $post
 */

function ct_post_filter_metabox_callback($post){
    $category = get_the_category($post->ID);
    $cat_ID = $category[0]->term_id;
    $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
    $filters = explode(',',$term_meta['tax_filter']);
    $post_filter = get_post_meta( $post->ID, 'post_filter', true );
?>
<label for="ct_post_filter">
     <h4><?php echo $term_meta['tax_filter_name'];?></h4>
     <select name="ct_post_filter">
       <option value="all">默认</option>
        <?php foreach($filters as $filter){ ?>
       <option value="<?php echo $filter; ?>" <?php if($post_filter == $filter){ echo 'selected';} ?>><?php echo $filter; ?></option>
       <?php } ?>
  </select>
</label>
<?php
}

/**
 * 页面关键词与描述.
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_keywords_description_callback($post)
{
    $keywords = get_post_meta($post->ID, 'tt_keywords', true);
    $description = get_post_meta($post->ID, 'tt_description', true); ?>
    <p><?php _e('页面关键词', 'tt'); ?></p>
    <textarea name="tt_keywords" rows="2" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($keywords)); ?></textarea>
    <p><?php _e('页面描述', 'tt'); ?></p>
    <textarea name="tt_description" rows="5" class="large-text code"><?php echo stripcslashes(htmlspecialchars_decode($description)); ?></textarea>

    <?php
}

/**
 * 商品信息.
 *
 * @since 2.0.0
 *
 * @param $post
 */
function ct_product_info_callback($post)
{
    $currency = get_post_meta($post->ID, 'tt_pay_currency', true); // 0 - credit 1 - cash
    $channel = get_post_meta($post->ID, 'tt_buy_channel', true) == 'taobao' ? 'taobao' : 'instation';
    $price = get_post_meta($post->ID, 'tt_product_price', true);
    $amount = get_post_meta($post->ID, 'tt_product_quantity', true);

    $taobao_link_raw = get_post_meta($post->ID, 'tt_taobao_link', true);
    $taobao_link = $taobao_link_raw ? esc_url($taobao_link_raw) : '';
    $sale_text = get_post_meta($post->ID, 'tt_product_sale_text',true);

    // 注意，折扣保存的是百分数的数值部分
    $discount_summary = ct_get_product_discount_array($post->ID); // 第1项为普通折扣, 第2项为会员(月付)折扣, 第3项为会员(年付)折扣, 第4项为会员(永久)折扣
    $site_discount = $discount_summary[0];
    $monthly_vip_discount = $discount_summary[1];
    $annual_vip_discount = $discount_summary[2];
    $permanent_vip_discount = $discount_summary[3];

    $limit_shop_day = get_post_meta($post->ID, 'tt_limit_shop_day', true);
    $limit_shop_count = get_post_meta($post->ID, 'tt_limit_shop_count', true);
    $download_links = get_post_meta($post->ID, 'tt_product_download_links', true);
    $pay_content = get_post_meta($post->ID, 'tt_product_pay_content', true);
    $buyer_emails_string = ct_get_buyer_emails($post->ID);
    $buyer_emails = is_array($buyer_emails_string) ? implode(';', $buyer_emails_string) : ''; ?>
    <p style="clear:both;font-weight:bold;">
        <?php echo sprintf(__('此商品购买按钮快捷插入短代码为[product id="%1$s"][/product]', 'tt'), $post->ID); ?>
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('基本信息', 'tt'); ?>
    </p>
    <p style="width:20%;float:left;"><?php _e('选择支付币种', 'tt'); ?>
        <select name="tt_pay_currency">
            <option value="0" <?php if ($currency != 1) {
        echo 'selected="selected"';
    } ?>><?php echo CREDIT_NAME; ?></option>
            <option value="1" <?php if ($currency == 1) {
        echo 'selected="selected"';
    } ?>><?php _e('人民币', 'tt'); ?></option>
        </select>
    </p>
    <p style="width:20%;float:left;"><?php _e('选择购买渠道', 'tt'); ?>
        <select name="tt_buy_channel">
            <option value="instation" <?php if ($channel != 'taobao') {
        echo 'selected="selected"';
    } ?>><?php _e('站内购买', 'tt'); ?></option>
            <option value="taobao" <?php if ($channel == 'taobao') {
        echo 'selected="selected"';
    } ?>><?php _e('淘宝链接', 'tt'); ?></option>
        </select>
    </p>
    <p style="width:20%;float:left;"><?php _e('商品售价 ', 'tt'); ?>
        <input name="tt_product_price" class="small-text code" value="<?php echo sprintf('%0.2f', $price); ?>" style="width:80px;height: 28px;">
    </p>
    <p style="width:20%;float:left;"><?php _e('商品数量 ', 'tt'); ?>
        <input name="tt_product_quantity" class="small-text code" value="<?php echo (int) $amount; ?>" style="width:80px;height: 28px;">
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('VIP会员折扣百分数(100代表原价)', 'tt'); ?>
    </p>
    <p style="width:33%;float:left;clear:left;"><?php _e('VIP月费会员折扣 ', 'tt'); ?>
        <input name="tt_monthly_vip_discount" class="small-text code" value="<?php echo $monthly_vip_discount; ?>" style="width:80px;height: 28px;"> %
    </p>
    <p style="width:33%;float:left;"><?php _e('VIP年费会员折扣 ', 'tt'); ?>
        <input name="tt_annual_vip_discount" class="small-text code" value="<?php echo $annual_vip_discount; ?>" style="width:80px;height: 28px;"> %
    </p>
    <p style="width:33%;float:left;"><?php _e('VIP永久会员折扣 ', 'tt'); ?>
        <input name="tt_permanent_vip_discount" class="small-text code" value="<?php echo $permanent_vip_discount; ?>" style="width:80px;height: 28px;"> %
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('限购天数结合限购次数使用，意为多少天内限制购买多少次', 'tt'); ?>
    </p>
    <p style="width:50%;float:left;clear:left;">限购时间（单位'天'，0为不限制）
        <input name="tt_limit_shop_day" class="small-text code" value="<?php echo $limit_shop_day; ?>" style="width:80px;height: 28px;">天
    </p>
    <p style="width:50%;float:left;">限购次数（单位'次'，0为不限制）
        <input name="tt_limit_shop_count" class="small-text code" value="<?php echo $limit_shop_count; ?>" style="width:80px;height: 28px;">次
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('促销信息', 'tt'); ?>
    </p>
    <p style="width:50%;float:left;clear:left;"><?php _e( '优惠促销折扣(100代表原价)', 'tt' );?>
        <input name="tt_product_promote_discount" class="small-text code" value="<?php echo $site_discount; ?>" style="width:80px;height: 28px;"> %
    </p>
    <p style="width:50%;float:left;"><?php _e( '促销标语(留空不显示)', 'tt' );?>
        <input name="tt_product_sale_text" class="small-text code" value="<?php echo $sale_text; ?>" style="width:120px;height: 28px;">(四字以内)
    </p>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('淘宝链接', 'tt'); ?>
    </p>
    <p style="clear:both;"><?php _e('购买渠道为淘宝时，请务必填写该项', 'tt'); ?></p>
    <textarea name="tt_taobao_link" rows="2" class="large-text code"><?php echo $taobao_link; ?></textarea>
    <p style="clear:both;font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:8px;">
        <?php _e('付费内容', 'tt'); ?>
    </p>
    <p style="clear:both;"><?php _e('付费查看下载链接,一行一个,每个资源格式为资源名|资源下载链接|密码', 'tt'); ?></p>
    <textarea name="tt_product_download_links" rows="5" class="large-text code"><?php echo $download_links; ?></textarea>
    <p style="clear:both;"><?php _e('付费查看的内容信息', 'tt'); ?></p>
    <textarea name="tt_product_pay_content" rows="5" class="large-text code"><?php echo $pay_content; ?></textarea>

    <p style="clear:both;"><?php _e('当前购买的用户邮箱', 'tt'); ?></p>
    <textarea name="tt_product_buyer_emails" rows="6" class="large-text code"><?php echo $buyer_emails; ?></textarea>

    <?php
}

/**
 * 保存文章时保存自定义数据.
 *
 * @since 2.0.0
 *
 * @param $post_id
 */
function ct_save_meta_box_data($post_id)
{
    // 检查安全字段验证
//    if ( ! isset( $_POST['tt_meta_box_nonce'] ) ) {
//        return;
//    }
    // 检查安全字段的值
//    if ( ! wp_verify_nonce( $_POST['tt_meta_box_nonce'], 'tt_meta_box' ) ) {
//        return;
//    }
    // 检查是否自动保存，自动保存则跳出
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // 检查用户权限
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    // 检查和更新字段
    if (isset($_POST['tt_embed_product'])) {
        update_post_meta($post_id, 'tt_embed_product', absint($_POST['tt_embed_product']));
    }

    if (isset($_POST['tt_source_title']) && isset($_POST['tt_source_link'])) {
        $cc = array(
            'source_title' => trim($_POST['tt_source_title']),
            'source_link' => trim($_POST['tt_source_link']),
        );
        update_post_meta($post_id, 'tt_post_copyright', maybe_serialize($cc));
    }

    if (isset($_POST['tt_post_title']) || isset($_POST['tt_post_keywords']) || isset($_POST['tt_post_description'])) {
        $tkd = array(
            'tt_post_title' => trim($_POST['tt_post_title']),
            'tt_post_keywords' => trim($_POST['tt_post_keywords']),
            'tt_post_description' => trim($_POST['tt_post_description']),
        );
        update_post_meta($post_id, 'tt_post_seo', maybe_serialize($tkd));
    }

    if(isset($_POST['tt_sale_content_currency'])){
        $currency = (int)$_POST['tt_sale_content_currency'] == 1 ? 1 : 0;
        update_post_meta($post_id, 'tt_sale_content_currency', $currency);
    }
    if(isset($_POST['tt_sale_content_price'])){
        update_post_meta($post_id, 'tt_sale_content_price', abs($_POST['tt_sale_content_price']));
    }

    if (isset($_POST['tt_free_dl'])/* && !empty($_POST['tt_free_dl'])*/) {
        update_post_meta($post_id, 'tt_free_dl', trim($_POST['tt_free_dl']));
    }

    if (isset($_POST['tt_sale_dl'])/* && !empty($_POST['tt_sale_dl'])*/) {
        update_post_meta($post_id, 'tt_sale_dl', trim($_POST['tt_sale_dl']));
    }

    if (isset($_POST['tt_sale_dl2'])/* && !empty($_POST['tt_sale_dl'])*/) {
        update_post_meta($post_id, 'tt_sale_dl2', trim($_POST['tt_sale_dl2']));
    }

    if (isset($_POST['tt_keywords']) && !empty($_POST['tt_keywords'])) {
        update_post_meta($post_id, 'tt_keywords', trim($_POST['tt_keywords']));
    }

    if (isset($_POST['tt_description']) && !empty($_POST['tt_description'])) {
        update_post_meta($post_id, 'tt_description', trim($_POST['tt_description']));
    }

    if (isset($_POST['tt_pay_currency'])) {
        $currency = (int) $_POST['tt_pay_currency'] == 1 ? 1 : 0;
        update_post_meta($post_id, 'tt_pay_currency', $currency);
    }

    if (isset($_POST['tt_buy_channel'])) {
        $channel = trim($_POST['tt_buy_channel']) == 'taobao' ? 'taobao' : 'instation';
        update_post_meta($post_id, 'tt_buy_channel', $channel);
    }

    if ($_POST['action'] == 'editpost' && $_POST['ct_post_enable_juhe_image']!=get_post_meta($post_id,'ct_post_enable_juhe_image',true)) {
        $ct_post_enable_juhe_image = $_POST['ct_post_enable_juhe_image'] == 'enable' ? 'enable' : 'disabled';
		update_post_meta($post_id, 'ct_post_enable_juhe_image', $ct_post_enable_juhe_image);
	}

    if (isset($_POST['ct_post_filter'])) {
        update_post_meta($post_id, 'post_filter', trim($_POST['ct_post_filter']));
    }

    if (isset($_POST['tt_taobao_link'])) {
        update_post_meta($post_id, 'tt_taobao_link', esc_url($_POST['tt_taobao_link']));
    }

    if (isset($_POST['tt_product_price'])) {
        update_post_meta($post_id, 'tt_product_price', abs($_POST['tt_product_price']));
    }

    if (isset($_POST['tt_product_quantity'])) {
        update_post_meta($post_id, 'tt_product_quantity', absint($_POST['tt_product_quantity']));
    }

    if (isset($_POST['tt_product_promote_discount']) && isset($_POST['tt_monthly_vip_discount']) && isset($_POST['tt_annual_vip_discount']) && isset($_POST['tt_permanent_vip_discount'])) {
        $discount_summary = array(
            absint($_POST['tt_product_promote_discount']),
            absint($_POST['tt_monthly_vip_discount']),
            absint($_POST['tt_annual_vip_discount']),
            absint($_POST['tt_permanent_vip_discount']),
        );
        update_post_meta($post_id, 'tt_product_discount', maybe_serialize($discount_summary));
    }

    if(isset($_POST['tt_limit_shop_count'])){
        update_post_meta($post_id, 'tt_limit_shop_count', trim($_POST['tt_limit_shop_count']));
    }

    if(isset($_POST['tt_limit_shop_day'])){
        update_post_meta($post_id, 'tt_limit_shop_day', trim($_POST['tt_limit_shop_day']));
    }

    if(isset($_POST['tt_product_sale_text'])){
        update_post_meta($post_id, 'tt_product_sale_text', trim($_POST['tt_product_sale_text']));
    }

    if (isset($_POST['tt_product_download_links'])) {
        update_post_meta($post_id, 'tt_product_download_links', trim($_POST['tt_product_download_links']));
    }

    if (isset($_POST['tt_product_pay_content'])) {
        update_post_meta($post_id, 'tt_product_pay_content', trim($_POST['tt_product_pay_content']));
    }

    if (isset($_POST['tt_embed_down_info_option']) || isset($_POST['tt_embed_down_info_demo_url']) || isset($_POST['tt_embed_down_info_file_version']) || isset($_POST['tt_embed_down_info_file_format']) || isset($_POST['tt_embed_down_info_file_size']) || isset($_POST['tt_embed_down_info_file_require'])) {
        $tt_embed_down_info = array(
            $_POST['tt_embed_down_info_option'],
            $_POST['tt_embed_down_info_demo_url'],
            $_POST['tt_embed_down_info_file_version'],
            $_POST['tt_embed_down_info_file_format'],
            $_POST['tt_embed_down_info_file_size'],
            $_POST['tt_embed_down_info_file_require'],
        );
        update_post_meta($post_id, 'tt_embed_down_info', maybe_serialize($tt_embed_down_info));
    }
}
add_action('save_post', 'ct_save_meta_box_data');

/**
 * 加载header模板
 *
 * @since 2.0.0
 *
 * @param string $name 特殊header的名字
 */
function ct_get_header($name = null)
{
    do_action('get_header', $name);

    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
        $templates[] = 'core/modules/mod.Header.'.ucfirst($name).'.php';
    }

    $templates[] = 'core/modules/mod.Header.php';

    locate_template($templates, true);
}

/**
 * 加载footer模板
 *
 * @since 2.0.0
 *
 * @param string $name 特殊footer的名字
 */
function ct_get_footer($name = null)
{
    do_action('get_footer', $name);

    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
        $templates[] = 'core/modules/mod.Footer.'.ucfirst($name).'.php';
    }

    $templates[] = 'core/modules/mod.Footer.php';

    locate_template($templates, true);
}

/**
 * 加载自定义路径下的Sidebar.
 *
 * @since   2.0.0
 *
 * @param string $name 特定Sidebar名
 */
function ct_get_sidebar($name = null)
{
    do_action('get_sidebar', $name);

    $templates = array();
    $name = (string) $name;
    if ('' !== $name) {
        $templates[] = 'core/modules/mod.Sidebar'.ucfirst($name).'.php';
    }

    $templates[] = 'core/modules/mod.Sidebar.php';

    locate_template($templates, true);
}

/* WordPress 后台禁用Google Open Sans字体，加速网站 */
function ct_remove_open_sans()
{
    wp_deregister_style('open-sans');
    wp_register_style('open-sans', false);
    wp_enqueue_style('open-sans', '');
}
add_action('init', 'ct_remove_open_sans');

/* 移除头部多余信息 */
function ct_remove_wp_version()
{
    return;
}
add_filter('the_generator', 'ct_remove_wp_version'); //WordPress的版本号

remove_action('wp_head', 'feed_links', 2); //包含文章和评论的feed
remove_action('wp_head', 'index_rel_link'); //当前文章的索引
remove_action('wp_head', 'feed_links_extra', 3); //额外的feed,例如category, tag页
remove_action('wp_head', 'start_post_rel_link', 10); //开始篇
remove_action('wp_head', 'parent_post_rel_link', 10); //父篇
remove_action('wp_head', 'adjacent_posts_rel_link', 10); //上、下篇.
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10); //rel=pre
remove_action('wp_head', 'wp_shortlink_wp_head', 10); //rel=shortlink
//remove_action('wp_head', 'rel_canonical' );

/* 阻止站内文章Pingback */
function ct_no_self_ping(&$links)
{
    $home = get_option('home');
    foreach ($links as $key => $link) {
        if (0 === strpos($link, $home)) {
            unset($links[$key]);
        }
    }
}
add_action('pre_ping', 'ct_no_self_ping');

/* 添加链接功能 */
add_filter('pre_option_link_manager_enabled', '__return_true');

/* 登录用户浏览站点时不显示工具栏 */
add_filter('show_admin_bar', '__return_false');

/* 移除emoji相关脚本 */
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('embed_head', 'print_emoji_detection_script');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

function ct_disable_emoji_tiny_mce_plugin($plugins)
{
    return array_diff($plugins, array('wpemoji'));
}
add_filter('tiny_mce_plugins', 'ct_disable_emoji_tiny_mce_plugin');

/* 移除wp-embed等相关功能 */
/**
 * Disable embeds on init.
 *
 * - Removes the needed query vars.
 * - Disables oEmbed discovery.
 * - Completely removes the related JavaScript.
 *
 * @since 1.0.0
 */
function ct_disable_embeds_init()
{
    /* @var WP $wp */
    global $wp;

    // Remove the embed query var.
    $wp->public_query_vars = array_diff($wp->public_query_vars, array(
        'embed',
    ));

    // Remove the REST API endpoint.
    remove_action('rest_api_init', 'wp_oembed_register_route');

    // Turn off oEmbed auto discovery.
    add_filter('embed_oembed_discover', '__return_false');

    // Don't filter oEmbed results.
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

    // Remove oEmbed discovery links.
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove oEmbed-specific JavaScript from the front-end and back-end.
    remove_action('wp_head', 'wp_oembed_add_host_js');
    add_filter('tiny_mce_plugins', 'ct_disable_embeds_tiny_mce_plugin');

    // Remove all embeds rewrite rules.
    add_filter('rewrite_rules_array', 'ct_disable_embeds_rewrites');

    // Remove filter of the oEmbed result before any HTTP requests are made.
    remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
}

add_action('init', 'ct_disable_embeds_init', 9999);

/**
 * Removes the 'wpembed' TinyMCE plugin.
 *
 * @since 1.0.0
 *
 * @param array $plugins list of TinyMCE plugins
 *
 * @return array the modified list
 */
function ct_disable_embeds_tiny_mce_plugin($plugins)
{
    return array_diff($plugins, array('wpembed'));
}

/**
 * Remove all rewrite rules related to embeds.
 *
 * @since 1.2.0
 *
 * @param array $rules wordPress rewrite rules
 *
 * @return array rewrite rules without embeds rules
 */
function ct_disable_embeds_rewrites($rules)
{
    foreach ($rules as $rule => $rewrite) {
        if (false !== strpos($rewrite, 'embed=true')) {
            unset($rules[$rule]);
        }
    }

    return $rules;
}

/**
 * Remove embeds rewrite rules on theme activation.
 *
 * @since 1.2.0
 */
function ct_disable_embeds_remove_rewrite_rules()
{
    add_filter('rewrite_rules_array', 'ct_disable_embeds_rewrites');
    flush_rewrite_rules();
}
add_action('load-themes.php', 'ct_disable_embeds_remove_rewrite_rules');

/**
 * Flush rewrite rules on theme deactivation.
 *
 * @since 1.2.0
 */
function ct_disable_embeds_flush_rewrite_rules()
{
    remove_filter('rewrite_rules_array', 'ct_disable_embeds_rewrites');
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'ct_disable_embeds_flush_rewrite_rules');

/**
 * 搜索结果排除页面(商店的搜素结果不处理).
 *
 * @since 2.0.0
 *
 * @param WP_Query $query
 *
 * @return WP_Query
 */
function ct_search_filter_page($query)
{
    if ($query->is_search && !$query->is_admin) {
        if (isset($query->query['post_type']) && $query->query['post_type'] == 'product') {
            return $query;
        }
        $query->set('post_type', 'post');
    }

    return $query;
}
add_filter('pre_get_posts', 'ct_search_filter_page');

/**
 * 摘要长度.
 *
 * @since 2.0.0
 *
 * @param $length
 *
 * @return mixed
 */
function ct_excerpt_length($length)
{
    return ct_get_option('tt_excerpt_length', $length);
}
add_filter('excerpt_length', 'ct_excerpt_length', 999);

/* 去除正文P标签包裹 */
//remove_filter( 'the_content', 'wpautop' );

/* 去除摘要P标签包裹 */
remove_filter('the_excerpt', 'wpautop');

/* HTML转义 */
//取消内容转义
remove_filter('the_content', 'wptexturize');
//取消摘要转义
//remove_filter('the_excerpt', 'wptexturize');
//取消评论转义
//remove_filter('comment_text', 'wptexturize');

/* 在文本小工具不自动添加P标签 */
add_filter('widget_text', 'shortcode_unautop');
/* 在文本小工具也执行短代码 */
add_filter('widget_text', 'do_shortcode');

/* 找回上传图片路径设置 */
if (get_option('upload_path') == 'wp-content/uploads' || get_option('upload_path') == null) {
    update_option('upload_path', 'wp-content/uploads');
}

/**
 * 中文名文件上传改名.
 *
 * @since 2.0.0
 *
 * @param $file
 *
 * @return mixed
 */
function short_md5($str) {
    return substr(md5($str), 8, 16);
    }
function ct_custom_upload_name($file){
    if(preg_match('/[一-龥]/u',$file['name'])):
        $ext=ltrim(strrchr($file['name'],'.'),'.');
        $filename = mt_rand(10,25) . time() . $file['name'];
        $file['name'] = short_md5($filename) . '.' . $ext;
    endif;

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'ct_custom_upload_name', 5, 1);

/**
 * 替换文章或评论内容外链为内链
 *
 * @since 2.0.0
 * @param $content
 * @return mixed
 */
function ct_links_to_internal_links($content){
    if(!ct_get_option('tt_disable_external_links', false)) {
        return $content;
    }
   $home = home_url();
        $white_list = ct_get_option('tt_external_link_whitelist');
        $white_links = !empty($white_list) ? explode(PHP_EOL, $white_list) : array();
        array_push($white_links, $home);
        $external = true;
        foreach ($white_links as $white_link) {
                if(strpos($content, trim($white_link))!==false) {
                    $external = false;
                    break;
                }
            }
            if($external===true){
                $new = $home . '/redirect/' . base64_encode($content);
                $content = str_replace($content, $new, $content);
            }


    return $content;
}
function ct_donate_links($content){
   $home = home_url();
   $new = $home . '/redirect/' . base64_encode($content);
   $content = str_replace($content, $new, $content);
   return $content;
}
function ct_convert_to_internal_links($content){
    if(!ct_get_option('tt_disable_external_links', false)) {
        return $content;
    }
    preg_match_all('/\shref=(\'|\")(http[^\'\"#]*?)(\'|\")([\s]?)/', $content, $matches);
    if($matches){
        $home = home_url();
        $white_list = ct_get_option('tt_external_link_whitelist');
        $white_links = !empty($white_list) ? explode(PHP_EOL, $white_list) : array();
        array_push($white_links, $home);
        foreach($matches[2] as $val){
            $external = true;
            foreach ($white_links as $white_link) {
                if(strpos($val, trim($white_link))!==false) {
                    $external = false;
                    break;
                }
            }
            if($external===true){
                $rep = $matches[1][0].$val.$matches[3][0];
                $new = '"'. $home . '/redirect/' . base64_encode($val). '" target="_blank"';
                $content = str_replace("$rep", "$new", $content);
            }
        }
    }
    return $content;
}
add_filter('the_content', 'ct_convert_to_internal_links', 99);
add_filter('comment_text', 'ct_convert_to_internal_links', 99);
add_filter('get_comment_text', 'ct_convert_to_internal_links', 99);
add_filter('get_comment_author_link', 'ct_convert_to_internal_links', 99);

/**
 * WordPress文字标签关键词自动内链.
 *
 * @since 2.0.0
 *
 * @param $content
 *
 * @return mixed
 */
function ct_tag_link($content)
{
    $match_num_from = 1;		//一篇文章中同一個標籤少於幾次不自動鏈接
    $match_num_to = 4;		//一篇文章中同一個標籤最多自動鏈接幾次
    $post_tags = get_the_tags();
    if (ct_get_option('tt_enable_k_post_tag_link', true) && $post_tags) {
        $sort_func = function ($a, $b) {
            if ($a->name == $b->name) {
                return 0;
            }

            return (strlen($a->name) > strlen($b->name)) ? -1 : 1;
        };
        usort($post_tags, $sort_func);
        $ex_word = '';
        $case = '';
        foreach ($post_tags as $tag) {
            $link = get_tag_link($tag->term_id);
            $keyword = $tag->name;
            $cleankeyword = stripslashes($keyword);
            $url = "<a href=\"$link\" class=\"tag-tooltip\" data-toggle=\"tooltip\" title=\"".str_replace('%s', addcslashes($cleankeyword, '$'), __('查看更多关于 %s 的文章', 'tt')).'"';
            $url .= ' target="_blank"';
            $url .= '>'.addcslashes($cleankeyword, '$').'</a>';
            $limit = rand($match_num_from, $match_num_to);
            $content = preg_replace('|(<a[^>]+>)(.*)<pre.*?>('.$ex_word.')(.*)<\/pre>(</a[^>]*>)|U'.$case, '$1$2$4$5', $content);
            $content = preg_replace('|(<img)(.*?)('.$ex_word.')(.*?)(>)|U'.$case, '$1$2$4$5', $content);
            $cleankeyword = preg_quote($cleankeyword, '\'');
            $regEx = '\'(?!((<.*?)|(<a.*?)))('.$cleankeyword.')(?!(([^<>]*?)>)|([^>]*?</a>))\'s'.$case;
            $content = preg_replace($regEx, $url, $content, $limit);
            $content = str_replace('', stripslashes($ex_word), $content);
        }
    }

    return $content;
}
add_filter('the_content', 'ct_tag_link', 12, 1);

/**
 * 转换为内链的外链跳转处理
 *
 * @return bool
 */
function clear_urlcan($url){

    $rstr='';

    $tmparr=parse_url($url);

    $rstr=empty($tmparr['scheme'])?'http://':$tmparr['scheme'].'://';

    $rstr.=$tmparr['host'].$tmparr['path'];

    return $rstr;

}
function ct_handle_external_links_redirect() {
    $base_url = home_url('/redirect/');
    $request_url = Utils::getPHPCurrentUrl();
    if(strpos(parse_url($request_url,PHP_URL_QUERY),'donate') !== false){
      parse_str(parse_url($request_url,PHP_URL_QUERY),$myArray);
      $post_id = $myArray['donate'];
      $user_id = get_current_user_id();
      $post = get_user_meta($user_id, 'donate_post',true);
      if(!in_array($post_id, $post)){
      if(!$post){
        $post = array();
      }
      array_push($post,$post_id);
      update_user_meta($user_id, 'donate_post',$post);
      update_user_meta($user_id, 'tt_active_down_count',(int) get_user_meta($user_id, 'tt_active_down_count',true)-1);
      }
      $request_url = clear_urlcan($request_url);
    }
    if (substr($request_url, 0, strlen($base_url)) != $base_url) {
        return false;
    }
    $key = str_ireplace($base_url, '', $request_url);
    if (!empty($key)) {
        $external_url = base64_decode($key);
        header('HTTP/1.1 200 OK');
                ?>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="Cache-Control" content="no-transform" />
        <meta http-equiv="Cache-Control" content="no-siteapp" />
        <meta name="robots" content="noindex, nofollow" />
        <meta name="applicable-device"content="pc,mobile">
        <meta name="HandheldFriendly" content="true"/>
        <meta name="description" content="跳转页面" />
        <meta name="keywords" content="跳转页面" />
        <noscript>
            <meta http-equiv="refresh" content="2;url='<?php echo $external_url; ?>';">
        </noscript>
<script>
function link_jump(){location.href="<?php echo $external_url; ?>";}
setTimeout(link_jump, 2000);
setTimeout(function(){window.opener=null;window.close();}, 50000);
</script>
<title>页面加载中,请稍候...</title>
<style type="text/css">
#loading{position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999999;background:#fff;}#loading-center{width:100%;position:absolute;top:47%;left:0;right:0;margin:0 auto;text-align:center;}#loading-center .dot{display:inline-block;width:10px;height:10px;border-radius:50px;margin-right:10px;background:#a26ff9;-webkit-animation:load 1.04s ease infinite;}#loading-center .dot:last-child{margin-right:0px;}@-webkit-keyframes load{0%{opacity:1;-webkit-transform:scale(1.6);}100%{opacity:.1;-webkit-transform:scale(0);}}#loading-center .dot:nth-child(1){-webkit-animation-delay:0.1s;}#loading-center .dot:nth-child(2){-webkit-animation-delay:0.2s;}#loading-center .dot:nth-child(3){-webkit-animation-delay:0.3s;}#loading-center .dot:nth-child(4){-webkit-animation-delay:0.4s;}#loading-center .dot:nth-child(5){-webkit-animation-delay:0.5s;}</style>
</head>
<body>
<div id="loading"> <div id="loading-center"> <div class="dot"></div> <div class="dot"></div> <div class="dot"></div> <div class="dot"></div> <div class="dot"></div> </div></div>
</body>
</html><?php
        exit;
    }
    return false;
}
add_action('template_redirect', 'ct_handle_external_links_redirect');

/**
 * 删除文章时删除自定义字段.
 *
 * @since 2.0.0
 *
 * @param $post_ID
 */
function ct_delete_custom_meta_fields($post_ID)
{
    if (!wp_is_post_revision($post_ID)) {
        delete_post_meta($post_ID, 'tt_post_star_users');
        delete_post_meta($post_ID, 'tt_sidebar');
        delete_post_meta($post_ID, 'tt_latest_reviewed');
        delete_post_meta($post_ID, 'tt_keywords'); // page
        delete_post_meta($post_ID, 'tt_description'); // page
        delete_post_meta($post_ID, 'tt_product_price'); // product //TODO more
        delete_post_meta($post_ID, 'tt_product_quantity');
        delete_post_meta($post_ID, 'tt_pay_currency');
        delete_post_meta($post_ID, 'tt_product_sales');
        delete_post_meta($post_ID, 'tt_product_discount');
        delete_post_meta($post_ID, 'tt_buy_channel');
        delete_post_meta($post_ID, 'tt_taobao_link');
        delete_post_meta($post_ID, 'tt_latest_rated');
    }
    // TODO optimization: use sql to delete all at once
}
add_action('delete_post', 'ct_delete_custom_meta_fields');

/**
 * 删除文章时删除相关附件.
 *
 * @since 2.0.0
 *
 * @param $post_ID
 */
function ct_delete_post_and_attachments($post_ID)
{
    global $wpdb;
    //删除特色图片
    $thumbnails = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
    foreach ($thumbnails as $thumbnail) {
        wp_delete_attachment($thumbnail->meta_value, true);
    }
    //删除图片附件
    $attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent = $post_ID AND post_type = 'attachment'");
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }
    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
}
add_action('before_delete_post', 'ct_delete_post_and_attachments');

/**
 * 获取页面模板，由于Tint的模板文件存放目录不位于主题根目录，需要重写`get_page_templates`方法以获取.
 *
 * @since   2.0.0
 *
 * @param WP_Post|null $post 当前编辑的页面实例，用于提供上下文环境
 *
 * @return array 页面模板数组
 */
function ct_get_page_templates($post = null)
{
    $theme = wp_get_theme();

    if ($theme->errors() && $theme->errors()->get_error_codes() !== array('theme_parent_invalid')) {
        return array();
    }

    $page_templates = wp_cache_get('page_templates-'.md5('Tint'), 'themes');

    if (!is_array($page_templates)) {
        $page_templates = array();
        $files = (array) Utils::scandir(CUTE_THEME_TPL.'/page', 'php', 0); // Note: 主要这里重新定义扫描模板的文件夹/core/templates/page
        foreach ($files as $file => $full_path) {
            if (!preg_match('|Template Name:(.*)$|mi', file_get_contents($full_path), $header)) {
                continue;
            }
            $page_templates[$file] = _cleanup_header_comment($header[1]);
        }
        wp_cache_add('page_templates-'.md5('Tint'), $page_templates, 'themes', 1800);
    }

    if ($theme->load_textdomain()) {
        foreach ($page_templates as &$page_template) {
            $page_template = translate($page_template, 'tt');
        }
    }

    $templates = (array) apply_filters('theme_page_templates', $page_templates, $theme, $post);

    return array_flip($templates);
}

/**
 * Page编辑页面的页面属性meta_box内容回调，重写了`page_attributes_meta_box`，以支持自定义页面模板的路径和可用模板选项.
 *
 * @since   2.0.0
 *
 * @param WP_Post $post 页面实例
 */
function ct_page_attributes_meta_box($post)
{
    $post_type_object = get_post_type_object($post->post_type);
    if ($post_type_object->hierarchical) {
        $dropdown_args = array(
            'post_type' => $post->post_type,
            'exclude_tree' => $post->ID,
            'selected' => $post->post_parent,
            'name' => 'parent_id',
            'show_option_none' => __('(no parent)'),
            'sort_column' => 'menu_order, post_title',
            'echo' => 0,
        );

        $dropdown_args = apply_filters('page_attributes_dropdown_pages_args', $dropdown_args, $post);
        $pages = wp_dropdown_pages($dropdown_args);
        if (!empty($pages)) {
            ?>
            <p><strong><?php _e('Parent', 'tt'); ?></strong></p>
            <label class="screen-reader-text" for="parent_id"><?php _e('Parent', 'tt'); ?></label>
            <?php echo $pages; ?>
            <?php
        }
    }

    if ('page' == $post->post_type && 0 != count(ct_get_page_templates($post)) && get_option('page_for_posts') != $post->ID) {
        $template = !empty($post->page_template) ? $post->page_template : false; ?>
        <p><strong><?php _e('Template', 'tt'); ?></strong><?php
            do_action('page_attributes_meta_box_template', $template, $post); ?></p>
        <label class="screen-reader-text" for="page_template"><?php _e('Page Template', 'tt'); ?></label><select name="tt_page_template" id="page_template">
            <?php
            $default_title = apply_filters('default_page_template_title', __('Default Template', 'tt'), 'meta-box'); ?>
            <option value="default"><?php echo esc_html($default_title); ?></option>
            <?php ct_page_template_dropdown($template); ?>
        </select>
        <?php
    } ?>
    <p><strong><?php _e('Order', 'tt'); ?></strong></p>
    <p><label class="screen-reader-text" for="menu_order"><?php _e('Order', 'tt'); ?></label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order); ?>" /></p>
    <?php if ('page' == $post->post_type && get_current_screen()->get_help_tabs()) {
        ?>
        <p><?php _e('Need help? Use the Help tab in the upper right of your screen.', 'tt'); ?></p>
        <?php
    }
}

/**
 * 移除默认并添加改写的Page编辑页面的页面属性meta_box，以支持自定义页面模板的路径和可用模板选项.
 *
 * @since   2.0.0
 */
function ct_replace_page_attributes_meta_box()
{
    remove_meta_box('pageparentdiv', 'page', 'side');
    add_meta_box('tt_pageparentdiv', __('Page Attributes', 'tt'), 'ct_page_attributes_meta_box', 'page', 'side', 'low');
}
add_action('admin_init', 'ct_replace_page_attributes_meta_box');

/**
 * Page编辑页面的页面属性meta_box内页面模板下拉选项内容.
 *
 * @since   2.0.0
 *
 * @param string $default 模板文件名
 *
 * @return string Html代码
 */
function ct_page_template_dropdown($default = '')
{
    $templates = ct_get_page_templates(get_post());
    ksort($templates);
    foreach (array_keys($templates) as $template) {
        $full_path = 'core/templates/page/'.$templates[$template];
        $selected = selected($default, $full_path, false);
        echo "\n\t<option value='".$full_path."' $selected>$template</option>";
    }

    return '';
}

/**
 * 保存页面时，保存模板的选择值
 *
 * @since   2.0.0
 *
 * @param int $post_id 即将保存的文章ID
 */
function ct_save_meta_box_page_template_data($post_id)
{
    $post_id = intval($post_id);
    // 检查是否自动保存，自动保存则跳出
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // 检查用户权限
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }
    // 是否页面
    if (!isset($_POST['post_type']) || 'page' != trim($_POST['post_type'])) {
        return;
    }

    if (!empty($_POST['tt_page_template'])) {
        $template = sanitize_text_field($_POST['tt_page_template']);
        $post = get_post($post_id);
        $post->page_template = $template;
        $page_templates = array_flip(ct_get_page_templates($post));
        if ('default' != $template && !isset($page_templates[basename($template)])) {
            if (ct_get_option('tt_theme_debug', false)) {
                wp_die(__('The page template is invalid', 'tt'), __('Invalid Page Template', 'tt'));
            }
            update_post_meta($post_id, '_wp_page_template', 'default');
        } else {
            update_post_meta($post_id, '_wp_page_template', $template);
        }
    }
}
add_action('save_post', 'ct_save_meta_box_page_template_data');

/**
 * 给Body添加额外的class(部分自定义页面无法使用wp的body_class函数生成独特的class).
 *
 * @since 2.0.0
 *
 * @param $classes
 *
 * @return array
 */
function ct_modify_body_classes($classes)
{
    if ($query_var = get_query_var('site_util')) {
        $classes[] = 'site_util-'.$query_var;
    } elseif ($query_var = get_query_var('me')) {
        $classes[] = 'me-'.$query_var;
    } elseif ($query_var = get_query_var('uctab')) {
        $classes[] = 'uc-'.$query_var;
    } elseif ($query_var = get_query_var('uc')) {
        $classes[] = 'uc-profile';
    } elseif ($query_var = get_query_var('action')) {
        $classes[] = 'action-'.$query_var;
    } elseif ($query_var = get_query_var('me_child_route')) {
        $classes[] = 'me me-'.$query_var;
    } elseif ($query_var = get_query_var('manage_child_route')) {
        $query_var = get_query_var('manage_grandchild_route') ? substr($query_var, -2) : $query_var;
        $classes[] = 'manage manage-'.$query_var;
    }

    if (is_home() && ct_get_option('tt_enable_tinection_home', false) && (!isset($_GET['mod']) || $_GET['mod'] != 'blog')) {
        $classes[] = 'cms-home';
    }

    //TODO more
    return $classes;
}
add_filter('body_class', 'ct_modify_body_classes');

//load_func('base/func.PostMeta');
/**
 * 保存文章时添加最近变动字段.
 *
 * @since   2.0.0
 *
 * @param   $post_ID
 */
function ct_add_post_review_fields($post_ID)
{
    if (!wp_is_post_revision($post_ID)) {
        update_post_meta($post_ID, 'tt_latest_reviewed', time());
    }
}
add_action('save_post', 'ct_add_post_review_fields');

/**
 * 删除文章时删除最近变动字段.
 *
 * @since   2.0.0
 *
 * @param   $post_ID
 */
function ct_delete_post_review_fields($post_ID)
{
    if (!wp_is_post_revision($post_ID)) {
        delete_post_meta($post_ID, 'tt_latest_reviewed');
    }
}
add_action('delete_post', 'ct_delete_post_review_fields');

/**
 * Rewrite/Permalink/Routes.
 */

/**
 * 强制使用伪静态
 *
 * @since   2.0.0
 */
function ct_force_permalink()
{
    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%.html');
        // TODO: 添加后台消息提示已更改默认固定链接，并请配置伪静态(伪静态教程等)
    }
}
add_action('load-themes.php', 'ct_force_permalink');

/**
 * 文章分页规则
 *
 * @since   2.0.0
 */
function ct_handle_post_page_routes_rewrite($wp_rewrite){
    if(get_option('permalink_structure')){
        $rules = $wp_rewrite->rules;
        foreach ($rules as $rule => $rewrite) {
            if ( $rule == '([0-9]+).html(/[0-9]+)?/?$' || $rule == '([^/]+).html(/[0-9]+)?/?$' ) {
                unset($rules[$rule]);
            }
        }
        // Note: me子路由与孙路由必须字母组成，不区分大小写
        $new_rules['(.*?)/([0-9]+)?_([0-9]+)?.html$'] = 'index.php?post_type=post&p=$matches[2]&page=$matches[3]';
        $new_rules['([0-9]+)?_([0-9]+)?.html$'] = 'index.php?post_type=post&p=$matches[1]&page=$matches[2]';
        $new_rules['(.*?)/([^/]+)_([0-9]+)?.html$'] = 'index.php?name=$matches[2]&page=$matches[3]';
        $new_rules['([^/]+)_([0-9]+)?.html$'] = 'index.php?name=$matches[1]&page=$matches[2]';

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
    return $wp_rewrite;
}
add_filter('generate_rewrite_rules', 'ct_handle_post_page_routes_rewrite');

function cancel_redirect_for_paged_posts($redirect_url){
        global $wp_query;
        if( is_single() && $wp_query->get( 'page' ) > 1 ){
            return false;
        }
        return $redirect_url;
    }
add_filter( 'redirect_canonical', 'cancel_redirect_for_paged_posts', 10, 2 );

/**
 * 短链接.
 *
 * @since   2.0.0
 */
function ct_rewrite_short_link()
{
    // 短链接前缀, 如https://www.kuacg.com/go/xxx中的go，为了便于识别短链接
    $prefix = ct_get_option('tt_short_link_prefix', 'go');
    //$url = Utils::getCurrentUrl(); //该方法需要利用wp的query
    $url = Utils::getPHPCurrentUrl();
    preg_match('/\/'.$prefix.'\/([0-9A-Za-z]*)/i', $url, $matches);
    if (!$matches) {
        return false;
    }
    $token = strtolower($matches[1]);
    $target_url = '';
    $records = ct_get_option('tt_short_link_records');
    $records = explode(PHP_EOL, $records);
    foreach ($records as $record) {
        $record = explode('|', $record);
        if (count($record) < 2) {
            continue;
        }
        if (strtolower(trim($record[0])) === $token) {
            $target_url = trim($record[1]);
            break;
        }
    }

    if ($target_url) {
        wp_redirect(esc_url_raw($target_url), 302);
        exit;
    }

    return false;
}
add_action('template_redirect', 'ct_rewrite_short_link');

/* Route : UCenter - e.g /@nickname/latest */

/**
 * 用户页路由(非默认作者页).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_set_user_page_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        // TODO: 用户链接前缀 `u` 是否可以自定义
        // Note: 用户名必须数字或字母组成，不区分大小写
//        if(stripos($ps, '%postname%') !== false){
//            // 默认为profile tab，但是链接不显示profile
//            $new_rules['@([一-龥a-zA-Z0-9]+)$'] = 'index.php?author_name=$matches[1]&uc=1';
//            // ucenter tabs
//            $new_rules['@([一-龥a-zA-Z0-9]+)/([A-Za-z]+)$'] = 'index.php?author_name=$matches[1]&uctab=$matches[2]&uc=1';
//            // 分页
//            $new_rules['@([一-龥a-zA-Z0-9]+)/([A-Za-z]+)/page/([0-9]{1,})$'] = 'index.php?author_name=$matches[1]&uctab=$matches[2]&uc=1&paged=$matches[3]';
//        }else{
        $new_rules['u/([0-9]{1,})$'] = 'index.php?author=$matches[1]&uc=1';
        $new_rules['u/([0-9]{1,})/([A-Za-z]+)$'] = 'index.php?author=$matches[1]&uctab=$matches[2]&uc=1';
        $new_rules['u/([0-9]{1,})/([A-Za-z]+)/page/([0-9]{1,})$'] = 'index.php?author=$matches[1]&uctab=$matches[2]&uc=1&tt_paged=$matches[3]';
//        }
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'ct_set_user_page_rewrite_rules'); // filter `rewrite_rules_array` 也可用.

/**
 * 为自定义的用户页添加query_var白名单，用于识别和区分用户页及作者页.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_user_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'uc'; // 添加参数白名单uc，代表是用户中心页，采用用户模板而非作者模板
        $public_query_vars[] = 'uctab'; // 添加参数白名单uc，代表是用户中心页，采用用户模板而非作者模板
        $public_query_vars[] = 'tt_paged';
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_user_page_query_vars');

/**
 * 自定义作者页链接.
 *
 * @since   2.0.0
 *
 * @param string $link      原始链接
 * @param int    $author_id 作者ID
 *
 * @return string
 */
function ct_custom_author_link($link, $author_id)
{
    $ps = get_option('permalink_structure');
    if (!$ps) {
        return $link;
    }
//    if(stripos($ps, '%postname%') !== false){
//        $nickname = get_user_meta($author_id, 'nickname', true);
//        // TODO: 解决nickname重复问题，用户保存资料时发出消息要求更改重复的名字，否则改为login_name，使用 `profile_update` action
//        return home_url('/@' . $nickname);
//    }
    return home_url('/u/'.strval($author_id));
}
add_filter('author_link', 'ct_custom_author_link', 10, 2);

/**
 * 用户链接解析Rewrite规则时正确匹配字段
 * // author_name传递的实际是nickname，而wp默认将其做login_name处理，需要修复
 * 同时对使用原始默认作者页链接的重定向至新的自定义链接.
 *
 * @since   2.0.0
 *
 * @param array $query_vars 全局查询变量
 *
 * @return array
 */
function ct_match_author_link_field($query_vars)
{
    if (is_admin()) {
        return $query_vars;
    }
    if (array_key_exists('author_name', $query_vars)) {
        $nickname = $query_vars['author_name'];
        global $wpdb;
        $author_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE `meta_key` = 'nickname' AND `meta_value` = %s ORDER BY user_id ASC LIMIT 1", sanitize_text_field($nickname)));
        $logged_user_id = get_current_user_id();

        // 如果是原始author链接访问，重定向至新的自定义链接 /author/nickname -> /@nickname
        if (!array_key_exists('uc', $query_vars)) {
            //wp_redirect(home_url('/@' . $nickname), 301);
            wp_redirect(get_author_posts_url($author_id), 301);
            exit;
        }

        // 对不不合法的/@nickname/xxx子路由，直接drop `author_name` 变量以引向404
        if (array_key_exists('uctab', $query_vars) && $uc_tab = $query_vars['uctab']) {
            if ($uc_tab === 'profile') {
                // @see func.Template.php - ct_get_user_template
                //wp_redirect(home_url('/@' . $nickname), 301);
                wp_redirect(get_author_posts_url($author_id), 301);
                exit;
            } elseif (!in_array($uc_tab, (array) json_decode(ALLOWED_UC_TABS)) || ($uc_tab === 'chat' && $logged_user_id == $author_id)) {
                unset($query_vars['author_name']);
                unset($query_vars['uctab']);
                unset($query_vars['uc']);
                $query_vars['error'] = '404';

                return $query_vars;
            } elseif ($uc_tab === 'chat' && !$logged_user_id) {
                // 用户未登录, 跳转至登录页面
                wp_redirect(ct_add_redirect(ct_url_for('signin'), get_author_posts_url($author_id).'/chat'), 302);
                exit;
            }
        }

        // 新链接访问时 /@nickname
        if ($author_id) {
            $query_vars['author'] = $author_id;
            unset($query_vars['author_name']);
        }
        // 找不对匹配nickname的用户id则将nickname当作display_name解析 // TODO: 是否需要按此解析，可能导致不可预见的错误
        return $query_vars;
    } elseif (array_key_exists('author', $query_vars)) {
        $logged_user_id = get_current_user_id();
        $author_id = $query_vars['author'];
        // 如果是原始author链接访问，重定向至新的自定义链接 /author/nickname -> /u/57
        if (!array_key_exists('uc', $query_vars)) {
            wp_redirect(get_author_posts_url($author_id), 301);
            exit;
        }

        // 对不不合法的/u/57/xxx子路由，引向404
        if (array_key_exists('uctab', $query_vars) && $uc_tab = $query_vars['uctab']) {
            if ($uc_tab === 'profile') {
                wp_redirect(get_author_posts_url($author_id), 301);
                exit;
            } elseif (!in_array($uc_tab, (array) json_decode(ALLOWED_UC_TABS)) || ($uc_tab === 'chat' && $logged_user_id == $author_id)) {
                unset($query_vars['author_name']);
                unset($query_vars['author']);
                unset($query_vars['uctab']);
                unset($query_vars['uc']);
                $query_vars['error'] = '404';

                return $query_vars;
            } elseif ($uc_tab === 'chat' && !$logged_user_id) {
                // 用户未登录, 跳转至登录页面
                wp_redirect(ct_add_redirect(ct_url_for('signin'), get_author_posts_url($author_id).'/chat'), 302);
                exit;
            }
        }

        return $query_vars;
    }

    return $query_vars;
}
add_filter('request', 'ct_match_author_link_field', 10, 1);

/* Route : Me - e.g /me/notifications/all */

/**
 * /me主路由处理.
 *
 * @since   2.0.0
 */
function ct_redirect_me_main_route()
{
    if (preg_match('/^\/me$/i', $_SERVER['REQUEST_URI'])) {
        if ($user_id = get_current_user_id()) {
            //$nickname = get_user_meta(get_current_user_id(), 'nickname', true);
            wp_redirect(get_author_posts_url($user_id), 302);
        } else {
            wp_redirect(ct_signin_url(ct_get_current_url()), 302);
        }
        exit;
    }
}
add_action('init', 'ct_redirect_me_main_route'); //the `init` hook is typically used by plugins to initialize. The current user is already authenticated by this time.

/**
 * /me子路由处理 - Rewrite.
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 *
 * @return object
 */
function ct_handle_me_child_routes_rewrite($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        // Note: me子路由与孙路由必须字母组成，不区分大小写
        $new_rules['me/([a-zA-Z]+)$'] = 'index.php?me_child_route=$matches[1]&is_me_route=1';
        $new_rules['me/([a-zA-Z]+)/([a-zA-Z]+)$'] = 'index.php?me_child_route=$matches[1]&me_grandchild_route=$matches[2]&is_me_route=1';
        $new_rules['me/order/([0-9]{1,})$'] = 'index.php?me_child_route=order&me_grandchild_route=$matches[1]&is_me_route=1'; // 我的单个订单详情
        $new_rules['me/editpost/([0-9]{1,})$'] = 'index.php?me_child_route=editpost&me_grandchild_route=$matches[1]&is_me_route=1'; // 编辑文章
        // 分页
        $new_rules['me/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?me_child_route=$matches[1]&is_me_route=1&paged=$matches[2]';
        $new_rules['me/([a-zA-Z]+)/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?me_child_route=$matches[1]&me_grandchild_route=$matches[2]&is_me_route=1&paged=$matches[3]';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    return $wp_rewrite;
}
add_filter('generate_rewrite_rules', 'ct_handle_me_child_routes_rewrite');

/**
 * /me子路由处理 - Template.
 *
 * @since   2.0.0
 */
function ct_handle_me_child_routes_template()
{
    $is_me_route = strtolower(get_query_var('is_me_route'));
    $me_child_route = strtolower(get_query_var('me_child_route'));
    $me_grandchild_route = strtolower(get_query_var('me_grandchild_route'));
    if ($is_me_route && $me_child_route) {
        global $wp_query;
        if ($wp_query->is_404()) {
            return;
        }

        //非Home
        $wp_query->is_home = false;

        //未登录的跳转到登录页
        if(!is_user_logged_in()) {
            wp_redirect(ct_add_redirect(ct_url_for('signin'), ct_get_current_url()), 302);
            exit;
        }

        $allow_routes = (array) json_decode(ALLOWED_ME_ROUTES);
        $allow_child = array_keys($allow_routes);
        // 非法的子路由处理
        if (!in_array($me_child_route, $allow_child)) {
            Utils::set404();

            return;
        }
        // 对于order/8单个我的订单详情路由，孙路由必须是数字
        // 对于editpost/8路由，孙路由必须是数字
        if ($me_child_route === 'order' && (!$me_grandchild_route || !preg_match('/([0-9]{1,})/', $me_grandchild_route))) {
            Utils::set404();

            return;
        }
        if ($me_child_route === 'editpost' && (!$me_grandchild_route || !preg_match('/([0-9]{1,})/', $me_grandchild_route))) {
            Utils::set404();

            return;
        }
        if ($me_child_route !== 'order' && $me_child_route !== 'editpost') {
            $allow_grandchild = $allow_routes[$me_child_route];
            // 对于可以有孙路由的一般不允许直接子路由，必须访问孙路由，比如/me/notifications 必须跳转至/me/notifications/all
            if (empty($me_grandchild_route) && is_array($allow_grandchild)) {
                wp_redirect(home_url('/me/'.$me_child_route.'/'.$allow_grandchild[0]), 302);
                exit;
            }
            // 非法孙路由处理
            if (is_array($allow_grandchild) && !in_array($me_grandchild_route, $allow_grandchild)) {
                Utils::set404();

                return;
            }
        }
        $template = CUTE_THEME_TPL.'/me/tpl.Me.'.ucfirst($me_child_route).'.php';
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'ct_handle_me_child_routes_template', 5);

/**
 * 为自定义的当前用户页(Me)添加query_var白名单.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_me_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'is_me_route';
        $public_query_vars[] = 'me_child_route';
        $public_query_vars[] = 'me_grandchild_route';
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_me_page_query_vars');

/* Route : Action - e.g /m/signin */

/**
 * 登录/注册/注销等动作页路由(/m).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_handle_action_page_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        //action (signin|signup|signout|refresh)
        // m->move(action)
        $new_rules['m/([A-Za-z_-]+)$'] = 'index.php?action=$matches[1]';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'ct_handle_action_page_rewrite_rules');

/**
 * 为自定义的Action页添加query_var白名单.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_action_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'action'; // 添加参数白名单action，代表是各种动作页
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_action_page_query_vars');

/**
 * 登录/注册/注销等动作页模板
 *
 * @since   2.0.0
 */
function ct_handle_action_page_template()
{
    $action = strtolower(get_query_var('action'));
    $allowed_actions = (array) json_decode(ALLOWED_M_ACTIONS);
    if ($action && in_array($action, array_keys($allowed_actions))) {
        global $wp_query;
        $wp_query->is_home = false;
        //$wp_query->is_page = true; //将该模板改为页面属性，而非首页
        $template = CUTE_THEME_TPL.'/actions/tpl.M.'.ucfirst($allowed_actions[$action]).'.php';
        load_template($template);
        exit;
    } elseif ($action && !in_array($action, array_keys($allowed_actions))) {
        // 非法路由处理
        Utils::set404();

        return;
    }
}
add_action('template_redirect', 'ct_handle_action_page_template', 5);

/* Route : OAuth - e.g /oauth/qq */

/**
 * OAuth登录处理页路由(/oauth).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_handle_oauth_page_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        //oauth (qq|weibo|weixin|...)
        $new_rules['oauth/([A-Za-z]+)$'] = 'index.php?oauth=$matches[1]';
        $new_rules['oauth/([A-Za-z]+)/last$'] = 'index.php?oauth=$matches[1]&oauth_last=1';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'ct_handle_oauth_page_rewrite_rules');

/**
 * 为自定义的Action页添加query_var白名单.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_oauth_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'oauth'; // 添加参数白名单oauth，代表是各种OAuth登录处理页
        $public_query_vars[] = 'oauth_last'; // OAuth登录最后一步，整合WP账户，自定义用户名
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_oauth_page_query_vars');

/**
 * OAuth登录处理页模板
 *
 * @since   2.0.0
 */
function ct_handle_oauth_page_template()
{
    $oauth = strtolower(get_query_var('oauth'));
    $oauth_last = get_query_var('oauth_last');
    if ($oauth) {
        if (in_array($oauth, (array) json_decode(ALLOWED_OAUTH_TYPES))):
            global $wp_query;
        $wp_query->is_home = false;
        //$wp_query->is_page = true; //将该模板改为页面属性，而非首页
        $template = $oauth_last ? CUTE_THEME_TPL.'/oauth/tpl.OAuth.Last.php' : CUTE_THEME_TPL.'/oauth/tpl.OAuth.php';
        load_template($template);
        exit; else:
            // 非法路由处理
            Utils::set404();

        return;
        endif;
    }
}
add_action('template_redirect', 'ct_handle_oauth_page_template', 5);

/* Route : Site - e.g /site/upgradebrowser */

/**
 * 网站级工具页路由(如浏览器升级提示、全站通告等)(/site).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_handle_site_util_page_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        //site_util (upgradeBrowser)
        $new_rules['site/([A-Za-z_-]+)$'] = 'index.php?site_util=$matches[1]';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'ct_handle_site_util_page_rewrite_rules');

/**
 * 为自定义的Site Util页添加query_var白名单.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_site_util_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'site_util'; // site_util，代表是网站级别的工具页面
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_site_util_page_query_vars');

/**
 * 网站级工具页模板
 *
 * @since   2.0.0
 */
function ct_handle_site_util_page_template()
{
    $util = get_query_var('site_util');
    $allowed_utils = (array) json_decode(ALLOWED_SITE_UTILS);
    if ($util && in_array(strtolower($util), array_keys($allowed_utils))) {
        global $wp_query;

//        if($wp_query->is_404()) {
//            return;
//        }

        $wp_query->is_home = false;
        //$wp_query->is_page = true; //将该模板改为页面属性，而非首页
        $template = CUTE_THEME_TPL.'/site/tpl.'.ucfirst($allowed_utils[$util]).'.php';
        load_template($template);
        exit;
    } elseif ($util) {
        // 非法路由处理
        Utils::set404();

        return;
    }
}
add_action('template_redirect', 'ct_handle_site_util_page_template', 5);

/* Route : Static - e.g /static/css/main.css */

/**
 * 静态路由，去除静态文件链接中的wp-content等字样(/static).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_handle_static_file_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        $explode_path = explode('/themes/', CUTE_THEME_DIR);
        $theme_name = next($explode_path);
        //static files route
        $new_rules = array(
            'static/(.*)' => 'wp-content/themes/'.$theme_name.'/assets/$1',
        );
        $wp_rewrite->non_wp_rules = $new_rules + $wp_rewrite->non_wp_rules;
    }
}
//add_action('generate_rewrite_rules', 'ct_handle_static_file_rewrite_rules');  // TODO: 需要Apache支持，或者同样Nginx对应方法

/* Route : API - e.g /api/post/1 */

/**
 * REST API路由，wp-json路由别名(/api).
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 */
function ct_handle_api_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        $new_rules = array();
        $new_rules['^api/?$'] = 'index.php?rest_route=/';
        $new_rules['^api/(.*)?'] = 'index.php?rest_route=/$matches[1]';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }
}
//add_action('generate_rewrite_rules', 'ct_handle_api_rewrite_rules'); //直接用 `rest_url_prefix` 更改wp-json至api @see core/api/api.Config.php

/* Route : Management - e.g /management/users */

/**
 * /management主路由处理.
 *
 * @since   2.0.0
 */
function ct_redirect_management_main_route(){
    if(preg_match('/^\/management([^\/]*)$/i', $_SERVER['REQUEST_URI'])){
        if(current_user_can('administrator')){
            //$nickname = get_user_meta(get_current_user_id(), 'nickname', true);
            wp_redirect(ct_url_for('manage_status'), 302);
        }elseif(!is_user_logged_in()) {
            wp_redirect(ct_add_redirect(ct_url_for('signin'), ct_get_current_url()), 302);
            exit;
        }elseif(!current_user_can('edit_users')) {
            wp_die(__('你没有权限访问该页面', 'tt'), __('错误: 没有权限', 'tt'), 403);
        }else{
            Utils::set404();
            return;
        }
        exit;
    }
    if(preg_match('/^\/management\/orders$/i', $_SERVER['REQUEST_URI'])){
        if(current_user_can('administrator')){
            wp_redirect(ct_url_for('manage_orders'), 302); // /management/orders -> management/orders/all
        }elseif(!is_user_logged_in()) {
            wp_redirect(ct_add_redirect(ct_url_for('signin'), ct_get_current_url()), 302);
            exit;
        }elseif(!current_user_can('edit_users')) {
            wp_die(__('你没有权限访问该页面', 'tt'), __('错误: 没有权限', 'tt'), 403);
        }else{
            Utils::set404();
            return;
        }
        exit;
    }
}
add_action('init', 'ct_redirect_management_main_route'); //the `init` hook is typically used by plugins to initialize. The current user is already authenticated by this time.

/**
 * /management子路由处理 - Rewrite.
 *
 * @since   2.0.0
 *
 * @param object $wp_rewrite WP_Rewrite
 *
 * @return object
 */
function ct_handle_management_child_routes_rewrite($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        // Note: management子路由与孙路由必须字母组成，不区分大小写
        $new_rules['management/([a-zA-Z]+)$'] = 'index.php?manage_child_route=$matches[1]&is_manage_route=1';
        //$new_rules['management/([a-zA-Z]+)/([a-zA-Z]+)$'] = 'index.php?manage_child_route=$matches[1]&manage_grandchild_route=$matches[2]&is_manage_route=1';
        $new_rules['management/orders/([a-zA-Z0-9_]+)$'] = 'index.php?manage_child_route=orders&manage_grandchild_route=$matches[1]&is_manage_route=1';
        $new_rules['management/applys/([a-zA-Z0-9_]+)$'] = 'index.php?manage_child_route=applys&manage_grandchild_route=$matches[1]&is_manage_route=1';
        $new_rules['management/users/([a-zA-Z0-9]+)$'] = 'index.php?manage_child_route=users&manage_grandchild_route=$matches[1]&is_manage_route=1';
        // 分页
        $new_rules['management/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?manage_child_route=$matches[1]&is_manage_route=1&paged=$matches[2]';
        $new_rules['management/orders/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?manage_child_route=orders&manage_grandchild_route=$matches[1]&is_manage_route=1&paged=$matches[2]';
        $new_rules['management/applys/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?manage_child_route=applys&manage_grandchild_route=$matches[1]&is_manage_route=1&paged=$matches[2]';
        $new_rules['management/users/([a-zA-Z]+)/page/([0-9]{1,})$'] = 'index.php?manage_child_route=users&manage_grandchild_route=$matches[1]&is_manage_route=1&paged=$matches[2]';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    return $wp_rewrite;
}
add_filter('generate_rewrite_rules', 'ct_handle_management_child_routes_rewrite');

/**
 * /management子路由处理 - Template.
 *
 * @since   2.0.0
 */
function ct_handle_manage_child_routes_template()
{
    $is_manage_route = strtolower(get_query_var('is_manage_route'));
    $manage_child_route = strtolower(get_query_var('manage_child_route'));
    $manage_grandchild_route = strtolower(get_query_var('manage_grandchild_route'));
    if ($is_manage_route && $manage_child_route) {
        //非Home
        global $wp_query;
        $wp_query->is_home = false;

        if ($wp_query->is_404()) {
            return;
        }

        //未登录的跳转到登录页
        if(!is_user_logged_in()) {
            wp_redirect(ct_add_redirect(ct_url_for('signin'), ct_get_current_url()), 302);
            exit;
        }

        //非管理员403处理
        if(!current_user_can('edit_users')) {
            wp_die(__('你没有权限访问该页面', 'tt'), __('错误: 没有权限', 'tt'), 403);
        }

        $allow_routes = (array) json_decode(ALLOWED_MANAGE_ROUTES);
        $allow_child = array_keys($allow_routes);
        // 非法的子路由处理
        if (!in_array($manage_child_route, $allow_child)) {
            Utils::set404();

            return;
        }

        if ($manage_child_route === 'orders' && $manage_grandchild_route) {
            if (preg_match('/([0-9]{1,})/', $manage_grandchild_route)) { // 对于orders/8单个订单详情路由，孙路由必须是数字
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.Order.php';
                load_template($template);
                exit;
            } elseif (in_array($manage_grandchild_route, $allow_routes['orders'])) { // 对于orders/all 指定类型订单列表路由，孙路由是all/cash/credit之中
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.Orders.php';
                load_template($template);
                exit;
            }
            Utils::set404();

            return;
        }
        if ($manage_child_route === 'applys' && $manage_grandchild_route) {
            if (preg_match('/([0-9]{1,})/', $manage_grandchild_route)) { // 对于orders/8单个订单详情路由，孙路由必须是数字
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.Apply.php';
                load_template($template);
                exit;
            } elseif (in_array($manage_grandchild_route, $allow_routes['applys'])) { // 对于orders/all 指定类型订单列表路由，孙路由是all/cash/credit之中
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.Applys.php';
                load_template($template);
                exit;
            }
            Utils::set404();

            return;
        }
        if ($manage_child_route === 'users' && $manage_grandchild_route) {
            if (preg_match('/([0-9]{1,})/', $manage_grandchild_route)) { // 对于users/57单个订单详情路由，孙路由必须是数字
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.User.php';
                load_template($template);
                exit;
            } elseif (in_array($manage_grandchild_route, $allow_routes['users'])) { // 对于users/all 指定类型订单列表路由，孙路由是all/administrator/editor/author/contributor/subscriber之中
                $template = CUTE_THEME_TPL.'/management/tpl.Manage.Users.php';
                load_template($template);
                exit;
            }
            Utils::set404();

            return;
        }
        if ($manage_child_route !== 'orders' && $manage_child_route !== 'users' && $manage_child_route !== 'applys') {
            // 除orders/users外不允许有孙路由
            if ($manage_grandchild_route) {
                Utils::set404();

                return;
            }
        }
        $template_id = ucfirst($manage_child_route);
        $template = CUTE_THEME_TPL.'/management/tpl.Manage.'.$template_id.'.php';
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'ct_handle_manage_child_routes_template', 5);

/**
 * 为自定义的管理页添加query_var白名单.
 *
 * @since   2.0.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_manage_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'is_manage_route';
        $public_query_vars[] = 'manage_child_route';
        $public_query_vars[] = 'manage_grandchild_route';
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_manage_page_query_vars');

/**
 * 刷新固定链接缓存.
 *
 * @since   2.0.0
 */
function ct_refresh_rewrite()
{
    // 如果启用了memcache等对象缓存，固定链接的重写规则缓存对应清除
    wp_cache_flush();

    // 刷新固定链接
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

/**
 * 刷新固定链接缓存.
 *
 * @since   2.0.0
 */
function tt_refresh_rewrite()
{
    // 如果启用了memcache等对象缓存，固定链接的重写规则缓存对应清除
    wp_cache_flush();

    // 刷新固定链接
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

/* Route : Thread - e.g /thread/1 */

/**
 * /thread路由处理 - Rewrite.
 *
 * @since   2.6.0
 *
 * @param object $wp_rewrite WP_Rewrite
 *
 * @return object
 */
function ct_handle_thread_routes_rewrite($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $new_rules['thread/create([^/]*)$'] = 'index.php?thread_route=create&is_thread_route=1';
        $new_rules['thread/edit/([0-9]+)([^/]*)$'] = 'index.php?thread_route=create&is_thread_route=1&pid=$matches[1]';

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    return $wp_rewrite;
}
add_filter('generate_rewrite_rules', 'ct_handle_thread_routes_rewrite');

/**
 * /thread路由处理 - Template.
 *
 * @since   2.6.0
 */
function ct_handle_thread_routes_template()
{
    $is_thread_route = strtolower(get_query_var('is_thread_route'));
    $thread_route = strtolower(get_query_var('thread_route'));
    if ($is_thread_route && $thread_route) {
        //非Home
        global $wp_query;
        $wp_query->is_home = false;

        if ($wp_query->is_404()) {
            return;
        }

        $template = THEME_TPL.'/thread/tpl.Thread.Main.php';
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'ct_handle_thread_routes_template', 5);

/**
 * 为自定义的Thread页添加query_var白名单.
 *
 * @since   2.6.0
 *
 * @param object $public_query_vars 公共全局query_vars
 *
 * @return object
 */
function ct_add_thread_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'is_thread_route';
        $public_query_vars[] = 'thread_route';
    }

    return $public_query_vars;
}
add_filter('query_vars', 'ct_add_thread_page_query_vars');

/**
 * 对于部分链接，拒绝搜索引擎索引.
 *
 * @since   2.0.0
 *
 * @param string $output Robots.txt内容
 * @param bool   $public
 *
 * @return string
 */
function ct_robots_modification($output, $public)
{
    $output .= "\nDisallow: /oauth";
    $output .= "\nDisallow: /m";
    $output .= "\nDisallow: /me";

    return $output;
}
add_filter('robots_txt', 'ct_robots_modification', 10, 2);

/**
 * 为部分页面添加noindex的meta标签.
 *
 * @since   2.0.0
 */
function ct_add_noindex_meta()
{
    if (get_query_var('is_uc') || get_query_var('action') || get_query_var('site_util') || get_query_var('is_me_route') || get_query_var('is_thread_route') || get_post_type() == 'thread') {
        wp_no_robots();
    }
}
add_action('wp_head', 'ct_add_noindex_meta');

/**
 * 添加周循环的定时任务周期选项.
 *
 * @since   2.0.0
 *
 * @param array $schedules
 *
 * @return array
 */
function ct_cron_add_weekly($schedules)
{
    $schedules['weekly'] = array(
        'interval' => 604800, // 1周 = 60秒 * 60分钟 * 24小时 * 7天
        'display' => __('Weekly', 'tt'),
    );

    return $schedules;
}
add_filter('cron_schedules', 'ct_cron_add_weekly');

/**
 * 每小时执行的定时任务
 *
 * @since   2.0.0
 */
function ct_setup_common_hourly_schedule()
{
    if (!wp_next_scheduled('ct_setup_common_hourly_event')) {
        // 1471708800是北京2016年8月21日00:00:00时间戳
        wp_schedule_event(1471708800, 'hourly', 'ct_setup_common_hourly_event');
    }
}
add_action('wp', 'ct_setup_common_hourly_schedule');

/**
 * 每天执行的定时任务
 *
 * @since   2.0.0
 */
function ct_setup_common_daily_schedule()
{
    if (!wp_next_scheduled('ct_setup_common_daily_event')) {
        // 1471708800是北京2016年8月21日00:00:00时间戳
        wp_schedule_event(1471708800, 'daily', 'ct_setup_common_daily_event');
    }
}
add_action('wp', 'ct_setup_common_daily_schedule');

/**
 * 每天执行的图床修复
 *
 * @since   2.0.0
 */
function ct_setup_common_img_schedule()
{
    if (!wp_next_scheduled('ct_setup_common_img_event')) {
        // 1471708800是北京2016年8月21日00:00:00时间戳
        wp_schedule_event(1471712400, 'daily', 'ct_setup_common_img_event');
    }
}
add_action('wp', 'ct_setup_common_img_schedule');

/**
 * 每两天执行的定时任务
 *
 * @since   2.0.0
 */
function ct_setup_common_twicedaily_schedule()
{
    if (!wp_next_scheduled('ct_setup_common_twicedaily_event')) {
        // 1471708800是北京2016年8月21日00:00:00时间戳
        wp_schedule_event(1471708800, 'twicedaily', 'ct_setup_common_twicedaily_event');
    }
}
add_action('wp', 'ct_setup_common_twicedaily_schedule');

/**
 * 每周执行的定时任务
 *
 * @since   2.0.0
 */
function ct_setup_common_weekly_schedule()
{
    if (!wp_next_scheduled('ct_setup_common_weekly_event')) {
        // 1471795200是北京2016年8月22日 星期一 00:00:00时间戳
        wp_schedule_event(1471795200, 'twicedaily', 'ct_setup_common_weekly_event');
    }
}
add_action('wp', 'ct_setup_common_weekly_schedule');


/* Vue shared global vars */
defined('VUETT') || define('VUETT', json_encode(array(
    'uid' => get_current_user_id(),
    'publicPath' => CUTE_THEME_CDN_ASSETS,
    'home' => esc_url_raw(home_url()),
    'themeRoot' => CUTE_THEME_URI,
    'commonServiceApi' => home_url('/api/v1/commonservice'),
    '_wpnonce' => wp_create_nonce('wp_rest')
)));
defined('FULL_THREAD_JS_VENDOR') || define('FULL_THREAD_JS_VENDOR', CUTE_THEME_CDN_ASSETS.'/js/'.JS_THREAD_VENDOR);
defined('FULL_THREAD_JS_APP') || define('FULL_THREAD_JS_APP', CUTE_THEME_CDN_ASSETS.'/js/'.JS_THREAD_APP);

/**
 * 注册Scripts.
 *
 * @since   2.0.0
 */
function ct_register_scripts()
{
    $jquery_url = json_decode(JQUERY_SOURCES)->{ct_get_option('tt_jquery', 'local_1')};
    if ( !is_admin() ) {
    wp_deregister_script( 'jquery' );
    wp_register_script('jquery', $jquery_url, array(), null, ct_get_option('tt_foot_jquery', false));
    wp_enqueue_script('jquery');
    }else{
    wp_register_script('ct_jquery', $jquery_url, array(), null, ct_get_option('tt_foot_jquery', false));
    wp_enqueue_script('ct_jquery');
    }
    //wp_register_script( 'tt_common', CUTE_THEME_CDN_ASSETS . '/js/' . JS_COMMON, array(), null, true );
    wp_register_script('ct_home', CUTE_THEME_CDN_ASSETS.'/js/'.JS_HOME, array(), null, true);
    wp_register_script('ct_front_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_FRONT_PAGE, array(), null, true);
    wp_register_script('ct_single_post', CUTE_THEME_CDN_ASSETS.'/js/'.JS_SINGLE, array(), null, true);
    wp_register_script('ct_single_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_PAGE, array(), null, true);
    wp_register_script('ct_archive_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_ARCHIVE, array(), null, true);
    wp_register_script('ct_product_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_PRODUCT, array(), null, true);
    wp_register_script('ct_products_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_PRODUCT_ARCHIVE, array(), null, true);
    wp_register_script('ct_uc_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_UC, array(), null, true);
    wp_register_script('ct_me_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_ME, array(), null, true);
    wp_register_script('ct_action_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_ACTION, array(), null, true);
    wp_register_script('ct_404_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_404, array(), null, true);
    wp_register_script('ct_site_utils', CUTE_THEME_CDN_ASSETS.'/js/'.JS_SITE_UTILS, array(), null, true);
    wp_register_script('ct_oauth_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_OAUTH, array(), null, true);
    wp_register_script('ct_manage_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_MANAGE, array(), null, true);
    wp_register_script('ct_thread_page', CUTE_THEME_CDN_ASSETS.'/js/'.JS_THREAD, array(), null, true);
    wp_register_script('ct_thread_vendor', FULL_THREAD_JS_VENDOR, array(), null, true);
    wp_register_script('ct_thread_app', FULL_THREAD_JS_APP, array('ct_thread_vendor'), null, true);

    $data = array(
        'debug' => ct_get_option('tt_theme_debug', false),
        'uid' => get_current_user_id(),
        'language' => get_option('WPLANG', 'zh_CN'),
        'apiRoot' => esc_url_raw(get_rest_url()),
        '_wpnonce' => wp_create_nonce('wp_rest'), // REST_API服务验证该nonce, 如果不提供将清除登录用户信息  @see rest-api.php `rest_cookie_check_errors`
        'home' => esc_url_raw(home_url()),
        'themeRoot' => CUTE_THEME_URI,
        'isHome' => is_home(),
        'commentsPerPage' => ct_get_option('tt_comments_per_page', 20),
        'sessionApiTail' => ct_get_option('tt_session_api', 'session'),
        'contributePostWordsMin' => absint(ct_get_option('tt_contribute_post_words_min', 100)),
        'o' => '0',
        'e' => get_bloginfo('admin_email'),
        'v' => trim(wp_get_theme()->get('Version')),
        'yzApi' => ct_get_option('tt_youzan_util_api', ''),
        'siteName' => get_bloginfo('name'),
        'weiboKey' => ct_get_option('tt_weibo_openkey'),
        'threadSlug' => ct_get_option('tt_thread_archives_slug', 'thread'),
        'threadVendor' => FULL_THREAD_JS_VENDOR,
        'publicPath' => CUTE_THEME_CDN_ASSETS,
    );
    if (is_single()) {
        $data['isSingle'] = true;
        $data['pid'] = get_queried_object_id();
    }

    $script = '';
    $post_type = get_post_type();

    if (is_home()) {
        $script = 'ct_home';
    } elseif($post_type === 'thread' || get_query_var('is_thread_route')) {
        $script = array('ct_thread_vendor', 'ct_thread_app');
    } elseif (is_single()) {
        $script = $post_type === 'product' ? 'ct_product_page' : ($post_type === 'bulletin' ? 'ct_single_page' : 'ct_single_post');
    } elseif ((is_archive() && !is_author()) || (is_search() && isset($_GET['in_shop']) && $_GET['in_shop'] == 1)) {
        $script = $post_type === 'product' || (is_search() && isset($_GET['in_shop']) && $_GET['in_shop'] == 1) ? 'ct_products_page' : 'ct_archive_page';
    } elseif (is_author()) {
        $script = 'ct_uc_page';
    } elseif (is_404()) {
        $script = 'ct_404_page';
    } elseif (get_query_var('is_me_route')) {
        $script = 'ct_me_page';
    } elseif (get_query_var('action')) {
        $script = 'ct_action_page';
    } elseif (is_front_page()) {
        $script = 'ct_front_page';
    } elseif (get_query_var('site_util')) {
        $script = 'ct_site_utils';
    } elseif (get_query_var('oauth')) {
        $script = 'ct_oauth_page';
    } elseif (get_query_var('is_manage_route')) {
        $script = 'ct_manage_page';
    } elseif (is_search()) {
        $script = 'ct_archive_page';
    } else {
        // is_page() ?
        $script = 'ct_single_page';
    }

    if ($script) {
        if (!is_array($script)) {
            $script = array($script);
        }
        foreach ($script as $key => $item) {
            if ($key == 0) {
                wp_localize_script($item, 'TT', $data);
            }
            wp_enqueue_script($item);
        }
    }
}
add_action('wp_enqueue_scripts', 'ct_register_scripts');

//load_func('base/func.Seo');
/**
 * 根据页面输出相应标题.
 *
 * @since   2.0.0
 *
 * @return string
 */
function ct_get_page_title()
{
    $space = ' - ';
    if(!ct_get_option('ct_title_space',true)){
      $space = '-';
    }
    $title = '';
    if ($action = get_query_var('action')) {
        switch ($action) {
            case 'signin':
                $title = __('Sign In', 'tt');
                break;
            case 'signup':
                $title = __('Sign Up', 'tt');
                break;
            case 'activate':
                $title = __('Activate Registration', 'tt');
                break;
            case 'signout':
                $title = __('Sign Out', 'tt');
                break;
            case 'findpass':
                $title = __('Find Password', 'tt');
                break;
            case 'resetpass':
                $title = __('Reset Password', 'tt');
                break;
        }

        return $title.$space.get_bloginfo('name');
    }
    if ($me_route = get_query_var('me_child_route')) {
        switch ($me_route) {
            case 'settings':
                $title = __('My Settings', 'tt');
                break;
            case 'notifications':
                $title = __('My Notifications', 'tt');
                break;
            case 'messages': //TODO grandchild route in/out msgbox
                $title = __('My Messages', 'tt');
                break;
            case 'stars':
                $title = __('My Stars', 'tt');
                break;
            case 'credits':
                $title = '我的'.CREDIT_NAME;
                break;
            case 'cash':
                $title = __('My Cash', 'tt');
                break;
            case 'orders':
                $title = __('My Orders', 'tt');
                break;
            case 'order':
                $title = __('My Order', 'tt');
                break;
            case 'apply':
                $title = '提现记录';
                break;
            case 'drafts':
                $title = __('My Drafts', 'tt');
                break;
            case 'newpost':
                $title = __('New Post', 'tt');
                break;
            case 'editpost':
                $title = __('Edit Post', 'tt');
                break;
            case 'membership':
                $title = __('My Membership', 'tt');
                break;
        }

        return $title.$space.get_bloginfo('name');
    }
    if ($site_util = get_query_var('site_util')) {
        switch ($site_util) {
            case 'checkout':
                $title = __('Check Out Orders', 'tt');
                break;
            case 'alipayreturn':
            case 'wxpayreturn':
            case 'payresult':
                $title = __('Payment Result', 'tt');
                break;
            case 'qrpay':
            case 'wxqrpay':
            case 'wxwappay':
            case 'wxjspay':
            case 'kpay':
            case 'codepay':
            case 'xunhupay':
            case 'youzanpay':
            case 'paypal':
            case 'aliqrpay':
                $title = __('Do Payment', 'tt');
                break;
            case 'download':
                global $origin_post;
                $title = __('Resources Download:', 'tt').$origin_post->post_title;
                break;
            case 'privacy-policies-and-terms':
                $title = __('Privacy Policies and Terms', 'tt');
                break;
            case 'alipayreturn':
                $title = __('Payment Result', 'tt');
                break;
            // TODO more
        }

        return $title.$space.get_bloginfo('name');
    }
    if ($oauth = get_query_var('oauth') && get_query_var('oauth_last')) {
        switch ($oauth) {
            case 'qq':
                $title = __('Complete Account Info - QQ Connect', 'tt');
                break;
            case 'weibo':
                $title = __('Complete Account Info - Weibo Connect', 'tt');
                break;
            case 'weixin':
                $title = __('Complete Account Info - Weixin Connect', 'tt');
                break;
        }

        return $title.$space.get_bloginfo('name');
    }
    if ($site_manage = get_query_var('manage_child_route')) {
        switch ($site_manage) {
            case 'status':
                $title = __('Site Statistic', 'tt');
                break;
            case 'posts':
                $title = __('Posts Management', 'tt');
                break;
            case 'comments':
                $title = __('Comments Management', 'tt');
                break;
            case 'users':
                $title = __('Users Management', 'tt');
                break;
            case 'orders':
                $title = __('Orders Management', 'tt');
                break;
            case 'coupons':
                $title = __('Coupons Management', 'tt');
                break;
            case 'invites':
                $title = __('邀请码管理', 'tt');
                break;
            case 'kamis':
                $title = __('卡密管理', 'tt');
                break;
            case 'applys':
                $title = __('提现管理', 'tt');
                break;
            case 'cards':
                $title = __('Cards Management', 'tt');
                break;
            case 'members':
                $title = __('Members Management', 'tt');
                break;
            case 'products':
                $title = __('Products Management', 'tt');
                break;
        }

        return $title.$space.get_bloginfo('name');
    }
    if (is_home() || is_front_page()) {
        $title = get_bloginfo('name').$space.get_bloginfo('description');
    } elseif (is_single() && get_post_type() != 'product') {
        global $post;
        $tkd = get_post_meta($post->ID, 'tt_post_seo', true);
        $tkd = $tkd ? maybe_unserialize($tkd) : array('tt_post_title' => '', 'tt_post_keywords' => '', 'tt_post_description' => '');
        $title = trim(wp_title('', false)) .$space.get_bloginfo('name');
        $title = $tkd['tt_post_title'] ? $tkd['tt_post_title'] : $title;
        if ($page = get_query_var('page') && get_query_var('page') > 1) {
            $title .= $space .'第'.get_query_var('page').'页';
        }
        if (get_query_var('is_thread_route') || get_post_type() == 'thread') {
            $title .= $space . __('Community', 'tt');
        }
    } elseif (is_page()) {
        $title = trim(wp_title('', false)).$space.get_bloginfo('name');
    } elseif (is_category()) {
        $cat_ID = get_query_var('cat');
        $category = get_queried_object();
        $des = $category->description ? $category->description.$space : '';
        $title = $category->cat_name.$space.$des.get_bloginfo('name');
        $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
        $title = $term_meta['tax_title'] ? $term_meta['tax_title'] : $title;
    } elseif (is_author()) {
        // TODO more tab titles
        $author = get_queried_object();
        $name = ct_get_privacy_mail($author->data->display_name);
        $title = sprintf(__('%s\'s Home Page', 'tt'), $name).$space.get_bloginfo('name');
    } elseif (get_post_type() == 'product') {
        if (is_archive()) {
            if (ct_is_product_category()) {
                $title = get_queried_object()->name.$space.__('Product Category', 'tt');
            } elseif (ct_is_product_tag()) {
                $title = get_queried_object()->name.$space.__('Product Tag', 'tt');
            } else {
                $title = ct_get_option('tt_shop_name', __('Market', 'tt')).$space.get_bloginfo('name');
            }
        } else {
            $title = trim(wp_title('', false)).$space.ct_get_option('tt_shop_name', __('Market', 'tt'));
        }
    } elseif (is_search()) {
        $title = __('Search', 'tt').get_search_query().$space.get_bloginfo('name');
    } elseif (is_year()) {
        $title = get_the_time(__('Y', 'tt')).__('posts archive', 'tt').$space.get_bloginfo('name');
    } elseif (is_month()) {
        $title = get_the_time(__('Y.n', 'tt')).__('posts archive', 'tt').$space.get_bloginfo('name');
    } elseif (is_day()) {
        $title = get_the_time(__('Y.n.j', 'tt')).__('posts archive', 'tt').$space.get_bloginfo('name');
    } elseif (is_tag()) {
        $title = __('Tag: ', 'tt').get_queried_object()->name.$space.get_bloginfo('name');
    } elseif (is_404()) {
        $title = __('Page Not Found', 'tt').$space.get_bloginfo('name');
    }

    if (empty($title) && (get_query_var('is_thread_route') || get_post_type() == 'thread')) {
        $title = get_bloginfo('name') . $space . __('Community', 'tt');
    }

    // paged
    if ($paged = get_query_var('paged') && get_query_var('paged') > 1) {
        $title .= $space .'第'.get_query_var('paged').'页';
    }

    return $title;
}

/**
 * 获取关键词和描述
 *
 * @since 2.0.0
 * @return array
 */
function ct_get_keywords_and_description() {
    $keywords = '';
    $description = '';
    if($action = get_query_var('action')) {
        switch ($action) {
            case 'signin':
                $keywords = __('Sign In', 'tt');
                break;
            case 'signup':
                $keywords = __('Sign Up', 'tt');
                break;
            case 'activate':
                $keywords = __('Activate Registration', 'tt');
                break;
            case 'signout':
                $keywords = __('Sign Out', 'tt');
                break;
            case 'findpass':
                $keywords = __('Find Password', 'tt');
                break;
        }
        $description = __('由Cute主题驱动', 'tt');
        return array(
            'keywords' => $keywords,
            'description' => $description
        );
    }
    if(is_home() || is_front_page()) {
        $keywords = ct_get_option('tt_home_keywords');
        $description = ct_get_option('tt_home_description');
    }elseif(is_single() && get_post_type() != 'product') {
        $tags = get_the_tags();
        $tag_names = array();
        if($tags){
            foreach ($tags as $tag){
                $tag_names[] = $tag->name;
            }
            $keywords = implode(',', $tag_names);
        }
        global $post;
        $tkd = get_post_meta($post->ID, 'tt_post_seo', true);
        $tkd = $tkd ? maybe_unserialize($tkd) : array('tt_post_title' => '', 'tt_post_keywords' => '', 'tt_post_description' => '');
        setup_postdata($post);
        $description = mb_substr(strip_tags(get_the_excerpt($post)), 0, 100);
        $keywords = $tkd['tt_post_keywords'] ? $tkd['tt_post_keywords'] : $keywords;
        $description = $tkd['tt_post_description'] ? $tkd['tt_post_description'] : $description;
    }elseif(is_page()){
        global $post;
        if($post->ID){
            $keywords = get_post_meta($post->ID, 'tt_keywords', true);
            $description = get_post_meta($post->ID, 'tt_description', true);
        }
    }elseif(is_category()) {
        $category = get_queried_object();
        $keywords = $category->name;
        $description = strip_tags($category->description);
        $cat_ID = get_query_var('cat');
        $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
        $keywords = $term_meta['tax_keywords'] ? $term_meta['tax_keywords'] : $keywords;
        $description = $term_meta['tax_description'] ? $term_meta['tax_description'] : $description;
    }elseif(is_author()){
        // TODO more tab titles
        $author = get_queried_object();
        $name = ct_get_privacy_mail($author->data->display_name);
        $keywords = $name . ',' . __('Ucenter', 'tt'). ','. __('Cute主题用户中心和商店系统', 'tt');
        $description = sprintf(__('%s\'s Home Page', 'tt'), $name) . __(',由Cute主题驱动', 'tt');
    }elseif(get_post_type() == 'product'){
        if(is_archive()){
            if(ct_is_product_category()) {
                $category = get_queried_object();
                $keywords = $category->name;
                $description = strip_tags($category->description);
            }elseif(ct_is_product_tag()){
                $tag = get_queried_object();
                $keywords = $tag->name;
                $description = strip_tags($tag->description);
            }else{
                $keywords = ct_get_option('tt_shop_keywords', __('Market', 'tt')) . ',' . __('Cute主题用户中心和商店系统', 'tt');
                $banner_title = ct_get_option('tt_shop_title', 'Shop Quality Products');
                $banner_subtitle = ct_get_option('tt_shop_sub_title', 'Themes - Plugins - Services');
                $description = $banner_title . ', ' . $banner_subtitle . ', ' . __('由Cute主题驱动(Cute, 一个功能强大的内嵌用户中心和商店系统的WordPress主题)', 'tt');
            }
        }else{
            global $post;
            $tags = array();
            if($post->ID){
                $tags = wp_get_post_terms($post->ID, 'product_tag');
            }
            $tag_names = array();
            foreach ($tags as $tag){
                $tag_names[] = $tag->name;
            }
            $keywords = implode(',', $tag_names);
            $description = strip_tags(get_the_excerpt());
        }
    }elseif(is_search()){
        //TODO
    }elseif(is_year()){
        //TODO
    }elseif(is_month()){
        //TODO
    }elseif(is_day()){
        //TODO
    }elseif(is_tag()){
        $tag = get_queried_object();
        $keywords = $tag->name;
        $description = strip_tags($tag->description);
    }elseif(is_404()){
        //TODO
    }

    return array(
        'keywords' => $keywords,
        'description' => $description
    );
}

/**
 * 动态边栏.
 *
 * @since   2.0.0
 *
 * @return string
 */
function ct_dynamic_sidebar()
{
    // 默认通用边栏
    $sidebar = 'sidebar_common';

    // 根据页面选择边栏
    if (is_home() && $option = ct_get_option('tt_home_sidebar')) {
        $sidebar = $option;
    }
    if (is_single() && $option = ct_get_option('tt_single_sidebar')) {
        $sidebar = $option;
    }
    if (is_archive() && $option = ct_get_option('tt_archive_sidebar')) {
        $sidebar = $option;
    }
    if (is_category() && $option = ct_get_option('tt_category_sidebar')) {
        $sidebar = $option;
    }
    if (is_search() && $option = ct_get_option('tt_search_sidebar')) {
        $sidebar = $option;
    }
    if (is_404() && $option = ct_get_option('tt_404_sidebar')) {
        $sidebar = $option;
    }
    if (is_page() && $option = ct_get_option('tt_page_sidebar')) {
        $sidebar = $option;
    }
    if (get_query_var('site_util') == 'download' && $option = ct_get_option('tt_download_sidebar')) {
        $sidebar = $option;
    }

    // 检查一个页面或文章是否有特指边栏
    if (is_singular()) {
        wp_reset_postdata();
        global $post;
        $meta = get_post_meta($post->ID, 'tt_sidebar', true);  //TODO: add post meta box for `tt_sidebar`
        if ($meta) {
            $sidebar = $meta;
        }
    }

    return $sidebar;
}

/**
 * 根据用户设置注册边栏.
 *
 * @since   2.0.0
 */
function ct_register_sidebars()
{
    $sidebars = (array) ct_get_option('tt_register_sidebars', array('sidebar_common' => true));
    $titles = array(
        'sidebar_common' => __('Common Sidebar', 'tt'),
        'sidebar_home' => __('Home Sidebar', 'tt'),
        'sidebar_single' => __('Single Sidebar', 'tt'),
        //'sidebar_archive'   =>    __('Archive Sidebar', 'tt'),
        //'sidebar_category'  =>    __('Category Sidebar', 'tt'),
        'sidebar_search' => __('Search Sidebar', 'tt'),
        //'sidebar_404'       =>    __('404 Sidebar', 'tt'),
        'sidebar_page' => __('Page Sidebar', 'tt'),
        'sidebar_download' => __('Download Page Sidebar', 'tt'),
    );
    foreach ($sidebars as $key => $value) {
        if (!$value) {
            continue;
        }
        $title = array_key_exists($key, $titles) ? $titles[$key] : $value;
        register_sidebar(
            array(
                'name' => $title,
                'id' => $key,
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            )
        );
    }
}
add_action('widgets_init', 'ct_register_sidebars');

/**
 * 自定义Index模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_index_template($template)
{
    //TODO: if(ct_get_option('layout')=='xxx') -> index-xxx.php
    unset($template);

    return CUTE_THEME_TPL.'/tpl.Index.php';
}
add_filter('index_template', 'ct_get_index_template', 10, 1);

/**
 * 自定义Home文章列表模板，优先级高于Index.
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_home_template($template)
{
    unset($template);

    return CUTE_THEME_TPL.'/tpl.Home.php';
}
add_filter('home_template', 'ct_get_home_template', 10, 1);

/**
 * 自定义首页静态页面模板，基于后台选项首页展示方式，与Index同级.
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_front_page_template($template)
{
    unset($template);

    return locate_template(array('core/templates/tpl.FrontPage.php', 'core/templates/tpl.Home.php', 'core/templates/tpl.Index.php'));
}
add_filter('front_page_template', 'ct_get_front_page_template', 10, 1);

/**
 * 自定义404模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_404_template($template)
{
    unset($template);

    return CUTE_THEME_TPL.'/tpl.404.php';
}
add_filter('404_template', 'ct_get_404_template', 10, 1);

/**
 * 自定义归档模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_archive_template($template)
{
    unset($template);

    return CUTE_THEME_TPL.'/tax/tpl.Archive.php';
}
add_filter('archive_template', 'ct_get_archive_template', 10, 1);

/**
 * 自定义作者模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return array 自定义模板路径数组
 */
function ct_get_author_template($template)
{
    unset($template);

    // 为不同角色用户定义不同模板
    // https://developer.wordpress.org/themes/basics/template-hierarchy/#example
    $author = get_queried_object();
    $role = count($author->roles) ? $author->roles[0] : 'subscriber';

    // 判断是否用户中心页(因为用户中心页和默认的作者页采用了相同的wp_query_object)
    if (get_query_var('uc') && intval(get_query_var('uc')) === 1) {
        $template = apply_filters('user_template', $author);
        if ($template === 'header-404') {
            return '';
        }
        if ($template) {
            return $template;
        }
    }

    $template = 'core/templates/tpl.Author.php'; // TODO: 是否废弃 tpl.Author类似模板，Author已合并至UC
    return locate_template(array('core/templates/tpl.Author.'.ucfirst($role).'.php', $template));
}
add_filter('author_template', 'ct_get_author_template', 10, 1);

/**
 * 获取用户页模板
 * // 主题将用户与作者相区分，作者页沿用默认的WP设计，展示作者的文章列表，用户页重新设计为用户的各种信息以及前台用户中心 //TODO 现在合并了, 废弃WP原有作者文章列表页(基础版无UC时才有).
 *
 * @since   2.0.0
 *
 * @param object $user WP_User对象
 *
 * @return string
 */
function ct_get_user_template($user)
{
    $templates = array();

    if ($user instanceof WP_User) {
        if ($uc_tab = get_query_var('uctab')) {
            // 由于profile tab是默认tab，直接使用/@nickname主路由，对于/@nickname/profile的链接会重定向处理，因此不放至允许的tabs中
            $allow_tabs = (array) json_decode(ALLOWED_UC_TABS);
            if (!in_array($uc_tab, $allow_tabs)) {
                return 'header-404';
            }
            $templates[] = 'core/templates/uc/tpl.UC.'.ucfirst(strtolower($uc_tab)).'.php';
        } else {
            //$role = $user->roles[0];
            $templates[] = 'core/templates/uc/tpl.UC.Profile.php';
            // Maybe dropped
            // TODO: maybe add membership templates
        }
    }
    $templates[] = 'core/templates/uc/tpl.UC.php';

    return locate_template($templates);
}
add_filter('user_template', 'ct_get_user_template', 10, 1);

/**
 * 自定义分类模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_category_template($template)
{
    unset($template);
    // TODO: add category slug support
    return locate_template(array('core/templates/tax/tpl.Category.php', 'core/templates/tax/tpl.Archive.php'));
}
add_filter('category_template', 'ct_get_category_template', 10, 1);

/**
 * 自定义标签模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径数组
 */
function ct_get_tag_template($template)
{
    unset($template);

    return locate_template(array('core/templates/tax/tpl.Tag.php', 'core/templates/tax/tpl.Archive.php'));
}
add_filter('tag_template', 'ct_get_tag_template', 10, 1);

/**
 * 自定义Taxonomy模板，Category/Tag均属于Taxonomy，可做备选模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_taxonomy_template($template)
{
    unset($template);

    return locate_template(array('core/templates/tax/tpl.Taxonomy.php', 'core/templates/tax/tpl.Archive.php'));
}
add_filter('taxonomy_template', 'ct_get_taxonomy_template', 10, 1);

/**
 * 自定义时间归档模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_date_template($template)
{
    unset($template);

    return locate_template(array('core/templates/tax/tpl.Date.php', 'core/templates/tax/tpl.Archive.php'));
}
add_filter('date_template', 'ct_get_date_template', 10, 1);

/**
 * 自定义默认页面模板(区别于自定义页面模板).
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_page_template($template)
{
    if (!empty($template)) {
        return $template;
    }
    unset($template);

    return locate_template(array('core/templates/page/tpl.Page.php'));
}
add_filter('page_template', 'ct_get_page_template', 10, 1);

/**
 * 自定义搜素结果页模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_search_template($template)
{
    unset($template);
    if (isset($_GET['in_shop']) && $_GET['in_shop'] == 1) {
        return locate_template(array('core/templates/shop/tpl.Product.Search.php'));
    }

    return locate_template(array('core/templates/tpl.Search.php'));
}
add_filter('search_template', 'ct_get_search_template', 10, 1);

/**
 * 自定义文章页模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_single_template($template)
{
    unset($template);
    $single = get_queried_object();

    return locate_template(array('core/templates/single/tpl.Single.'.$single->slug.'.php', 'core/templates/single/tpl.Single.'.$single->ID.'.php', 'core/templates/single/tpl.Single.php'));
}
add_filter('single_template', 'ct_get_single_template', 10, 1);

/**
 * 自定义附件页模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_attachment_template($template)
{
    unset($template);

    return locate_template(array('core/templates/attachments/tpl.Attachment.php'));
}
add_filter('attachment_template', 'ct_get_attachment_template', 10, 1);

/**
 * 自定义[Plain] Text附件模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return array 自定义模板路径数组
 */
function ct_get_text_template($template)
{
    //TODO: other MIME types, e.g `video`
    unset($template);

    return locate_template(array('core/templates/attachments/tpl.MIMEText.php', 'core/templates/attachments/tpl.Attachment.php'));
}
add_filter('text_template', 'ct_get_text_template', 10, 1);
add_filter('plain_template', 'ct_get_text_template', 10, 1);
add_filter('text_plain_template', 'ct_get_text_template', 10, 1);

/**
 * 自定义弹出评论模板
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_comments_popup_template($template)
{
    unset($template);

    return CUTE_THEME_TPL.'/tpl.CommentPopup.php';
}
add_filter('comments_popup', 'ct_get_comments_popup_template', 10, 1);

/**
 * 自定义嵌入式文章模板
 * WordPress 4.4新功能
 * https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/.
 *
 * @since   2.0.0
 *
 * @param string $template 默认模板路径
 *
 * @return string 自定义模板路径
 */
function ct_get_embed_template($template)
{
    unset($template);

    return CUTE_THEME_TPL.'/tpl.Embed.php';
}
add_filter('embed_template', 'ct_get_embed_template', 10, 1);

/**
 * CMS首页各分类使用的模板
 *
 * @param $cat_id
 *
 * @return string
 */
function ct_get_cms_cat_template ($cat_id) {
    $default = 'Style_0';
    $key = sprintf('tt_cms_home_cat_style_%d', $cat_id);
    $option = ct_get_option($key, $default);
    if (in_array($option, array('Style_0', 'Style_1', 'Style_2', 'Style_3', 'Style_4', 'Style_5', 'Style_6', 'Style_7'))) {
        return $option;
    }
    return $default;
}

/**
 * 获取文章缩略图.
 *
 * @since   2.0.0
 *
 * @param   int | object    文章id或WP_Post对象
 * @param string | array $size 图片尺寸
 *
 * @return string
 */
function ct_get_thumb($post = null, $size = 'thumbnail')
{
    if (is_numeric($post) && $post <= 0) {
        $specifiedImage = '';
        if ($post == -4) {
            // 充值积分
            $specifiedImage = CUTE_THEME_URI.'/assets/img/credit-thumb.png';
        } elseif ($post == -1 || $post == -2) {
            // 月度/季度会员
            $specifiedImage = CUTE_THEME_URI.'/assets/img/membership-thumb.png';
        } elseif ($post == -3) {
            // 年付会员
            $specifiedImage = CUTE_THEME_URI.'/assets/img/annual-membership-thumb.png';
        } elseif ($post == -9) {
            // 邀请码
            $specifiedImage = CUTE_THEME_URI.'/assets/img/invite-thumb.png';
        } elseif ($post == -8) {
            // 捐赠
            $specifiedImage = CUTE_THEME_URI.'/assets/img/donate-thumb.png';
        }
        return PostImage::getOptimizedImageUrl($specifiedImage, $size);
    }

    if (!$post) {
        global $post;
    }
    $post = get_post($post);

    // 优先利用缓存
    $callback = function () use ($post, $size) {
        $instance = new PostImage($post, $size);
        if(ct_get_option('ct_enable_juhe_image', false) && ct_get_option('ct_enable_thumb_juhe_image', false)){
        $vm = JuheImageVM::getInstance($instance->getThumb());
        $data = $vm->modelData;
        return $data->url;
        }else{
          return $instance->getThumb();
        }
    };
    $instance = new PostImage($post, $size);

    return ct_cached($instance->cache_key, $callback, 'thumb', 60 * 60 * 24 * 7);
}

/**
 * 获取用户权限描述字符.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return string
 */
function ct_get_user_cap_string($user_id)
{
    if (user_can($user_id, 'manage_options')) {
        return __('Site Manager', 'tt');
    }
    if (user_can($user_id, 'edit_others_posts')) {
        return __('Editor', 'tt');
    }
    if (user_can($user_id, 'publish_posts')) {
        return __('Author', 'tt');
    }
    if (user_can($user_id, 'edit_posts')) {
        return __('Contributor', 'tt');
    }

    return __('Reader', 'tt');
}

/**
 * 获取用户评论头衔
 *
 * @since 2.0.0
 * @param $user_id
 * @return string
 */
function ct_get_user_comment_cap ($user_id) {
    if(user_can($user_id,'install_plugins')) {
        return '<span class="user_level user_level_admin">站长</span>';
    }
    if($user_id == 0) {
        return '<span class="user_level">游客</span>';
    }
    return '<span class="user_level user_level_user">用户</span>';
}

/**
 * 获取用户的封面.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $size
 * @param $default
 *
 * @return string
 */
function ct_get_user_cover($user_id, $size = 'full', $default = '')
{
    if (!in_array($size, array('full', 'mini'))) {
        $size = 'full';
    }
    if ($cover = get_user_meta($user_id, 'tt_user_cover', true)) {
        return $cover . $size . '.jpg';
    }

    return $default ? $default : CUTE_THEME_ASSET.'/img/user-default-cover-'.$size.'.jpg';
}

/**
 * 获取用户正在关注的人数.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_count_user_following($user_id)
{
    return ct_count_following($user_id);
}

/**
 * 获取用户的粉丝数量.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_count_user_followers($user_id)
{
    return ct_count_followers($user_id);
}

/**
 * 获取作者的文章被浏览总数.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $view_key
 *
 * @return int
 */
function ct_count_author_posts_views($user_id, $view_key = 'views')
{
    global $wpdb;
    $sql = $wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->postmeta RIGHT JOIN $wpdb->posts ON $wpdb->postmeta.meta_key='%s' AND $wpdb->posts.post_author=%d AND $wpdb->postmeta.post_id=$wpdb->posts.ID", $view_key, $user_id);
    $count = $wpdb->get_var($sql);

    return $count;
}

/**
 * 统计某个作者的文章被赞的总次数.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return null|string
 */
function ct_count_author_posts_stars($user_id)
{
    global $wpdb;
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta  WHERE meta_key='%s' AND post_id IN (SELECT ID FROM $wpdb->posts WHERE post_author=%d)", 'tt_post_star_users', $user_id);
    $count = $wpdb->get_var($sql);

    return $count;
}

/**
 * 获取用户点赞的所有文章ID.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return array
 */
function ct_get_user_star_post_ids($user_id)
{
    global $wpdb;
    $sql = $wpdb->prepare("SELECT `post_id` FROM $wpdb->postmeta  WHERE `meta_key`='%s' AND `meta_value`=%d", 'tt_post_star_users', $user_id);
    $results = $wpdb->get_results($sql);
    //ARRAY_A -> array(3) { [0]=> array(1) { [0]=> string(4) "1420" } [1]=> array(1) { [0]=> string(3) "242" } [2]=> array(1) { [0]=> string(4) "1545" } }
    //OBJECT -> array(3) { [0]=> object(stdClass)#3862 (1) { ["post_id"]=> string(4) "1420" } [1]=> object(stdClass)#3863 (1) { ["post_id"]=> string(3) "242" } [2]=> object(stdClass)#3864 (1) { ["post_id"]=> string(4) "1545" } }
    $ids = array();
    foreach ($results as $result) {
        $ids[] = intval($result->post_id);
    }
    $ids = array_unique($ids);
    rsort($ids); //从大到小排序
    return $ids;
}

/**
 * 统计用户点赞(收藏)的文章数.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_count_user_star_posts($user_id)
{
    return count(ct_get_user_star_post_ids($user_id));
}

/**
 * 获取一定数量特定角色用户.
 *
 * @since 2.0.0
 *
 * @param $role
 * @param $offset
 * @param $limit
 *
 * @return array
 */
function ct_get_users_with_role($role, $offset = 0, $limit = 20)
{
    // TODO $role 过滤
    $user_query = new WP_User_Query(
        array(
            'role' => $role,
            'orderby' => 'ID',
            'order' => 'ASC',
            'number' => $limit,
            'offset' => $offset,
        )
    );
    $users = $user_query->get_results();
    if (!empty($users)) {
        return $users;
    }

    return array();
}

/**
 * 获取管理员用户的ID.
 *
 * @since 2.0.0
 *
 * @return array
 */
function ct_get_administrator_ids()
{
    $ids = array();
    $administrators = ct_get_users_with_role('Administrator');
    foreach ($administrators as $administrator) {
        $ids[] = $administrator->ID;
    }

    return $ids;
}

/**
 * 获取用户私信对话地址
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return string
 */
function ct_get_user_chat_url($user_id)
{
    return get_author_posts_url($user_id).'/chat';
}

/**
 * 将用户的资料编辑页面链接改至前台.
 *
 * @since 2.0.0
 *
 * @param $url
 *
 * @return mixed
 */
function ct_custom_profile_edit_link($url)
{
    return is_admin() ? $url : ct_url_for('my_settings');
}
add_filter('edit_profile_url', 'ct_custom_profile_edit_link');

/**
 * 将普通用户的文章编辑链接改至前台.
 *
 * @since 2.0.0
 *
 * @param $url
 * @param $post_id
 *
 * @return string
 */
function ct_frontend_edit_post_link($url, $post_id){
    if( !current_user_can('publish_posts') ){
        $url = ct_url_for('edit_post', $post_id);
    }
    return $url;
}
add_filter('get_edit_post_link', 'ct_frontend_edit_post_link', 10, 2);

/**
 * 拒绝普通用户访问后台
 *
 * @since 2.0.0
 * @return void
 */
function ct_redirect_wp_admin(){
    if( is_admin() && is_user_logged_in() && !current_user_can('read') && ( !defined('DOING_AJAX') || !DOING_AJAX )  ){
        wp_redirect( ct_url_for('my_settings') );
        exit;
    }
}
add_action( 'init', 'ct_redirect_wp_admin' );

/**
 * 记录用户登录时间、IP等信息.
 *
 * @since 2.0.0
 *
 * @param $login
 * @param $user
 */
function ct_update_user_latest_login($login, $user)
{
    if (!$user) {
        $user = get_user_by('login', $login);
    }
    $latest_login = get_user_meta($user->ID, 'tt_latest_login', true);
    $latest_login_ip = get_user_meta($user->ID, 'tt_latest_login_ip', true);
    update_user_meta($user->ID, 'tt_latest_login_before', $latest_login);
    update_user_meta($user->ID, 'tt_latest_login', current_time('mysql'));
    update_user_meta($user->ID, 'tt_latest_ip_before', $latest_login_ip);
    update_user_meta($user->ID, 'tt_latest_login_ip', $_SERVER['REMOTE_ADDR']);
}
add_action('wp_login', 'ct_update_user_latest_login', 10, 2);

/**
 * 获取用户的真实IP.
 *
 * @since 2.0.0
 */
function ct_get_true_ip()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realIP = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $realIP = $realIP[0];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realIP = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $realIP = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realIP = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realIP = getenv('HTTP_CLIENT_IP');
        } else {
            $realIP = getenv('REMOTE_ADDR');
        }
    }
    $_SERVER['REMOTE_ADDR'] = $realIP;
}
add_action('init', 'ct_get_true_ip');

/**
 * 对封禁账户处理.
 *
 * @since   2.0.0
 */
function ct_handle_banned_user()
{
    if ($user_id = get_current_user_id()) {
        if (current_user_can('administrator')) {
            return;
        }
        $ban_status = get_user_meta($user_id, 'tt_banned', true);
        if ($ban_status) {
            wp_die(sprintf(__('Your account is banned for reason: %s', 'tt'), get_user_meta($user_id, 'tt_banned_reason', true)), __('Account Banned', 'tt'), 404); //TODO add banned time
        }
    }
}
add_action('template_redirect', 'ct_handle_banned_user');
add_action('admin_menu', 'ct_handle_banned_user');

/**
 * 获取用户账户状态
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $return
 *
 * @return array|bool
 */
function ct_get_account_status($user_id, $return = 'bool')
{
    $ban = get_user_meta($user_id, 'tt_banned', true);
    if ($ban) {
        if ($return == 'bool') {
            return true;
        }
        $reason = get_user_meta($user_id, 'tt_banned_reason', true);
        $time = get_user_meta($user_id, 'tt_banned_time', true);

        return array(
            'banned' => true,
            'banned_reason' => strval($reason),
            'banned_time' => strval($time),
        );
    }

    return $return == 'bool' ? false : array(
        'banned' => false,
    );
}

/**
 * 封禁用户.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param string $reason
 * @param string $return
 *
 * @return array|bool
 */
function ct_ban_user($user_id, $reason = '', $return = 'bool')
{
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return $return == 'bool' ? false : array(
            'success' => false,
            'message' => __('The specified user is not existed', 'tt'),
        );
    }
    if (update_user_meta($user_id, 'tt_banned', 1)) {
        update_user_meta($user_id, 'tt_banned_reason', $reason);
        update_user_meta($user_id, 'tt_banned_time', current_time('mysql'));
        // 清理Profile缓存
        // ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM');
        ct_clear_cache_by_key('ct_cache_daily_vm_UCProfileVM_author'.$user_id);

        return $return == 'bool' ? true : array(
            'success' => true,
            'message' => __('The specified user is banned', 'tt'),
        );
    }

    return $return == 'bool' ? false : array(
        'success' => false,
        'message' => __('Error occurs when banning the user', 'tt'),
    );
}

/**
 * 解禁用户.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param string $return
 *
 * @return array|bool
 */
function ct_unban_user($user_id, $return = 'bool')
{
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return $return == 'bool' ? false : array(
            'success' => false,
            'message' => __('The specified user is not existed', 'tt'),
        );
    }
    if (update_user_meta($user_id, 'tt_banned', 0)) {
        //update_user_meta($user_id, 'tt_banned_reason', '');
        //update_user_meta($user_id, 'tt_banned_time', '');
        // 清理Profile缓存
        // ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM');
        ct_clear_cache_by_key('ct_cache_daily_vm_UCProfileVM_author'.$user_id);

        return $return == 'bool' ? true : array(
            'success' => true,
            'message' => __('The specified user is unlocked', 'tt'),
        );
    }

    return $return == 'bool' ? false : array(
        'success' => false,
        'message' => __('Error occurs when unlock the user', 'tt'),
    );
}

/**
 * 输出UC小工具中登录后的内容
 *
 * @since 2.0.0
 * @return void
 */
function ct_uc_widget_content() {
    $user = wp_get_current_user();
    $vm = UCWidgetVM::getInstance($user->ID);
    $info = $vm->modelData;
    ?>
    <?php if($vm->isCache && $vm->cacheTime) { ?>
    <!-- UC Widget cached <?php echo $vm->cacheTime; ?> -->
    <?php } ?>
    <div class="user-card_content">
    <a class="user_avatar-link" href="<?php echo $info->my_settings; ?>" title="<?php echo $info->display_name; ?>" tabindex="-1"><img class="avatar" src="<?php echo $info->avatar; ?>" alt=""></a>
    <div class="user-fields"> <span class="user-name"><?php echo $info->display_name; ?></span> <span class="user_level"><?php echo $info->cap; ?></span> </div>
    <div class="user-interact"><a class="btn btn-primary btn-sigout" href="<?php echo ct_signout_url(); ?>" title="注销">注销</a><a class="btn btn-primary" href="<?php echo $info->my_settings; ?>" title="个人中心">个人中心</a></div>

    <?php
    $links = array();
    $links[] = array(
        'title' => __('My HomePage', 'tt'),
        'url' => $info->HomePage,
        'class' => 'home'
    );
    if( current_user_can( 'manage_options' ) ) {
        $links[] = array(
            'title' => __('Manage Dashboard', 'tt'),
            'url' => $info->admin_url,
            'class' => 'admin'
        );
    }
    $links[] = array(
        'title' => __('Add New Post', 'tt'),
        'url' => $info->new_post,
        'class' => 'new_post'
    );
    ?>
    <div class="active">
        <?php foreach($links as $link) { ?>
            <a class="<?php echo $link['class']; ?>" href="<?php echo $link['url']; ?>"><?php echo $link['title'] . ' &raquo;'; ?></a>
        <?php } ?>
    </div>
    <?php
    $credit = ct_get_user_credit($info->ID);
    $unread_count = ct_count_messages('chat', 0);
    $stared_count = $info->stared_count;

    $statistic_info = array(
        array(
            'title' => __('Posts', 'tt'),
            'url' => $info->uc_latest,
            'count' => $info->user_posts,
            'class' => 'posts'
        ),
        array(
            'title' => __('Comments', 'tt'),
            'url' => $info->uc_comments,
            'count' => $info->uc_comments_count,
            'class' => 'comments'
        ),
        array(
            'title' => __('Stars', 'tt'),
            'url' => $info->uc_stars,
            'count' => $stared_count,
            'class' => 'stars'
        ),
    );
    if($unread_count) {
        $statistic_info[] = array(
            'title' => __('Unread Messages', 'tt'),
            'url' => ct_url_for('in_msg'),
            'count' => $unread_count,
            'class' => 'messages'
        );
    }
    $statistic_info[] = array(
        'title' => CREDIT_NAME,
        'url' => ct_url_for('my_credits'),
        'count' => $credit,
        'class' => 'credits'
    );
    ?>
    <div class="user-stats">
        <?php
        foreach ($statistic_info as $info_item) { ?>
            <span class="<?php echo $info_item['class']; ?>" ><?php printf('<a href="%2$s">%3$s</a><span class="unit">%1$s</span>', $info_item['title'], $info_item['url'], $info_item['count']); ?></span>
        <?php } ?>

    </div>
    <div class="input-group">
            <span class="input-group-addon"><?php _e('Ref url for this page', 'tt'); ?></span>
            <input class="tin_aff_url form-control" type="text" class="form-control" value="<?php echo add_query_arg('ref', $user->ID, Utils::getPHPCurrentUrl()); ?>">
    </div>
    </div>
    <?php
}

/**
 * 站内信欢迎新注册用户并通知完善账号信息.
 *
 * @since 2.0.4
 *
 * @param $user_id
 */
function ct_welcome_for_new_registering($user_id)
{
    $blog_name = get_bloginfo('name');
    ct_create_message($user_id, 0, 'System', 'notification', sprintf( __('欢迎来到%1$s, 请首先在个人设置中完善您的账号信息, 如邮件地址是必需的', 'tt'), $blog_name ), '', 0, 'publish');
    //ct_create_pm($user_id, $blog_name, sprintf(__('欢迎来到%1$s, 请首先在个人设置中完善您的账号信息, 如邮件地址是必需的', 'tt'), $blog_name), true);
}
add_action('user_register', 'ct_welcome_for_new_registering');

/**
 * 获取用户的大部分资料信息
 * @param $user_id
 * @return array|null
 */
function ct_get_user_profile($user_id) {
    $data = get_userdata($user_id);
    if(!$data) return null;

    $user_info = array();
    $user_info['ID'] = $user_id;
    $user_info['username'] = $data->user_login;
    $user_info['display_name'] = $data->display_name;
    $user_info['nickname'] = $data->nickname;
    $user_info['email'] = $data->user_email;
    $user_info['member_since'] = mysql2date('Y/m/d', $data->user_registered);
    $user_info['member_days'] = max(1, round(( strtotime(date('Y-m-d')) - strtotime( $data->user_registered ) ) /3600/24));
    $user_info['site'] = $data->user_url;
    $user_info['description'] = $data->description;
    $user_info['bio'] = $data->description;

    $user_info['avatar'] = ct_get_avatar($data->ID, 'medium');

    $user_info['latest_login'] = mysql2date('Y/m/d g:i:s A', $data->tt_latest_login);
    $user_info['latest_login_before'] = mysql2date('Y/m/d g:i:s A', $data->tt_latest_login_before);
    $user_info['last_login_ip'] = $data->tt_latest_ip_before;
    $user_info['this_login_ip'] = $data->tt_latest_login_ip;


    $user_info['qq'] = $data->tt_qq ? 'http://wpa.qq.com/msgrd?v=3&uin=' . $data->tt_qq . '&site=qq&menu=yes' : '';
    $user_info['weibo'] = $data->tt_weibo ? 'http://weibo.com/' . $data->tt_weibo : '';
    $user_info['weixin'] = $data->tt_weixin;
    $user_info['twitter'] = $data->tt_twitter ? 'https://twitter.com/' . $data->tt_twitter : '';
    $user_info['facebook'] = $data->tt_facebook ? 'https://www.facebook.com/' . $data->tt_facebook : '';
    $user_info['googleplus'] = $data->tt_googleplus ? 'https://plus.google.com/u/0/' . $data->tt_googleplus : '';
    $user_info['alipay_pay'] = $data->tt_alipay_pay_qr;
    $user_info['wechat_pay'] = $data->tt_wechat_pay_qr;

    $user_info['referral'] = ct_get_referral_link($data->ID);
    $user_info['banned'] = $data->tt_banned;
    return $user_info;
}

/**
 * 替换摘要more字样.
 *
 * @param $more
 *
 * @return mixed
 */
function ct_excerpt_more($more)
{
    $read_more = ct_get_option('tt_read_more', ' ···');

    return $read_more;
}
add_filter('excerpt_more', 'ct_excerpt_more');

/* 关注和粉丝 */

/**
 * 获取正在关注的用户列表.
 *
 * @since 2.0.0
 *
 * @param $uid
 * @param $limit
 * @param $offset
 *
 * @return array|int|null|object
 */
function ct_get_following($uid, $limit = 20, $offset = 0)
{
    $uid = absint($uid);
    $limit = absint($limit);
    if (!$uid) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `follow_user_id`=%d AND `follow_status` IN(1,2) ORDER BY `follow_time` DESC LIMIT %d OFFSET %d", $uid, $limit, $offset));

    return $results;
}

/**
 * 获取正在关注的用户数量.
 *
 * @since 2.0.0
 *
 * @param $uid
 *
 * @return int
 */
function ct_count_following($uid)
{
    $uid = absint($uid);
    if (!$uid) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE follow_user_id=%d AND follow_status IN(1,2)", $uid));

    return $count;
}

/**
 * 获取粉丝数据.
 *
 * @since 2.0.0
 *
 * @param $uid
 * @param int $limit
 * @param int $offset
 *
 * @return array|bool|null|object
 */
function ct_get_followers($uid, $limit = 20, $offset = 0)
{
    $uid = absint($uid);
    $limit = absint($limit);
    if (!$uid) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `user_id`=%d AND `follow_status` IN(1,2) ORDER BY `follow_time` DESC LIMIT %d OFFSET %d", $uid, $limit, $offset));

    return $results;
}

/**
 * 获取粉丝数量.
 *
 * @since 2.0.0
 *
 * @param $uid
 *
 * @return int
 */
function ct_count_followers($uid)
{
    $uid = absint($uid);
    if (!$uid) {
        return false;
    }
    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `user_id`=%d AND `follow_status` IN(1,2)", $uid));

    return $count;
}

/**
 * 关注或取消关注.
 *
 * @since 2.0.0
 *
 * @param $followed_id
 * @param string $action
 * @param int    $follower_id
 *
 * @return object|bool|WP_Error|array
 */
function ct_follow_unfollow($followed_id, $action = 'follow', $follower_id = 0)
{
    date_default_timezone_set('Asia/Shanghai');
    $followed = get_user_by('ID', absint($followed_id));
    if (!$followed) {
        return new WP_Error('user_not_found', __('The user you are following not exist', 'tt'), array('status' => 403));
    }
    if (!$follower_id) {
        $follower_id = get_current_user_id();
    }
    if (!$follower_id) {
        return new WP_Error('user_not_logged_in', __('You must sign in to follow someone', 'tt'), array('status' => 403));
    }
    if ($followed_id == $follower_id) {
        return new WP_Error('invalid_follow', __('You cannot follow yourself', 'tt'), array('status' => 403));
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    if ($action == 'unfollow') {
        $check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `user_id`=%d AND `follow_user_id`=%d", $followed_id, $follower_id));
        $status = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `user_id`=%d AND `follow_user_id`=%d AND `follow_status` IN(1,2)", $follower_id, $followed_id));
        $status1 = 0;
        $status2 = $status ? 1 : 0;
        if ($check) {
            if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `follow_status`=%d WHERE `user_id`=%d AND follow_user_id=%d", $status1, $followed_id, $follower_id))) {
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET follow_status=%d WHERE user_id=%d AND follow_user_id=%d", $status2, $follower_id, $followed_id));

                return array(
                    'success' => true,
                    'message' => __('Unfollow user successfully', 'tt'),
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Unfollow user failed', 'tt'),
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => __('Unfollow user failed, you do not have followed him', 'tt'),
            );
        }
    } else {
        $check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `user_id`=%d AND `follow_user_id`=%d", $followed_id, $follower_id));
        $status = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `user_id`=%d AND `follow_user_id`=%d AND `follow_status` IN(1,2)", $follower_id, $followed_id));
        $status1 = $status ? 2 : 1;
        $status2 = $status ? 2 : 0;
        $time = current_time('mysql');
        if ($check) {
            if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `follow_status`=%d, `follow_time`='%s' WHERE `user_id`=%d AND `follow_user_id`=%d", $status1, $time, $followed_id, $follower_id))) {
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET `follow_status`=%d WHERE `user_id`=%d AND `follow_user_id`=%d", $status2, $follower_id, $followed_id));

                return array(
                    'success' => true,
                    'message' => __('Follow user successfully', 'tt'),
                    'followEach' => (bool) $status,
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Follow user failed', 'tt'),
                );
            }
        } else {
            if ($wpdb->query($wpdb->prepare("INSERT INTO $table_name (user_id, follow_user_id, follow_status, follow_time) VALUES (%d, %d, %d, %s)", $followed_id, $follower_id, $status1, $time))) {
                $wpdb->query($wpdb->prepare("UPDATE $table_name SET `follow_status`=%d WHERE `user_id`=%d AND `follow_user_id`=%d", $status2, $follower_id, $followed_id));

                return array(
                    'success' => true,
                    'message' => __('Follow user successfully', 'tt'),
                    'followEach' => (bool) $status,
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Follow user failed', 'tt'),
                );
            }
        }
    }
}

/**
 * 关注用户.
 *
 * @since 2.0.0
 *
 * @param $uid
 *
 * @return WP_Error|array
 */
function ct_follow($uid)
{
    return ct_follow_unfollow($uid);
}

/**
 * 取消关注.
 *
 * @since 2.0.0
 *
 * @param $uid
 *
 * @return WP_Error|array
 */
function ct_unfollow($uid)
{
    return ct_follow_unfollow($uid, 'unfollow');
}

/**
 * 根据关注状态输出对应的关注按钮.
 *
 * @param $uid
 *
 * @return string
 */
function ct_follow_button($uid)
{
    $uid = absint($uid);
    if (!$uid) {
        return '';
    }

    $current_uid = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix.'tt_follow';
    $check = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE `user_id`=%d AND `follow_user_id`=%d AND `follow_status` IN(1,2)", $uid, $current_uid));
    if ($check) {
        if ($check->follow_status == 2) { // 互相关注
            $button = '<a class="follow-btn followed" href="javascript: void 0" title="'.__('Unfollow', 'tt').'" data-uid="'.$uid.'" data-act="unfollow"><i class="tico tico-exchange"></i><span>'.__('FOLLOWED EACH', 'tt').'</span></a>';
        } else {
            $button = '<a class="follow-btn followed" href="javascript: void 0" title="'.__('Unfollow', 'tt').'" data-uid="'.$uid.'" data-act="unfollow"><i class="tico tico-user-check"></i><span>'.__('FOLLOWED', 'tt').'</span></a>';
        }
    } else {
        $button = '<a class="follow-btn unfollowed" href="javascript: void 0" title="'.__('Follow the user', 'tt').'" data-uid="'.$uid.'" data-act="follow"><i class="tico tico-user-plus"></i><span>'.__('FOLLOW', 'tt').'</span></a>';
    }

    return $button;
}

/**
 * 创建消息.
 *
 *
 * @since 2.0.0
 *
 * @param int    $user_id   接收用户ID
 * @param int    $sender_id 发送者ID(可空)
 * @param string $sender    发送者
 * @param string $type      消息类型(notification/chat/credit)
 * @param string $title     消息标题
 * @param string $content   消息内容
 * @param int    $read      (已读/未读)
 * @param string $status    消息状态(publish/trash)
 * @param string $date      消息时间
 *
 * @return bool
 */
function ct_create_message($user_id = 0, $sender_id = 0, $sender, $type = '', $title = '', $content = '', $read = MsgReadStatus::UNREAD, $status = 'publish', $date = '')
{
    $user_id = absint($user_id);
    $sender_id = absint($sender_id);
    $title = sanitize_text_field($title);

    if (!$user_id || empty($title)) {
        return false;
    }

    $type = $type ? sanitize_text_field($type) : 'chat';
    $date = $date ? $date : current_time('mysql');
    $content = htmlspecialchars($content);

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    if ($wpdb->query($wpdb->prepare("INSERT INTO $table_name (user_id, sender_id, sender, msg_type, msg_title, msg_content, msg_read, msg_status, msg_date) VALUES (%d, %d, %s, %s, %s, %s, %d, %s, %s)", $user_id, $sender_id, $sender, $type, $title, $content, $read, $status, $date))) {
        return true;
    }

    return false;
}

/**
 * 创建一条私信
 *
 * @param $receiver_id
 * @param $sender
 * @param $text
 * @param $send_mail
 *
 * @return bool
 */
function ct_create_pm($receiver_id, $sender, $text, $send_mail = false)
{
    // 清理未读消息统计数的缓存
    if (wp_using_ext_object_cache()) {
        $key = 'tt_user_'.$receiver_id.'_unread';
        wp_cache_delete($key);
    }

    if ($sender instanceof WP_User && $sender->ID) {
        if ($send_mail && $sender->user_email) {
            $subject = sprintf(__('%1$s向你发送了一条消息 - %2$s', 'tt'), $sender->display_name, get_bloginfo('name'));
            $args = array(
                'senderName' => $sender->display_name,
                'message' => $text,
                'chatLink' => ct_url_for('uc_chat', $sender),
            );
            //cute_async_mail('', get_user_by('id', $receiver_id)->user_email, $subject, $args, 'pm');
            cute_mail('', get_user_by('id', $receiver_id)->user_email, $subject, $args, 'pm');
        }

        return ct_create_message($receiver_id, $sender->ID, $sender->display_name, 'chat', $text);
    } elseif (is_int($sender)) {
        $sender = get_user_by('ID', $sender);
        if ($send_mail && $sender->user_email) {
            $subject = sprintf(__('%1$s向你发送了一条消息 - %2$s', 'tt'), $sender->display_name, get_bloginfo('name'));
            $args = array(
                'senderName' => $sender->display_name,
                'message' => $text,
                'chatLink' => ct_url_for('uc_chat', $sender),
            );
            //cute_async_mail('', get_user_by('id', $receiver_id)->user_email, $subject, $args, 'pm');
            cute_mail('', get_user_by('id', $receiver_id)->user_email, $subject, $args, 'pm');
        }

        return ct_create_message($receiver_id, $sender->ID, $sender->display_name, 'chat', $text);
    }

    return false;
}

/**
 * 标记消息阅读状态
 *
 * @since 2.0.0
 *
 * @param $id
 * @param int $read
 *
 * @return bool
 */
function ct_mark_message($id, $read = MsgReadStatus::READ)
{
    $id = absint($id);
    $user_id = get_current_user_id(); //确保只能标记自己的消息

    if ((!$id || !$user_id)) {
        return false;
    }

    $read = $read == MsgReadStatus::UNREAD ?: MsgReadStatus::READ;

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `msg_read` = %d WHERE `msg_id` = %d AND `user_id` = %d", $read, $id, $user_id))) {
        // 清理未读消息统计数的缓存
        if (wp_using_ext_object_cache()) {
            $key = 'tt_user_'.$user_id.'_unread';
            wp_cache_delete($key);
        }

        return true;
    }

    return false;
}

/**
 * 标记所有未读消息已读.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function ct_mark_all_message_read($sender_id) {
    $user_id = get_current_user_id();
    if(!$user_id) return false;

    global $wpdb;
    $table_name = $wpdb->prefix . 'tt_messages';

    if($wpdb->query( $wpdb->prepare("UPDATE $table_name SET `msg_read` = 1 WHERE `user_id` = %d AND `msg_read` = 0 AND `sender_id` = %d", $user_id, $sender_id) )) {
        // 清理未读消息统计数的缓存
        if(wp_using_ext_object_cache()) {
            $key = 'tt_user_' . $user_id . '_unread';
            wp_cache_delete($key);
        }
        return true;
    }
    return false;
}

/**
 * 获取单条消息.
 *
 * @since 2.0.0
 *
 * @param $msg_id
 *
 * @return bool|object
 */
function ct_get_message($msg_id)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    } // 用于防止获取其他用户的消息

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    $row = $wpdb->get_row(sprintf("SELECT * FROM $table_name WHERE `msg_id`=%d AND `user_id`=%d OR `sender_id`=%d", $msg_id, $user_id, $user_id));
    if ($row) {
        return $row;
    }

    return false;
}

/**
 * 查询消息.
 *
 * @since 2.0.0
 *
 * @param string $type       (notification/chat/credit)
 * @param int    $limit
 * @param int    $offset
 * @param int    $read
 * @param string $msg_status
 * @param int    $sender_id
 * @param bool   $count
 *
 * @return array|bool|null|object|int
 */
function ct_get_messages($type = 'chat', $limit = 20, $offset = 0, $read = MsgReadStatus::UNREAD, $msg_status = 'publish', $sender_id = 0, $count = false)
{
    $user_id = get_current_user_id();

    if (!$user_id) {
        return false;
    }

    if (is_array($type)) {
        $type = implode("','", $type); //NOTE  IN('comment','star','update','notification') IN表达式的引号
    }
    if (!in_array($read, array(MsgReadStatus::READ, MsgReadStatus::UNREAD, MsgReadStatus::ALL))) {
        $read = MsgReadStatus::UNREAD;
    }
    if (!in_array($msg_status, array('publish', 'trash', 'all'))) {
        $msg_status = 'publish';
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    $sql = sprintf("SELECT %s FROM $table_name WHERE `user_id`=%d%s AND `msg_type` IN('$type')%s%s ORDER BY (CASE WHEN `msg_read`='all' THEN 1 ELSE 0 END) DESC, `msg_id` DESC%s", $count ? 'COUNT(*)' : '*', $user_id, $sender_id ? " AND `sender_id`=$sender_id" : '', $read != MsgReadStatus::ALL ? " AND `msg_read`=$read" : '', $msg_status != 'all' ? " AND `msg_status`='$msg_status'" : '', $count ? '' : " LIMIT $offset, $limit");

    $results = $count ? $wpdb->get_var($sql) : $wpdb->get_results($sql);
    if ($results) {
        return $results;
    }

    return 0;
}

/**
 * 指定类型消息计数.
 *
 * @since 2.0.0
 *
 * @param string $type
 * @param int    $read
 * @param string $msg_status
 * @param int    $sender_id
 *
 * @return array|bool|int|null|object
 */
function ct_count_messages($type = 'chat', $read = MsgReadStatus::UNREAD, $msg_status = 'publish', $sender_id = 0)
{
    return ct_get_messages($type, 0, 0, $read, $msg_status, $sender_id, true);
}

/**
 * 获取未读消息.
 *
 * @since 2.0.0
 *
 * @param string $type
 * @param int    $limit
 * @param int    $offset
 * @param string $msg_status
 *
 * @return array|bool|int|null|object
 */
function ct_get_unread_messages($type = 'chat', $limit = 20, $offset = 0, $msg_status = 'publish')
{
    return ct_get_messages($type, $limit, $offset, MsgReadStatus::UNREAD, $msg_status);
}

/**
 * 未读消息计数.
 *
 * @since 2.0.0
 *
 * @param string $type
 * @param string $msg_status
 *
 * @return array|bool|int|null|object
 */
function ct_count_unread_messages($type = 'chat', $msg_status = 'publish')
{
    return ct_count_messages($type, MsgReadStatus::UNREAD, $msg_status);
}

/**
 * 获取积分消息.
 *
 *
 * @since 2.0.0
 *
 * @param int    $limit
 * @param int    $offset
 * @param string $msg_status
 *
 * @return array|bool|int|null|object
 */
function ct_get_credit_messages($limit = 20, $offset = 0, $msg_status = 'all')
{ //TODO: 积分消息不应该有msg_status，不可删除
    $messages = ct_get_messages('credit', $limit, $offset, MsgReadStatus::ALL, $msg_status); //NOTE: 积分消息不分已读未读
    return $messages ? $messages : array();
}

/**
 * 获取现金余额变动消息.
 *
 *
 * @since 2.2.0
 *
 * @param int    $limit
 * @param int    $offset
 * @param string $msg_status
 *
 * @return array|bool|int|null|object
 */
function ct_get_cash_messages($limit = 20, $offset = 0, $msg_status = 'all')
{ //TODO: 余额消息不应该有msg_status，不可删除
    $messages = ct_get_messages('cash', $limit, $offset, MsgReadStatus::ALL, $msg_status); //NOTE: 余额消息不分已读未读
    return $messages ? $messages : array();
}

/**
 * 积分消息计数.
 *
 * @since 2.0.0
 *
 * @return array|bool|int|null|object
 */
function ct_count_credit_messages()
{
    return ct_count_messages('credit', MsgReadStatus::ALL, 'all');
}

/**
 * 现金余额相关消息计数.
 *
 * @since 2.2.0
 *
 * @return array|bool|int|null|object
 */
function ct_count_cash_messages()
{
    return ct_count_messages('cash', MsgReadStatus::ALL, 'all');
}

/**
 * 获取收到的对话消息.
 *
 * @since 2.0.0
 *
 * @param $sender_id
 * @param int $limit
 * @param int $offset
 * @param int $read
 *
 * @return array|bool|int|null|object
 */
function ct_get_pm($sender_id = 0, $limit = 20, $offset = 0, $read = MsgReadStatus::UNREAD)
{
    return ct_get_messages('chat', $limit, $offset, $read, 'publish', $sender_id);
}

/**
 * 获取来自指定发送者的聊天消息数量(sender_id为0时不指定发送者).
 *
 * @param int $sender_id
 * @param int $read
 *
 * @return int
 */
function ct_count_pm($sender_id = 0, $read = MsgReadStatus::UNREAD)
{
    return ct_count_messages('chat', $read, 'publish', $sender_id);
}

function ct_count_pm_cached($user_id = 0, $sender_id = 0, $read = MsgReadStatus::UNREAD)
{
    if (wp_using_ext_object_cache()) {
        $user_id = $user_id ?: get_current_user_id();
        $key = 'tt_user_'.$user_id.'_unread';
        $cache = wp_cache_get($key);
        if ($cache !== false) {
            return (int) $cache;
        }
        $unread = ct_count_pm($sender_id, $read);
        wp_cache_add($key, $unread, '', 3600);

        return $unread;
    }

    return ct_count_pm($sender_id, $read);
}

/**
 * 获取我发送的消息($to_user为0时不指定收件人).
 *
 * @since 2.0.0
 *
 * @param int        $to_user
 * @param int        $limit
 * @param int        $offset
 * @param int|string $read
 * @param string     $msg_status
 * @param bool       $count
 *
 * @return array|bool|int|null|object|string
 */
function ct_get_sent_pm($to_user = 0, $limit = 20, $offset = 0, $read = MsgReadStatus::ALL, $msg_status = 'publish', $count = false)
{
    $sender_id = get_current_user_id();

    if (!$sender_id) {
        return false;
    }

    $type = 'chat';
    if (!in_array($read, array(MsgReadStatus::UNREAD, MsgReadStatus::READ, MsgReadStatus::UNREAD))) {
        $read = MsgReadStatus::ALL;
    }
    if (!in_array($msg_status, array('publish', 'trash', 'all'))) {
        $msg_status = 'publish';
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    $sql = sprintf("SELECT %s FROM $table_name WHERE `sender_id`=%d%s AND `msg_type` IN('$type')%s%s ORDER BY (CASE WHEN `msg_read`='all' THEN 1 ELSE 0 END) DESC, `msg_date` DESC%s", $count ? 'COUNT(*)' : '*', $sender_id, $to_user ? " AND `user_id`=$to_user" : '', $read != MsgReadStatus::ALL ? " AND `msg_read`='$read'" : '', $msg_status != 'all' ? " AND `msg_status`='$msg_status'" : '', $count ? '' : " LIMIT $offset, $limit");

    $results = $count ? $wpdb->get_var($sql) : $wpdb->get_results($sql);
    if ($results) {
        return $results;
    }

    return 0;
}

/**
 * 获取我发送的消息数量.
 *
 * @since 2.0.0
 *
 * @param int $to_user
 * @param int $read
 *
 * @return int
 */
function ct_count_sent_pm($to_user = 0, $read = MsgReadStatus::ALL)
{
    return ct_get_sent_pm($to_user, 0, 0, $read, 'publish', true);
}

/**
 * 删除消息.
 *
 * @since 2.0.0
 *
 * @param $msg_id
 *
 * @return bool
 */
function ct_trash_message($msg_id)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `msg_status` = 'trash' WHERE `msg_id` = %d AND `user_id` = %d", $msg_id, $user_id)) || $wpdb->query($wpdb->prepare("UPDATE $table_name SET `msg_status` = 'trash' WHERE `msg_id` = %d AND `sender_id` = %d", $msg_id, $user_id))) { //TODO optimize
        return true;
    }

    return false;
}

/**
 * 恢复消息.
 *
 * @since 2.0.0
 *
 * @param $msg_id
 *
 * @return bool
 */
function ct_restore_message($msg_id)
{ //NOTE: 应该不用
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';

    if ($wpdb->query($wpdb->prepare("UPDATE $table_name SET `msg_status` = 'publish' WHERE `msg_id` = %d AND `user_id` = %d", $msg_id, $user_id))) {
        return true;
    }

    return false;
}

/**
 * 获取对话(双向消息).
 *
 * @since 2.0.0
 *
 * @param $one_uid
 * @param int    $limit
 * @param int    $offset
 * @param int    $read
 * @param string $msg_status
 * @param bool   $count
 *
 * @return array|bool|int|null|object|string
 */
function ct_get_bothway_chat($one_uid, $limit = 20, $offset = 0, $read = MsgReadStatus::UNREAD, $msg_status = 'publish', $count = false)
{
    $user_id = get_current_user_id();

    if (!$user_id) {
        return false;
    }

    if (!in_array($read, array(MsgReadStatus::UNREAD, MsgReadStatus::READ, MsgReadStatus::ALL))) {
        $read = MsgReadStatus::UNREAD;
    }
    if (!in_array($msg_status, array('publish', 'trash', 'all'))) {
        $msg_status = 'publish';
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'tt_messages';
    $concat_id_str = '\''.$one_uid.'_'.$user_id.'\','.'\''.$user_id.'_'.$one_uid.'\'';

    $sql = sprintf("SELECT %s FROM $table_name WHERE CONCAT_WS('_', `user_id`, `sender_id`) IN (%s) AND `msg_type`='chat'%s%s ORDER BY (CASE WHEN `msg_read`='all' THEN 1 ELSE 0 END) DESC, `msg_date` DESC%s", $count ? 'COUNT(*)' : '*', $concat_id_str, $read != MsgReadStatus::ALL ? " AND `msg_read`='$read'" : '', $msg_status != 'all' ? " AND `msg_status`='$msg_status'" : '', $count ? '' : " LIMIT $offset, $limit");
    $results = $count ? $wpdb->get_var($sql) : $wpdb->get_results($sql);

    if ($results) {
        return $results;
    }

    return 0;
}

/**
 * 捕获链接中的推广者.
 *
 * @since 2.0.0
 */
function ct_retrieve_referral_keyword()
{
    if (isset($_REQUEST['ref'])) {
        $ref = absint($_REQUEST['ref']);
        do_action('tt_ref', $ref);
    }
}
//add_action('template_redirect', 'ct_retrieve_referral_keyword');

function ct_handle_ref($ref)
{
    //TODO
}
//add_action('tt_ref', 'ct_handle_ref', 10, 1);

/**
 * 在主查询生成前过滤参数(因为使用了原生paged分页参数, 导致作者页文章以外其他tab的分页不能超过文章分页数量, 否则404).
 *
 * @since 2.0.0
 *
 * @param WP_Query $q
 */
function ct_reset_uc_pre_get_posts( $q ) { //TODO 分页不存在时返回404
    if($uctab = get_query_var('uctab') && $q->is_main_query()) {
        if(in_array($uctab, array('comments', 'stars', 'followers', 'following', 'chat'))) {
            $q->set( 'posts_per_page', 1 );
            $q->set( 'offset', 0 ); //此时paged参数不起作用
        }
    }elseif($manage = get_query_var('manage_child_route') && $q->is_main_query()){
        if(in_array($manage, array('orders', 'users', 'members', 'coupons', 'cards'))) {
            $q->set( 'posts_per_page', 1 );
            $q->set( 'offset', 0 ); //此时paged参数不起作用
        }
    }elseif($me = get_query_var('me_child_route') && $q->is_main_query()){
        if(in_array($me, array('orders', 'users', 'credits', 'messages', 'following', 'followers'))) {
            $q->set( 'posts_per_page', 1 );
            $q->set( 'offset', 0 ); //此时paged参数不起作用
        }
    }
}
add_action( 'pre_get_posts', 'ct_reset_uc_pre_get_posts' );

/**
 * 获取用户的积分.
 *
 * @since 2.0.0
 *
 * @param int $user_id
 *
 * @return int
 */
function ct_get_user_credit($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();

    return (int) get_user_meta($user_id, 'tt_credits', true);
}

/**
 * 获取用户已经消费的积分.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_get_user_consumed_credit($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();

    return (int) get_user_meta($user_id, 'tt_consumed_credits', true);
}

/**
 * 更新用户积分(添加积分或消费积分).
 *
 * @since 2.0.0
 *
 * @param int    $user_id
 * @param int    $amount
 * @param string $msg
 * @param bool   $admin_handle
 *
 * @return bool
 */
function ct_update_user_credit($user_id = 0, $amount = 0, $msg = '', $admin_handle = false)
{
    $user_id = $user_id ?: get_current_user_id();
    $before_credits = (int) get_user_meta($user_id, 'tt_credits', true);
    // 管理员直接更改用户积分
    if ($admin_handle) {
        $update = update_user_meta($user_id, 'tt_credits', (int) $amount + $before_credits);
        if ($update) {
            if ($amount > 0){
            // 添加积分消息
            $msg = $msg ?: sprintf(__('系统管理员给你增加 %2$d %1$s, 当前%1$s %3$d', 'tt'), CREDIT_NAME,max(0, (int) $amount), max(0, (int) $amount) + $before_credits);
            }else{
            $msg = $msg ?: sprintf(__('系统管理员给你减少 %2$d %1$s, 当前%1$s %3$d', 'tt'), CREDIT_NAME,abs((int) $amount), (int) $amount + $before_credits);
            }
            ct_create_message($user_id, 0, 'System', 'credit', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }

        return (bool) $update;
    }
    // 普通更新
    if ($amount > 0) {
        $update = update_user_meta($user_id, 'tt_credits', $before_credits + $amount); //Meta ID if the key didn't exist; true on successful update; false on failure or if $meta_value is the same as the existing meta value in the database.
        if ($update) {
            // 添加积分消息
            $msg = $msg ?: sprintf(__('获得 %d %s', 'tt'), $amount,CREDIT_NAME);
            ct_create_message($user_id, 0, 'System', 'credit', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }
    } elseif ($amount < 0) {
        if ($before_credits + $amount < 0) {
            return false;
        }
        $before_consumed = (int) get_user_meta($user_id, 'tt_consumed_credits', true);
        update_user_meta($user_id, 'tt_consumed_credits', $before_consumed - $amount);
        $update = update_user_meta($user_id, 'tt_credits', $before_credits + $amount);
        if ($update) {
            // 添加积分消息
            $msg = $msg ?: sprintf(__('花费 %2$d %1$s, 当前%1$s %3$d', 'tt'), CREDIT_NAME,absint($amount), (int) $amount + $before_credits);
            ct_create_message($user_id, 0, 'System', 'credit', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }
    }
    delete_transient('ct_cache_daily_vm_MeCreditRecordsVM_user' . $user_id);
    return true;
}

/**
 * 积分充值到账.
 *
 * @since 2.0.0
 *
 * @param $order_id
 */
function ct_add_credits_by_order($order_id)
{
    $order = ct_get_order($order_id);
    if (!$order || $order->order_status != OrderStatus::TRADE_SUCCESS) {
        return;
    }

    $user = get_user_by('id', $order->user_id);
  //$credit_price = abs(ct_get_option('tt_hundred_credit_price', 100));
  //$buy_credits = intval($order->order_total_price * 100 / $credit_price);
    $buy_credits = $order->order_quantity;
    $ct_deposit_credit_count = ct_get_option('ct_deposit_credit_count',3);
    for ($i=1; $i <= $ct_deposit_credit_count; $i++) {
      if(!ct_get_option('ct_deposit_credit',false)) break;
      $ct_deposit_credit = ct_get_option('ct_deposit_credit_pay_'.$i,1);
      if($order->order_total_price == $ct_deposit_credit){
      $buy_credits = ct_get_option('ct_deposit_credit_'.$i,10);
      break;
      }
    }
    ct_update_user_credit($order->user_id, $buy_credits, sprintf(__('充值 <strong>%d</strong> %s, 花费 %0.2f 元', 'tt'), $buy_credits,CREDIT_NAME, $order->order_total_price));

    // 发送邮件
    $blog_name = get_bloginfo('name');
    $subject = sprintf(__('充值 %s 完成 - %s', 'tt'), CREDIT_NAME,$blog_name);
    $args = array(
        'blogName' => $blog_name,
        'creditsNum' => $buy_credits,
        'currentCredits' => ct_get_user_credit($user->ID),
        'adminEmail' => get_option('admin_email'),
    );
    // cute_async_mail('', $user->user_email, $subject, $args, 'charge-credits-success');
    cute_mail('', $user->user_email, $subject, $args, 'charge-credits-success');
}

/**
 * 使用积分支付.
 *
 * @since 2.0.0
 *
 * @param int    $amount
 * @param string $product_subject
 * @param bool   $rest
 *
 * @return bool|WP_Error
 */
function ct_credit_pay($amount = 0, $product_subject = '', $rest = false)
{
    $amount = absint($amount);
    $user_id = get_current_user_id();
    if (!$user_id) {
        return $rest ? new WP_Error('unknown_user', __('You must sign in before payment', 'tt'), array('status' => 403)) : false;
    }

    $credits = (int) get_user_meta($user_id, 'tt_credits', true);
    if ($credits < $amount) {
        return $rest ? new WP_Error('insufficient_credits', sprintf(__('%s不足，请先充值再购买哦！', 'tt'), CREDIT_NAME), array('status' => 403)) : false;
    }

//    $new_credits = $credits - $amount;
//    $update = update_user_meta($user_id, 'tt_credits', $new_credits);
//    if($update) {
//        $consumed = (int)get_user_meta($user_id, 'tt_consumed_credits', true);
//        update_user_meta($user_id, 'tt_consumed_credits', $consumed + $amount);
//        // 添加积分消息
//        $msg = sprintf(__('Spend %d credits', 'tt') , absint($amount));
//        ct_create_message( $user_id, 0, 'System', 'credit', $msg, '', 0, 'publish');
//    }

    $msg = $product_subject ? sprintf(__('花费 %2$d %1$s购买了 %3$s,当前%1$s %4$d', 'tt'), CREDIT_NAME,$amount, $product_subject, $credits - $amount) : '';
    ct_update_user_credit($user_id, $amount * (-1), $msg); //TODO confirm update
    return true;
}

/**
 * 用户注册时添加推广人和奖励积分.
 *
 * @since 2.0.0
 *
 * @param $user_id
 */
function ct_update_credit_by_user_register($user_id)
{
    if (isset($_COOKIE['tt_ref']) && is_numeric($_COOKIE['tt_ref'])) {
        $ref_from = absint($_COOKIE['tt_ref']);
        //链接推广人与新注册用户(推广人meta)
        if (get_user_meta($ref_from, 'tt_ref_users', true)) {
            $ref_users = get_user_meta($ref_from, 'tt_ref_users', true);
            if (empty($ref_users)) {
                $ref_users = $user_id;
            } else {
                $ref_users .= ','.$user_id;
            }
            update_user_meta($ref_from, 'tt_ref_users', $ref_users);
        } else {
            update_user_meta($ref_from, 'tt_ref_users', $user_id);
        }
        //链接推广人与新注册用户(注册人meta)
        update_user_meta($user_id, 'tt_ref', $ref_from);
        $rec_reg_num = (int) ct_get_option('tt_rec_reg_num', '5');
        $rec_reg = json_decode(get_user_meta($ref_from, 'tt_rec_reg', true));
        $ua = $_SERVER['REMOTE_ADDR'].'&'.$_SERVER['HTTP_USER_AGENT'];
        if (!$rec_reg) {
            $rec_reg = array();
            $new_rec_reg = array($ua);
        } else {
            $new_rec_reg = $rec_reg;
            array_push($new_rec_reg, $ua);
        }
        if ((count($rec_reg) < $rec_reg_num) && !in_array($ua, $rec_reg)) {
            update_user_meta($ref_from, 'tt_rec_reg', json_encode($new_rec_reg));

            $reg_credit = (int) ct_get_option('tt_rec_reg_credit', '30');
            if ($reg_credit) {
                ct_update_user_credit($ref_from, $reg_credit, sprintf(__('获得注册推广（来自%1$s的注册）奖励%2$s%3$s', 'tt'), get_the_author_meta('display_name', $user_id), $reg_credit,CREDIT_NAME));
            }
        }
    }
    $credit = ct_get_option('tt_reg_credit', 50);
    if ($credit) {
        ct_update_user_credit($user_id, $credit, sprintf(__('获得注册奖励%s%s', 'tt'), $credit,CREDIT_NAME));
    }
    $member = ct_get_option('tt_reg_member', 0);
    $blog_name = get_bloginfo('name');
    if ($member) {
        ct_add_or_update_member( $user_id, $member);
        ct_create_message($user_id, 0, 'System', 'notification', sprintf( __('欢迎注册%1$s, 恭喜您获得注册送会员资格，会员已充值到账，请查收！', 'tt'), $blog_name ), '', 0, 'publish');
    }
}
add_action('user_register', 'ct_update_credit_by_user_register');

/**
 * 推广访问奖励积分.
 *
 * @since 2.0.0
 */
function ct_update_credit_by_referral_view()
{
    if (isset($_COOKIE['tt_ref']) && is_numeric($_COOKIE['tt_ref'])) {
        $ref_from = absint($_COOKIE['tt_ref']);
        $rec_view_num = (int) ct_get_option('tt_rec_view_num', '50');
        $rec_view = json_decode(get_user_meta($ref_from, 'tt_rec_view', true));
        $ua = $_SERVER['REMOTE_ADDR'].'&'.$_SERVER['HTTP_USER_AGENT'];
        if (!$rec_view) {
            $rec_view = array();
            $new_rec_view = array($ua);
        } else {
            $new_rec_view = $rec_view;
            array_push($new_rec_view, $ua);
        }
        //推广人推广访问数量，不受每日有效获得积分推广次数限制，但限制同IP且同终端刷分
        if (!in_array($ua, $rec_view)) {
            $ref_views = (int) get_user_meta($ref_from, 'tt_aff_views', true);
            ++$ref_views;
            update_user_meta($ref_from, 'tt_aff_views', $ref_views);
        }
        //推广奖励，受每日有效获得积分推广次数限制及同IP终端限制刷分
        if ((count($rec_view) < $rec_view_num) && !in_array($ua, $rec_view)) {
            update_user_meta($ref_from, 'tt_rec_view', json_encode($new_rec_view));
            $view_credit = (int) ct_get_option('tt_rec_view_credit', '5');
            if ($view_credit) {
                ct_update_user_credit($ref_from, $view_credit, sprintf(__('获得访问推广奖励%d%s', 'tt'), $view_credit,CREDIT_NAME));
            }
        }
    }
}
add_action('tt_ref', 'ct_update_credit_by_referral_view');

/**
 * 发表评论时给作者添加积分.
 *
 * @since 2.0.0
 *
 * @param $comment_id
 * @param $comment_object
 */
function ct_comment_add_credit($comment_id, $comment_object)
{
    $user_id = $comment_object->user_id;
    if ($user_id) {
        $rec_comment_num = (int) ct_get_option('tt_rec_comment_num', 10);
        $rec_comment_credit = (int) ct_get_option('tt_rec_comment_credit', 5);
        $rec_comment = (int) get_user_meta($user_id, 'tt_rec_comment', true);

        if ($rec_comment < $rec_comment_num && $rec_comment_credit) {
            ct_update_user_credit($user_id, $rec_comment_credit, sprintf(__('获得评论回复奖励%d%s', 'tt'), $rec_comment_credit,CREDIT_NAME));
            update_user_meta($user_id, 'tt_rec_comment', $rec_comment + 1);
        }
    }
}
add_action('wp_insert_comment', 'ct_comment_add_credit', 99, 2);

/**
 * 每天 00:00 清空推广数据.
 *
 * @since 2.0.0
 */
function ct_clear_rec_setup_schedule()
{
    if (!wp_next_scheduled('ct_clear_rec_daily_event')) {
        wp_schedule_event('1193875200', 'daily', 'ct_clear_rec_daily_event');
    }
}
add_action('wp', 'ct_clear_rec_setup_schedule');

function ct_do_clear_rec_daily()
{
    global $wpdb;
    $wpdb->query(" DELETE FROM $wpdb->usermeta WHERE meta_key='tt_rec_view' OR meta_key='tt_rec_reg' OR meta_key='tt_rec_post' OR meta_key='tt_rec_comment' OR meta_key='tt_resource_dl_users' "); // TODO tt_resource_dl_users
}
add_action('ct_clear_rec_daily_event', 'ct_do_clear_rec_daily');

/**
 * 在后台用户列表中显示积分.
 *
 * @since 2.0.0
 *
 * @param $columns
 *
 * @return mixed
 */
function ct_credit_column($columns)
{
    $columns['tt_credit'] = CREDIT_NAME;

    return $columns;
}
add_filter('manage_users_columns', 'ct_credit_column');

function ct_credit_column_callback($value, $column_name, $user_id)
{
    if ('tt_credit' == $column_name) {
        $credit = intval(get_user_meta($user_id, 'tt_credits', true));
        $void = intval(get_user_meta($user_id, 'tt_consumed_credits', true));
        $value = sprintf(__('总%4$s %1$d, 已消费 %2$d %4$s, 剩余 %3$d %4$s', 'tt'), $credit + $void, $void, $credit,CREDIT_NAME);
    }

    return $value;
}
add_action('manage_users_custom_column', 'ct_credit_column_callback', 10, 3);

/**
 * 按积分排序获取用户排行.
 *
 * @since 2.0.0
 *
 * @param int $limits
 * @param int $offset
 *
 * @return array|null|object
 */
function ct_credits_rank($limits = 10, $offset = 0)
{
    global $wpdb;
    $limits = (int) $limits;
    $offset = absint($offset);
    $ranks = $wpdb->get_results(" SELECT DISTINCT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key='tt_credits' ORDER BY -meta_value ASC LIMIT $limits OFFSET $offset");

    return $ranks;
}

/**
 * 创建积分充值订单.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param int $amount // 积分数量为100*$amount
 *
 * @return array|bool
 */
function ct_create_credit_charge_order($user_id, $amount = 1)
{
    $amount = abs($amount);
    if (!$amount) {
        return false;
    }
    $order_id = ct_generate_order_num();
    $order_time = current_time('mysql');
    $product_id = Product::CREDIT_CHARGE;
    $product_name = Product::CREDIT_CHARGE_NAME;
    $currency = 'cash';
    $hundred_credits_price = ct_get_option('tt_hundred_credit_price', 100);
    $order_price = sprintf('%0.2f', 1 / $hundred_credits_price);
    $order_quantity = $amount * $hundred_credits_price;
    $order_total_price = sprintf('%0.2f', $amount);

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $insert = $wpdb->insert(
        $orders_table,
        array(
            'parent_id' => 0,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'order_time' => $order_time,
            'order_price' => $order_price,
            'order_currency' => $currency,
            'order_quantity' => $order_quantity,
            'order_total_price' => $order_total_price,
            'user_id' => $user_id,
        ),
        array('%d', '%s', '%d', '%s', '%s', '%f', '%s', '%d', '%f', '%d')
    );
    if ($insert) {
        return array(
            'insert_id' => $wpdb->insert_id,
            'order_id' => $order_id,
            'total_price' => $order_total_price,
        );
    }

    return false;
}

/**
 * 创建余额充值订单.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param int $amount // 积分数量为100*$amount
 *
 * @return array|bool
 */
function ct_create_cash_charge_order($user_id, $amount = 1)
{
    $amount = abs($amount);
    if (!$amount || !$user_id) {
        return false;
    }
    $order_id = ct_generate_order_num();
    $order_time = current_time('mysql');
    $product_id = Product::CASH_CHARGE;
    $product_name = Product::CASH_CHARGE_NAME;
    $currency = 'cash';
    $order_quantity = $amount * 100;
    $order_total_price = $amount;

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $insert = $wpdb->insert(
        $orders_table,
        array(
            'parent_id' => 0,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'order_time' => $order_time,
            'order_price' => 1,
            'order_currency' => $currency,
            'order_quantity' => $order_quantity,
            'order_total_price' => $order_total_price,
            'user_id' => $user_id,
        ),
        array('%d', '%s', '%d', '%s', '%s', '%f', '%s', '%d', '%f', '%d')
    );
    if ($insert) {
        return array(
            'insert_id' => $wpdb->insert_id,
            'order_id' => $order_id,
            'total_price' => $order_total_price,
        );
    }

    return false;
}


/**
 * 获取每日签到按钮HTML.
 *
 * @since 2.0.0
 *
 * @param int $user_id
 *
 * @return string
 */
function ct_daily_sign_anchor($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    if (get_user_meta($user_id, 'tt_daily_sign', true)) {
        date_default_timezone_set('Asia/Shanghai');
        $sign_date_meta = get_user_meta($user_id, 'tt_daily_sign', true);
        $sign_date = date('Y-m-d', strtotime($sign_date_meta));
        $now_date = date('Y-m-d', time());
        if ($sign_date != $now_date) {
            return '<a class="btn btn-info btn-daily-sign" href="javascript:;" title="签到获得'.CREDIT_NAME.'">'.__('Daily Sign', 'tt').'</a>';
        } else {
            return '<a class="btn btn-warning btn-daily-sign signed" href="javascript:;" title="'.sprintf(__('Signed on %s', 'tt'), $sign_date_meta).'">'.__('Signed today', 'tt').'</a>';
        }
    } else {
        return '<a class="btn btn-primary btn-daily-sign" href="javascript:;" id="daily_sign" title="签到获得'.CREDIT_NAME.'">'.__('Daily Sign', 'tt').'</a>';
    }
}

/**
 * 每日签到.
 *
 * @since 2.0.0
 *
 * @return WP_Error|bool
 */
function ct_daily_sign()
{
    date_default_timezone_set('Asia/Shanghai');
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('user_not_sign_in', __('You must sign in before daily sign', 'tt'), array('status' => 401));
    }
    $date = date('Y-m-d H:i:s', time());
    $sign_date_meta = get_user_meta($user_id, 'tt_daily_sign', true);
    $sign_date = date('Y-m-d', strtotime($sign_date_meta));
    $now_date = date('Y-m-d', time());
    if ($sign_date != $now_date):
        update_user_meta($user_id, 'tt_daily_sign', $date);
    $credits = (int) ct_get_option('tt_daily_sign_credits', 10);
    ct_update_user_credit($user_id, $credits, sprintf(__('签到获得 %d %s', 'tt'), $credits,CREDIT_NAME));
    return true; else:
        return new WP_Error('daily_signed', __('You have signed today', 'tt'), array('status' => 200));
    endif;
}

/**
 * 获取用户的现金余额.
 *
 * @since 2.2.0
 *
 * @param int $user_id
 *
 * @return float
 */
function ct_get_user_cash($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    // 注意 余额按分为单位存储
    return sprintf('%0.2f', (int) get_user_meta($user_id, 'tt_cash', true) / 100);
}

/**
 * 获取用户已经消费的现金.
 *
 * @since 2.2.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_get_user_consumed_cash($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();

    return sprintf('%0.2f', (int) get_user_meta($user_id, 'tt_consumed_cash', true) / 100);
}

/**
 * 更新用户余额(充值或消费现金余额).
 *
 * @since 2.2.0
 *
 * @param int    $user_id
 * @param int    $amount(单位：分)
 * @param string $msg
 * @param bool   $admin_handle
 *
 * @return bool
 */
function ct_update_user_cash($user_id = 0, $amount = 0, $msg = '', $admin_handle = false)
{
    $user_id = $user_id ?: get_current_user_id();
    $before_cash = (int) get_user_meta($user_id, 'tt_cash', true);
    // 管理员直接更改用户余额
    if ($admin_handle) {
        $update = update_user_meta($user_id, 'tt_cash', (int) $amount + $before_cash);
        if ($update) {
            // 添加余额变动消息
            if ($amount > 0){
            $msg = $msg ?: sprintf(__('Administrator add %s cash to you, current cash balance %s', 'tt'), sprintf('%0.2f', max(0, (int) $amount) / 100), sprintf('%0.2f', (int) ($amount + $before_cash) / 100));
            }else{
            $msg = $msg ?: sprintf(__('系统管理员给你余额减少 %s 元 , 当前余额 %s 元', 'tt'), sprintf('%0.2f', abs((int) $amount / 100)), sprintf('%0.2f', (int) ($amount + $before_cash) / 100));
            }
            ct_create_message($user_id, 0, 'System', 'cash', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }

        return (bool) $update;
    }
    // 普通更新
    if ($amount > 0) {
        $update = update_user_meta($user_id, 'tt_cash', $before_cash + $amount); //Meta ID if the key didn't exist; true on successful update; false on failure or if $meta_value is the same as the existing meta value in the database.
        if ($update) {
            // 添加余额变动消息
            $msg = $msg ?: sprintf(__('Charge %s cash, current cash balance %s', 'tt'), sprintf('%0.2f', $amount / 100), sprintf('%0.2f', (int) ($amount + $before_cash) / 100));
            ct_create_message($user_id, 0, 'System', 'cash', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }
    } elseif ($amount < 0) {
        if ($before_cash + $amount < 0) {
            return false;
        }
        $before_consumed = (int) get_user_meta($user_id, 'tt_consumed_cash', true);
        update_user_meta($user_id, 'tt_consumed_cash', $before_consumed - $amount);
        $update = update_user_meta($user_id, 'tt_cash', $before_cash + $amount);
        if ($update) {
            // 添加余额变动消息
            $msg = $msg ?: sprintf(__('Spend %s cash, current cash balance %s', 'tt'), sprintf('%0.2f', absint($amount) / 100), sprintf('%0.2f', (int) ($amount + $before_cash) / 100));
            ct_create_message($user_id, 0, 'System', 'cash', $msg, '', MsgReadStatus::UNREAD, 'publish');
        }
    }

    return true;
}

/**
 * 余额充值到账(目前只能卡密充值).
 *
 * @since 2.2.0
 *
 * @param $card_id
 * @param $card_pwd
 *
 * @return bool|WP_Error
 */
function ct_add_cash_by_card($card_id, $card_pwd)
{
    $card = ct_get_card($card_id, $card_pwd);
    if (!$card) {
        return new WP_Error('card_not_exist', __('Card is not exist', 'tt'), array('status' => 403));
    } elseif ($card->status != 1) {
        return new WP_Error('card_invalid', __('Card is not valid', 'tt'), array('status' => 403));
    }

    ct_mark_card_used($card->id);

    $user = wp_get_current_user();
    $cash = intval($card->denomination);
    $type = intval($card->type);
    $balance_cash = ct_get_user_cash($user->ID);
    $balance_credit = ct_get_user_credit($user->ID);
    if($type==1){
    $update = ct_update_user_credit($user->ID, $cash, sprintf(__('通过充值卡充值 <strong>%2$s</strong> %1$s，当前剩余 %3$s %1$s', 'tt'), CREDIT_NAME,$cash, $cash + $balance));
    }else{
      $update = ct_update_user_cash($user->ID, $cash, sprintf(__('Charge <strong>%s</strong> Cash by card, current cash balance %s', 'tt'), sprintf('%0.2f', $cash / 100), sprintf('%0.2f', $cash / 100 + $balance)));
    }

    // 发送邮件
    $blog_name = get_bloginfo('name');
    if($type==1){
    $subject = sprintf(__('充值 %s 完成 - %s', 'tt'), CREDIT_NAME,$blog_name);
    $args = array(
        'blogName' => $blog_name,
        'creditsNum' => $cash,
        'currentCredits' => ct_get_user_credit($user->ID),
        'adminEmail' => get_option('admin_email'),
    );
    // cute_async_mail('', $user->user_email, $subject, $args, 'charge-credits-success');
    cute_mail('', $user->user_email, $subject, $args, 'charge-credits-success');
    }else{
    $subject = sprintf(__('Charge Cash Successfully - %s', 'tt'), $blog_name);
    $args = array(
        'blogName' => $blog_name,
        'cashNum' => sprintf('%0.2f', $cash / 100),
        'currentCash' => ct_get_user_cash($user->ID),
        'adminEmail' => get_option('admin_email'),
    );
    // cute_async_mail('', $user->user_email, $subject, $args, 'charge-cash-success');
    cute_mail('', $user->user_email, $subject, $args, 'charge-cash-success');
    }
    $data= array(
        'cash' => $cash,
        'type' => $type
    );
    if ($update) {
        return $data;
    }

    return $update;
}

/**
 * 余额充值到账.
 *
 * @since 2.0.0
 *
 * @param $order_id
 */
function ct_add_cash_by_order($order_id)
{
    $order = ct_get_order($order_id);
    if (!$order || $order->order_status != OrderStatus::TRADE_SUCCESS) {
        return;
    }

    $user = get_user_by('id', $order->user_id);
    $buy_cash = $order->order_quantity;
    $ct_deposit_cash_count = ct_get_option('ct_deposit_cash_count',3);
    for ($i=1; $i <= $ct_deposit_cash_count; $i++) {
      if(!ct_get_option('ct_deposit_cash',false)) break;
      $ct_deposit_cash = ct_get_option('ct_deposit_cash_pay_'.$i,1);
      if($order->order_total_price == $ct_deposit_cash){
      $buy_cash = ct_get_option('ct_deposit_cash_'.$i,10) * 100;
      break;
      }
    }
    ct_update_user_cash($order->user_id, $buy_cash, sprintf(__('充值 <strong>%d</strong> 元, 在线支付 %0.2f 元', 'tt'), $buy_cash / 100, $order->order_total_price));

    // 发送邮件
    $blog_name = get_bloginfo('name');
    $subject = sprintf(__('Charge Cash Successfully - %s', 'tt'), $blog_name);
    $args = array(
        'blogName' => $blog_name,
        'cashNum' => sprintf('%0.2f', $buy_cash / 100),
        'currentCash' => ct_get_user_cash($user->ID),
        'adminEmail' => get_option('admin_email'),
    );
    // cute_async_mail('', $user->user_email, $subject, $args, 'charge-cash-success');
    cute_mail('', $user->user_email, $subject, $args, 'charge-cash-success');
}

/**
 * 使用现金余额支付.
 *
 * @since 2.2.0
 *
 * @param float  $amount
 * @param string $product_subject
 * @param bool   $rest
 *
 * @return bool|WP_Error
 */
function ct_cash_pay($amount = 0.0, $product_subject = '', $rest = false)
{
    $amount = abs($amount);
    $user_id = get_current_user_id();
    if (!$user_id) {
        return $rest ? new WP_Error('unknown_user', __('You must sign in before payment', 'tt'), array('status' => 403)) : false;
    }

    $balance = (float) ct_get_user_cash($user_id);
    if ($amount - $balance >= 0.0001) {
        return $rest ? new WP_Error('insufficient_cash', __('You do not have enough cash to accomplish this payment', 'tt'), array('status' => 403)) : false;
    }

    $msg = $product_subject ? sprintf(__('Cost %0.2f cash to buy %s, current cash balance %s', 'tt'), $amount, $product_subject, $balance - $amount) : '';
    ct_update_user_cash($user_id, (int) ($amount * (-100)), $msg);
    return true;
}

/**
 * 在后台用户列表中显示余额.
 *
 * @since 2.2.0
 *
 * @param $columns
 *
 * @return mixed
 */
function ct_cash_column($columns)
{
    $columns['tt_cash'] = __('Cash Balance', 'tt');

    return $columns;
}
add_filter('manage_users_columns', 'ct_cash_column');

function ct_cash_column_callback($value, $column_name, $user_id)
{
    if ('tt_cash' == $column_name) {
        $cash = intval(get_user_meta($user_id, 'tt_cash', true));
        $void = intval(get_user_meta($user_id, 'tt_consumed_cash', true));
        $value = sprintf(__('总额 %1$s 元, 已消费 %2$s 元, 余额 %3$s 元', 'tt'), sprintf('%0.2f', ($cash + $void) / 100), sprintf('%0.2f', $void / 100), sprintf('%0.2f', $cash / 100));
    }

    return $value;
}
add_action('manage_users_custom_column', 'ct_cash_column_callback', 10, 3);

/**
 * 通过卡号卡密获取卡记录.
 *
 * @since 2.2.0
 *
 * @param $card_id
 * @param $card_secret
 *
 * @return array|null|object|void
 */
function ct_get_card($card_id, $card_secret)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    $row = $wpdb->get_row(sprintf("SELECT * FROM $cards_table WHERE `card_id`='%s' AND `card_secret`='%s'", $card_id, $card_secret));

    return $row;
}

/**
 * 标记卡已被使用.
 *
 * @since 2.2.0
 *
 * @param $id
 *
 * @return false|int
 */
function ct_mark_card_used($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    $update = $wpdb->update(
        $cards_table,
        array(
            'status' => 0,
        ),
        array('id' => $id),
        array('%d'),
        array('%d')
    );

    return $update;
}

/**
 * 统计卡数量.
 *
 * @since 2.2.0
 *
 * @param $in_effect
 *
 * @return int
 */
function ct_count_cards($in_effect = false)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    if ($in_effect) {
        $sql = sprintf("SELECT COUNT(*) FROM $cards_table WHERE `status`=1");
    } else {
        $sql = "SELECT COUNT(*) FROM $cards_table";
    }
    $count = $wpdb->get_var($sql);

    return $count;
}

/**
 * 删除card记录.
 *
 * @since 2.2.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_delete_card($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    $delete = $wpdb->delete(
        $cards_table,
        array('id' => $id),
        array('%d')
    );

    return (bool) $delete;
}

/**
 * 随机生成一定数量的卡
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_gen_cards($quantity, $denomination, $type=1)
{
    $raw_cards = array();
    $cards = array();
    $place_holders = array();
    $denomination = absint($denomination);
    $create_time = current_time('mysql');
    for ($i = 0; $i < $quantity; ++$i) {
        $card_id = Utils::generateRandomStr(10, 'number');
        $card_secret = Utils::generateRandomStr(16);
        array_push($raw_cards, array(
            'card_id' => $card_id,
            'card_secret' => $card_secret,
        ));
        array_push($cards, $card_id, $card_secret, $denomination, $create_time,$type, 1);
        array_push($place_holders, "('%s', '%s', '%d', '%s', '%d', '%d')");
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';

    $query = "INSERT INTO $cards_table (card_id, card_secret, denomination, create_time, type, status) VALUES ";
    $query .= implode(', ', $place_holders);
    $result = $wpdb->query($wpdb->prepare("$query ", $cards));

    if (!$result) {
        return false;
    }

    return $raw_cards;
}

/**
 * 获取多条充值卡
 *
 * @since 2.2.0
 *
 * @param int  $limit
 * @param int  $offset
 * @param bool $in_effect
 *
 * @return array|null|object
 */
function ct_get_cards($limit = 20, $offset = 0, $in_effect = false)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_cards';
    if ($in_effect) {
        $sql = sprintf("SELECT * FROM $cards_table WHERE `status`=1 ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
    } else {
        $sql = sprintf("SELECT * FROM $cards_table ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
    }
    $results = $wpdb->get_results($sql);

    return $results;
}

/**
 * 获取用户开通会员订单记录.
 *
 * @since 2.0.0
 *
 * @param int $user_id
 * @param int $limit
 * @param int $offset
 *
 * @return array|null|object
 */
function ct_get_user_member_orders($user_id = 0, $limit = 20, $offset = 0)
{
    global $wpdb;
    $user_id = $user_id ?: get_current_user_id();
    $prefix = $wpdb->prefix;
    $table = $prefix.'tt_orders';
    $vip_orders = $wpdb->get_results(sprintf('SELECT * FROM %s WHERE `user_id`=%d AND `product_id` IN (-1,-2,-3) ORDER BY `id` DESC LIMIT %d OFFSET %d', $table, $user_id, $limit, $offset));

    return $vip_orders;
}

/**
 * 统计用户会员订单数量.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return int
 */
function ct_count_user_member_orders($user_id)
{
    global $wpdb;
    $user_id = $user_id ?: get_current_user_id();
    $prefix = $wpdb->prefix;
    $table = $prefix.'tt_orders';
    $count = $wpdb->get_var(sprintf('SELECT COUNT(*) FROM %s WHERE `user_id`=%d AND `product_id` IN (-1,-2,-3)', $table, $user_id));

    return (int) $count;
}

/**
 * 获取会员类型描述文字.
 *
 * @since 2.0.0
 *
 * @param $code
 *
 * @return string|void
 */
function ct_get_member_type_string($code)
{
    switch ($code) {
        case Member::PERMANENT_VIP:
            $type = __('Permanent Membership', 'tt');
            break;
        case Member::ANNUAL_VIP:
            $type = __('Annual Membership', 'tt');
            break;
        case Member::MONTHLY_VIP:
            $type = __('Monthly Membership', 'tt');
            break;
        case Member::EXPIRED_VIP:
            $type = __('Expired Membership', 'tt');
            break;
        default:
            $type = __('None Membership', 'tt');
    }

    return $type;
}

/**
 * 获取用户会员状态文字(有效性).
 *
 * @since 2.0.0
 *
 * @param $code
 *
 * @return string|void
 */
function ct_get_member_status_string($code)
{
    switch ($code) {
        case Member::PERMANENT_VIP:
        case Member::ANNUAL_VIP:
        case Member::MONTHLY_VIP:
            return __('In Effective', 'tt');
            break;
        case Member::EXPIRED_VIP:
            return __('Expired', 'tt');
            break;
        default:
            return __('N/A', 'tt');
    }
}

/**
 * 根据会员ID获取会员记录.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return array|null|object|void
 */
function ct_get_member($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $row = $wpdb->get_row(sprintf("SELECT * FROM $members_table WHERE `id`=%d", $id));

    return $row;
}

/**
 * 根据用户ID获取会员记录.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return array|null|object|void
 */
function ct_get_member_row($user_id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $row = $wpdb->get_row(sprintf("SELECT * FROM $members_table WHERE `user_id`=%d", $user_id));

    return $row;
}

/**
 * 添加会员记录(如果已存在记录则更新).
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param $vip_type
 * @param $start_time
 * @param $end_time
 * @param bool $admin_handle 是否管理员手动操作
 *
 * @return bool|int
 */
function ct_add_or_update_member($user_id, $vip_type, $start_time = 0, $end_time = 0, $admin_handle = false){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix . 'tt_members';

    if(!in_array($vip_type, array(Member::NORMAL_MEMBER, Member::MONTHLY_VIP, Member::ANNUAL_VIP, Member::PERMANENT_VIP))){
        $vip_type = Member::NORMAL_MEMBER;
    }
    $duration = 0;
    switch ($vip_type){
        case Member::PERMANENT_VIP:
            $duration = Member::PERMANENT_VIP_PERIOD;
            break;
        case Member::ANNUAL_VIP:
            $duration = Member::ANNUAL_VIP_PERIOD;
            break;
        case Member::MONTHLY_VIP:
            $duration = Member::MONTHLY_VIP_PERIOD;
            break;
    }

    if(!$start_time) {
        $start_time = (int)current_time('timestamp');
    }elseif(is_string($start_time)){
        $start_time = strtotime($start_time);
    }

    if(is_string($end_time)){
        $end_time = strtotime($end_time);
    }
    $now = time();
    $row = ct_get_member_row($user_id);
    if($row) {
        $prev_end_time = strtotime($row->endTime);
        if($prev_end_time - $now > 100){ //尚未过期
            $start_time = strtotime($row->startTime); //使用之前的开始时间
            $end_time = $end_time ? : strtotime($row->endTime) + $duration;
        }else{ //已过期
            $start_time = $now;
            $end_time = $end_time ? : $now + $duration;
        }
        $update = $wpdb->update(
            $members_table,
            array(
                'user_type' => $vip_type,
                'startTime' => date('Y-m-d H:i:s', $start_time),
                'endTime' => date('Y-m-d H:i:s', $end_time),
                'endTimeStamp' => $end_time
            ),
            array('user_id' => $user_id),
            array('%d', '%s', '%s', '%d'),
            array('%d')
        );

        // 清理会员缓存
        delete_transient('ct_cache_daily_vm_MeMembershipVM_user'.$user_id);
        // 发送邮件
        $admin_handle ? ct_promote_vip_email($user_id, $vip_type, date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time)) : ct_open_vip_email($user_id, $vip_type, date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time));
        // 站内消息
        ct_create_message($user_id, 0, 'System', 'notification', __('你的会员状态发生了变化', 'tt'), sprintf( __('会员类型: %1$s, 到期时间: %2$s', 'tt'), ct_get_member_type_string($vip_type), date('Y-m-d H:i:s', $end_time) ));
        return $update !== false;
    }
    $start_time = $now;
    $end_time = $end_time ? : $now + $duration;
    $insert = $wpdb->insert(
        $members_table,
        array(
            'user_id' => $user_id,
            'user_type' => $vip_type,
            'startTime' => date('Y-m-d H:i:s', $start_time),
            'endTime' => date('Y-m-d H:i:s', $end_time),
            'endTimeStamp' => $end_time
        ),
        array('%d', '%d', '%s', '%s', '%d')
    );
    if($insert) {
        // 清理会员缓存
        delete_transient('ct_cache_daily_vm_MeMembershipVM_user'.$user_id);
        // 发送邮件
        $admin_handle ? ct_promote_vip_email($user_id, $vip_type, date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time)) : ct_open_vip_email($user_id, $vip_type, date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time));
        // 站内消息
        ct_create_message($user_id, 0, 'System', 'notification', __('你的会员状态发生了变化', 'tt'), sprintf( __('会员类型: %1$s, 到期时间: %2$s', 'tt'), ct_get_member_type_string($vip_type), date('Y-m-d H:i:s', $end_time) ));

        return $wpdb->insert_id;
    }
    return false;
}

/**
 * 删除会员记录.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return bool
 */
function ct_delete_member($user_id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $delete = $wpdb->delete(
        $members_table,
        array('user_id' => $user_id),
        array('%d')
    );
    return (bool) $delete;
}

/**
 * 删除会员记录.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_delete_member_by_id($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $delete = $wpdb->delete(
        $members_table,
        array('id' => $id),
        array('%d')
    );
    return (bool) $delete;
}

/**
 * 获取所有指定类型会员.
 *
 * @since 2.0.0
 *
 * @param int $member_type // -1 代表all
 * @param int $limit
 * @param int $offset
 *
 * @return array|null|object
 */
function ct_get_vip_members($member_type = -1, $limit = 20, $offset = 0)
{
    if ($member_type != -1 && !in_array($member_type, array(Member::MONTHLY_VIP, Member::ANNUAL_VIP, Member::PERMANENT_VIP))) {
        $member_type = -1;
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $now = time();

    if ($member_type == -1) {
        $sql = sprintf("SELECT * FROM $members_table WHERE `user_type`>0 AND `endTimeStamp`>=%d LIMIT %d OFFSET %d", $now, $limit, $offset);
    } else {
        $sql = sprintf("SELECT * FROM $members_table WHERE `user_type`=%d AND `endTimeStamp`>%d LIMIT %d OFFSET %d", $member_type, $now, $limit, $offset);
    }

    $results = $wpdb->get_results($sql);

    return $results;
}

/**
 * 统计指定类型会员数量.
 *
 * @since 2.0.0
 *
 * @param int $member_type
 *
 * @return int
 */
function ct_count_vip_members($member_type = -1)
{
    if ($member_type != -1 && !in_array($member_type, array(Member::MONTHLY_VIP, Member::ANNUAL_VIP, Member::PERMANENT_VIP))) {
        $member_type = -1;
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $members_table = $prefix.'tt_members';
    $now = time();

    if ($member_type == -1) {
        $sql = sprintf("SELECT COUNT(*) FROM $members_table WHERE `user_type`>0 AND `endTimeStamp`>=%d", $now);
    } else {
        $sql = sprintf("SELECT COUNT(*) FROM $members_table WHERE `user_type`=%d AND `endTimeStamp`>%d", $member_type, $now);
    }

    $count = $wpdb->get_var($sql);

    return $count;
}

/**
 * 会员标识.
 *
 * @since 2.0.0
 *
 * @param $user_id
 *
 * @return string
 */
function ct_get_member_icon($user_id)
{
    $member = new Member($user_id);
    if ($member->ct_is_permanent_vip()) {
        return '<i class="vipico permanent_member" title="永久会员"></i>';
    } elseif ($member->ct_is_annual_vip()) {
        return '<i class="vipico annual_member" title="年费会员"></i>';
    } elseif ($member->ct_is_monthly_vip()) {
        return '<i class="vipico monthly_member" title="VIP会员"></i>';
    }

    return '<i class="vipico normal_member"></i>';
}

/**
 * 获取充值VIP价格
 *
 * @since 2.0.0
 *
 * @param int $vip_type
 *
 * @return float
 */
function ct_get_vip_price($vip_type = Member::MONTHLY_VIP)
{
    switch ($vip_type) {
        case Member::MONTHLY_VIP:
            $price = ct_get_option('tt_monthly_vip_price', 10);
            break;
        case Member::ANNUAL_VIP:
            $price = ct_get_option('tt_annual_vip_price', 100);
            break;
        case Member::PERMANENT_VIP:
            $price = ct_get_option('tt_permanent_vip_price', 199);
            break;
        default:
            $price = 0;
    }

    return sprintf('%0.2f', $price);
}

/**
 * 创建开通VIP的订单.
 *
 * @since 2.0.0
 *
 * @param $user_id
 * @param int $vip_type
 *
 * @return array|bool
 */
function ct_create_vip_order($user_id, $vip_type = 1)
{
    if (!in_array($vip_type * (-1), array(Product::MONTHLY_VIP, Product::ANNUAL_VIP, Product::PERMANENT_VIP))) {
        $vip_type = Product::PERMANENT_VIP;
    }
    if(!$user_id){
      return false;
    }
    $order_id = ct_generate_order_num();
    $order_time = current_time('mysql');
    $product_id = $vip_type * (-1);
    $currency = 'cash';
    $order_price = ct_get_vip_price($vip_type);
    $order_total_price = $order_price;

    switch ($vip_type * (-1)) {
        case Product::MONTHLY_VIP:
            $product_name = Product::MONTHLY_VIP_NAME;
            break;
        case Product::ANNUAL_VIP:
            $product_name = Product::ANNUAL_VIP_NAME;
            break;
        case Product::PERMANENT_VIP:
            $product_name = Product::PERMANENT_VIP_NAME;
            break;
        default:
            $product_name = '';
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $insert = $wpdb->insert(
        $orders_table,
        array(
            'parent_id' => 0,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'order_time' => $order_time,
            'order_price' => $order_price,
            'order_currency' => $currency,
            'order_quantity' => 1,
            'order_total_price' => $order_total_price,
            'user_id' => $user_id,
        ),
        array('%d', '%s', '%d', '%s', '%s', '%f', '%s', '%d', '%f', '%d')
    );
    if ($insert) {
        return array(
            'insert_id' => $wpdb->insert_id,
            'order_id' => $order_id,
            'total_price' => $order_total_price,
        );
    }

    return false;
}

/**
 * 根据订单产品ID获取对应VIP开通类型名.
 *
 * @since 2.0.0
 *
 * @param $product_id
 *
 * @return string
 */
function ct_get_vip_product_name($product_id)
{
    switch ($product_id) {
        case Product::PERMANENT_VIP:
            return Product::PERMANENT_VIP_NAME;
        case Product::ANNUAL_VIP:
            return Product::ANNUAL_VIP_NAME;
        case Product::MONTHLY_VIP:
            return Product::MONTHLY_VIP_NAME;
        default:
            return '';
    }
}
function get_url_root($url){
    if(!$url){
        return $url;
    }
    $state_domain = array(
        'al','hi','gx','xz','xj','qh','nx','yn','gz','hb','ha','jx','ah','cq','ln','zj','hl','jl','nm','he','dz','af','ar','ae','aw','om','az','eg','et','ie','ee','ad','ao','ai','ag','at','au','mo','bb','pg','bs','pk','py','ps','bh','pa','br','by','bm','bg','mp','bj','be','is','pr','ba','pl','bo','bz','bw','bt','bf','bi','bv','kp','gq','dk','de','tl','tp','tg','dm','do','ru','ec','er','fr','fo','pf','gf','tf','va','ph','fj','fi','cv','fk','gm','cg','cd','co','cr','gg','gd','gl','ge','cu','gp','gu','gy','kz','ht','kr','nl','an','hm','hn','ki','dj','kg','gn','gw','ca','gh','ga','kh','cz','zw','cm','qa','ky','km','ci','kw','cc','hr','ke','ck','lv','ls','la','lb','lt','lr','ly','li','re','lu','rw','ro','mg','im','mv','mt','mw','my','ml','mk','mh','mq','yt','mu','mr','us','um','as','vi','mn','ms','bd','pe','fm','mm','md','ma','mc','mz','mx','nr','np','ni','ne','ng','nu','no','nf','na','za','aq','gs','eu','pw','pn','pt','jp','js','se','ch','sv','ws','yu','sl','sn','cy','sc','sa','cx','st','sh','kn','lc','sm','pm','vc','lk','sk','si','sj','sz','sd','sr','sb','so','tj','tw','th','tz','to','tc','tt','tn','tv','tr','tm','tk','wf','vu','gt','ve','bn','ug','ua','uy','uz','es','eh','gr','hk','sg','nc','nz','hu','sy','jm','am','ac','ye','iq','ir','il','it','in','id','uk','vg','io','jo','vn','zm','je','td','gi','cl','cf','cn','yr','com','arpa','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','me','mobi','asia','ax','bl','bq','cat','cw','gb','jobs','mf','rs','su','sx','tel','travel'
    );

    if(!preg_match("/^http/is", $url)){
        $url="http://".$url;
    }
    $url_parse = parse_url(strtolower($url));
    $urlarr = explode(".", $url_parse['host']);
    $count = count($urlarr);

    if($count <= 2){
        $res = $url_parse['host'];
    }elseif($count > 2){
        $last = array_pop($urlarr);
        $last_1 = array_pop($urlarr);
        $last_2 = array_pop($urlarr);

        $res = $last_1.'.'.$last;

        if(in_array($last, $state_domain)){
            $res = $last_1.'.'.$last;
        }

        if(in_array($last_1, $state_domain)){
            $res = $last_2.'.'.$last_1.'.'.$last;
        }
    }
    return $res;
}
function ct_library(){
$authorization = ct_get_option('tt_auth_code');
if(is_admin() || strpos($_SERVER['REQUEST_URI'],'/wp-admin/') !==false || strpos($_SERVER['REQUEST_URI'],'/m/signin') !==false || strpos($_SERVER['REQUEST_URI'],'/api/v1/session') !==false){return true;}
$style=base64_decode('PHN0eWxlIHR5cGU9InRleHQvY3NzIj5wcm9ncmVzcyxzdWIsc3Vwe3ZlcnRpY2FsLWFsaWduOmJhc2VsaW5lfWJ1dHRvbixocixpbnB1dHtvdmVyZmxvdzp2aXNpYmxlfWltZyxsZWdlbmR7bWF4LXdpZHRoOjEwMCV9W3R5cGU9Y2hlY2tib3hdLFt0eXBlPXJhZGlvXSxsZWdlbmR7Ym94LXNpemluZzpib3JkZXItYm94O3BhZGRpbmc6MH0jdHRnZy0xLCN0dGdnLTIsI3R0Z2ctMywjdHRnZy00LGJvZHkuc2l0ZV91dGlsLWRvd25sb2FkICN0dGdnLTF7bWluLWhlaWdodDo5MHB4fWJvZHksaHRtbHtoZWlnaHQ6MTAwJX1hLGE6aG92ZXJ7dGV4dC1kZWNvcmF0aW9uOm5vbmV9LnBhZ2luYXRpb24tbmV3LC50ZXh0LWNlbnRlciwudHRnZz4udGctaW5uZXIsYm9keS5lcnJvci1wYWdlIGZvb3Rlcnt0ZXh0LWFsaWduOmNlbnRlcn1odG1se2ZvbnQtZmFtaWx5OnNhbnMtc2VyaWZ9Ym9keSxidXR0b24saW5wdXQsb3B0Z3JvdXAsc2VsZWN0LHRleHRhcmVhe21hcmdpbjowfWFydGljbGUsYXNpZGUsZGV0YWlscyxmaWdjYXB0aW9uLGZpZ3VyZSxmb290ZXIsaGVhZGVyLG1haW4sbWVudSxuYXYsc2VjdGlvbixzdW1tYXJ5e2Rpc3BsYXk6YmxvY2t9YXVkaW8sY2FudmFzLHByb2dyZXNzLHZpZGVve2Rpc3BsYXk6aW5saW5lLWJsb2NrfWF1ZGlvOm5vdChbY29udHJvbHNdKXtkaXNwbGF5Om5vbmU7aGVpZ2h0OjB9W2hpZGRlbl0sdGVtcGxhdGV7ZGlzcGxheTpub25lfWF7YmFja2dyb3VuZC1jb2xvcjp0cmFuc3BhcmVudDstd2Via2l0LXRleHQtZGVjb3JhdGlvbi1za2lwOm9iamVjdHN9YTphY3RpdmUsYTpob3ZlcntvdXRsaW5lLXdpZHRoOjB9YWJiclt0aXRsZV17Ym9yZGVyLWJvdHRvbTowO3RleHQtZGVjb3JhdGlvbjp1bmRlcmxpbmU7dGV4dC1kZWNvcmF0aW9uOnVuZGVybGluZSBkb3R0ZWR9YixzdHJvbmd7Zm9udC13ZWlnaHQ6Ym9sZGVyfWRmbntmb250LXN0eWxlOml0YWxpY31oMXttYXJnaW46LjY3ZW0gMH1tYXJre2JhY2tncm91bmQtY29sb3I6I2ZmMDtjb2xvcjojMDAwfXNtYWxse2ZvbnQtc2l6ZTo4MCV9c3ViLHN1cHtmb250LXNpemU6NzUlO2xpbmUtaGVpZ2h0OjA7cG9zaXRpb246cmVsYXRpdmV9c3Vie2JvdHRvbTotLjI1ZW19c3Vwe3RvcDotLjVlbX1pbWd7Ym9yZGVyLXN0eWxlOm5vbmV9c3ZnOm5vdCg6cm9vdCl7b3ZlcmZsb3c6aGlkZGVufWNvZGUsa2JkLHByZSxzYW1we2ZvbnQtZmFtaWx5Om1vbm9zcGFjZSxtb25vc3BhY2U7Zm9udC1zaXplOjFlbX1maWd1cmV7bWFyZ2luOjFlbSA0MHB4fWhye2JveC1zaXppbmc6Y29udGVudC1ib3g7aGVpZ2h0OjB9YnV0dG9uLHNlbGVjdHt0ZXh0LXRyYW5zZm9ybTpub25lfVt0eXBlPXJlc2V0XSxbdHlwZT1zdWJtaXRdLGJ1dHRvbixodG1sIFt0eXBlPWJ1dHRvbl17LXdlYmtpdC1hcHBlYXJhbmNlOmJ1dHRvbn1bdHlwZT1idXR0b25dOjotbW96LWZvY3VzLWlubmVyLFt0eXBlPXJlc2V0XTo6LW1vei1mb2N1cy1pbm5lcixbdHlwZT1zdWJtaXRdOjotbW96LWZvY3VzLWlubmVyLGJ1dHRvbjo6LW1vei1mb2N1cy1pbm5lcntib3JkZXItc3R5bGU6bm9uZTtwYWRkaW5nOjB9W3R5cGU9YnV0dG9uXTotbW96LWZvY3VzcmluZyxbdHlwZT1yZXNldF06LW1vei1mb2N1c3JpbmcsW3R5cGU9c3VibWl0XTotbW96LWZvY3VzcmluZyxidXR0b246LW1vei1mb2N1c3Jpbmd7b3V0bGluZTpCdXR0b25UZXh0IGRvdHRlZCAxcHh9ZmllbGRzZXR7Ym9yZGVyOjFweCBzb2xpZCBzaWx2ZXI7bWFyZ2luOjAgMnB4O3BhZGRpbmc6LjM1ZW0gLjYyNWVtIC43NWVtfWxlZ2VuZHtjb2xvcjppbmhlcml0O2Rpc3BsYXk6dGFibGU7d2hpdGUtc3BhY2U6bm9ybWFsfVt0eXBlPW51bWJlcl06Oi13ZWJraXQtaW5uZXItc3Bpbi1idXR0b24sW3R5cGU9bnVtYmVyXTo6LXdlYmtpdC1vdXRlci1zcGluLWJ1dHRvbntoZWlnaHQ6YXV0b31bdHlwZT1zZWFyY2hdey13ZWJraXQtYXBwZWFyYW5jZTp0ZXh0ZmllbGQ7b3V0bGluZS1vZmZzZXQ6LTJweH1bdHlwZT1zZWFyY2hdOjotd2Via2l0LXNlYXJjaC1jYW5jZWwtYnV0dG9uLFt0eXBlPXNlYXJjaF06Oi13ZWJraXQtc2VhcmNoLWRlY29yYXRpb257LXdlYmtpdC1hcHBlYXJhbmNlOm5vbmV9Ojotd2Via2l0LWlucHV0LXBsYWNlaG9sZGVye2NvbG9yOmluaGVyaXQ7b3BhY2l0eTouNTR9Ojotd2Via2l0LWZpbGUtdXBsb2FkLWJ1dHRvbnstd2Via2l0LWFwcGVhcmFuY2U6YnV0dG9uO2ZvbnQ6aW5oZXJpdH0udHRnZz4udGctaW5uZXIgaW1ne2Rpc3BsYXk6YmxvY2s7d2lkdGg6MTAwJX0jdHRnZy0xe21hcmdpbi10b3A6LTEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fWJvZHkuc2luZ2xlICN0dGdnLTEsYm9keS5zaXRlX3V0aWwtZG93bmxvYWQgI3R0Z2ctMXttYXJnaW4tYm90dG9tOi0yMHB4O3BhZGRpbmctcmlnaHQ6MTVweDttYXJnaW4tdG9wOjEwcHh9I3R0Z2ctMiwjdHRnZy0ze21hcmdpbi10b3A6LTEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fSN0dGdnLTR7bWFyZ2luLXRvcDoxMHB4O21hcmdpbi1ib3R0b206LTEwcHh9Ym9keS5zaW5nbGUgI3R0Z2ctNHttYXJnaW4tdG9wOi0yMHB4O21hcmdpbi1ib3R0b206MTBweH0jdHRnZy02e21hcmdpbi1ib3R0b206MjBweH0jdHRnZy04LCN0dGdnLTl7bWFyZ2luLXRvcDoxMHB4O21hcmdpbi1ib3R0b206LTEwcHh9QG1lZGlhKG1heC13aWR0aDo5NjBweCl7LnR0Z2d7ZGlzcGxheTpub25lfX0ucGFnaW5hdGlvbi1uZXd7bWFyZ2luOjA7cGFkZGluZzoyMHB4O2ZvbnQtc2l6ZToxNHB4O2Rpc3BsYXk6YmxvY2t9LnBhZ2luYXRpb24tbmV3PnVse2Rpc3BsYXk6aW5saW5lLWJsb2NrO21hcmdpbi1sZWZ0OjA7bWFyZ2luLWJvdHRvbTowO3BhZGRpbmc6MH0ucGFnaW5hdGlvbi1uZXc+dWw+bGl7ZGlzcGxheTppbmxpbmV9LnBhZ2luYXRpb24tbmV3PnVsPmxpPmEsLnBhZ2luYXRpb24tbmV3PnVsPmxpPnNwYW57bWFyZ2luOjAgMnB4O2Zsb2F0OmxlZnQ7cGFkZGluZzo1cHggMTJweDtiYWNrZ3JvdW5kLWNvbG9yOiNkZGQ7Y29sb3I6IzY2Njtib3JkZXItcmFkaXVzOjJweDtvcGFjaXR5Oi44OH0ucGFnaW5hdGlvbi1uZXc+dWw+bGk+YTpob3ZlciwucGFnaW5hdGlvbi1uZXc+dWw+bGk+c3Bhbjpob3ZlcntjdXJzb3I6cG9pbnRlcjtvcGFjaXR5OjF9LnBhZ2luYXRpb24tbmV3PnVsPmxpPi5jdXJyZW50e2JhY2tncm91bmQtY29sb3I6IzQ1YjZmNztjb2xvcjojZmZmO29wYWNpdHk6MX0ucGFnaW5hdGlvbi1uZXc+dWw+bGk+LmN1cnJlbnQ6aG92ZXJ7Y3Vyc29yOmRlZmF1bHR9LnBhZ2luYXRpb24tbmV3PnVsPmxpPi5kb3RzLC5wYWdpbmF0aW9uLW5ldz51bD5saT4ubWF4LXBhZ2V7b3BhY2l0eToxO2JhY2tncm91bmQtY29sb3I6dHJhbnNwYXJlbnR9Ojotd2Via2l0LXNjcm9sbGJhcnt3aWR0aDo4cHg7aGVpZ2h0OjhweDtiYWNrZ3JvdW5kLWNvbG9yOnJnYmEoMCwwLDAsLjEpfTo6LXdlYmtpdC1zY3JvbGxiYXItdHJhY2t7Ym9yZGVyLXJhZGl1czoxMHB4O2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuMDEpOy13ZWJraXQtYm94LXNoYWRvdzppbnNldCAwIDAgNnB4IHRyYW5zcGFyZW50fTo6LXdlYmtpdC1zY3JvbGxiYXItdHJhY2s6aG92ZXJ7YmFja2dyb3VuZC1jb2xvcjpyZ2JhKDAsMCwwLC4yKTstd2Via2l0LWJveC1zaGFkb3c6aW5zZXQgMCAwIDZweCByZ2JhKDAsMCwwLC40KX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRyYWNrOmFjdGl2ZXtiYWNrZ3JvdW5kLWNvbG9yOnJnYmEoMCwwLDAsLjIpOy13ZWJraXQtYm94LXNoYWRvdzppbnNldCAwIDAgNnB4IHJnYmEoMCwwLDAsLjEpfTo6LXdlYmtpdC1zY3JvbGxiYXItdGh1bWJ7Ym9yZGVyLXJhZGl1czoxMHB4O2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuMik7LXdlYmtpdC1ib3gtc2hhZG93Omluc2V0IDFweCAxcHggMCByZ2JhKDAsMCwwLC4xKX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRodW1iOmhvdmVye2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuNCk7LXdlYmtpdC1ib3gtc2hhZG93Omluc2V0IDFweCAxcHggMCByZ2JhKDAsMCwwLC4xKX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRodW1iOmFjdGl2ZXtiYWNrZ3JvdW5kOnJnYmEoMCwwLDAsLjQpfWh0bWx7Zm9udC1zaXplOjYyLjUlO2NvbG9yOiMwMDA7YmFja2dyb3VuZDojZmZmOy13ZWJraXQtdGV4dC1zaXplLWFkanVzdDoxMDAlOy1tcy10ZXh0LXNpemUtYWRqdXN0OjEwMCU7dGV4dC1yZW5kZXJpbmc6b3B0aW1pemVMZWdpYmlsaXR5Oy13ZWJraXQtZm9udC1zbW9vdGhpbmc6YW50aWFsaWFzZWQ7LW1vei1vc3gtZm9udC1zbW9vdGhpbmc6Z3JheXNjYWxlfWJvZHl7Zm9udDoxNHB4LzEuNSBUaXRpbGxpdW0sIkx1Y2lkYSBTYW5zIFR5cGV3cml0ZXIiLCJIZWx2ZXRpY2EgTmV1ZSIsSGVsdmV0aWNhLEFyaWFsLCJcNUZBRVw4RjZGXDk2QzVcOUVEMSIsIk1pY3Jvc29mdCBZYUhlaSIsIlw1QjhCXDRGNTMiO2NvbG9yOiMzNDQ5NWU7bWluLWhlaWdodDoxMDAlfWgxLGgyLGgzLGg0LGg1LGg2e2ZvbnQtZmFtaWx5OmluaGVyaXQ7Zm9udC13ZWlnaHQ6NTAwO2xpbmUtaGVpZ2h0OmluaGVyaXR9LmgxLC5oMiBoMywuaDMsaDEsaDJ7bWFyZ2luLXRvcDoyMHB4O21hcmdpbi1ib3R0b206MTBweH0uaDQsLmg1LC5oNixoNCxoNSxoNnttYXJnaW4tdG9wOjEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fS5oMSxoMXtmb250LXNpemU6My42cmVtfS5oMixoMntmb250LXNpemU6M3JlbX0uaDMsaDN7Zm9udC1zaXplOjIuNHJlbX0uaDQsaDR7Zm9udC1zaXplOjEuOHJlbX0uaDUsaDV7Zm9udC1zaXplOjEuNnJlbX0uaDYsaDZ7Zm9udC1zaXplOjEuNHJlbX1saSxvbCx1bHtsaXN0LXN0eWxlOm5vbmU7cGFkZGluZzowO21hcmdpbjowfXB7bWFyZ2luOjAgMCAxMHB4fWF7Y29sb3I6IzA3YjZlODstd2Via2l0LXRyYW5zaXRpb246YWxsIC4zcyBlYXNlOy1vLXRyYW5zaXRpb246YWxsIC4zcyBlYXNlO3RyYW5zaXRpb246YWxsIC4zcyBlYXNlfWE6Zm9jdXMsYnV0dG9uOmZvY3Vze291dGxpbmU6MH1idXR0b246aG92ZXJ7Y3Vyc29yOnBvaW50ZXJ9YnV0dG9uLGlucHV0LG9wdGdyb3VwLHNlbGVjdCx0ZXh0YXJlYXtjb2xvcjppbmhlcml0O2ZvbnQ6aW5oZXJpdH1pbnB1dCx0ZXh0YXJlYXtmb250LXNpemU6aW5oZXJpdDtsaW5lLWhlaWdodDppbmhlcml0O2JvcmRlcjoxcHggc29saWQgI2RkZDtib3JkZXItcmFkaXVzOjNweH1pbnB1dDpmb2N1cyx0ZXh0YXJlYTpmb2N1c3tvdXRsaW5lOjA7Ym9yZGVyLWNvbG9yOiM0NWI2Zjd9dGV4dGFyZWF7b3ZlcmZsb3c6YXV0bztjb2xvcjojMjIyO3Jlc2l6ZTpub25lfWlucHV0Oi13ZWJraXQtYXV0b2ZpbGwsc2VsZWN0Oi13ZWJraXQtYXV0b2ZpbGwsdGV4dGFyZWE6LXdlYmtpdC1hdXRvZmlsbHstd2Via2l0LWJveC1zaGFkb3c6MCAwIDAgMWUzcHggI2ZmZiBpbnNldH1pbnB1dDotd2Via2l0LWF1dG9maWxsOmZvY3VzLHNlbGVjdDotd2Via2l0LWF1dG9maWxsOmZvY3VzLHRleHRhcmVhOi13ZWJraXQtYXV0b2ZpbGw6Zm9jdXN7LXdlYmtpdC1ib3gtc2hhZG93OjAgMCAwIDFlM3B4ICNmZmYgaW5zZXR9YWRkcmVzcyxjaXRlLGVtLGksdmFye2ZvbnQtc3R5bGU6bm9ybWFsfWVte2Rpc3BsYXk6aW5saW5lLWJsb2NrfS5jbGVhcmZpeHt6b29tOjF9LmNsZWFyZml4OmFmdGVyLC5jbGVhcmZpeDpiZWZvcmV7Y29udGVudDonJztkaXNwbGF5OnRhYmxlfS5jbGVhcmZpeDphZnRlcntjbGVhcjpib3RofSp7LXdlYmtpdC1ib3gtc2l6aW5nOmJvcmRlci1ib3g7LW1vei1ib3gtc2l6aW5nOmJvcmRlci1ib3g7Ym94LXNpemluZzpib3JkZXItYm94fS52aXNpYmxlLWxnLWJsb2NrLC52aXNpYmxlLWxnLWlubGluZSwudmlzaWJsZS1sZy1pbmxpbmUtYmxvY2ssLnZpc2libGUtbWQtYmxvY2ssLnZpc2libGUtbWQtaW5saW5lLC52aXNpYmxlLW1kLWlubGluZS1ibG9jaywudmlzaWJsZS1zbS1ibG9jaywudmlzaWJsZS1zbS1pbmxpbmUsLnZpc2libGUtc20taW5saW5lLWJsb2NrLC52aXNpYmxlLXhzLWJsb2NrLC52aXNpYmxlLXhzLWlubGluZSwudmlzaWJsZS14cy1pbmxpbmUtYmxvY2t7ZGlzcGxheTpub25lIWltcG9ydGFudH1AbWVkaWEobWF4LXdpZHRoOjc2N3B4KXsudmlzaWJsZS14cy1ibG9ja3tkaXNwbGF5OmJsb2NrIWltcG9ydGFudH0udmlzaWJsZS14cy1pbmxpbmV7ZGlzcGxheTppbmxpbmUhaW1wb3J0YW50fS52aXNpYmxlLXhzLWlubGluZS1ibG9ja3tkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnR9fUBtZWRpYShtaW4td2lkdGg6NzY4cHgpIGFuZCAobWF4LXdpZHRoOjk5MXB4KXsudmlzaWJsZS1zbS1ibG9ja3tkaXNwbGF5OmJsb2NrIWltcG9ydGFudH0udmlzaWJsZS1zbS1pbmxpbmV7ZGlzcGxheTppbmxpbmUhaW1wb3J0YW50fS52aXNpYmxlLXNtLWlubGluZS1ibG9ja3tkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnR9fUBtZWRpYShtaW4td2lkdGg6OTkycHgpIGFuZCAobWF4LXdpZHRoOjExOTlweCl7LnZpc2libGUtbWQtYmxvY2t7ZGlzcGxheTpibG9jayFpbXBvcnRhbnR9LnZpc2libGUtbWQtaW5saW5le2Rpc3BsYXk6aW5saW5lIWltcG9ydGFudH0udmlzaWJsZS1tZC1pbmxpbmUtYmxvY2t7ZGlzcGxheTppbmxpbmUtYmxvY2shaW1wb3J0YW50fX1AbWVkaWEobWluLXdpZHRoOjEyMDBweCl7LnZpc2libGUtbGctYmxvY2t7ZGlzcGxheTpibG9jayFpbXBvcnRhbnR9LnZpc2libGUtbGctaW5saW5le2Rpc3BsYXk6aW5saW5lIWltcG9ydGFudH0udmlzaWJsZS1sZy1pbmxpbmUtYmxvY2t7ZGlzcGxheTppbmxpbmUtYmxvY2shaW1wb3J0YW50fX0ubXQtNSwubXQ1e21hcmdpbi10b3A6NXB4fS5tdC0xMCwubXQxMHttYXJnaW4tdG9wOjEwcHh9Lm10LTE1LC5tdDE1e21hcmdpbi10b3A6MTVweH0ubXQtMjAsLm10MjB7bWFyZ2luLXRvcDoyMHB4fS5tdC0zMCwubXQzMHttYXJnaW4tdG9wOjMwcHh9Lm1yLTUsLm1yNXttYXJnaW4tcmlnaHQ6NXB4fS5tci0xMCwubXIxMHttYXJnaW4tcmlnaHQ6MTBweH0ubXItMTUsLm1yMTV7bWFyZ2luLXJpZ2h0OjE1cHh9Lm1yLTIwLC5tcjIwe21hcmdpbi1yaWdodDoyMHB4fS5tci0zMCwubXIzMHttYXJnaW4tcmlnaHQ6MzBweH0ubWItNSwubWI1e21hcmdpbi1ib3R0b206NXB4fS5tYi0xMCwubWIxMHttYXJnaW4tYm90dG9tOjEwcHh9Lm1iLTE1LC5tYjE1e21hcmdpbi1ib3R0b206MTVweH0ubWItMjAsLm1iMjB7bWFyZ2luLWJvdHRvbToyMHB4fS5tYi0zMCwubWIzMHttYXJnaW4tYm90dG9tOjMwcHh9Lm1sLTUsLm1sNXttYXJnaW4tbGVmdDo1cHh9Lm1sLTEwLC5tbDEwe21hcmdpbi1sZWZ0OjEwcHh9Lm1sLTE1LC5tbDE1e21hcmdpbi1sZWZ0OjE1cHh9Lm1sLTIwLC5tbDIwe21hcmdpbi1sZWZ0OjIwcHh9Lm1sLTMwLC5tbDMwe21hcmdpbi1sZWZ0OjMwcHh9LnB1bGwtbGVmdHtmbG9hdDpsZWZ0fS5wdWxsLXJpZ2h0e2Zsb2F0OnJpZ2h0fS50ZXh0LW11dGVke2NvbG9yOiM3ZjhjOGR9LmNhcHRpb24tbXV0ZWR7Y29sb3I6Izg4OTlhNn0udGV4dC1wcmltYXJ5e2NvbG9yOiMzNDQ5NWV9LnRleHQtc3VjY2Vzc3tjb2xvcjojMmVjYzcxfS50ZXh0LWRhbmdlcntjb2xvcjojZTc0YzNjfS50ZXh0LWluZm97Y29sb3I6IzM0OThkYn0udGV4dC13YXJuaW5ne2NvbG9yOiNmMWM0MGZ9LmhpZGV7ZGlzcGxheTpub25lfS5pbnZpc2libGV7dmlzaWJpbGl0eTpoaWRkZW59LmlzLXZpc2libGUsLnZpc2libGV7dmlzaWJpbGl0eTp2aXNpYmxlfS5iNjRfcmlnaHR7YmFja2dyb3VuZC1pbWFnZTp1cmwoZGF0YTppbWFnZS9wbmc7YmFzZTY0LGlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFCQUFBQUFRQ0FZQUFBQWY4LzloQUFBQUNYQklXWE1BQUE3RUFBQU94QUdWS3c0YkFBQUJPRWxFUVZSNFhxVlRJV3ZEUUJoOUhWV0J3V3pFWUdvdzE5S3BRaUJxcnFLaVlxcFZrZjBCY1RYOUFTZHIyakJaTVJGUmUxQ1l5c2pjWURBb1RJUzV3Q0I2ZVhBWHNqdDJZZXpCbWUvNzN2ZmVGMTU2TUZCVjFRTEFIRUJvdENTQXhQTzhIVm80YXhHdjZwZm5uL2sybG5FWVBBUm9QOWJZNHd4bm9VQUhVSVY4L2JTK09Md2Y0TUxzWm9ibDdiSUVNS3pkbkxTRFI1R0pUakt4ZjkyRHMrU0FKL0RtMnRxQWpkOHd1WjRnaXpLazkybXpoQnh5NldET2dndlJLQUt4ZWQ3OGNFSXVGNFRIajZOVDNULzNVWHdWU045U2FDaE95QVYvVUxmUmh3SGVTY1dWWElFdzFDMllEcWlrbFR2VjlRSVpYQVpRb0JJVnFleFVWeHpKQlFuRFlidUFVMTF4RXAxRXBuQmdCY21keHBjNmlVT2R4R2s4amt2RFNWZVVwODFIWkthWmJXNFZkMExkWjkvTUhtYzRxemo0OSsvOERSZVRwOVM3dlk3dUFBQUFBRWxGVGtTdVFtQ0MpfS5iNjRfZXJyb3J7YmFja2dyb3VuZC1pbWFnZTp1cmwoZGF0YTppbWFnZS9wbmc7YmFzZTY0LGlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFCQUFBQUFRQ0FZQUFBQWY4LzloQUFBQUNYQklXWE1BQUE3RUFBQU94QUdWS3c0YkFBQUJQMGxFUVZSNFhxV1R2VW9EUVJTRnI0TkJXUkFEK2dBK2dNWG1EV0psRmJDM2lDQlkyUW0yWW10dEt5aFlhYUZ0R2lQWTJDV0ZEMkJqRXdpdWdrdjhYZWNiY21IbUVnVEpnVXMyMzcxbmRtWTRPeU5HWlZsdWVkRDIxVFN0VzE5bldaYWRLckRHRlYrOTE1dE9OZGhzVlUvTGtoU01IalBNcWkvc1lBeDZiM3M3OVkvTGMvbExjOXU3a2gwY0ZSNDAvRzRlM1poZmxZZjd3VnhielVOWktYOC9PUlptOGNBZFovNjZ2OHRwWUZ5NjdsTEpJcFl6aXdldjQ4SkdIc1J5aTNVZFZuTmdzZkRnNWZ6cFpUWHo2cnQ0cmhDLzBUTzlaQmJ2ckJoOVB2Umx1TEdXdlBYbnBZQ0ZucFdUS1dVWGlNL01tNm40VGlZR3FLdkJNZWZuLzBTbXdjTHJpT2U4RDRmS25sbnZCQllMRDE1Tklpbk1OVWpJWEZqTU5ZMTluOFJHRW1VU1JraitIV1VlQUt5NmNOR1IybnBMckdEMG1GRXpmT3JQK1JmNCt4VDhFc2t3TUFBQUFBQkpSVTVFcmtKZ2dnPT0pfWltZy5sYXp5e29wYWNpdHk6LjM7LXdlYmtpdC10cmFuc2Zvcm06c2NhbGUoLjkpOy1tb3otdHJhbnNmb3JtOnNjYWxlKC45KTt0cmFuc2Zvcm06c2NhbGUoLjkpOy13ZWJraXQtdHJhbnNpdGlvbjphbGwgZWFzZS1pbi1vdXQgLjNzOy1tb3otdHJhbnNpdGlvbjphbGwgZWFzZS1pbi1vdXQgLjNzO3RyYW5zaXRpb246YWxsIGVhc2UtaW4tb3V0IC4zczstd2Via2l0LXBlcnNwZWN0aXZlLW9yaWdpbjp0b3AgY2VudGVyOy1tb3otcGVyc3BlY3RpdmUtb3JpZ2luOnRvcCBjZW50ZXI7cGVyc3BlY3RpdmUtb3JpZ2luOnRvcCBjZW50ZXJ9aW1nLmxhenkuc2hvd3tvcGFjaXR5OjE7LXdlYmtpdC10cmFuc2Zvcm06c2NhbGUoMSk7LW1vei10cmFuc2Zvcm06c2NhbGUoMSk7dHJhbnNmb3JtOnNjYWxlKDEpfWJvZHkuZXJyb3ItcGFnZSBoZWFkZXJ7bWF4LXdpZHRoOjk5MHB4O2hlaWdodDo2MHB4O21hcmdpbjowIGF1dG87bGluZS1oZWlnaHQ6NjBweH1ib2R5LmVycm9yLXBhZ2UgLndyYXBwZXJ7d2lkdGg6MTAwJTtoZWlnaHQ6Y2FsYygxMDAlIC0gMTEwcHgpO3RleHQtYWxpZ246Y2VudGVyfWJvZHkuZXJyb3ItcGFnZSAud3JhcHBlciAubWFpbntwYWRkaW5nLXRvcDo3NXB4fWJvZHkuZXJyb3ItcGFnZSAud3JhcHBlciAubWFpbiBoMXtmb250LXNpemU6NC42cmVtO21hcmdpbi1ib3R0b206MjBweH1ib2R5LmVycm9yLXBhZ2UgLndyYXBwZXIgLm1haW4gcC5kaWUtbXNne2ZvbnQtc2l6ZToyLjVyZW19Ym9keS5lcnJvci1wYWdlIC53cmFwcGVyIC5tYWluICNsaW5rQmFja0hvbWV7cG9zaXRpb246cmVsYXRpdmU7bWFyZ2luLXRvcDo1MHB4O2Rpc3BsYXk6aW5saW5lLWJsb2NrO2ZvbnQtc2l6ZToyLjRyZW07Y29sb3I6IzQ1YjZmN31ib2R5LmVycm9yLXBhZ2UgZm9vdGVye2hlaWdodDo0MHB4O2xpbmUtaGVpZ2h0OjQwcHg7Zm9udC1zaXplOjEuNHJlbX1ib2R5LmVycm9yLXBhZ2UgZm9vdGVyIHB7cG9zaXRpb246cmVsYXRpdmV9Ym9keS5lcnJvci1wYWdlIGZvb3RlciBwIHNwYW4uY29weXtwb3NpdGlvbjpyZWxhdGl2ZTt0b3A6NHB4O2ZvbnQtc2l6ZToycmVtfTwvc3R5bGU+');
$kurl = get_url_root(strtolower($_SERVER['HTTP_HOST']));
$info = base64_decode(get_option('cute_library'));
$info_key=explode('=BZG/M', $info);
if(!get_option('cute_library') || base64_decode($info_key[0]) != md5($authorization) || time() - (base64_decode($info_key[1]) / 5) > 3600){
	if($json_get = file_get_contents(base64_decode('aHR0cDovL2F1dGgua3VhY2cuY29tL3Byb2R1Y3QvY3V0ZS5waHA/YXBpPUFQSV9jdXRlJnVybD0=').$kurl.base64_decode('JnZhbHVlPWN1dGUmYXV0aG9yaXphdGlvbj0=').$authorization)){
		$row_json=json_decode($json_get,true);
        $key=base64_decode($row_json['key']);
        $key=explode('=BZG/M', $key);
		if(!empty($authorization) && !empty($row_json['key']) && ($row_json['code'] == 'yes' && base64_decode($key[0]) == md5($authorization) && time() - (base64_decode($key[1]) / 5) <= 3600*24)){
			update_option('cute_library', $row_json['key']);
		}elseif($row_json['code'] != 'yes'){
          exit($row_json['msg']);
        }else{
          if(empty($authorization)){
            $code = 101;
          }elseif(empty($row_json['key'])){
            $code = 102;
          }elseif($row_json['code'] != 'yes'){
            $code = 103;
          }elseif(base64_decode($key[0]) != md5($authorization)){
            $code = 104;
          }elseif(time() - (base64_decode($key[1]) / 8) <= 3600*24){
            $code = 105;
          }else{
            $code = time() - (base64_decode($key[1]) / 8);
          }
          exit('<!DOCTYPE html><html lang="zh"><head><meta http-equiv="Content-Type"content="text/html; charset=UTF-8"/><meta name="robots" content="noindex,follow"><title>非法请求</title>'.$style.'</head><body class="error error-page wp-die"><header class="header special-header"></header><div class="wrapper container no-aside"><div class="row"><div class="main inner-wrap"><h1>非法请求</h1><p class="die-msg '.$code.'">显示此页面表示你正在尝试劫持授权请求，如果没有购买，请访问<a href="https://www.kuacg.com/shop/23874.html"target="_blank"title="购买Cute主题">在线商店</a>购买。</p><p><a class="btn btn-lg btn-success link-home"id="linkBackHome"href="/"title="Go Back Home"role="button">返回首页</a></p></div></div></div><footer class="footer special-footer"><p><span class="copy">©</span> 非法请求</p></footer></body></html>');
        }
	}else{
		exit('<!DOCTYPE html><html lang="zh"><head><meta http-equiv="Content-Type"content="text/html; charset=UTF-8"/><meta name="robots" content="noindex,follow"><title>连接授权服务器失败</title>'.$style.'</head><body class="error error-page wp-die"><header class="header special-header"></header><div class="wrapper container no-aside"><div class="row"><div class="main inner-wrap"><h1>网站人气太旺了！请稍后再试！</h1><p class="die-msg">当前访问人数过多，服务器繁忙，请稍候再试。</p><p><a class="btn btn-lg btn-success link-home" id="linkBackHome" href="/" title="Go Back Home" role="button">返回首页</a></p></div></div></div><footer class="footer special-footer"><div class="foot-copyright">©&nbsp;2019 本站由WordPress驱动 · <b style="color: #ff4425;">♥</b> 并由 <a href="https://www.kuacg.com/shop/23874.html" title="Cute" rel="link" target="_blank">Cute</a> 主题定义&amp; Design by <a href="https://www.kuacg.com/" rel="link" title="酷ACG资源网">酷ACG资源网.</a> </div></footer></body></html>');
	}
  }
  return true;
}
ct_library();
function cute_extend(){
$authorization = ct_get_option('tt_extend_auth_code');
if(preg_match('/[一-龥]/u',$authorization) || empty($authorization) || strlen($authorization) < 30){
  return false;
}
$style=base64_decode('PHN0eWxlIHR5cGU9InRleHQvY3NzIj5wcm9ncmVzcyxzdWIsc3Vwe3ZlcnRpY2FsLWFsaWduOmJhc2VsaW5lfWJ1dHRvbixocixpbnB1dHtvdmVyZmxvdzp2aXNpYmxlfWltZyxsZWdlbmR7bWF4LXdpZHRoOjEwMCV9W3R5cGU9Y2hlY2tib3hdLFt0eXBlPXJhZGlvXSxsZWdlbmR7Ym94LXNpemluZzpib3JkZXItYm94O3BhZGRpbmc6MH0jdHRnZy0xLCN0dGdnLTIsI3R0Z2ctMywjdHRnZy00LGJvZHkuc2l0ZV91dGlsLWRvd25sb2FkICN0dGdnLTF7bWluLWhlaWdodDo5MHB4fWJvZHksaHRtbHtoZWlnaHQ6MTAwJX1hLGE6aG92ZXJ7dGV4dC1kZWNvcmF0aW9uOm5vbmV9LnBhZ2luYXRpb24tbmV3LC50ZXh0LWNlbnRlciwudHRnZz4udGctaW5uZXIsYm9keS5lcnJvci1wYWdlIGZvb3Rlcnt0ZXh0LWFsaWduOmNlbnRlcn1odG1se2ZvbnQtZmFtaWx5OnNhbnMtc2VyaWZ9Ym9keSxidXR0b24saW5wdXQsb3B0Z3JvdXAsc2VsZWN0LHRleHRhcmVhe21hcmdpbjowfWFydGljbGUsYXNpZGUsZGV0YWlscyxmaWdjYXB0aW9uLGZpZ3VyZSxmb290ZXIsaGVhZGVyLG1haW4sbWVudSxuYXYsc2VjdGlvbixzdW1tYXJ5e2Rpc3BsYXk6YmxvY2t9YXVkaW8sY2FudmFzLHByb2dyZXNzLHZpZGVve2Rpc3BsYXk6aW5saW5lLWJsb2NrfWF1ZGlvOm5vdChbY29udHJvbHNdKXtkaXNwbGF5Om5vbmU7aGVpZ2h0OjB9W2hpZGRlbl0sdGVtcGxhdGV7ZGlzcGxheTpub25lfWF7YmFja2dyb3VuZC1jb2xvcjp0cmFuc3BhcmVudDstd2Via2l0LXRleHQtZGVjb3JhdGlvbi1za2lwOm9iamVjdHN9YTphY3RpdmUsYTpob3ZlcntvdXRsaW5lLXdpZHRoOjB9YWJiclt0aXRsZV17Ym9yZGVyLWJvdHRvbTowO3RleHQtZGVjb3JhdGlvbjp1bmRlcmxpbmU7dGV4dC1kZWNvcmF0aW9uOnVuZGVybGluZSBkb3R0ZWR9YixzdHJvbmd7Zm9udC13ZWlnaHQ6Ym9sZGVyfWRmbntmb250LXN0eWxlOml0YWxpY31oMXttYXJnaW46LjY3ZW0gMH1tYXJre2JhY2tncm91bmQtY29sb3I6I2ZmMDtjb2xvcjojMDAwfXNtYWxse2ZvbnQtc2l6ZTo4MCV9c3ViLHN1cHtmb250LXNpemU6NzUlO2xpbmUtaGVpZ2h0OjA7cG9zaXRpb246cmVsYXRpdmV9c3Vie2JvdHRvbTotLjI1ZW19c3Vwe3RvcDotLjVlbX1pbWd7Ym9yZGVyLXN0eWxlOm5vbmV9c3ZnOm5vdCg6cm9vdCl7b3ZlcmZsb3c6aGlkZGVufWNvZGUsa2JkLHByZSxzYW1we2ZvbnQtZmFtaWx5Om1vbm9zcGFjZSxtb25vc3BhY2U7Zm9udC1zaXplOjFlbX1maWd1cmV7bWFyZ2luOjFlbSA0MHB4fWhye2JveC1zaXppbmc6Y29udGVudC1ib3g7aGVpZ2h0OjB9YnV0dG9uLHNlbGVjdHt0ZXh0LXRyYW5zZm9ybTpub25lfVt0eXBlPXJlc2V0XSxbdHlwZT1zdWJtaXRdLGJ1dHRvbixodG1sIFt0eXBlPWJ1dHRvbl17LXdlYmtpdC1hcHBlYXJhbmNlOmJ1dHRvbn1bdHlwZT1idXR0b25dOjotbW96LWZvY3VzLWlubmVyLFt0eXBlPXJlc2V0XTo6LW1vei1mb2N1cy1pbm5lcixbdHlwZT1zdWJtaXRdOjotbW96LWZvY3VzLWlubmVyLGJ1dHRvbjo6LW1vei1mb2N1cy1pbm5lcntib3JkZXItc3R5bGU6bm9uZTtwYWRkaW5nOjB9W3R5cGU9YnV0dG9uXTotbW96LWZvY3VzcmluZyxbdHlwZT1yZXNldF06LW1vei1mb2N1c3JpbmcsW3R5cGU9c3VibWl0XTotbW96LWZvY3VzcmluZyxidXR0b246LW1vei1mb2N1c3Jpbmd7b3V0bGluZTpCdXR0b25UZXh0IGRvdHRlZCAxcHh9ZmllbGRzZXR7Ym9yZGVyOjFweCBzb2xpZCBzaWx2ZXI7bWFyZ2luOjAgMnB4O3BhZGRpbmc6LjM1ZW0gLjYyNWVtIC43NWVtfWxlZ2VuZHtjb2xvcjppbmhlcml0O2Rpc3BsYXk6dGFibGU7d2hpdGUtc3BhY2U6bm9ybWFsfVt0eXBlPW51bWJlcl06Oi13ZWJraXQtaW5uZXItc3Bpbi1idXR0b24sW3R5cGU9bnVtYmVyXTo6LXdlYmtpdC1vdXRlci1zcGluLWJ1dHRvbntoZWlnaHQ6YXV0b31bdHlwZT1zZWFyY2hdey13ZWJraXQtYXBwZWFyYW5jZTp0ZXh0ZmllbGQ7b3V0bGluZS1vZmZzZXQ6LTJweH1bdHlwZT1zZWFyY2hdOjotd2Via2l0LXNlYXJjaC1jYW5jZWwtYnV0dG9uLFt0eXBlPXNlYXJjaF06Oi13ZWJraXQtc2VhcmNoLWRlY29yYXRpb257LXdlYmtpdC1hcHBlYXJhbmNlOm5vbmV9Ojotd2Via2l0LWlucHV0LXBsYWNlaG9sZGVye2NvbG9yOmluaGVyaXQ7b3BhY2l0eTouNTR9Ojotd2Via2l0LWZpbGUtdXBsb2FkLWJ1dHRvbnstd2Via2l0LWFwcGVhcmFuY2U6YnV0dG9uO2ZvbnQ6aW5oZXJpdH0udHRnZz4udGctaW5uZXIgaW1ne2Rpc3BsYXk6YmxvY2s7d2lkdGg6MTAwJX0jdHRnZy0xe21hcmdpbi10b3A6LTEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fWJvZHkuc2luZ2xlICN0dGdnLTEsYm9keS5zaXRlX3V0aWwtZG93bmxvYWQgI3R0Z2ctMXttYXJnaW4tYm90dG9tOi0yMHB4O3BhZGRpbmctcmlnaHQ6MTVweDttYXJnaW4tdG9wOjEwcHh9I3R0Z2ctMiwjdHRnZy0ze21hcmdpbi10b3A6LTEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fSN0dGdnLTR7bWFyZ2luLXRvcDoxMHB4O21hcmdpbi1ib3R0b206LTEwcHh9Ym9keS5zaW5nbGUgI3R0Z2ctNHttYXJnaW4tdG9wOi0yMHB4O21hcmdpbi1ib3R0b206MTBweH0jdHRnZy02e21hcmdpbi1ib3R0b206MjBweH0jdHRnZy04LCN0dGdnLTl7bWFyZ2luLXRvcDoxMHB4O21hcmdpbi1ib3R0b206LTEwcHh9QG1lZGlhKG1heC13aWR0aDo5NjBweCl7LnR0Z2d7ZGlzcGxheTpub25lfX0ucGFnaW5hdGlvbi1uZXd7bWFyZ2luOjA7cGFkZGluZzoyMHB4O2ZvbnQtc2l6ZToxNHB4O2Rpc3BsYXk6YmxvY2t9LnBhZ2luYXRpb24tbmV3PnVse2Rpc3BsYXk6aW5saW5lLWJsb2NrO21hcmdpbi1sZWZ0OjA7bWFyZ2luLWJvdHRvbTowO3BhZGRpbmc6MH0ucGFnaW5hdGlvbi1uZXc+dWw+bGl7ZGlzcGxheTppbmxpbmV9LnBhZ2luYXRpb24tbmV3PnVsPmxpPmEsLnBhZ2luYXRpb24tbmV3PnVsPmxpPnNwYW57bWFyZ2luOjAgMnB4O2Zsb2F0OmxlZnQ7cGFkZGluZzo1cHggMTJweDtiYWNrZ3JvdW5kLWNvbG9yOiNkZGQ7Y29sb3I6IzY2Njtib3JkZXItcmFkaXVzOjJweDtvcGFjaXR5Oi44OH0ucGFnaW5hdGlvbi1uZXc+dWw+bGk+YTpob3ZlciwucGFnaW5hdGlvbi1uZXc+dWw+bGk+c3Bhbjpob3ZlcntjdXJzb3I6cG9pbnRlcjtvcGFjaXR5OjF9LnBhZ2luYXRpb24tbmV3PnVsPmxpPi5jdXJyZW50e2JhY2tncm91bmQtY29sb3I6IzQ1YjZmNztjb2xvcjojZmZmO29wYWNpdHk6MX0ucGFnaW5hdGlvbi1uZXc+dWw+bGk+LmN1cnJlbnQ6aG92ZXJ7Y3Vyc29yOmRlZmF1bHR9LnBhZ2luYXRpb24tbmV3PnVsPmxpPi5kb3RzLC5wYWdpbmF0aW9uLW5ldz51bD5saT4ubWF4LXBhZ2V7b3BhY2l0eToxO2JhY2tncm91bmQtY29sb3I6dHJhbnNwYXJlbnR9Ojotd2Via2l0LXNjcm9sbGJhcnt3aWR0aDo4cHg7aGVpZ2h0OjhweDtiYWNrZ3JvdW5kLWNvbG9yOnJnYmEoMCwwLDAsLjEpfTo6LXdlYmtpdC1zY3JvbGxiYXItdHJhY2t7Ym9yZGVyLXJhZGl1czoxMHB4O2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuMDEpOy13ZWJraXQtYm94LXNoYWRvdzppbnNldCAwIDAgNnB4IHRyYW5zcGFyZW50fTo6LXdlYmtpdC1zY3JvbGxiYXItdHJhY2s6aG92ZXJ7YmFja2dyb3VuZC1jb2xvcjpyZ2JhKDAsMCwwLC4yKTstd2Via2l0LWJveC1zaGFkb3c6aW5zZXQgMCAwIDZweCByZ2JhKDAsMCwwLC40KX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRyYWNrOmFjdGl2ZXtiYWNrZ3JvdW5kLWNvbG9yOnJnYmEoMCwwLDAsLjIpOy13ZWJraXQtYm94LXNoYWRvdzppbnNldCAwIDAgNnB4IHJnYmEoMCwwLDAsLjEpfTo6LXdlYmtpdC1zY3JvbGxiYXItdGh1bWJ7Ym9yZGVyLXJhZGl1czoxMHB4O2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuMik7LXdlYmtpdC1ib3gtc2hhZG93Omluc2V0IDFweCAxcHggMCByZ2JhKDAsMCwwLC4xKX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRodW1iOmhvdmVye2JhY2tncm91bmQtY29sb3I6cmdiYSgwLDAsMCwuNCk7LXdlYmtpdC1ib3gtc2hhZG93Omluc2V0IDFweCAxcHggMCByZ2JhKDAsMCwwLC4xKX06Oi13ZWJraXQtc2Nyb2xsYmFyLXRodW1iOmFjdGl2ZXtiYWNrZ3JvdW5kOnJnYmEoMCwwLDAsLjQpfWh0bWx7Zm9udC1zaXplOjYyLjUlO2NvbG9yOiMwMDA7YmFja2dyb3VuZDojZmZmOy13ZWJraXQtdGV4dC1zaXplLWFkanVzdDoxMDAlOy1tcy10ZXh0LXNpemUtYWRqdXN0OjEwMCU7dGV4dC1yZW5kZXJpbmc6b3B0aW1pemVMZWdpYmlsaXR5Oy13ZWJraXQtZm9udC1zbW9vdGhpbmc6YW50aWFsaWFzZWQ7LW1vei1vc3gtZm9udC1zbW9vdGhpbmc6Z3JheXNjYWxlfWJvZHl7Zm9udDoxNHB4LzEuNSBUaXRpbGxpdW0sIkx1Y2lkYSBTYW5zIFR5cGV3cml0ZXIiLCJIZWx2ZXRpY2EgTmV1ZSIsSGVsdmV0aWNhLEFyaWFsLCJcNUZBRVw4RjZGXDk2QzVcOUVEMSIsIk1pY3Jvc29mdCBZYUhlaSIsIlw1QjhCXDRGNTMiO2NvbG9yOiMzNDQ5NWU7bWluLWhlaWdodDoxMDAlfWgxLGgyLGgzLGg0LGg1LGg2e2ZvbnQtZmFtaWx5OmluaGVyaXQ7Zm9udC13ZWlnaHQ6NTAwO2xpbmUtaGVpZ2h0OmluaGVyaXR9LmgxLC5oMiBoMywuaDMsaDEsaDJ7bWFyZ2luLXRvcDoyMHB4O21hcmdpbi1ib3R0b206MTBweH0uaDQsLmg1LC5oNixoNCxoNSxoNnttYXJnaW4tdG9wOjEwcHg7bWFyZ2luLWJvdHRvbToxMHB4fS5oMSxoMXtmb250LXNpemU6My42cmVtfS5oMixoMntmb250LXNpemU6M3JlbX0uaDMsaDN7Zm9udC1zaXplOjIuNHJlbX0uaDQsaDR7Zm9udC1zaXplOjEuOHJlbX0uaDUsaDV7Zm9udC1zaXplOjEuNnJlbX0uaDYsaDZ7Zm9udC1zaXplOjEuNHJlbX1saSxvbCx1bHtsaXN0LXN0eWxlOm5vbmU7cGFkZGluZzowO21hcmdpbjowfXB7bWFyZ2luOjAgMCAxMHB4fWF7Y29sb3I6IzA3YjZlODstd2Via2l0LXRyYW5zaXRpb246YWxsIC4zcyBlYXNlOy1vLXRyYW5zaXRpb246YWxsIC4zcyBlYXNlO3RyYW5zaXRpb246YWxsIC4zcyBlYXNlfWE6Zm9jdXMsYnV0dG9uOmZvY3Vze291dGxpbmU6MH1idXR0b246aG92ZXJ7Y3Vyc29yOnBvaW50ZXJ9YnV0dG9uLGlucHV0LG9wdGdyb3VwLHNlbGVjdCx0ZXh0YXJlYXtjb2xvcjppbmhlcml0O2ZvbnQ6aW5oZXJpdH1pbnB1dCx0ZXh0YXJlYXtmb250LXNpemU6aW5oZXJpdDtsaW5lLWhlaWdodDppbmhlcml0O2JvcmRlcjoxcHggc29saWQgI2RkZDtib3JkZXItcmFkaXVzOjNweH1pbnB1dDpmb2N1cyx0ZXh0YXJlYTpmb2N1c3tvdXRsaW5lOjA7Ym9yZGVyLWNvbG9yOiM0NWI2Zjd9dGV4dGFyZWF7b3ZlcmZsb3c6YXV0bztjb2xvcjojMjIyO3Jlc2l6ZTpub25lfWlucHV0Oi13ZWJraXQtYXV0b2ZpbGwsc2VsZWN0Oi13ZWJraXQtYXV0b2ZpbGwsdGV4dGFyZWE6LXdlYmtpdC1hdXRvZmlsbHstd2Via2l0LWJveC1zaGFkb3c6MCAwIDAgMWUzcHggI2ZmZiBpbnNldH1pbnB1dDotd2Via2l0LWF1dG9maWxsOmZvY3VzLHNlbGVjdDotd2Via2l0LWF1dG9maWxsOmZvY3VzLHRleHRhcmVhOi13ZWJraXQtYXV0b2ZpbGw6Zm9jdXN7LXdlYmtpdC1ib3gtc2hhZG93OjAgMCAwIDFlM3B4ICNmZmYgaW5zZXR9YWRkcmVzcyxjaXRlLGVtLGksdmFye2ZvbnQtc3R5bGU6bm9ybWFsfWVte2Rpc3BsYXk6aW5saW5lLWJsb2NrfS5jbGVhcmZpeHt6b29tOjF9LmNsZWFyZml4OmFmdGVyLC5jbGVhcmZpeDpiZWZvcmV7Y29udGVudDonJztkaXNwbGF5OnRhYmxlfS5jbGVhcmZpeDphZnRlcntjbGVhcjpib3RofSp7LXdlYmtpdC1ib3gtc2l6aW5nOmJvcmRlci1ib3g7LW1vei1ib3gtc2l6aW5nOmJvcmRlci1ib3g7Ym94LXNpemluZzpib3JkZXItYm94fS52aXNpYmxlLWxnLWJsb2NrLC52aXNpYmxlLWxnLWlubGluZSwudmlzaWJsZS1sZy1pbmxpbmUtYmxvY2ssLnZpc2libGUtbWQtYmxvY2ssLnZpc2libGUtbWQtaW5saW5lLC52aXNpYmxlLW1kLWlubGluZS1ibG9jaywudmlzaWJsZS1zbS1ibG9jaywudmlzaWJsZS1zbS1pbmxpbmUsLnZpc2libGUtc20taW5saW5lLWJsb2NrLC52aXNpYmxlLXhzLWJsb2NrLC52aXNpYmxlLXhzLWlubGluZSwudmlzaWJsZS14cy1pbmxpbmUtYmxvY2t7ZGlzcGxheTpub25lIWltcG9ydGFudH1AbWVkaWEobWF4LXdpZHRoOjc2N3B4KXsudmlzaWJsZS14cy1ibG9ja3tkaXNwbGF5OmJsb2NrIWltcG9ydGFudH0udmlzaWJsZS14cy1pbmxpbmV7ZGlzcGxheTppbmxpbmUhaW1wb3J0YW50fS52aXNpYmxlLXhzLWlubGluZS1ibG9ja3tkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnR9fUBtZWRpYShtaW4td2lkdGg6NzY4cHgpIGFuZCAobWF4LXdpZHRoOjk5MXB4KXsudmlzaWJsZS1zbS1ibG9ja3tkaXNwbGF5OmJsb2NrIWltcG9ydGFudH0udmlzaWJsZS1zbS1pbmxpbmV7ZGlzcGxheTppbmxpbmUhaW1wb3J0YW50fS52aXNpYmxlLXNtLWlubGluZS1ibG9ja3tkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnR9fUBtZWRpYShtaW4td2lkdGg6OTkycHgpIGFuZCAobWF4LXdpZHRoOjExOTlweCl7LnZpc2libGUtbWQtYmxvY2t7ZGlzcGxheTpibG9jayFpbXBvcnRhbnR9LnZpc2libGUtbWQtaW5saW5le2Rpc3BsYXk6aW5saW5lIWltcG9ydGFudH0udmlzaWJsZS1tZC1pbmxpbmUtYmxvY2t7ZGlzcGxheTppbmxpbmUtYmxvY2shaW1wb3J0YW50fX1AbWVkaWEobWluLXdpZHRoOjEyMDBweCl7LnZpc2libGUtbGctYmxvY2t7ZGlzcGxheTpibG9jayFpbXBvcnRhbnR9LnZpc2libGUtbGctaW5saW5le2Rpc3BsYXk6aW5saW5lIWltcG9ydGFudH0udmlzaWJsZS1sZy1pbmxpbmUtYmxvY2t7ZGlzcGxheTppbmxpbmUtYmxvY2shaW1wb3J0YW50fX0ubXQtNSwubXQ1e21hcmdpbi10b3A6NXB4fS5tdC0xMCwubXQxMHttYXJnaW4tdG9wOjEwcHh9Lm10LTE1LC5tdDE1e21hcmdpbi10b3A6MTVweH0ubXQtMjAsLm10MjB7bWFyZ2luLXRvcDoyMHB4fS5tdC0zMCwubXQzMHttYXJnaW4tdG9wOjMwcHh9Lm1yLTUsLm1yNXttYXJnaW4tcmlnaHQ6NXB4fS5tci0xMCwubXIxMHttYXJnaW4tcmlnaHQ6MTBweH0ubXItMTUsLm1yMTV7bWFyZ2luLXJpZ2h0OjE1cHh9Lm1yLTIwLC5tcjIwe21hcmdpbi1yaWdodDoyMHB4fS5tci0zMCwubXIzMHttYXJnaW4tcmlnaHQ6MzBweH0ubWItNSwubWI1e21hcmdpbi1ib3R0b206NXB4fS5tYi0xMCwubWIxMHttYXJnaW4tYm90dG9tOjEwcHh9Lm1iLTE1LC5tYjE1e21hcmdpbi1ib3R0b206MTVweH0ubWItMjAsLm1iMjB7bWFyZ2luLWJvdHRvbToyMHB4fS5tYi0zMCwubWIzMHttYXJnaW4tYm90dG9tOjMwcHh9Lm1sLTUsLm1sNXttYXJnaW4tbGVmdDo1cHh9Lm1sLTEwLC5tbDEwe21hcmdpbi1sZWZ0OjEwcHh9Lm1sLTE1LC5tbDE1e21hcmdpbi1sZWZ0OjE1cHh9Lm1sLTIwLC5tbDIwe21hcmdpbi1sZWZ0OjIwcHh9Lm1sLTMwLC5tbDMwe21hcmdpbi1sZWZ0OjMwcHh9LnB1bGwtbGVmdHtmbG9hdDpsZWZ0fS5wdWxsLXJpZ2h0e2Zsb2F0OnJpZ2h0fS50ZXh0LW11dGVke2NvbG9yOiM3ZjhjOGR9LmNhcHRpb24tbXV0ZWR7Y29sb3I6Izg4OTlhNn0udGV4dC1wcmltYXJ5e2NvbG9yOiMzNDQ5NWV9LnRleHQtc3VjY2Vzc3tjb2xvcjojMmVjYzcxfS50ZXh0LWRhbmdlcntjb2xvcjojZTc0YzNjfS50ZXh0LWluZm97Y29sb3I6IzM0OThkYn0udGV4dC13YXJuaW5ne2NvbG9yOiNmMWM0MGZ9LmhpZGV7ZGlzcGxheTpub25lfS5pbnZpc2libGV7dmlzaWJpbGl0eTpoaWRkZW59LmlzLXZpc2libGUsLnZpc2libGV7dmlzaWJpbGl0eTp2aXNpYmxlfS5iNjRfcmlnaHR7YmFja2dyb3VuZC1pbWFnZTp1cmwoZGF0YTppbWFnZS9wbmc7YmFzZTY0LGlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFCQUFBQUFRQ0FZQUFBQWY4LzloQUFBQUNYQklXWE1BQUE3RUFBQU94QUdWS3c0YkFBQUJPRWxFUVZSNFhxVlRJV3ZEUUJoOUhWV0J3V3pFWUdvdzE5S3BRaUJxcnFLaVlxcFZrZjBCY1RYOUFTZHIyakJaTVJGUmUxQ1l5c2pjWURBb1RJUzV3Q0I2ZVhBWHNqdDJZZXpCbWUvNzN2ZmVGMTU2TUZCVjFRTEFIRUJvdENTQXhQTzhIVm80YXhHdjZwZm5uL2sybG5FWVBBUm9QOWJZNHd4bm9VQUhVSVY4L2JTK09Md2Y0TUxzWm9ibDdiSUVNS3pkbkxTRFI1R0pUakt4ZjkyRHMrU0FKL0RtMnRxQWpkOHd1WjRnaXpLazkybXpoQnh5NldET2dndlJLQUt4ZWQ3OGNFSXVGNFRIajZOVDNULzNVWHdWU045U2FDaE95QVYvVUxmUmh3SGVTY1dWWElFdzFDMllEcWlrbFR2VjlRSVpYQVpRb0JJVnFleFVWeHpKQlFuRFlidUFVMTF4RXAxRXBuQmdCY21keHBjNmlVT2R4R2s4amt2RFNWZVVwODFIWkthWmJXNFZkMExkWjkvTUhtYzRxemo0OSsvOERSZVRwOVM3dlk3dUFBQUFBRWxGVGtTdVFtQ0MpfS5iNjRfZXJyb3J7YmFja2dyb3VuZC1pbWFnZTp1cmwoZGF0YTppbWFnZS9wbmc7YmFzZTY0LGlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFCQUFBQUFRQ0FZQUFBQWY4LzloQUFBQUNYQklXWE1BQUE3RUFBQU94QUdWS3c0YkFBQUJQMGxFUVZSNFhxV1R2VW9EUVJTRnI0TkJXUkFEK2dBK2dNWG1EV0psRmJDM2lDQlkyUW0yWW10dEt5aFlhYUZ0R2lQWTJDV0ZEMkJqRXdpdWdrdjhYZWNiY21IbUVnVEpnVXMyMzcxbmRtWTRPeU5HWlZsdWVkRDIxVFN0VzE5bldaYWRLckRHRlYrOTE1dE9OZGhzVlUvTGtoU01IalBNcWkvc1lBeDZiM3M3OVkvTGMvbExjOXU3a2gwY0ZSNDAvRzRlM1poZmxZZjd3VnhielVOWktYOC9PUlptOGNBZFovNjZ2OHRwWUZ5NjdsTEpJcFl6aXdldjQ4SkdIc1J5aTNVZFZuTmdzZkRnNWZ6cFpUWHo2cnQ0cmhDLzBUTzlaQmJ2ckJoOVB2Umx1TEdXdlBYbnBZQ0ZucFdUS1dVWGlNL01tNm40VGlZR3FLdkJNZWZuLzBTbXdjTHJpT2U4RDRmS25sbnZCQllMRDE1Tklpbk1OVWpJWEZqTU5ZMTluOFJHRW1VU1JraitIV1VlQUt5NmNOR1IybnBMckdEMG1GRXpmT3JQK1JmNCt4VDhFc2t3TUFBQUFBQkpSVTVFcmtKZ2dnPT0pfWltZy5sYXp5e29wYWNpdHk6LjM7LXdlYmtpdC10cmFuc2Zvcm06c2NhbGUoLjkpOy1tb3otdHJhbnNmb3JtOnNjYWxlKC45KTt0cmFuc2Zvcm06c2NhbGUoLjkpOy13ZWJraXQtdHJhbnNpdGlvbjphbGwgZWFzZS1pbi1vdXQgLjNzOy1tb3otdHJhbnNpdGlvbjphbGwgZWFzZS1pbi1vdXQgLjNzO3RyYW5zaXRpb246YWxsIGVhc2UtaW4tb3V0IC4zczstd2Via2l0LXBlcnNwZWN0aXZlLW9yaWdpbjp0b3AgY2VudGVyOy1tb3otcGVyc3BlY3RpdmUtb3JpZ2luOnRvcCBjZW50ZXI7cGVyc3BlY3RpdmUtb3JpZ2luOnRvcCBjZW50ZXJ9aW1nLmxhenkuc2hvd3tvcGFjaXR5OjE7LXdlYmtpdC10cmFuc2Zvcm06c2NhbGUoMSk7LW1vei10cmFuc2Zvcm06c2NhbGUoMSk7dHJhbnNmb3JtOnNjYWxlKDEpfWJvZHkuZXJyb3ItcGFnZSBoZWFkZXJ7bWF4LXdpZHRoOjk5MHB4O2hlaWdodDo2MHB4O21hcmdpbjowIGF1dG87bGluZS1oZWlnaHQ6NjBweH1ib2R5LmVycm9yLXBhZ2UgLndyYXBwZXJ7d2lkdGg6MTAwJTtoZWlnaHQ6Y2FsYygxMDAlIC0gMTEwcHgpO3RleHQtYWxpZ246Y2VudGVyfWJvZHkuZXJyb3ItcGFnZSAud3JhcHBlciAubWFpbntwYWRkaW5nLXRvcDo3NXB4fWJvZHkuZXJyb3ItcGFnZSAud3JhcHBlciAubWFpbiBoMXtmb250LXNpemU6NC42cmVtO21hcmdpbi1ib3R0b206MjBweH1ib2R5LmVycm9yLXBhZ2UgLndyYXBwZXIgLm1haW4gcC5kaWUtbXNne2ZvbnQtc2l6ZToyLjVyZW19Ym9keS5lcnJvci1wYWdlIC53cmFwcGVyIC5tYWluICNsaW5rQmFja0hvbWV7cG9zaXRpb246cmVsYXRpdmU7bWFyZ2luLXRvcDo1MHB4O2Rpc3BsYXk6aW5saW5lLWJsb2NrO2ZvbnQtc2l6ZToyLjRyZW07Y29sb3I6IzQ1YjZmN31ib2R5LmVycm9yLXBhZ2UgZm9vdGVye2hlaWdodDo0MHB4O2xpbmUtaGVpZ2h0OjQwcHg7Zm9udC1zaXplOjEuNHJlbX1ib2R5LmVycm9yLXBhZ2UgZm9vdGVyIHB7cG9zaXRpb246cmVsYXRpdmV9Ym9keS5lcnJvci1wYWdlIGZvb3RlciBwIHNwYW4uY29weXtwb3NpdGlvbjpyZWxhdGl2ZTt0b3A6NHB4O2ZvbnQtc2l6ZToycmVtfTwvc3R5bGU+');
$kurl = get_url_root(strtolower($_SERVER['HTTP_HOST']));
$info = base64_decode(get_option('cute_extend'));
$info_key=explode('=BZG/M', $info);
if(!get_option('cute_extend') || base64_decode($info_key[0]) != md5($authorization) || time() - (base64_decode($info_key[1]) / 5) > 3600){
	if($json_get = file_get_contents(base64_decode('aHR0cDovL2F1dGgua3VhY2cuY29tL3Byb2R1Y3QvY3V0ZV9leHRlbmQucGhwP2FwaT1BUElfY3V0ZV9leHRlbmQmdXJsPQ==').$kurl.base64_decode('JnZhbHVlPWN1dGVfZXh0ZW5kJmF1dGhvcml6YXRpb249').$authorization)){
		$row_json=json_decode($json_get,true);
        $key=base64_decode($row_json['key']);
        $key=explode('=BZG/M', $key);
		if(!empty($authorization) && !empty($row_json['key']) && ($row_json['code'] == 'yes' && base64_decode($key[0]) == md5($authorization) && time() - (base64_decode($key[1]) / 5) <= 3600*24)){
			update_option('cute_extend', $row_json['key']);
            return true;
		}elseif($row_json['code'] != 'yes'){
          return false;
        }else{
          return false;
        }
	}else{
		return false;
    }
  }
  return true;
}

/**
 * 查询IP地址
 *
 * @since 2.0.0
 *
 * @param $ip
 *
 * @return array|mixed|object
 */
function ct_query_ip_addr($ip)
{
    $url = 'http://freeapi.ipip.net/'.$ip;
    $body = wp_remote_retrieve_body(wp_remote_get($url));
    $arr = json_decode($body);
    if ($arr[1] == $arr[2]) {
        array_splice($arr, 2, 1);
    }

    return implode($arr);
}

// Toggle content
function ct_sc_toggle_content($atts, $content = null)
{
    $content = do_shortcode($content);
    extract(shortcode_atts(array('hide' => 'no', 'title' => '', 'color' => ''), $atts));

    return '<div class="'.ct_conditional_class('toggle-wrap', $hide == 'no', 'show').'"><div class="toggle-click-btn" style="color:'.$color.'"><i class="tico tico-angle-right"></i>'.$title.'</div><div class="toggle-content">'.$content.'</div></div>';
}
add_shortcode('toggle', 'ct_sc_toggle_content');

// 插入商品短代码
function ct_sc_product($atts, $content = null)
{
    extract(shortcode_atts(array('id' => ''), $atts));
    if (!empty($id)) {
        $vm = EmbedProductVM::getInstance(intval($id));
        $data = $vm->modelData;
        if (!isset($data->product_id)) {
            return $id;
        }
        $templates = new League\Plates\Engine(CUTE_THEME_TPL.'/plates');
        $rating = $data->product_rating;
        $args = array(
            'thumb' => $data->product_thumb,
            'link' => $data->product_link,
            'name' => $data->product_name,
            'price' => $data->product_price,
            'currency' => $data->product_currency,
            'rating_value' => $rating['value'],
            'rating_count' => $rating['count'],
            'rating_percent' => $rating['percent'],
            'min_price' => $data->product_min_price,
            'discount' => $data->product_discount,
            'price_icon' => $data->price_icon,
            'product_views' => $data->product_views,
            'product_sales' => $data->product_sales,

        );

        return $templates->render('embed-product', $args);
    }

    return '';
}
add_shortcode('product', 'ct_sc_product');

// 插入文章短代码
function ct_sc_post($atts, $content = null){
    extract(shortcode_atts(array('id'=>''), $atts));
    if(!empty($id)) {
        $vm = EmbedPostVM::getInstance(intval($id));
        $data = $vm->modelData;
        $templates = new League\Plates\Engine(CUTE_THEME_TPL . '/plates');
        $args = array(
            'thumb' => $data->thumb,
            'post_link' => $data->post_link,
            'post_title' => $data->post_title,
            'comment_count' => $data->comment_count,
            'category' => $data->category,
            'author' => $data->author,
            'author_url' => $data->author_url,
            'time' => $data->time,
            'datetime' => $data->datetime,
            'description' => $data->description,
            'views' => $data->views,

        );
        return $templates->render('embed-post', $args);
    }
    return '';
}
add_shortcode('post', 'ct_sc_post');

// Button
function ct_sc_button($atts, $content = null){
    extract(shortcode_atts(array('class'=>'default','size'=>'default','href'=>'','title'=>''), $atts));
    if(!empty($href)) {
        return '<a class="btnhref" href="' . $href . '" title="' . $title . '" target="_blank"><button type="button" class="btn btn-' . $class .' btn-' . $size . '">' . $content . '</button></a>';
    }else{
        return '<button type="button" class="btn btn-' . $class . ' btn-' . $size . '">' . $content . '</button>';
    }
}
add_shortcode('button', 'ct_sc_button');

// Call out
function ct_sc_infoblock($atts, $content = null){
    $content = do_shortcode($content);
    extract(shortcode_atts(array('class'=>'info','title'=>''), $atts));
    return '<div class="contextual-callout callout-' . $class . '"><h4>' . $title . '</h4><p>' . $content . '</p></div>';
}
add_shortcode('callout', 'ct_sc_infoblock');

// Info bg
function ct_sc_infobg($atts, $content = null){
    $content = do_shortcode($content);
    extract(shortcode_atts(array('class'=>'info','closebtn'=>'no','bgcolor'=>'','color'=>'','showicon'=>'yes','title'=>''), $atts));
    $close_btn = $closebtn=='yes' ? '<span class="infobg-close"><i class="tico tico-close"></i></span>' : '';
    $div_class = $showicon!='no' ? 'contextual-bg bg-' . $class . ' showicon' : 'bg-' . $class . ' contextual-bg';
    $content = $title ? '<h4>' . $title . '</h4><p>' . $content . '</p>' : '<p>' . $content . '</p>';
    return '<div class="' . $div_class . '">' . $close_btn . $content . '</div>';
}
add_shortcode('infobg', 'ct_sc_infobg');

// Login to visual
function ct_sc_l2v( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) && !is_user_logged_in() ) return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限登录用户阅读此隐藏内容</div><p class="gray">请先登录</p><p class="fs12"><button class="user-login mr10">登录</button><button class="user-register"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button></p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">游客</span></span></div>';
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为隐藏内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttl2v', 'ct_sc_l2v' );

// Review to visual
function ct_sc_r2v( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            $user_id = get_current_user_id();
            global $post;
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                if(!ct_user_is_comment($post->ID)) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限评论用户阅读此隐藏内容</div><p class="gray">请先评论</p><p class="fs12"><button><a href="#respond" data-tooltip="评论">去评论</a></button></p></div> </div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为隐藏内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttr2v', 'ct_sc_r2v' );

// 付费可见
function ct_sc_sale_content( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id() ? get_current_user_id() : -1;
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                 $currency = get_post_meta($post->ID, 'tt_sale_content_currency', true); // 0 - credit 1 - cash
                 $price = get_post_meta($post->ID, 'tt_sale_content_price', true);
                 $sales = ct_count_post_orders($post->ID,0);
                 $currency_text = $currency == 1 ? '元' : CREDIT_NAME;
                 $currency = $currency == 1 ? 'cash' : 'credit';
                 $info_text = is_user_logged_in() ? '' : '<button class="user-login mr10">登录/注册</button>';
                 if(!is_user_logged_in() && !ct_get_option('tt_enable_no_login_down', true)){
                   return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">当前隐藏内容需要支付 <font color="#FF0000">'.$price.'</font> '.$currency_text.'</div><p class="t-c">已有 <font color="#FF0000">'.$sales.'</font> 人支付</p><p class="fs12"><button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button></p></div> </div>';
                 }
                if(!ct_check_bought_post_resources2($post->ID, '0')) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">当前隐藏内容需要支付 <font color="#FF0000">'.$price.'</font> '.$currency_text.'</div><p class="t-c">已有 <font color="#FF0000">'.$sales.'</font> 人支付</p><p class="fs12">'.$info_text.'<button class="user-register"><a class="buy-content" href="javascript:;" data-post-id="'.$post->ID.'" data-resource-seq="0" data-post-type="'.$currency.'">立即购买</a></button></p></div> </div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为付费内容：</span>'.$content.'</div>';
}
add_shortcode( 'tt_sale_content', 'ct_sc_sale_content' );

// 购买某商品可见
function ct_sc_sale_product( $atts, $content ){
    $content = do_shortcode($content);
    extract(shortcode_atts(array('id'=>''), $atts));
    if( !is_null( $content ) && !empty($id) ) :
        $product = get_post($id);
        if(!is_user_logged_in()){
            $content = '<div class="bg-lr2v contextual-bg bg-info"><i class="tico tico-group"></i>' . __('此处内容需要 <span class="user-login">登录</span> 并购买<a href="'.get_permalink($product).'"><i class="tico tico-cart"></i>此商品</a>才可见', 'tt') . '</div>';
        }else{
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                if(!ct_check_user_has_buy_product($id, $user_id)) {
                    $content = '<div class="bg-lr2v contextual-bg bg-info"><i class="tico tico-group"></i>' . __('此处内容需要购买<a href="'.get_permalink($product).'"><i class="tico tico-cart"></i>此商品</a>才可见' , 'tt'). '</div>';
                }
            }
        }
    endif;
    return $content;
}
add_shortcode( 'tt_sale_product', 'ct_sc_sale_product' );

// 会员可见
function ct_sc_vipv( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $button = !is_user_logged_in() ? '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>' : '<button class="open-vip" data-resource-seq="vip1">加入会员</button>';
                $info_text = is_user_logged_in() ? '请先加入会员' : '请先登录并加入会员';
                if(!$member->ct_is_vip()) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限以下用户组阅读此隐藏内容</div><span class="user-lv vip1 mr10">月费会员</span><span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span><p class="gray">'.$info_text.'</p><p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttvipv', 'ct_sc_vipv' );
// 月费会员可见
function ct_sc_vip1v( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $button = !is_user_logged_in() ? '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>' : '<button class="open-vip" data-resource-seq="vip1">加入会员</button>';
                $info_text = is_user_logged_in() ? '请先加入会员' : '请先登录并加入会员';
                if($member->vip_type < 1 || $member->vip_type == 9) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限以下用户组阅读此隐藏内容</div><span class="user-lv vip1 mr10">月费会员</span><span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span><p class="gray">'.$info_text.'</p><p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttvip1v', 'ct_sc_vip1v' );
// 年费会员可见
function ct_sc_vip2v( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $button = !is_user_logged_in() ? '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>' : '<button class="open-vip" data-resource-seq="vip2">加入会员</button>';
                $info_text = is_user_logged_in() ? '请先加入年费会员以上' : '请先登录并加入年费会员以上';
                if($member->vip_type < 2 || $member->vip_type == 9) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限以下用户组阅读此隐藏内容</div><span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span><p class="gray">'.$info_text.'</p><p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttvip2v', 'ct_sc_vip2v' );
// 永久会员可见
function ct_sc_vip3v( $atts, $content ){
    $content = do_shortcode($content);
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $button = !is_user_logged_in() ? '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>' : '<button class="open-vip" data-resource-seq="vip3">加入会员</button>';
                $info_text = is_user_logged_in() ? '请先加入永久会员' : '请先登录并加入永久会员';
                if($member->vip_type != 3) {
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限以下用户组阅读此隐藏内容</div><span class="user-lv vip3 mr10">永久会员</span><p class="gray">'.$info_text.'</p><p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttvip3v', 'ct_sc_vip3v' );

// 组合短代码
function ct_sc_custom( $atts, $content ){
    $content = do_shortcode($content);
    extract(shortcode_atts(array('vip'=>'1'), $atts));
    if( !is_null( $content ) ) :
            global $post;
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $currency = get_post_meta($post->ID, 'tt_sale_content_currency', true); // 0 - credit 1 - cash
                $price = get_post_meta($post->ID, 'tt_sale_content_price', true);
                $sales = ct_count_post_orders($post->ID,0);
                $currency = $currency == 1 ? 'cash' : 'credit';
                $currency_text = $currency == 'cash' ? '元' : CREDIT_NAME;
                if(!is_user_logged_in() && ($price <= 0 || $currency == 'credit' || !ct_get_option('tt_enable_no_login_down', true))){
                      $button = '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>';
                    }elseif(!is_user_logged_in() && ct_get_option('tt_enable_no_login_down', true) && $price > 0 && $currency == 'cash'){
                      $button = '<button class="user-register user-login mr10">登录</button><button class="user-register"><a class="buy-content" href="javascript:;" data-post-id="'.$post->ID.'" data-resource-seq="0" data-post-type="'.$currency.'">立即购买</a></button>';
                    }elseif(is_user_logged_in() && $price > 0){
                      $button = '<button class="open-vip mr10" data-resource-seq="vip1">加入会员</button><button class="user-register"><a class="buy-content" href="javascript:;" data-post-id="'.$post->ID.'" data-resource-seq="0" data-post-type="'.$currency.'">立即购买</a></button>';
                    }else{
                      $button = '<button class="open-vip mr10" data-resource-seq="vip1">加入会员</button>';
                    }
                if(($member->vip_type < $vip || $member->vip_type == 9) && $price > 0 && !ct_check_bought_post_resources2($post->ID, '0')) {
                    $new_content =  '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c"><div class="mb10">仅限以下用户组阅读此隐藏内容</div>';
                    if($vip == 1){
                      $new_content .= '<span class="user-lv vip1 mr10">月费会员</span><span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span>';
                    }elseif($vip == 2){
                      $new_content .= '<span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span>';
                    }else{
                      $new_content .= '<span class="user-lv vip3 mr10">永久会员</span>';
                    }
                    $new_content .= '<div class="mb10"></div><div class="mb10">或者支付 <font color="#FF0000">'.$price.'</font> '.$currency_text.'查看剩余内容</div><p class="t-c">已有 <font color="#FF0000">'.$sales.'</font> 人支付</p>';
                    $new_content .= '<p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                    return $new_content;
                }
            }
    endif;
    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
}
add_shortcode( 'ttcustom', 'ct_sc_custom' );
// Pre tag
function ct_to_pre_tag($atts, $content)
{
    return '<div class="precode clearfix"><pre class="lang:default decode:true " >'.str_replace('#038;', '', htmlspecialchars($content, ENT_COMPAT, 'UTF-8')).'</pre></div>';
}
add_shortcode('php', 'ct_to_pre_tag');

/**
 * 检查用户是否购买了文章内付费资源.
 *
 * @since 2.0.0
 *
 * @param $post_id
 * @param $resource_seq
 *
 * @return bool
 */
function ct_check_bought_post_resources($post_id, $resource_seq)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    }

    $user_bought = get_user_meta($user_id, 'tt_bought_posts', true);
    if (empty($user_bought)) {
        return false;
    }
    $user_bought = maybe_unserialize($user_bought);
    if (!isset($user_bought['p_'.$post_id])) {
        return false;
    }

    $post_bought_resources = $user_bought['p_'.$post_id];
    if (isset($post_bought_resources[$resource_seq]) && $post_bought_resources[$resource_seq]) {
        return true;
    }

    return false;
}

/**
 * 获取用户的对某一文章下资源的订单(只返回成功的订单).
 *
 * @since 2.0.0
 *
 * @param int $user_id
 * @param int $post_id
 *
 * @return array|null|object
 */
function ct_get_user_post_resource_orders($user_id, $post_id)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) {
        return null;
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $sql = sprintf("SELECT * FROM $orders_table WHERE `deleted`=0 AND `user_id`=%d AND `product_id`=%d AND `order_status`=4 ORDER BY `id` DESC", $user_id, $post_id);
    $results = $wpdb->get_results($sql);

    return $results;
}

/**
 * 购买文章内容资源.
 *
 * @since 2.0.0
 *
 * @param $post_id
 * @param $resource_seq
 * @param $is_new_type
 *
 * @return WP_Error|array
 */
function ct_bought_post_resource($post_id, $resource_seq, $is_new_type = false) {
    $user = wp_get_current_user();
    $user_id = $user->ID;
    if(!$user_id) {
        return new WP_Error('user_not_signin', __('You must sign in to continue your purchase', 'tt'), array('status' => 401));
    }
    $cat_ID = get_the_category($post_id)[0]->term_id;
    $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
    //检查文章资源是否存在
    if($resource_seq != '0') {
    $resource_meta_key = $is_new_type ? 'tt_sale_dl2' : 'tt_sale_dl';
    $post_resources = explode(PHP_EOL, trim(get_post_meta($post_id, $resource_meta_key, true)));
    if(!isset($post_resources[$resource_seq - 1])) {
        return new WP_Error('post_resource_not_exist', __('The resource you willing to buy is not existed', 'tt'), array('status' => 404));
    }
    $the_post_resource = explode('|', $post_resources[$resource_seq - 1]);
    // <!-- 资源名称|资源下载url1_密码1,资源下载url2_密码2|资源价格|币种 -->
    $currency = $is_new_type && isset($the_post_resource[3]) && strtolower(trim($the_post_resource[3]) === 'cash') ? 'cash' : 'credit';
    $price = isset($the_post_resource[2]) ? abs(trim($the_post_resource[2])) : 1;
    $resource_name = $the_post_resource[0];
    if ($is_new_type) {
        $pans = explode(',', $the_post_resource[1]);
        $pan_detail = explode('__', $pans[0]);
        $resource_link = $pan_detail[0];
        $resource_pass = isset($pan_detail[1]) ? trim($pan_detail[1]) : __('None', 'tt');
        $resource_pass2 = isset($the_post_resource[4]) ? trim($the_post_resource[4]) : __('None', 'tt');
    } else {
        $resource_link = $the_post_resource[1];
        $resource_pass = isset($the_post_resource[3]) ? trim($the_post_resource[3]) : __('None', 'tt');
    }
    } elseif($term_meta['tax_free_img']) {
    $resource_name = '付费显示内容';
    $currency = $term_meta['tax_img_currency']; // 0 - credit 1 - cash
    $price = $term_meta['tax_img_price'];
    $currency =isset($currency) && $currency == 1 ? 'cash' : 'credit';
    } else {
    $resource_name = '付费显示内容';
    $currency = get_post_meta($post_id, 'tt_sale_content_currency', true); // 0 - credit 1 - cash
    $price = get_post_meta($post_id, 'tt_sale_content_price', true);
    $currency =isset($currency) && $currency == 1 ? 'cash' : 'credit';
     }
    //检查是否已购买
    if($is_new_type ? ct_check_bought_post_resources2($post_id, $resource_seq) : ct_check_bought_post_resources($post_id, $resource_seq)) {
        return new WP_Error('post_resource_bought', __('You have bought the resource yet, do not repeat a purchase', 'tt'), array('status' => 200));
    }

    // 先计算VIP价格优惠
    $member = new Member($user);
    $vip_price = $price;
    $vip_type = $member->vip_type;
    $tt_monthly_vip_down_count = ct_get_option('tt_monthly_vip_down_count');
    $tt_annual_vip_down_count = ct_get_option('tt_annual_vip_down_count');
    $tt_permanent_vip_down_count = ct_get_option('tt_permanent_vip_down_count');
    $vip_down_count = (int) get_user_meta($user_id, 'tt_vip_down_count', true);
    switch ($vip_type) {
        case Member::MONTHLY_VIP:
            $vip_price = $is_new_type ? round(ct_get_option('tt_monthly_vip_discount', 100) * $price / 100,2) : absint(ct_get_option('tt_monthly_vip_discount', 100) * $price / 100);
            if($tt_monthly_vip_down_count > 0 && $vip_down_count >= $tt_monthly_vip_down_count){
            $vip_price = $price;
            }
            break;
        case Member::ANNUAL_VIP:
            $vip_price = $is_new_type ? round(ct_get_option('tt_annual_vip_discount', 90) * $price / 100,2) : absint(ct_get_option('tt_annual_vip_discount', 90) * $price / 100);
            if($tt_annual_vip_down_count > 0 && $vip_down_count >= $tt_annual_vip_down_count){
            $vip_price = $price;
            }
            break;
        case Member::PERMANENT_VIP:
            $vip_price = $is_new_type ? round(ct_get_option('tt_permanent_vip_discount', 80) * $price / 100,2) : absint(ct_get_option('tt_permanent_vip_discount', 80) * $price / 100);
            if($tt_permanent_vip_down_count > 0 && $vip_down_count >= $tt_permanent_vip_down_count){
            $vip_price = $price;
            }
            break;
    }
    $vip_string = ct_get_member_type_string($vip_type);

    if ($is_new_type) {
        $create = ct_create_resource_order($post_id, $resource_name, $resource_seq, $vip_price, $currency === 'cash');
        if ($create instanceof WP_Error) {
            return $create;
        } elseif (!$create) {
            return new WP_Error('create_order_failed', __('Create order failed', 'tt'), array('status' => 403));
        }
        $checkout_nonce = wp_create_nonce('checkout');
        $checkout_url = add_query_arg(array('oid' => $create['order_id'], 'spm' => $checkout_nonce), ct_url_for('checkout'));
        if ($vip_price - 0 >= 0.01) {
            $create['url'] = $checkout_url;
        } else {
            $create = array_merge($create, array(
                'cost' => 0,
                'text' => sprintf(__('消费: %1$d (%2$s优惠, 原价%3$d)', 'tt'), $vip_price, $vip_string, $price),
                'vip_str' => $vip_string
            ));
        }
        return ct_api_success(__('Create order successfully', 'tt'), array('data' => $create));
    } else {
        //检查用户积分是否足够
        $payment = ct_credit_pay($vip_price, $resource_name, true);
        if($payment instanceof WP_Error) {
            return $payment;
        }

        $user_bought = get_user_meta($user_id, 'tt_bought_posts', true);
        if(empty($user_bought)){
            $user_bought = array(
                'p_' . $post_id => array($resource_seq => true)
            );
        }else{
            $user_bought = maybe_unserialize($user_bought);
            if(!isset($user_bought['p_' . $post_id])) {
                $user_bought['p_' . $post_id] = array($resource_seq => true);
            }else{
                $buy_seqs = $user_bought['p_' . $post_id];
                $buy_seqs[$resource_seq] = true;
                $user_bought['p_' . $post_id] = $buy_seqs;
            }
        }
        $save = maybe_serialize($user_bought);
        $update = update_user_meta($user_id, 'tt_bought_posts', $save);
        if(!$update){
            return new WP_Error('post_resource_bought_failure', __('Failed to buy the resource, or maybe you have bought before', 'tt'), array('status' => 403));
        }

        // 发送邮件
        $subject = __('Payment for the resource finished successfully', 'tt');
        $balance = get_user_meta($user_id, 'tt_credits', true);
        if($resource_seq != '0') {
        $args = array(
            'adminEmail' => get_option('admin_email'),
            'resourceName' => $resource_name,
            'resourceLink' => $resource_link,
            'resourcePass' => $resource_pass,
            'spentCredits' => $price,
            'creditsBalance' => $balance
        );
        }else{
         $args = array(
            'adminEmail' => get_option('admin_email'),
            'resourceName' => '付费查看内容',
            'resourceLink' => '无',
            'resourcePass' => '无',
            'spentCredits' => $price,
            'creditsBalance' => $balance
        );
        }
        cute_async_mail('', $user->user_email, $subject, $args, 'buy-resource');

        if($price - $vip_price > 0) {
            $text = sprintf(__('消费%5$s: %1$d (%2$s优惠, 原价%3$d)<br>当前%5$s余额: %4$d', 'tt'), $vip_price, $vip_string, $price, $balance,CREDIT_NAME);
            $cost = $vip_price;
        }else{
            $text = sprintf(__('消费%3$s: %1$d<br>当前%3$s余额: %2$d', 'tt'), $price, $balance,CREDIT_NAME);
            $cost = $price;
        }
        return array(
            'cost' => $cost,
            'text' => $text,
            'vip_str' => $vip_string,
            'balance' => $balance
        );
    }
}

/**
 * 游客购买文章内容资源.
 *
 * @since 2.0.0
 *
 * @param $post_id
 * @param $resource_seq
 * @param $is_new_type
 *
 * @return WP_Error|array
 */
function ct_no_login_bought_post_resource($post_id, $resource_seq) {
    //检查是否已购买
    if(ct_check_bought_post_resources2($post_id, $resource_seq)) {
        return new WP_Error('post_resource_bought', __('You have bought the resource yet, do not repeat a purchase', 'tt'), array('status' => 200));
    }
    $cat_ID = get_the_category($post_id)[0]->term_id;
    $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
    //检查文章资源是否存在
    if($resource_seq != '0') {
    $resource_meta_key = 'tt_sale_dl2';
    $post_resources = explode(PHP_EOL, trim(get_post_meta($post_id, $resource_meta_key, true)));
    if(!isset($post_resources[$resource_seq - 1])) {
        return new WP_Error('post_resource_not_exist', __('The resource you willing to buy is not existed', 'tt'), array('status' => 404));
    }
    $the_post_resource = explode('|', $post_resources[$resource_seq - 1]);
    $price = isset($the_post_resource[2]) ? abs(trim($the_post_resource[2])) : 1;
    $resource_name = $the_post_resource[0];
    } elseif($term_meta['tax_free_img']) {
    $resource_name = '付费显示内容';
    $currency = $term_meta['tax_img_currency']; // 0 - credit 1 - cash
    $price = $term_meta['tax_img_price'];
    $currency =isset($currency) && $currency == 1 ? 'cash' : 'credit';
    } else {
    $resource_name = '付费显示内容';
    $currency = get_post_meta($post_id, 'tt_sale_content_currency', true);
    $price = get_post_meta($post_id, 'tt_sale_content_price', true);
     }
    $create = ct_create_resource_order($post_id, $resource_name, $resource_seq, $price, 'cash');
    if ($create instanceof WP_Error) {
        return $create;
    } elseif (!$create) {
        return new WP_Error('create_order_failed', __('Create order failed', 'tt'), array('status' => 403));
    }
    return ct_api_success(__('Create order successfully', 'tt'), array('data' => $create));
}

/**
 * 内嵌资源积分返还给作者.
 *
 * @since 2.6.0
 */
function ct_add_bought_resource_rewards($order_id) {
    $order = ct_get_order($order_id);
    if (!$order || $order->order_status != OrderStatus::TRADE_SUCCESS) {
        return;
    }
    preg_match('/([0-9]+)_([0-9]+)/i', $order_id, $matches);
    if (!$matches || count($matches) < 3) {
        return;
    }
    $resource_seq = $matches[2] * 1;
    $product_id = $order->product_id;
    $author_id = get_post_field('post_author', $product_id);
    $post_resources = explode(PHP_EOL, trim(get_post_meta($product_id, 'tt_sale_dl2', true)));
    if (!isset($post_resources[$resource_seq - 1]) && $resource_seq != 0) {
        return false;
    }
    $currency = $order->order_currency;
    $price = $order->order_total_price * 1;
    $resource_name = $order->product_name;
    $add_ratio = ct_get_option('tt_bought_resource_rewards_ratio', 100) / 100;
    if ($currency == 'cash') {
        $ratio = ct_get_option('tt_hundred_credit_price', 100);
        $price = $price * $ratio * $add_ratio;
    } else {
        $price = intval($price * $add_ratio);
    }
    return ct_update_user_credit($author_id, $price, sprintf(__('付费资源>>%1$s<<售出奖励%2$d%3$s', 'tt'), $resource_name, $price,CREDIT_NAME), false);
}
add_action('ct_order_status_change', 'ct_add_bought_resource_rewards');

/**
 * 创建资源订单(文章内嵌资源对接订单系统).
 *
 * @since 2.4.0
 *
 * @param $product_id
 * @param string $product_name
 * @param number $resource_seq
 * @param $order_price
 * @param $is_cash
 *
 * @return bool|array
 */
function ct_create_resource_order($product_id, $product_name, $resource_seq, $order_price = 1, $is_cash)
{
    $user_id = get_current_user_id() ? get_current_user_id() : -1;
    $order_id = ct_generate_order_num().'_'.$resource_seq;
    $order_time = current_time('mysql');
    $currency = $is_cash ? 'cash' : 'credit';
    $order_quantity = 1;
    $order_total_price = $order_price;

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $insert = $wpdb->insert(
        $orders_table,
        array(
            'parent_id' => 0,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'order_time' => $order_time,
            'order_price' => $order_price,
            'order_currency' => $currency,
            'order_quantity' => $order_quantity,
            'order_total_price' => $order_total_price,
            'user_id' => $user_id,
            'order_status' => OrderStatus::WAIT_PAYMENT,
        ),
        array(
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%f',
            '%s',
            '%d',
            '%f',
            '%d',
            '%d',
        )
    );
    if ($insert) {
        $tt_vip_down_count = get_user_meta($user_id, 'tt_vip_down_count', true);
        update_user_meta($user_id, 'tt_vip_down_count', (int) $tt_vip_down_count + 1);
        if ($currency == 'cash') {
            do_action('ct_order_status_change', $order_id);
        }

        return array(
            'insert_id' => $wpdb->insert_id,
            'order_id' => $order_id,
            'total_price' => $order_total_price,
        );
    }

    return false;
}

/**
 * 检查用户是否购买了文章内付费资源.
 *
 * @since 2.0.0
 *
 * @param $post_id
 * @param $resource_seq
 *
 * @return bool
 */
function ct_check_bought_post_resources2($post_id, $resource_seq)
{
    $user_id = get_current_user_id() ? get_current_user_id() : -1;
    if(!$user_id && !isset($_COOKIE['ttpay_' . $post_id])){
      return false;
    }
    $orders = ct_get_user_post_resource_orders($user_id, $post_id);
    if (count($orders) == 0) {
        return false;
    }
    $suffix = '_'.$resource_seq;
    $length = strlen($suffix);
    foreach ($orders as $order) {
        $order_id = $order->order_id;
        if(!is_user_logged_in()){
         $order_id = ct_decrypt($_COOKIE['ttpay_' . $post_id], ct_get_option('tt_private_token'));
        }
        if (substr($order_id, -1 * $length) == $suffix) {
            return true;
        }
    }

    return false;
}

/**
 * 获取文章内嵌的资源列表.
 *
 * @param $post_id
 *
 * @return array
 */
function ct_get_post_sale_resources($post_id)
{
    $sale_dls = trim(get_post_meta($post_id, 'tt_sale_dl2', true));
    $sale_dls = !empty($sale_dls) ? explode(PHP_EOL, $sale_dls) : array();
    $resources = array();
    $seq = 0;
    foreach ($sale_dls as $sale_dl) {
        $sale_dl = explode('|', $sale_dl);
        if (count($sale_dl) < 3) {
            continue;
        } else {
            ++$seq;
        }
        $resource = array();
        $resource['seq'] = $seq;
        $resource['name'] = $sale_dl[0];
        $pans = explode(',', $sale_dl[1]);
        $downloads = array();
        foreach ($pans as $pan) {
            $pan_details = explode('__', $pan);
            array_push($downloads, array(
               'url' => $pan_details[0],
                'password' => $pan_details[1],
                'password2' => $sale_dl[4],
            ));
        }
        $resource['downloads'] = $downloads;
        $resource['price'] = isset($sale_dl[2]) ? trim($sale_dl[2]) : 1;
        $resource['currency'] = strtolower(trim($sale_dl[3]));
        array_push($resources, $resource);
    }

    return $resources;
}

/**
 * 获取并生成文章内嵌资源的HTML内容用于邮件发送
 *
 * @param $post_id
 * @param $seq
 *
 * @return array|string
 */
function ct_get_post_download_content($post_id, $seq)
{
    $content = '';
    $resources = ct_get_post_sale_resources($post_id);
    if ($seq == 0 || $seq > count($resources)) {
        return $content;
    }
    $resource = $resources[$seq - 1];
    $downloads = $resource['downloads'];
    foreach ($downloads as $download) {
        $content .= sprintf(__('<li style="margin: 0 0 10px 0;"><p style="padding: 5px 0; margin: 0;">%1$s</p><p style="padding: 5px 0; margin: 0;">下载链接：<a href="%2$s" title="%1$s" target="_blank">%2$s</a> 提取密码：%3$s 解压密码：%4$s</p></li>', 'tt'), $resource['name'], $download['url'], $download['password'],$download['password2']);
    }

    return $content;
}

/**
 * 给上传的图片生成独一无二的图片名.
 *
 * @since 2.0.0
 *
 * @param $filename
 * @param $type
 *
 * @return string
 */
function ct_unique_img_name($filename, $type)
{
    $tmp_name = mt_rand(10, 25).time().$filename;
    $ext = str_replace('image/', '', $type);

    return md5($tmp_name).'.'.$ext;
}

/**
 * 获取图片信息.
 *
 * @since 2.0.0
 *
 * @param $img
 *
 * @return array|bool
 */
function ct_get_img_info($img)
{
    $imageInfo = getimagesize($img);
    if ($imageInfo !== false) {
        $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
        $info = array(
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageType,
            'mime' => $imageInfo['mime'],
        );

        return $info;
    } else {
        return false;
    }
}

/**
 * 裁剪图片并转换为JPG.
 *
 * @since 2.0.0
 *
 * @param $ori
 * @param string $dst
 * @param int    $dst_width
 * @param int    $dst_height
 * @param bool   $delete_ori
 */
function ct_resize_img($ori, $dst = '', $dst_width = 100, $dst_height = 100, $delete_ori = false)
{ //绝对路径, 带文件名

    $original_ratio = $dst_width / $dst_height;
    $info = ct_get_img_info($ori);

    if ($info) {
        if ($info['type'] == 'jpg' || $info['type'] == 'jpeg') {
            $im = imagecreatefromjpeg($ori);
        }
        if ($info['type'] == 'gif') {
            $im = imagecreatefromgif($ori);
        }
        if ($info['type'] == 'png') {
            $im = imagecreatefrompng($ori);
        }
        if ($info['type'] == 'bmp') {
            $im = imagecreatefromwbmp($ori);
        }
        if ($info['width'] / $info['height'] > $original_ratio) {
            $height = intval($info['height']);
            $width = $height * $original_ratio;
            $x = ($info['width'] - $width) / 2;
            $y = 0;
        } else {
            $width = intval($info['width']);
            $height = $width / $original_ratio;
            $x = 0;
            $y = ($info['height'] - $height) / 2;
        }
        $new_img = imagecreatetruecolor($width, $height);
        imagecopy($new_img, $im, 0, 0, $x, $y, $info['width'], $info['height']);
        $scale = $dst_width / $width;
        $target = imagecreatetruecolor($dst_width, $dst_height);
        $final_w = intval($width * $scale);
        $final_h = intval($height * $scale);
        imagecopyresampled($target, $new_img, 0, 0, 0, 0, $final_w, $final_h, $width, $height);
        imagejpeg($target, $dst ?: $ori);
        imagedestroy($im);
        imagedestroy($new_img);
        imagedestroy($target);

        if ($delete_ori) {
            unlink($ori);
        }
    }

    return;
}

function ct_copy_img($ori, $dst = '', $delete_ori = false)
{

    $info = ct_get_img_info($ori);

    if ($info) {
        if ($info['type'] == 'jpg' || $info['type'] == 'jpeg') {
            $im = imagecreatefromjpeg($ori);
        }
        if ($info['type'] == 'gif') {
            $im = imagecreatefromgif($ori);
        }
        if ($info['type'] == 'png') {
            $im = imagecreatefrompng($ori);
        }
        if ($info['type'] == 'bmp') {
            $im = imagecreatefromwbmp($ori);
        }

        $new_img = imagecreatetruecolor($info['width'], $info['height']);
        imagecopy($new_img, $im, 0, 0, 0, 0, $info['width'], $info['height']);
        $scale = 1;
        $target = imagecreatetruecolor($info['width'], $info['height']);
        $final_w = intval($info['width'] * $scale);
        $final_h = intval($info['height'] * $scale);
        imagecopyresampled($target, $new_img, 0, 0, 0, 0, $final_w, $final_h, $info['width'], $info['height']);
        imagejpeg($target, $dst ?: $ori);
        imagedestroy($im);
        imagedestroy($new_img);
        imagedestroy($target);

        if ($delete_ori) {
            unlink($ori);
        }
    }

    return;
}

/**
 * 根据头像上传配置用户头像类型并清理对应VM缓存.
 *
 * @since 2.0.0
 *
 * @param int $user_id
 */
function ct_update_user_avatar_by_upload($user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    update_user_meta($user_id, 'tt_avatar_type', 'custom');

    //删除VM缓存
    //ct_clear_cache_key_like('ct_cache_daily_vm_MeSettingsVM_user' . $user_id);
    delete_transient('ct_cache_daily_vm_MeSettingsVM_user'.$user_id);
    //ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM_author' . $user_id);
    delete_transient('ct_cache_daily_vm_UCProfileVM_author'.$user_id);
    //删除头像缓存
    //ct_clear_cache_key_like('ct_cache_daily_avatar_' . strval($user_id));
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'small'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'medium'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'large'.Utils::getCurrentDateTimeStr('day')));
}

/**
 * 根据头像上传配置用户背景图类型并清理对应VM缓存.
 *
 * @since 2.6.0
 *
 * @param int $user_id
 * @param string $meta
 */
function ct_update_user_cover_by_upload($user_id = 0, $meta)
{
    $user_id = $user_id ?: get_current_user_id();
    update_user_meta($user_id, 'tt_user_cover', $meta);

    //删除VM缓存
    //ct_clear_cache_key_like('ct_cache_daily_vm_MeSettingsVM_user' . $user_id);
    delete_transient('ct_cache_daily_vm_MeSettingsVM_user'.$user_id);
    //ct_clear_cache_key_like('ct_cache_daily_vm_UCProfileVM_author' . $user_id);
    delete_transient('ct_cache_daily_vm_UCProfileVM_author'.$user_id);
}

/**
 * 开放平台登录后清理头像等资料缓存.
 *
 * @param $user_id
 * @param string $avatar_type
 */
function ct_update_user_avatar_by_oauth($user_id, $avatar_type = 'qq')
{
    if (!$user_id) {
        return;
    }

    //update_user_meta($user_id, 'tt_avatar_type', $avatar_type); //TODO filter $avatar_type

    //删除VM缓存
    delete_transient('ct_cache_daily_vm_MeSettingsVM_user'.$user_id);
    delete_transient('ct_cache_daily_vm_UCProfileVM_author'.$user_id);
    //删除头像缓存
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'small'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'medium'.Utils::getCurrentDateTimeStr('day')));
    delete_transient('ct_cache_daily_avatar_'.$user_id.'_'.md5(strval($user_id).'large'.Utils::getCurrentDateTimeStr('day')));
}

/**
 * 判断用户是否已经绑定了开放平台账户.
 *
 * @since 2.0.0
 *
 * @param string $type
 * @param int    $user_id
 *
 * @return bool
 */
function ct_has_connect($type = 'qq', $user_id = 0)
{
    $user_id = $user_id ?: get_current_user_id();
    switch ($type) {
        case 'qq':
            $instance = new OpenQQ($user_id);

            return $instance->isOpenConnected();
        case 'weibo':
            $instance = new OpenWeibo($user_id);

            return $instance->isOpenConnected();
        case 'weixin':
            $instance = new OpenWeiXin($user_id);

            return $instance->isOpenConnected();
        default :
            return false;
    }

    return false;
}

/**
 * 执行向API发送的action.
 *
 * @since 2.0.0
 *
 * @param $action
 *
 * @return WP_Error|WP_REST_Response
 */
function get_rand($proArr) {
  $result = '';

  //概率数组的总概率精度
  $proSum = array_sum($proArr);

  //概率数组循环
  foreach ($proArr as $key => $proCur) {
    $randNum = mt_rand(1, $proSum);
    if ($randNum <= $proCur) {
      $result = $key;
      break;
    } else {
      $proSum -= $proCur;
    }
  }
  unset ($proArr);

  return $result;
}
function ct_exec_api_actions($action)
{
    switch ($action) {
        case 'daily_sign':
            $result = ct_daily_sign();
            if ($result instanceof WP_Error) {
                return $result;
            }
            if ($result) {
                return ct_api_success(sprintf(__('签到成功，获得 %d %s', 'tt'), (int) ct_get_option('tt_daily_sign_credits', 10),CREDIT_NAME));
            }
            break;
        case 'credits_charge':
            $min_credit_price = ct_get_option('tt_hundred_min_credit_price',5);
            if ($_POST['amount'] < $min_credit_price) {
                return ct_api_fail('最低' . $min_credit_price . '元起充');
            }
            $charge_order = ct_create_credit_charge_order(get_current_user_id(), $_POST['amount']);
            if (!$charge_order) {
                return ct_api_fail(CREDIT_NAME.'充值订单创建失败');
            } elseif (is_array($charge_order) && isset($charge_order['order_id'])) {
                $checkout_nonce = wp_create_nonce('checkout');
                $checkout_url = add_query_arg(array('oid' => $charge_order['order_id'], 'spm' => $checkout_nonce), ct_url_for('checkout'));
                $charge_order['url'] = $checkout_url;
                return ct_api_success(__('Create order successfully', 'tt'), array('data' => $charge_order));
            }
            break;
        case 'credits_lottery':
            $user_id = get_current_user_id();
            $credit_price = ct_get_option('tt_lottery_credit_price',10);
            $user_credit = (int) get_user_meta($user_id, 'tt_credits', true);
            if($credit_price>$user_credit){
              return ct_api_fail(CREDIT_NAME.'不足，请先充值！');
            }
            $amount = '-' . $credit_price;
            $result = ct_update_user_credit($user_id, $amount,sprintf(__('抽奖活动消耗%1$d%2$s', 'tt'), abs($amount),CREDIT_NAME), true);
            if ($result) {
                $prize_arr = array(
                '0' => array('id'=>0,'v'=>ct_get_option('ct_credit_lottery_chance_1')),
                '1' => array('id'=>1,'v'=>ct_get_option('ct_credit_lottery_chance_2')),
                '2' => array('id'=>2,'v'=>ct_get_option('ct_credit_lottery_chance_3')),
                '3' => array('id'=>3,'v'=>ct_get_option('ct_credit_lottery_chance_4')),
                '4' => array('id'=>4,'v'=>ct_get_option('ct_credit_lottery_chance_5')),
                '5' => array('id'=>5,'v'=>ct_get_option('ct_credit_lottery_chance_6')),
                '6' => array('id'=>6,'v'=>ct_get_option('ct_credit_lottery_chance_7')),
                '7' => array('id'=>7,'v'=>ct_get_option('ct_credit_lottery_chance_8')),
              );
              foreach ($prize_arr as $key => $val) {
                $arr[$val['id']] = $val['v'];
              }
              $data['value'] = get_rand($arr);
              $data['credit'] = (int) get_user_meta($user_id, 'tt_credits', true);
              ct_lottery_give($data['value']);
              return ct_api_success(__('抽奖成功', 'tt'), array('data' => $data));
            }
            break;
        case 'add_credits':
            $user_id = absint($_POST['uid']);
            $amount = absint($_POST['num']);
            $result = ct_update_user_credit($user_id, $amount, '', true);
            if ($result) {
                return ct_api_success('更新用户'.CREDIT_NAME.'成功');
            }

            return ct_api_fail('更新用户'.CREDIT_NAME.'失败');
        case 'take_credits':
            $user_id = absint($_POST['uid']);
            $amount = '-' . $_POST['num'];
            $result = ct_update_user_credit($user_id, $amount, '', true);
            if ($result) {
                return ct_api_success('更新用户'.CREDIT_NAME.'成功');
            }

            return ct_api_fail('更新用户'.CREDIT_NAME.'失败');
        case 'add_cash':
            $user_id = absint($_POST['uid']);
            $amount = absint($_POST['num']);
            $result = ct_update_user_cash($user_id, $amount, '', true);
            if ($result) {
                return ct_api_success(__('Update user cash successfully', 'tt'));
            }

            return ct_api_fail(__('Update user cash failed', 'tt'));
        case 'take_cash':
            $user_id = absint($_POST['uid']);
            $amount = '-' . $_POST['num'];
            $result = ct_update_user_cash($user_id, $amount, '', true);
            if ($result) {
                return ct_api_success(__('Update user cash successfully', 'tt'));
            }

            return ct_api_fail(__('Update user cash failed', 'tt'));
        case 'user_tixian':
            if(!get_user_meta(get_current_user_id(), 'tt_alipay_email', true)){
              return ct_api_fail(__('提现失败，请到个人设置中补充支付宝账户！', 'tt'));
            }
            $result = ct_create_tixian_order();
            if ($result) {
                return ct_api_success(__('提现成功！请等待到账！', 'tt'));
            }

            return ct_api_fail(__('提现失败，请检查余额是否正常', 'tt'));
        case 'apply_status':
            $order_id = $_POST['id'];
            $status = absint($_POST['status']);
            $result = ct_update_tixian_order($order_id, array('order_success_time' => current_time('mysql'), 'order_status' => $status), array('%s', '%d'));
            if ($result) {
                return ct_api_success(__('提现操作完成', 'tt'));
            }

            return ct_api_fail(__('提现操作失败', 'tt'));
        case 'apply_card':
            $card_id = htmlspecialchars($_POST['card_id']);
            $card_secret = htmlspecialchars($_POST['card_secret']);
            $result = ct_add_cash_by_card($card_id, $card_secret);
            if ($result instanceof WP_Error) {
                return $result;
            } elseif ($result['cash'] && $result['type'] == 1) {
                return ct_api_success(sprintf(__('应用充值卡充值成功, %s增加 %s', 'tt'), CREDIT_NAME,$result['cash']));
            } elseif ($result['cash'] && $result['type'] == 2) {
                return ct_api_success(sprintf(__('Apply card to charge successfully, balance add %0.2f', 'tt'), $result['cash'] / 100));
            }

            return ct_api_fail(__('Apply card to charge failed', 'tt'));
    }

    return null;
}

/**
 * 创建公告自定义文章类型.
 *
 * @since 2.0.5
 */
function ct_create_bulletin_post_type()
{
    $bulletin_slug = ct_get_option('tt_bulletin_archives_slug', 'bulletin');
    register_post_type('bulletin',
        array(
            'labels' => array(
                'name' => _x('Bulletins', 'taxonomy general name', 'tt'),
                'singular_name' => _x('Bulletin', 'taxonomy singular name', 'tt'),
                'add_new' => __('Add New Bulletin', 'tt'),
                'add_new_item' => __('Add New Bulletin', 'tt'),
                'edit' => __('Edit', 'tt'),
                'edit_item' => __('Edit Bulletin', 'tt'),
                'new_item' => __('Add Bulletin', 'tt'),
                'view' => __('View', 'tt'),
                'all_items' => __('All Bulletins', 'tt'),
                'view_item' => __('View Bulletin', 'tt'),
                'search_items' => __('Search Bulletin', 'tt'),
                'not_found' => __('Bulletin not found', 'tt'),
                'not_found_in_trash' => __('Bulletin not found in trash', 'tt'),
                'parent' => __('Parent Bulletin', 'tt'),
                'menu_name' => __('Bulletins', 'tt'),
            ),

            'public' => true,
            'menu_position' => 16,
            'supports' => array('title', 'author', 'editor', 'excerpt'),
            'taxonomies' => array(''),
            'menu_icon' => 'dashicons-megaphone',
            'has_archive' => false,
            'rewrite' => array('slug' => $bulletin_slug),
        )
    );
}
add_action('init', 'ct_create_bulletin_post_type');

/**
 * 为公告启用单独模板
 *
 * @since 2.0.0
 *
 * @param $template_path
 *
 * @return string
 */
function ct_include_bulletin_template_function($template_path)
{
    if (get_post_type() == 'bulletin') {
        if (is_single()) {
            //指定单个公告模板
            if ($theme_file = locate_template(array('core/templates/bulletins/tpl.Bulletin.php'))) {
                $template_path = $theme_file;
            }
        }
    }

    return $template_path;
}
add_filter('template_include', 'ct_include_bulletin_template_function', 1);

/**
 * 自定义公告的链接.
 *
 * @since 2.0.0
 *
 * @param $link
 * @param object $post
 *
 * @return string|void
 */
function ct_custom_bulletin_link($link, $post = null)
{
    $bulletin_slug = ct_get_option('tt_bulletin_archives_slug', 'bulletin');
    $bulletin_slug_mode = ct_get_option('tt_bulletin_link_mode') == 'post_name' ? $post->post_name : $post->ID;
    if ($post->post_type == 'bulletin') {
        return home_url($bulletin_slug.'/'.$bulletin_slug_mode.'.html');
    } else {
        return $link;
    }
}
add_filter('post_type_link', 'ct_custom_bulletin_link', 1, 2);

/**
 * 处理公告自定义链接Rewrite规则.
 *
 * @since 2.0.0
 */
function ct_handle_custom_bulletin_rewrite_rules()
{
    $bulletin_slug = ct_get_option('tt_bulletin_archives_slug', 'bulletin');
    if (ct_get_option('tt_bulletin_link_mode') == 'post_name'):
        add_rewrite_rule(
            $bulletin_slug.'/([一-龥a-zA-Z0-9_-]+)?.html([\s\S]*)?$',
            'index.php?post_type=bulletin&name=$matches[1]',
            'top'); else:
        add_rewrite_rule(
            $bulletin_slug.'/([0-9]+)?.html([\s\S]*)?$',
            'index.php?post_type=bulletin&p=$matches[1]',
            'top');
    endif;
}
add_action('init', 'ct_handle_custom_bulletin_rewrite_rules');

/**
 * 允许投稿者上传图片.
 */
function ct_allow_contributor_uploads()
{
    $contributor = get_role('contributor');
    $contributor->add_cap('upload_files');
}

if (current_user_can('contributor') && !current_user_can('upload_files')) {
    add_action('init', 'ct_allow_contributor_uploads');
}

/**
 * 前台投稿页面的媒体上传预览准备数据filter掉post_id.
 */
function ct_remove_post_id_for_front_contribute($settings)
{
    if (get_query_var('me_child_route') === 'newpost') {
        $settings['post'] = array();
    }

    return $settings;
}

if (!is_admin()) {
    add_filter('media_view_settings', 'ct_remove_post_id_for_front_contribute', 10, 1);
}

if (CUTE_PRO && ct_get_option('tt_enable_k_thread', false)) {
load_func('thread/func.Thread');
load_func('thread/func.Thread.Data');
load_func('thread/func.Thread.Service');
}

/**
 * 创建common服务绑定的错误.
 *
 * @param $message
 * @param int $status
 *
 * @return WP_Error
 */
function ct_create_common_error($message, $status = 400)
{
    return new WP_Error('rest_common_service_error', $message, array('status' => $status));
}

/**
 * 创建common服务绑定的响应.
 *
 * @param $data
 * @param string $message
 * @param int    $status
 *
 * @return array
 */
function ct_create_common_response($data, $message = '', $status = 200)
{
    return array(
        'data' => $data,
        'message' => $message,
        'code' => $status,
    );
}

function ct_exec_common_api_services($service, $params)
{
    $func_name = 'ct_exec_common_service_'.implode('_', explode('.', $service));
    if (!function_exists($func_name)) {
        return new WP_Error('rest_common_service_not_implement', __('Sorry, the service is not implemented.', 'tt'), array('status' => 400));
    }

    return $func_name($params);
}

/**
 * 积分小工具服务-数据.
 */
function ct_exec_common_service_common_widget_credit_data($params)
{
    $credits = intval(ct_get_user_credit());

    $has_signed = false;
    if (get_user_meta($params['uid'], 'tt_daily_sign', true)) {
        date_default_timezone_set('Asia/Shanghai');
        $sign_date_meta = get_user_meta($params['uid'], 'tt_daily_sign', true);
        $sign_date = date('Y-m-d', strtotime($sign_date_meta));
        $now_date = date('Y-m-d', time());
        if ($sign_date == $now_date) {
            $has_signed = true;
        }
    }
    $data = array(
        'credits' => $credits,
        'signed' => $has_signed,
    );

    return ct_create_common_response($data);
}

/**
 * 积分小工具服务-签到.
 */
function ct_exec_common_service_common_widget_credit_sign($params)
{
    $result = ct_daily_sign();
    if ($result instanceof WP_Error) {
        return ct_create_common_error($result->get_error_message(), $result->get_error_code());
    }
    return ct_create_common_response(array(
        'credits' => intval(ct_get_user_credit())
    ));
}

if (CUTE_PRO && ct_get_option('tt_enable_shop', true)) {
    load_func('func.Shop.Loader');
}

/* 载入类 */
load_class('class.Avatar');
load_class('class.Open');
load_class('class.Wxmp');
load_class('class.PostImage');
load_class('class.Utils');
load_class('class.Member');
load_class('class.Async.Task');
load_class('class.Async.Email');
load_class('class.Enum');
load_class('class.WeiBoUploader');
load_class('class.WeiBoException');
load_class('class.Categories.Images');
load_class('class.QRcode');
// Plates模板引擎
load_class('plates/Engine');
load_class('plates/Extension/ExtensionInterface');
load_class('plates/Template/Data');
load_class('plates/Template/Directory');
load_class('plates/Template/FileExtension');
load_class('plates/Template/Folder');
load_class('plates/Template/Folders');
load_class('plates/Template/Func');
load_class('plates/Template/Functions');
load_class('plates/Template/Name');
load_class('plates/Template/Template');
load_class('plates/Extension/Asset');
load_class('plates/Extension/URI');

if (is_admin()) {
    load_class('class.Tgm.Plugin.Activation');
}
if (CUTE_PRO && ct_get_option('tt_enable_shop', false)) {
    load_class('shop/class.Product');
    load_class('shop/class.OrderStatus');
    load_func('shop/alipay/alipay_notify.class');
    load_func('shop/alipay/aliqrpay.class');
    load_func('shop/alipay/alipay.class');
    load_func('shop/wxpay/wxpay.class');
    load_func('shop/wxpay/wxh5.class');
    load_func('shop/wxpay/wxjs.class');
    load_func('shop/other/mugglepay.class');
    load_func('shop/other/codepay.class');
    if (CUTE_EXTEND){
    load_func('shop/other/xunhupay.class');
    }
}

/* 载入数据模型 */
load_vm('vm.Base');
load_vm('vm.Home.Slides');
load_vm('vm.Home.Popular');
load_vm('vm.Stickys');
load_vm('vm.Home.CMSCats');
load_vm('vm.Home.Cats');
load_vm('vm.Home.Latest');
load_vm('vm.Home.FeaturedCategory');
load_vm('vm.Single.Post');
load_vm('vm.Single.Page');
load_vm('vm.Post.Comments');
load_vm('vm.Category.Posts');
load_vm('vm.Tag.Posts');
load_vm('vm.Date.Archive');
load_vm('vm.Term.Posts');
load_vm('vm.Embed.Post');
load_vm('vm.Juhe.Image');
load_vm('widgets/vm.Widget.Author');
load_vm('widgets/vm.Widget.HotHit.Posts');
load_vm('widgets/vm.Widget.HotReviewed.Posts');
load_vm('widgets/vm.Widget.Recent.Comments');
load_vm('widgets/vm.Widget.Latest.Posts');
load_vm('widgets/vm.Widget.CreditsRank');
load_vm('widgets/vm.Widget.HotProduct');
load_vm('widgets/vm.Widget.UC');
load_vm('uc/vm.UC.Latest');
load_vm('uc/vm.UC.Product');
load_vm('uc/vm.UC.Stars');
load_vm('uc/vm.UC.Comments');
load_vm('uc/vm.UC.Followers');
load_vm('uc/vm.UC.Following');
load_vm('uc/vm.UC.Chat');
load_vm('uc/vm.UC.Profile');
load_vm('me/vm.Me.Settings');
load_vm('me/vm.Me.Credits');
load_vm('me/vm.Me.Drafts');
load_vm('me/vm.Me.Messages');
load_vm('me/vm.Me.Notifications');
load_vm('me/vm.Me.EditPost');
load_vm('vm.Search');
if (CUTE_PRO && ct_get_option('tt_enable_shop', false)) {
    load_vm('shop/vm.Shop.Header.SubNav');
    load_vm('shop/vm.Shop.Home');
    load_vm('shop/vm.Shop.Category');
    load_vm('shop/vm.Shop.Tag');
    load_vm('shop/vm.Shop.Search');
    load_vm('shop/vm.Shop.Product');
    load_vm('shop/vm.Shop.Comment');
    load_vm('shop/vm.Shop.LatestRated');
    load_vm('shop/vm.Shop.ViewHistory');
    load_vm('shop/vm.Embed.Product');
}
load_vm('bulletin/vm.Bulletin');
load_vm('bulletin/vm.Bulletins');
if (CUTE_PRO) {
    load_vm('me/vm.Me.Order');
    load_vm('me/vm.Me.Orders');
    load_vm('me/vm.Me.Apply');
    load_vm('me/vm.Me.Membership');
    load_vm('me/vm.Me.Cash');
    load_vm('management/vm.Mg.Status');
    load_vm('management/vm.Mg.Comments');
    load_vm('management/vm.Mg.Coupons');
    load_vm('management/vm.Mg.Invites');
    load_vm('management/vm.Mg.Kamis');
    load_vm('management/vm.Mg.Members');
    load_vm('management/vm.Mg.Orders');
    load_vm('management/vm.Mg.Order');
    load_vm('management/vm.Mg.Applys');
    load_vm('management/vm.Mg.Apply');
    load_vm('management/vm.Mg.Posts');
    load_vm('management/vm.Mg.Users');
    load_vm('management/vm.Mg.User');
    load_vm('management/vm.Mg.Products');
    load_vm('management/vm.Mg.Cards');
}

/* 载入小工具 */
load_widget('wgt.TagCloud');
load_widget('wgt.Author');
load_widget('wgt.HotHits.Posts');
load_widget('wgt.HotReviews.Posts');
load_widget('wgt.RecentComments');
load_widget('wgt.Latest.Posts');
load_widget('wgt.UC');
load_widget('wgt.EnhancedText');
load_widget('wgt.Donate');
load_widget('wgt.AwardCoupon');
load_widget('wgt.CreditsRank');
load_widget('wgt.Search');
load_widget('wgt.HotProduct');
load_widget('wgt.Statistic');
load_widget('wgt.CreditIntro');
load_widget('wgt.Down');

/* 实例化异步任务类实现注册异步任务钩子 */
new AsyncEmail();
/*
==================================================
fancybox图片灯箱效果
==================================================
*/
if(strpos($_SERVER['REQUEST_URI'] ,'api')===false && !ct_get_option('tt_enable_k_fancybox', false)){
add_filter('the_content', 'fancybox1',9);
add_filter('the_content', 'fancybox2',8);
}
function fancybox1($content){
    global $post;
    $pattern = "/<a(.*?)href=('|\")([^>]*)('|\")([^>]*)><img(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3$4$5><duang$6</a>';
    $content = preg_replace($pattern, $replacement, $content);
    $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)><img(.*?)<\/a>/i";
    $replacement = '<img$7';
    $content = preg_replace($pattern, $replacement, $content);
    $pattern = '/<img([^>]*)class="([^"]*)"([^>]*)>/i';
    $replacement = '<img$1$3>';
    $content = preg_replace($pattern, $replacement, $content);
    $pattern = '/<img([^>]*)alt="([^"]*)"([^>]*)>/i';
    $replacement = '<img$1$3>';
    $content = preg_replace($pattern, $replacement, $content);
    $pattern = "/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")([^>]*)>/i";
    $replacement = '<a$1href=$2$3.$4$5 data-fancybox="images"><img$1class="lazy" src="'.LAZY_PENDING_IMAGE .'" data-original=$2$3.$4$5$6></a>';
    $content = preg_replace($pattern, $replacement, $content);
    $pattern = "/<a(.*?)href=('|\")([^>]*)('|\")([^>]*)><duang(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3$4$5><img$6</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
function fancybox2($content){
    global $post;
    $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>([^<img]*)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 data-fancybox="images"$6>$7</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
/*
==================================================
文章列表输出文章ID
==================================================
*/
add_filter('manage_posts_columns', 'customer_post_id_columns');
function customer_post_id_columns($columns) {
        $columns['post_id'] = '文章ID';
        return $columns;
}
add_action('manage_posts_custom_column', 'customer_post_id_columns_value', 10, 2);
function customer_post_id_columns_value($column, $post_id) {
        if ($column == 'post_id') {
             echo $post_id;
        }
        return;
}
/*
==================================================
获取文章图片
==================================================
*/
function get_mypost_thumbnail($post_ID){
    $post = get_post($post_ID);
    if (has_post_thumbnail($post)) {
            $timthumb_src = wp_get_attachment_image_src( get_post_thumbnail_id($post_ID), 'full' );
            $url = $timthumb_src[0];
    } else {
        if(!$post_content){
            $post = get_post($post_ID);
            $post_content = $post->post_content;
        }
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
        if( $matches && isset($matches[1]) && isset($matches[1][0]) ){
            $url =  $matches[1][0];
        }else{
            $url = CUTE_THEME_ASSET . '/img/thumb/' . mt_rand(1, absint(40)) . '.jpg';
        }
    }
    return $url;
}
/*
==================================================
生成封面相关功能
==================================================
*/
add_action('wp_ajax_nopriv_create-bigger-image', 'get_bigger_img');
add_action('wp_ajax_create-bigger-image', 'get_bigger_img');
function draw_txt_to($card, $pos, $str, $iswrite, $font_file)
{
    $_str_h = $pos['top'];
    $fontsize = $pos['fontsize'];
    $width = $pos['width'];
    $margin_lift = $pos['left'];
    $hang_size = $pos['hang_size'];
    $temp_string = '';
    $tp = 0;
    $font_color = imagecolorallocate($card, $pos['color'][0], $pos['color'][1], $pos['color'][2]);
    $i = 0;
    while ($i < mb_strlen($str)) {
        $box = imagettfbbox($fontsize, 0, $font_file, mi_str_encode($temp_string));
        $_string_length = $box[2] - $box[0];
        $temptext = mb_substr($str, $i, 1);
        $temp = imagettfbbox($fontsize, 0, $font_file, mi_str_encode($temptext));
        if ($_string_length + $temp[2] - $temp[0] < $width) {
            $temp_string .= mb_substr($str, $i, 1);
            if ($i == mb_strlen($str) - 1) {
                $_str_h = $_str_h + $hang_size;
                $_str_h += $hang_size;
                $tp = $tp + 1;
                if ($iswrite) {
                    imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, mi_str_encode($temp_string));
                }
            }
        } else {
            $texts = mb_substr($str, $i, 1);
            $isfuhao = preg_match('/[\\pP]/u', $texts) ? true : false;
            if ($isfuhao) {
                $temp_string .= $texts;
                $f = mb_substr($str, $i + 1, 1);
                $fh = preg_match('/[\\pP]/u', $f) ? true : false;
                if ($fh) {
                    $temp_string .= $f;
                    $i = $i + 1;
                }
            } else {
                $i = $i + -1;
            }
            $tmp_str_len = mb_strlen($temp_string);
            $s = mb_substr($temp_string, $tmp_str_len - 1, 1);
            if (is_firstfuhao($s)) {
                $temp_string = rtrim($temp_string, $s);
                $i = $i + -1;
            }
            $_str_h = $_str_h + $hang_size;
            $_str_h += $hang_size;
            $tp = $tp + 1;
            if ($iswrite) {
                imagettftext($card, $fontsize, 0, $margin_lift, $_str_h, $font_color, $font_file, mi_str_encode($temp_string));
            }
            $temp_string = '';
        }
        $i = $i + 1;
    }
    return $tp * $hang_size;
}
function is_firstfuhao($str)
{
    $fuhaos = array('0' => '"', '1' => '“', '2' => '\'', '3' => '<', '4' => '《');
    return in_array($str, $fuhaos);
}
function mi_str_encode($string)
{
    return $string;
	$len = strlen($string);
    $buf = '';
    $i = 0;
    while ($i < $len) {
        if (ord($string[$i]) <= 127) {
            $buf .= $string[$i];
        } elseif (ord($string[$i]) < 192) {
            $buf .= '&#xfffd;';
        } elseif (ord($string[$i]) < 224) {
            $buf .= sprintf('&#%d;', ord($string[$i + 0]) + ord($string[$i + 1]));
            $i = $i + 1;
            $i += 1;
        } elseif (ord($string[$i]) < 240) {
            ord($string[$i + 2]);
            $buf .= sprintf('&#%d;', ord($string[$i + 0]) + ord($string[$i + 1]) + ord($string[$i + 2]));
            $i = $i + 2;
            $i += 2;
        } else {
            ord($string[$i + 2]);
            ord($string[$i + 3]);
            $buf .= sprintf('&#%d;', ord($string[$i + 0]) + ord($string[$i + 1]) + ord($string[$i + 2]) + ord($string[$i + 3]));
            $i = $i + 3;
            $i += 3;
        }
        $i = $i + 1;
    }
    return $buf;
}
function substr_ext($str, $start = 0, $length, $charset = 'utf-8', $suffix = '')
{
    if (function_exists('mb_substr')) {
        return mb_substr($str, $start, $length, $charset) . $suffix;
    }
    if (function_exists('iconv_substr')) {
        return iconv_substr($str, $start, $length, $charset) . $suffix;
    }
    $re['utf-8'] = '/[-]|[?-?][?-?]|[?-?][?-?]{2}|[?-?][?-?]{3}/';
    $re['gb2312'] = '/[-]|[?-?][?-?]/';
    $re['gbk'] = '/[-]|[?-?][@-?]/';
    $re['big5'] = '/[-]|[?-?]([@-~]|?-?])/';
    preg_match_all($re[$charset], $str, $match);
    $slice = join('', array_slice($match[0], $start, $length));
    return $slice . $suffix;
}
function create_bigger_image($post_id, $date, $title, $content, $head_img, $qrcode_img = null)
{
    $im = imagecreatetruecolor(750, 1334);
    $white = imagecolorallocate($im, 255, 255, 255);
    $gray = imagecolorallocate($im, 200, 200, 200);
    $foot_text_color = imagecolorallocate($im, 153, 153, 153);
    $black = imagecolorallocate($im, 0, 0, 0);
    $title_text_color = imagecolorallocate($im, 51, 51, 51);
    $english_font = get_template_directory() . '/assets/fonts/Montserrat-Regular.ttf';
    $chinese_font = get_template_directory() . '/assets/fonts/MFShangYa_Regular.otf';
    $chinese_font_2 = get_template_directory() . '/assets/fonts/hanyixizhongyuan.ttf';
    imagefill($im, 0, 0, $white);
    $head_img = imagecreatefromstring(file_get_contents(CUTE_THEME_URI . '/core/library/timthumb/Timthumb.php?src='.$head_img.'&h=680&w=750&zc=1&a=c&q=100&s=1'));
    imagecopy($im, $head_img, 0, 0, 0, 0, 750, 680);
    $day = $date['day'];
    $day_width = imagettfbbox(85, 0, $english_font, $day);
    $day_width = abs($day_width[2] - $day_width[0]);
    $year = $date['year'];
    $year_width = imagettfbbox(24, 0, $english_font, $year);
    $year_width = abs($year_width[2] - $year_width[0]);
    $day_left = ($year_width - $day_width) / 2;
    $fenge = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';
    $fenge_width = 750;
    $bg_img = CUTE_THEME_URI . '/assets/img/icon/bg.png';
    $bg_str = file_get_contents($bg_img);
    $bg_size = getimagesizefromstring($bg_str);
    $bg_img = imagecreatefromstring($bg_str);
    imagecopyresized($im, $bg_img, 40, 490, 0, 0, 150, 150, $bg_size[0], $bg_size[1]);
    imagettftext($im, 80, 0, 50 + $day_left, 575, $white, $english_font, $day);
    imageline($im, 50, 590, 50 + $year_width, 590, $white);
    imagettftext($im, 24, 0, 50, 625, $white, $english_font, $year);
    imagettftext($im, 10, 0, 0, 1160, $foot_text_color, $chinese_font_2, $fenge);
    $title = mi_str_encode($title);
    $title_width = imagettfbbox(28, 0, $chinese_font, $title);
    $title_width = abs($title_width[2] - $title_width[0]);
    $title_left = (750 - $title_width) / 2;
    //imagettftext($im, 28, 0, $title_left, 830, $black, $chinese_font, $title);
    $conf_t = array('color' => array('0' => 0, '1' => 0, '2' => 0), 'fontsize' => 28, 'width' => 650, 'left' => 50, 'top' => 700, 'hang_size' => 30);
    draw_txt_to($im, $conf_t, $title, true, $chinese_font);
    $conf = array('color' => array('0' => 99, '1' => 99, '2' => 99), 'fontsize' => 21, 'width' => 610, 'left' => 70, 'top' => 870, 'hang_size' => 20);
    draw_txt_to($im, $conf, $content, true, $chinese_font_2);
    $style = array();
    imagesetstyle($im, $style);
    imageline($im, 0, 1136, 750, 1136, IMG_COLOR_STYLED);
    $foot_text = ct_get_option('tt_postfm_description');
    $foot_text = $foot_text ? $foot_text : get_bloginfo('description');
    $foot_text = mi_str_encode($foot_text);
    $logo_img = ct_get_option('tt_postfm_logo')['url'];
    $logo_img = imagecreatefromstring(file_get_contents(CUTE_THEME_URI . '/core/library/timthumb/Timthumb.php?src='.$logo_img.'&h=40&w=181&zc=1&a=c&q=100&s=1'));
    if ($qrcode_img) {
        imagecopy($im, $logo_img, 50, 1200, 0, 0, 181, 40);
        imagettftext($im, 14, 0, 25, 1275, $foot_text_color, $chinese_font_2, $foot_text);
        $qrcode_str = file_get_contents($qrcode_img);
        $qrcode_size = getimagesizefromstring($qrcode_str);
        $qrcode_img = imagecreatefromstring($qrcode_str);
        imagecopyresized($im, $qrcode_img, 600, 1185, 0, 0, 100, 100, $qrcode_size[0], $qrcode_size[1]);
    } else {
        imagecopy($im, $logo_img, 284, 1200, 0, 0, 181, 40);
        $foot_text_width = imagettfbbox(14, 0, $chinese_font, $foot_text);
        $foot_text_width = abs($foot_text_width[2] - $foot_text_width[0]);
        $foot_text_left = 750 - $foot_text_width / 2;
        imagettftext($im, 14, 0, $foot_text_left, 1275, $foot_text_color, $chinese_font_2, $foot_text);
    }
    ob_start ();
    imagepng ($im);
    $image_data = ob_get_contents ();
    ob_end_clean ();
    //得到这个结果，可以直接用于前端的img标签显示
    $image_data_base64 = "data:image/png;base64,". base64_encode ($image_data);
    return $image_data_base64;
}

function get_bigger_img()
{
    $post_id = sanitize_text_field($_POST['id']);
    if (wp_verify_nonce($_POST['nonce'], 'mi-create-bigger-image-' . $post_id)) {
        get_the_time('d', $post_id);
        get_the_time('Y/m', $post_id);
        $date = array('day' => get_the_time('d', $post_id), 'year' => get_the_time('Y/m', $post_id));
        $title = get_the_title($post_id);
        $share_title = get_the_title($post_id);
        $title = substr_ext($title, 0, 35, 'utf-8', '');
        $post = get_post($post_id);
        $content = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
        $content = preg_replace('#<script[^>]*?>.*?</script>#si','',$content);
        $content = preg_replace('#<style[^>]*?>.*?</style>#si','',$content);
        $content = preg_replace('#<pre[^>]*?>.*?</pre>#si','',$content);
        $content = substr_ext(strip_tags(strip_shortcodes($content)), 0, 100, 'utf-8', '...');
        $share_content = '【' . $share_title . '】' . substr_ext(strip_tags(strip_shortcodes($content)), 0, 80, 'utf-8', '');
        $content = str_replace(PHP_EOL, '', strip_tags(apply_filters('the_content', $content)));
        $head_img = get_mypost_thumbnail($post_id);
        $qrcode_img = home_url('/').'site/qr?key=bigger&text=' . add_query_arg('ref', get_current_user_id(), get_the_permalink($post_id));
        $result = create_bigger_image($post_id, $date, $title, $content, $head_img, $qrcode_img);
        if ($result) {
            $share_link = sprintf('https://service.weibo.com/share/share.php?url=%s&type=button&language=zh_cn&searchPic=true%s&title=%s', urlencode(get_the_permalink($post_id)), $pic, $share_content);
            $msg = array('s' => 200, 'src' => $result, 'share' => $share_link);
        } else {
            $msg = array('s' => 403, 'm' => 'bigger 封面生成失败，请稍后再试！');
        }
    } else {
        $msg = array('s' => 403, 'm' => '嘿嘿嘿！');
    }
    echo json_encode($msg);
    exit(0);
}
/*
==================================================
增加评论html代码支持
==================================================
*/
function sig_allowed_html_tags_in_comments() {
    error_reporting(0);
    define('CUSTOM_TAGS', true);
    global $allowedtags;
    $allowedtags = array(
        'img' => array(
			'class' => true,
			'alt' => true,
			'align' => true,
			'border' => true,
			'height' => true,
			'hspace' => true,
			'longdesc' => true,
			'vspace' => true,
			'src' => true,
			'usemap' => true,
			'width' => true,
		),
        'a' => array(
			'href' => true,
			'rel' => true,
			'rev' => true,
			'name' => true,
			'target' => true,
		),
		'font' => array(
			'color' => true,
			'face' => true,
			'size' => true,
		),
        'strong' => array(),
		'em' => array(),
		'blockquote' => array(
			'cite' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'del' => array(
			'datetime' => true,
		),
		'u' => array(),
		'pre' => array(
			'class' => true,
			'width' => true,
		),
		'code' => array()
    );
}
add_action('init', 'sig_allowed_html_tags_in_comments', 10);
function comments_url($comment_data) {

    $comment_data['comment_content'] = preg_replace('/\[url\=(.*?)\](.*?)\[\/url\]/', '<a href="$1" target="_blank" rel="nofollow">$2</a>', $comment_data['comment_content']);
    return ($comment_data);
}
add_filter('preprocess_comment', 'comments_url');

/**
 * 自定义分类页标题关键字和描述.
 *
 * @since LTS
 *
 */
// 新建分类页面添加自定义字段输入框
add_action( 'category_add_form_fields', 'add_tax_custom_field');
// 编辑分类页面添加自定义字段输入框
add_action( 'category_edit_form_fields', 'edit_tax_custom_field');

// 保存自定义字段数据
add_action( 'edited_category', 'save_tax_meta', 10, 2 );
add_action( 'create_category', 'save_tax_meta', 10, 2 );
function add_tax_custom_field(){
    ?>
        <div class="form-field">
            <label for="term_meta[tax_title]">分类标题</label>
            <input type="text" name="term_meta[tax_title]" id="term_meta[tax_title]" value="" />
            <p class="description">输入分类标题</p>
        </div>


        <div class="form-field">
            <label for="term_meta[tax_keywords]">分类关键字</label>
            <input type="text" name="term_meta[tax_keywords]" id="term_meta[tax_keywords]" value="" />
            <p class="description">输入分类关键字</p>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_description]">分类描述</label>
            <input type="text" name="term_meta[tax_description]" id="term_meta[tax_description]" value="" />
            <p class="description">输入分类描述</p>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_tuzhan_img]">是否启用竖版图站专用缩略图</label>
            <select name="term_meta[tax_tuzhan_img]">
            <option value="0" selected="selected">关闭</option>
            <option value="1">启用</option>
        </select>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_free_img]">是否启用分类图片付费限制</label>
            <select name="term_meta[tax_free_img]">
            <option value="0" selected="selected">关闭</option>
            <option value="1">启用</option>
        </select>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_free_img_count]">分类图片付费限制图片数量</label>
            <input type="text" name="term_meta[tax_free_img_count]" id="term_meta[tax_free_img_count]" value="" />
            <p class="description">输入分类图片付费限制图片数量</p>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_img_currency]">分类图片付费限制支付方式</label>
            <select name="term_meta[tax_img_currency]">
            <option value="0"><?php echo CREDIT_NAME; ?></option>
            <option value="1">现金</option>
        </select>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_img_price]">分类图片付费限制支付价格</label>
            <input type="text" name="term_meta[tax_img_price]" id="term_meta[tax_img_price]" value="" />
            <p class="description">输入分类图片付费限制支付价格</p>
        </div>

        <div class="form-field">
            <label for="term_meta[tax_img_vip]">分类图片会员限制</label>
            <select name="term_meta[tax_img_vip]">
            <option value="0">禁用</option>
            <option value="1">月费会员</option>
            <option value="2">年费会员</option>
            <option value="3">永久会员</option>
        </select>
        </div>
        <div class="form-field">
            <label for="term_meta[tax_filter_name]">分类筛选名称</label>
            <input type="text" name="term_meta[tax_filter_name]" id="term_meta[tax_filter_name]" value="" />
            <p class="description">输入分类筛选名称</p>
        </div>
        <div class="form-field">
            <label for="term_meta[tax_filter_ename]">分类筛选英文标识</label>
            <input type="text" name="term_meta[tax_filter_ename]" id="term_meta[tax_filter_ename]" value="" />
            <p class="description">输入分类筛选英文标识</p>
        </div>
        <div class="form-field">
            <label for="term_meta[tax_filter]">分类筛选内容（英文逗号隔开）</label>
            <input type="text" name="term_meta[tax_filter]" id="term_meta[tax_filter]" value="" />
            <p class="description">输入分类筛选内容（英文逗号隔开）</p>
        </div>
    <?php
    }

/**
 * 编辑分类页面添加自定义字段输入框
 *
 * @uses get_option()       从option表中获取option数据
 * @uses esc_url()          确保字符串是url
 */
function edit_tax_custom_field( $term ){

        // $term_id 是当前分类的id
        $term_id = $term->term_id;

        // 获取已保存的option
        $term_meta = get_option( "kuacg_taxonomy_$term_id" );
        // option是一个二维数组
        $title = $term_meta['tax_title'] ? $term_meta['tax_title'] : '';

        $keywords = $term_meta['tax_keywords'] ? $term_meta['tax_keywords'] : '';

        $description = $term_meta['tax_description'] ? $term_meta['tax_description'] : '';

        $tax_tuzhan_img = $term_meta['tax_tuzhan_img'] ? $term_meta['tax_tuzhan_img'] : '';

        $tax_free_img = $term_meta['tax_free_img'] ? $term_meta['tax_free_img'] : '';

        $tax_free_img_count = $term_meta['tax_free_img_count'] ? $term_meta['tax_free_img_count'] : '';

        $tax_img_currency = $term_meta['tax_img_currency'] ? $term_meta['tax_img_currency'] : '';

        $tax_img_price = $term_meta['tax_img_price'] ? $term_meta['tax_img_price'] : '';

        $tax_img_vip = $term_meta['tax_img_vip'] ? $term_meta['tax_img_vip'] : '';

        $tax_filter_name = $term_meta['tax_filter_name'] ? $term_meta['tax_filter_name'] : '';

        $tax_filter_ename = $term_meta['tax_filter_ename'] ? $term_meta['tax_filter_ename'] : '';

        $tax_filter = $term_meta['tax_filter'] ? $term_meta['tax_filter'] : '';
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_title]">分类标题</label>
                <td>
                    <input type="text" name="term_meta[tax_title]" id="term_meta[tax_title]" value="<?php echo $title; ?>" />
                    <p class="description">输入分类标题</p>
                </td>
            </th>
        </tr>


        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_keywords]">分类关键字</label>
                <td>
                    <input type="text" name="term_meta[tax_keywords]" id="term_meta[tax_keywords]" value="<?php echo $keywords; ?>" />
                    <p class="description">输入分类关键字</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_description]">分类描述</label>
                <td>
                    <input type="text" name="term_meta[tax_description]" id="term_meta[tax_description]" value="<?php echo $description; ?>" />
                    <p class="description">输入分类描述</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_tuzhan_img]">是否启用竖版图站专用缩略图</label>
                <td>
                    <select name="term_meta[tax_tuzhan_img]">
            <option value="0" <?php if($tax_tuzhan_img != 1){echo 'selected="selected"';} ?>>关闭</option>
            <option value="1" <?php if($tax_tuzhan_img == 1){echo 'selected="selected"';} ?>>启用</option>
        </select>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_free_img]">是否启用分类图片付费限制</label>
                <td>
                    <select name="term_meta[tax_free_img]">
            <option value="0" <?php if($tax_free_img != 1){echo 'selected="selected"';} ?>>关闭</option>
            <option value="1" <?php if($tax_free_img == 1){echo 'selected="selected"';} ?>>启用</option>
        </select>
                </td>
            </th>
        </tr>

       <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_free_img_count]">分类图片付费限制图片数量</label>
                <td>
                    <input type="text" name="term_meta[tax_free_img_count]" id="term_meta[tax_free_img_count]" value="<?php echo $tax_free_img_count; ?>" />
                    <p class="description">分类图片付费限制图片数量</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_img_currency]">分类图片付费限制支付方式</label>
                <td>
                    <select name="term_meta[tax_img_currency]">
            <option value="0" <?php if($tax_img_currency != 1){echo 'selected="selected"';} ?>><?php echo CREDIT_NAME; ?></option>
            <option value="1" <?php if($tax_img_currency == 1){echo 'selected="selected"';} ?>>现金</option>
        </select>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_img_price]">分类图片付费限制支付价格</label>
                <td>
                    <input type="text" name="term_meta[tax_img_price]" id="term_meta[tax_img_price]" value="<?php echo $tax_img_price; ?>" />
                    <p class="description">输入分类图片付费限制支付价格</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_img_vip]">分类图片会员限制</label>
                <td>
                    <select name="term_meta[tax_img_vip]">
            <option value="0" <?php if($tax_img_vip != 1 && $tax_img_vip != 2 && $tax_img_vip != 3){echo 'selected="selected"';} ?>>禁用</option>
            <option value="1" <?php if($tax_img_vip == 1){echo 'selected="selected"';} ?>>月费会员</option>
            <option value="2" <?php if($tax_img_vip == 2){echo 'selected="selected"';} ?>>年费会员</option>
            <option value="3" <?php if($tax_img_vip == 3){echo 'selected="selected"';} ?>>永久会员</option>
        </select>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_filter_name]">分类筛选名称</label>
                <td>
                    <input type="text" name="term_meta[tax_filter_name]" id="term_meta[tax_filter_name]" value="<?php echo $tax_filter_name; ?>" />
                    <p class="description">输入分类筛选名称</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_filter_ename]">分类筛选英文标识</label>
                <td>
                    <input type="text" name="term_meta[tax_filter_ename]" id="term_meta[tax_filter_ename]" value="<?php echo $tax_filter_ename; ?>" />
                    <p class="description">输入分类筛选英文标识</p>
                </td>
            </th>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="term_meta[tax_filter]">分类筛选内容（英文逗号隔开）</label>
                <td>
                    <input type="text" name="term_meta[tax_filter]" id="term_meta[tax_filter]" value="<?php echo $tax_filter; ?>" />
                    <p class="description">输入分类筛选内容（英文逗号隔开）</p>
                </td>
            </th>
        </tr>
    <?php
    }

/**
 * 保存自定义字段的数据
 *
 * @uses get_option()      从option表中获取option数据
 * @uses update_option()   更新option数据，如果没有就新建option
 */
function save_tax_meta( $term_id ){

        if ( isset( $_POST['term_meta'] ) ) {

            // $term_id 是当前分类的id
            $t_id = $term_id;
            $term_meta = array();

            // 获取表单传过来的POST数据，POST数组一定要做过滤
            $term_meta['tax_title'] = isset ( $_POST['term_meta']['tax_title'] ) ? $_POST['term_meta']['tax_title'] : '';
            $term_meta['tax_keywords'] = isset ( $_POST['term_meta']['tax_keywords'] ) ? $_POST['term_meta']['tax_keywords'] : '';
            $term_meta['tax_description'] = isset ( $_POST['term_meta']['tax_description'] ) ? $_POST['term_meta']['tax_description'] : '';
            $term_meta['tax_tuzhan_img'] = isset ( $_POST['term_meta']['tax_tuzhan_img'] ) ? $_POST['term_meta']['tax_tuzhan_img'] : '';
            $term_meta['tax_free_img'] = isset ( $_POST['term_meta']['tax_free_img'] ) ? $_POST['term_meta']['tax_free_img'] : '';
            $term_meta['tax_free_img_count'] = isset ( $_POST['term_meta']['tax_free_img_count'] ) ? $_POST['term_meta']['tax_free_img_count'] : '';
            $term_meta['tax_img_currency'] = isset ( $_POST['term_meta']['tax_img_currency'] ) ? $_POST['term_meta']['tax_img_currency'] : '';
            $term_meta['tax_img_price'] = isset ( $_POST['term_meta']['tax_img_price'] ) ? $_POST['term_meta']['tax_img_price'] : '';
            $term_meta['tax_img_vip'] = isset ( $_POST['term_meta']['tax_img_vip'] ) ? $_POST['term_meta']['tax_img_vip'] : '';
            $term_meta['tax_filter_name'] = isset ( $_POST['term_meta']['tax_filter_name'] ) ? $_POST['term_meta']['tax_filter_name'] : '';
            $term_meta['tax_filter_ename'] = isset ( $_POST['term_meta']['tax_filter_ename'] ) ? $_POST['term_meta']['tax_filter_ename'] : '';
            $term_meta['tax_filter'] = isset ( $_POST['term_meta']['tax_filter'] ) ? $_POST['term_meta']['tax_filter'] : '';


            // 保存option数组
            update_option( "kuacg_taxonomy_$t_id", $term_meta );

        } // if isset( $_POST['term_meta'] )
    } // save_tax_meta
/**
 * 检查评论数据黑名单
 *
 */
function ct_blacklist_check($author, $email, $url, $comment, $user_ip, $user_agent) {
	$mod_keys = trim( ct_get_option('tt_comment_blacklist_check') );
	if ( '' == $mod_keys )
		return false; // If moderation keys are empty

	// Ensure HTML tags are not being used to bypass the blacklist.
	$comment_without_html = wp_strip_all_tags( $comment );

	$words = explode(",", $mod_keys );

	foreach ( (array) $words as $word ) {
		$word = trim($word);

		// Skip empty lines
		if ( empty($word) ) { continue; }

		// Do some escaping magic so that '#' chars in the
		// spam words don't break things:
		$word = preg_quote($word, '#');

		$pattern = "#$word#i";
		if (
			   preg_match($pattern, $author)
			|| preg_match($pattern, $email)
			|| preg_match($pattern, $url)
			|| preg_match($pattern, $comment)
			|| preg_match($pattern, $comment_without_html)
			|| preg_match($pattern, $user_ip)
			|| preg_match($pattern, $user_agent)
		 )
			return true;
	}
	return false;
}
function tt_comment_blacklist_check($comment) {
    if (!current_user_can('edit_users') && ct_blacklist_check($comment['comment_author'], $comment['comment_author_email'], $comment['comment_author_url'], $comment['comment_content'], $comment['comment_author_IP'], $comment['comment_agent'])) {
        wp_die(__('评论中含有禁止内容！', 'tt'), __('温馨提示', 'tt'), 403);
      }elseif(!is_user_logged_in() && (get_user_by('login', $comment['comment_author_email']) || get_user_by('email', $comment['comment_author_email']))) {
        wp_die(__('该邮箱已注册，禁止冒充！如你是帐号持有者请登录后再提交评论！', 'tt'), __('温馨提示', 'tt'), 403);
      }else{
        return $comment;
    }
}
add_filter('preprocess_comment', 'tt_comment_blacklist_check');

/**
 * 创建邀请码数据表
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */

function ct_install_invites_table(){
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $table_charset = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $table_charset .= " COLLATE $wpdb->collate";
        }
    }

    $create_invites_sql = "CREATE TABLE $invites_table (id int(11) NOT NULL auto_increment,invite_code varchar(20) NOT NULL,invite_type varchar(20) NOT NULL default 'once',invite_status int(11) NOT NULL default 1,begin_date datetime NOT NULL default '0000-00-00 00:00:00',expire_date datetime NOT NULL default '0000-00-00 00:00:00',PRIMARY KEY (id),INDEX invitecode_index(invite_code)) ENGINE = MyISAM $table_charset;";
    maybe_create_table($invites_table, $create_invites_sql);
}
add_action('admin_init', 'ct_install_invites_table');

/**
 * 添加invite.
 *
 * @since 2.0.0
 *
 * @param $code
 * @param string $type
 * @param float  $discount
 * @param $begin_date
 * @param $expire_date
 *
 * @return bool|int|WP_Error
 */
function ct_add_invite($code, $type = 'once', $begin_date, $expire_date)
{

    //检查code重复
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    $exist = $wpdb->get_row(sprintf("SELECT * FROM $invites_table WHERE `invite_code`='%s'", $code));
    if ($exist) {
        return new WP_Error('邀请码已存在', __('这个邀请码已存在', 'tt'), array('status' => 403));
    }

    $begin_date = $begin_date ?: current_time('mysql');
    $expire_date = $expire_date ?: current_time('mysql'); //TODO 默认有效期天数
    //添加记录
    $insert = $wpdb->insert(
        $invites_table,
        array(
            'invite_code' => $code,
            'invite_type' => $type,
            'begin_date' => $begin_date,
            'expire_date' => $expire_date,
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%s',
        )
    );
    if ($insert) {
        return $wpdb->insert_id;
    }

    return false;
}

/**
 * 删除invite记录.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_delete_invite($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    $delete = $wpdb->delete(
        $invites_table,
        array('id' => $id),
        array('%d')
    );

    return (bool) $delete;
}

/**
 * 自动清理已关闭订单.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_auto_delete_close_order()
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_orders';
    $delete = $wpdb->delete(
        $invites_table,
        array('order_status' => 9),
        array('%d')
    );

    return (bool) $delete;
}
add_action('ct_setup_common_daily_event', 'ct_auto_delete_close_order');

/**
 * 每日0点自动重置会员下载次数.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_auto_delete_vip_down_count()
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'usermeta';
    $update = $wpdb->update(
        $table_name,
        array('meta_value' => '0'),
        array('meta_key' => 'tt_vip_down_count'),
        array('%s'),
        array('%s')
    );
    if($update){
      $rs = $wpdb->get_results("SELECT * FROM $table_name where meta_key = 'tt_vip_down_count'");
      foreach($rs as $r){
      wp_cache_delete($r->user_id, 'user_meta');
    }
    }
    return false;
}
add_action('ct_setup_common_daily_event', 'ct_auto_delete_vip_down_count');

/**
 * 更新invite.
 *
 * @since 2.0.0
 *
 * @param $id
 * @param $data
 * @param $format
 *
 * @return bool
 */
function ct_update_invite($id, $data, $format)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    $update = $wpdb->update(
        $invites_table,
        $data,
        array('id' => $id),
        $format,
        array('%d')
    );

    return !($update === false);
}

/**
 * 根据ID查询单个优惠码
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return array|null|object|void
 */
function ct_get_invite($id)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    $invite = $wpdb->get_row(sprintf("SELECT * FROM $invites_table WHERE `id`=%d", $id));

    return $invite;
}

/**
 * 获取多条invites.
 *
 * @since 2.0.0
 *
 * @param int  $limit
 * @param int  $offset
 * @param bool $in_effect
 *
 * @return array|null|object
 */
function ct_get_invites($limit = 20, $offset = 0, $in_effect = false)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    if ($in_effect) {
        $now = new DateTime();
        $sql = sprintf("SELECT * FROM $invites_table WHERE `invite_status`=1 AND `begin_date`<'%s' AND `expire_date`>'%s' ORDER BY id DESC LIMIT %d OFFSET %d", $now, $now, $limit, $offset);
    } else {
        $sql = sprintf("SELECT * FROM $invites_table ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
    }
    $results = $wpdb->get_results($sql);

    return $results;
}

/**
 * 统计优惠码数量.
 *
 * @since 2.0.0
 *
 * @param $in_effect
 *
 * @return int
 */
function ct_count_invites($in_effect = false)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_invites';
    if ($in_effect) {
        $now = new DateTime();
        $sql = sprintf("SELECT COUNT(*) FROM $invites_table WHERE `invite_status`=1 AND `begin_date`<'%s' AND `expire_date`>'%s'", $now, $now);
    } else {
        $sql = "SELECT COUNT(*) FROM $invites_table";
    }
    $count = $wpdb->get_var($sql);

    return $count;
}

/**
 * 检查邀请码有效性.
 *
 * @since 2.0.0
 *
 * @param $code
 *
 * @return object|WP_Error
 */
function ct_check_invite($code)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $coupons_table = $prefix.'tt_invites';
    $coupon = $wpdb->get_row(sprintf("SELECT * FROM $coupons_table WHERE `invite_code`='%s'", $code));
    if (!$coupon) {
        return new WP_Error('邀请码不存在', __('这个邀请码不存在', 'tt'), array('status' => 403));
    }
    if (!($coupon->invite_status)) {
        return new WP_Error('邀请码已使用', __('这个邀请码已经使用过了', 'tt'), array('status' => 403));
    }
    $timestamp = time();
    if ($timestamp < strtotime($coupon->begin_date)) {
        return new WP_Error('邀请码未生效', __('邀请码还未生效', 'tt'), array('status' => 403));
    }
    if ($timestamp > strtotime($coupon->expire_date)) {
        return new WP_Error('邀请码失效', __('这个邀请码已经失效', 'tt'), array('status' => 403));
    }
    if ($coupon->invite_type == 'once') {
        $mark_used = ct_update_invite($coupon->id, array('invite_status' => 0), array('%d'));
    }
    return $coupon;
}
/**
 * 随机生成一定数量的卡
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_gen_invites($quantity, $type, $begin_date, $expire_date){
    $raw_cards = array();
    $cards = array();
    $place_holders = array();
    for ($i = 0; $i < $quantity; ++$i) {
        $card = Utils::generateRandomStr(8);
        array_push($raw_cards, $card);
        array_push($cards, $card, $type, $begin_date, $expire_date);
        array_push($place_holders, "('%s', '%s', '%s', '%s')");
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_invites';

    $query = "INSERT INTO $cards_table (invite_code, invite_type, begin_date, expire_date) VALUES ";
    $query .= implode(', ', $place_holders);
    $result = $wpdb->query($wpdb->prepare("$query ", $cards));

    if (!$result) {
        return false;
    }

    return true;
}
/**
 * 邀请码订单自动发货
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_send_order_invite($order_id){
    $invite_option = ct_get_option('tt_enable_k_invite', false);
    if(!$invite_option){
        return;
    }
    $order = ct_get_order($order_id);
    if(!$order || $order->order_status != OrderStatus::TRADE_SUCCESS){
        return;
    }

    if($order->product_id == '-9') {
       $invite_active_time = ct_get_option('tt_k_invite_active_time');
       $card = 'Pay'.Utils::generateRandomStr(5);
       $begin_date = date("Y-m-d H:i:s",time());
       $expire_date = date("Y-m-d H:i:s",strtotime('+'.$invite_active_time.' day'));
       ct_add_invite($card, 'once', $begin_date, $expire_date);
       ct_update_order($order_id, array(
             'trade_no' => $card
         ), array('%s'));
      }
    }
add_action('ct_order_status_change', 'ct_send_order_invite');
/**
 * 捐赠订单自动发货
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_send_order_donate($order_id){
    $donate_option = ct_get_option('tt_enable_k_donate', false);
    $donate_count = ct_get_option('tt_k_donate_active_count', '10');
    if(!$donate_option){
        return;
    }
    $order = ct_get_order($order_id);
    $user_id = $order->user_id;
    if(!$order || $order->order_status != OrderStatus::TRADE_SUCCESS){
        return;
    }

    if($order->product_id == '-8') {
       if($user_id){
       update_user_meta($user_id, 'donate_order', ct_encrypt($order->order_id, ct_get_option('tt_private_token')));
       if($donate_count > 0){
           update_user_meta($user_id, 'tt_active_down_count', $donate_count);
           update_user_meta($user_id, 'donate_post', '');
           wp_cache_delete($user_id, 'user_meta');
         }
       }
       ct_update_order($order->order_id, array(
             'trade_no' => 'Pay'.ct_encrypt($order->order_id, ct_get_option('tt_private_token'))
         ), array('%s'));
    }
}
add_action('ct_order_status_change', 'ct_send_order_donate');
/**
 * 获取一定数量的标签列表
 *
 * @since 2.2.0
 *
 */

function ct_get_custom_post_tags($post_id, $count = '10') {
    $tags = wp_get_post_tags($post_id);
    if (!$tags) {
        return;
    }
    $html = '';
    $i = 0;
    foreach($tags as $tag) {
        $i++;
        $tag_link = get_tag_link($tag->term_id);
        if ($i <= $count) {
            $html .= '<a href="'.$tag_link.'" rel="tag">'.$tag->name.'</a>';
        } else {
            $html .= '<a href="'.$tag_link.'" rel="tag" style="display: none;">'.$tag->name.'</a>';
        }
    }
    return $html;
}

/**
 * 隐藏邮箱信息
 *
 * @since 2.2.0
 *
 */
function ct_get_privacy_mail($mail) {
  $email_display_name = explode('@', $mail);
  $mail = strpos($mail,'@') != false && !current_user_can('edit_users') ? substr_replace($email_display_name[0],'**',-2,2).'@'.$email_display_name[1]:$mail;
  return $mail;
}
/*
==================================================
在前台投稿和文章编辑页面的[添加媒体]只显示用户自己上传的文件
==================================================
*/
function my_upload_media( $wp_query_obj ) {
	global $current_user, $pagenow;
	if( !is_a( $current_user, 'WP_User') )
		return;
	if( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' )
		return;
	if( !current_user_can( 'manage_options' ) && !current_user_can('manage_media_library') )
		$wp_query_obj->set('author', $current_user->ID );
	return;
}
add_action('pre_get_posts','my_upload_media');

/*
==================================================
在[媒体库]只显示用户上传的文件
==================================================
*/
function my_media_library( $wp_query ) {
    if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/upload.php' ) !== false ) {
        if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_media_library' ) ) {
            global $current_user;
            $wp_query->set( 'author', $current_user->id );
        }
    }
}
add_filter('parse_query', 'my_media_library' );
/**
 * 推广系统自动赠送返利
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_send_order_rec_rebate($order_id){
    $open_tt_rec_rebate = ct_get_option('tt_rec_rebate',false);
    $open_tt_coupon_rebate = ct_get_option('tt_coupon_rebate',false);
    if(!$open_tt_rec_rebate && !$open_tt_coupon_rebate){
        return;
    }
    $order = ct_get_order($order_id);
    $coupon = ct_get_coupon($order->coupon_id);
    $coupon_user_id = (int) $coupon->user_id;
    $user_id = (int) get_user_meta($order->user_id, 'tt_ref', true);
    $msg_text = '注册推广';
    $tt_rec_rebate_ratio = ct_get_option('tt_rec_rebate_ratio');
    $tt_coupon_rebate_ratio = ct_get_option('tt_coupon_rebate_ratio');
    $amount = $tt_rec_rebate_ratio * $order->order_total_price;
    if(!empty($coupon_user_id) && $open_tt_coupon_rebate){
      $user_id = $coupon_user_id;
      $msg_text = '优惠码推广';
      $amount = $tt_coupon_rebate_ratio * $order->order_total_price;
    }

    if(!$order || $order->order_status != OrderStatus::TRADE_SUCCESS || empty($user_id)){
        return;
    }

    $before_cash = (int) get_user_meta($user_id, 'tt_cash', true);
    $before_credit = (int) get_user_meta($user_id, 'tt_credits', true);
    if($order->order_currency == 'cash') {
        $msg = sprintf(__('获得%s提成（来自%s的消费）奖励 %s 元 , 当前余额 %s 元', 'tt'), $msg_text,get_user_by('ID',$order->user_id)->display_name, sprintf('%0.2f', max(0, (int) $amount) / 100), sprintf('%0.2f', (max(0, (int) $amount) + $before_cash) / 100));
        return ct_update_user_cash($user_id, $amount, $msg);
       }else{
        $amount = intval($amount / 100);
        $msg = sprintf(__('获得%2$s提成（来自%3$s的消费）奖励 %4$s %1$s , 当前%1$s %5$s', 'tt'), CREDIT_NAME,$msg_text,get_user_by('ID',$order->user_id)->display_name, max(0, (int) $amount), max(0, (int) $amount) + $before_credit);
        return ct_update_user_credit($user_id, $amount, $msg);
    }
    }
add_action('ct_order_status_change', 'ct_send_order_rec_rebate');

/**
 * 优惠码推广自动赠送返利
 *
 * @since 2.2.0
 *
 * @param $quantity
 * @param $denomination
 *
 * @return array | bool
 */
function ct_send_order_coupon_rebate($order_id){
    $open_tt_coupon_rebate = ct_get_option('tt_coupon_rebate');
    if(!$open_tt_coupon_rebate){
        return;
    }
    $order = ct_get_order($order_id);
    $coupon = ct_get_coupon($order->coupon_id);
    $user_id = (int) $coupon->user_id;
    if(!$order || $order->order_status != OrderStatus::TRADE_SUCCESS || empty($user_id)){
        return;
    }
    $tt_coupon_rebate_ratio = ct_get_option('tt_coupon_rebate_ratio');
    $amount = $tt_coupon_rebate_ratio * $order->order_total_price;
    $before_cash = (int) get_user_meta($user_id, 'tt_cash', true);
    $before_credit = (int) get_user_meta($user_id, 'tt_credits', true);
    if($order->order_currency == 'cash') {
        $msg = sprintf(__('获得优惠码推广提成（来自%s的消费）奖励 %s 元 , 当前余额 %s 元', 'tt'), get_user_by('ID',$order->user_id)->display_name, sprintf('%0.2f', max(0, (int) $amount) / 100), sprintf('%0.2f', (max(0, (int) $amount) + $before_cash) / 100));
        return ct_update_user_cash($user_id, $amount, $msg);
       }else{
        $amount = intval($amount / 100);
        $msg = sprintf(__('获得优惠码推广提成（来自%2$s的消费）奖励 %3$s %1$s , 当前 %4$s %1$s', 'tt'), CREDIT_NAME,get_user_by('ID',$order->user_id)->display_name, max(0, (int) $amount), max(0, (int) $amount) + $before_credit);
        return ct_update_user_credit($user_id, $amount, $msg);
    }
    }
//add_action('ct_order_status_change', 'ct_send_order_coupon_rebate');
/**
 * 获取用户未付款订单数
 *
 * @since 2.2.0
 *
 */
function ct_get_no_pay_order_count($user_id){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $cards_table = $prefix.'tt_orders';
    $sql = sprintf("SELECT COUNT(*) FROM $cards_table WHERE `user_id`=$user_id and `parent_id`=0 and `order_status`=1 and `deleted`=0");
    $count = $wpdb->get_var($sql);

    return $count;
}
/**
 * 获取用户获取付费资源的实际价格
 *
 * @since 2.2.0
 *
 */
function ct_get_specified_user_post_price($price, $currency, $user_id = 0) {
    $price = $currency == 'cash' ? sprintf('%0.2f', $price) : (int)$price;

    $discount_summary = array(100, intval(ct_get_option('tt_monthly_vip_discount', 100)), intval(ct_get_option('tt_annual_vip_discount', 90)), intval(ct_get_option('tt_permanent_vip_discount', 80)));

    $user_id = $user_id ? : get_current_user_id();
    if(!$user_id) {
        return $currency == 'cash' ? sprintf('%0.2f', $price * absint($discount_summary[0]) / 100) : intval($price * absint($discount_summary[0]) / 100);
    }
    $member = new Member($user_id);
    switch ($member->vip_type){
        case Member::MONTHLY_VIP:
            $discount = $discount_summary[1];
            break;
        case Member::ANNUAL_VIP:
            $discount = $discount_summary[2];
            break;
        case Member::PERMANENT_VIP:
            $discount = $discount_summary[3];
            break;
        default:
            $discount = $discount_summary[0];
            break;
    }
    $discount = min($discount_summary[0], $discount); // 会员的价格不能高于普通打折价

    // 最低价格

    return $currency == 'cash' ? sprintf('%0.2f', $price * absint($discount) / 100) : intval($price * absint($discount) / 100);
}
/**
 * 列表页视频相关功能
 *
 * @since 2.2.0
 *
 */
$wboptions = array();
//page参数为在页面和文章中都添加面板 ，可以添加自定义文章类型
//context参数为面板在后台的位置，比如side则显示在侧栏
$wbboxinfo = array('title' => '文章视频', 'id'=>'ashubox', 'page'=>array('page','post'), 'context'=>'normal', 'priority'=>'low', 'callback'=>'');

$wboptions[] = array(
            "name" => "文章视频地址(也可以直接填写视频url地址)",
            "desc" => "",
            "id" => "ashu_video",
            "std" => "",
            "button_label"=>'上传视频',
            "type" => "media"
            );
$wbox_shop_metabox = array('title' => '封面视频', 'id'=>'wboxbox', 'page'=>array('product'), 'context'=>'normal', 'priority'=>'low', 'callback'=>'');

$wbox_shop_mitem = array( array(
  "name" => "商品视频地址(也可以直接填写视频url地址)",
            "desc" => "",
            "id" => "ashu_video",
            "std" => "",
            "button_label"=>'上传视频',
            "type" => "media"

  ));

$new_box = new ashu_meta_box($wboptions, $wbboxinfo);
$shop_box = new ashu_meta_box($wbox_shop_mitem, $wbox_shop_metabox);
class ashu_meta_box{
    var $options;
    var $boxinfo;
    //构造函数
    function ashu_meta_box($options,$boxinfo){
        $this->options = $options;
        $this->boxinfo = $boxinfo;

        add_action('admin_menu', array(&$this, 'init_boxes'));
        add_action('save_post', array(&$this, 'save_postdata'));
    }

    //初始化
    function init_boxes(){
        $this->create_meta_box();
    }

    /*************************/
    function add_hijack_var()
    {
        echo "<meta name='hijack_target' content='".$_GET['hijack_target']."' />\n";
    }

    //创建自定义面板
    function create_meta_box(){
        if ( function_exists('add_meta_box') && is_array($this->boxinfo['page']) )
        {
            foreach ($this->boxinfo['page'] as $area)
            {
                if ($this->boxinfo['callback'] == '') $this->boxinfo['callback'] = 'new_meta_boxes';

                add_meta_box(
                    $this->boxinfo['id'],
                    $this->boxinfo['title'],
                    array(&$this, $this->boxinfo['callback']),
                    $area, $this->boxinfo['context'],
                    $this->boxinfo['priority']
                );
            }
        }
    }

    //创建自定义面板的显示函数
    function new_meta_boxes(){
        global $post;
        //根据类型调用显示函数
        foreach ($this->options as $option)
        {
            if (method_exists($this, $option['type']))
            {
                $meta_box_value = get_post_meta($post->ID, $option['id'], true);
                if($meta_box_value != "") $option['std'] = $meta_box_value;

                echo '<div class="alt kriesi_meta_box_alt meta_box_'.$option['type'].' meta_box_'.$this->boxinfo['context'].'">';
                $this->{$option['type']}($option);
                echo '</div>';
            }
        }

        //隐藏域
        echo'<input type="hidden" name="'.$this->boxinfo['id'].'_noncename" id="'.$this->boxinfo['id'].'_noncename" value="'.wp_create_nonce( 'ashumetabox' ).'" />';
    }

    //保存字段数据
    function save_postdata() {
        if( isset( $_POST['post_type'] ) && in_array($_POST['post_type'],$this->boxinfo['page'] ) && (isset($_POST['save']) || isset($_POST['publish']) ) ){
        $post_id = $_POST['post_ID'];

        foreach ($this->options as $option) {
            if (!wp_verify_nonce($_POST[$this->boxinfo['id'].'_noncename'], 'ashumetabox')) {
                return $post_id ;
            }
            //判断权限
            if ( 'page' == $_POST['post_type'] ) {
                if ( !current_user_can( 'edit_page', $post_id  ))
                return $post_id ;
            } else {
                if ( !current_user_can( 'edit_post', $post_id  ))
                return $post_id ;
            }
            //将预定义字符转换为html实体
            if( $option['type'] == 'tinymce' ){
                    $data =  stripslashes($_POST[$option['id']]);
            }elseif( $option['type'] == 'checkbox' ){
                    $data =  $_POST[$option['id']];
            }else{
                $data = htmlspecialchars($_POST[$option['id']], ENT_QUOTES,"UTF-8");
            }

            if(get_post_meta($post_id , $option['id']) == "")
            add_post_meta($post_id , $option['id'], $data, true);

            elseif($data != get_post_meta($post_id , $option['id'], true))
            update_post_meta($post_id , $option['id'], $data);

            elseif($data == "")
            delete_post_meta($post_id , $option['id'], get_post_meta($post_id , $option['id'], true));

        }
        }
    }
    //显示标题
    function title($values){
        echo '<h2>'.$values['name'].'</h2>';
    }
    //文本框
    function text($values){
        if(isset($this->database_options[$values['id']])) $values['std'] = $this->database_options[$values['id']];

        echo '<h2>'.$values['name'].'</h2>';
        echo '<p><input type="text" size="'.$values['size'].'" value="'.$values['std'].'" id="'.$values['id'].'" name="'.$values['id'].'"/>';
        echo $values['desc'].'<br/></p>';
        echo '<br/>';
    }
    //文本域
    function textarea($values){
        if(isset($this->database_options[$values['id']])) $values['std'] = $this->database_options[$values['id']];

        echo '<h2>'.$values['name'].'</h2>';
        echo '<p><textarea class="kriesi_textarea" cols="60" rows="5" id="'.$values['id'].'" name="'.$values['id'].'">'.$values['std'].'</textarea>';
        echo $values['desc'].'<br/></p>';
        echo '<br/>';
    }
    //媒体上传
    function media($values){
        if(isset($this->database_options[$values['id']])) $values['std'] = $this->database_options[$values['id']];

        //图片上传按钮
        global $post_ID, $temp_ID;
        $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
        $media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
        $image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src&amp;type=image");

        $button = '<a href="'.$image_upload_iframe_src.'&amp;hijack_target='.$values['id'].'&amp;TB_iframe=true" id="'.$values['id'].'" class="k_hijack button thickbox" onclick="return false;" >上传</a>';

        //判断图片格式,图片预览
        $image = '';
        if($values['std'] != '') {
            $fileextension = substr($values['std'], strrpos($values['std'], '.') + 1);
            $extensions = array('png','gif','jpeg','jpg','pdf','tif');

            if(in_array($fileextension, $extensions))
            {
                $image = '<img src="'.$values['std'].'" />';
            }
        }

        echo '<div id="'.$values['id'].'_div" class="kriesi_preview_pic">'.$image .'</div>';
        echo '<p>'.$values['name'].'</p><p>';
        if($values['desc'] != "") echo '<p>'.$values['desc'].'<br/>';
        echo '<input class="kriesi_preview_pic_input" type="text" size="'.@$values['size'].'" value="'.$values['std'].'" name="'.$values['id'].'"/>'.$button;
        echo '</p>';
        echo '<br/>';
    }
    //单选框
    function radio( $values ){
        if(isset($this->database_options[$values['id']]))
            $values['std'] = $this->database_options[$values['id']];
        echo '<h2>'.$values['name'].'</h2>';
        foreach( $values['buttons'] as $key=>$value ) {
            $checked ="";
            if($values['std'] == $key) {
                $checked = 'checked = "checked"';
            }
            echo '<input '.$checked.' type="radio" class="kcheck" value="'.$key.'" name="'.$values['id'].'"/>'.$value;
        }
    }
    //复选框
    function checkbox($values){
        echo '<h2>'.$values['name'].'</h2>';
        foreach( $values['buttons'] as $key=>$value ) {
            $checked ="";
            if( is_array($values['std']) && in_array($key,$values['std'])) {
                $checked = 'checked = "checked"';
            }
            echo '<input '.$checked.' type="checkbox" class="kcheck" value="'.$key.'" name="'.$values['id'].'[]"/>'.$value;
        }
        echo '<label for="'.$values['id'].'">'.$values['desc'].'</label><br/></p>';
    }
    //下拉框
    function dropdown($values){
        echo '<h2>'.$values['name'].'</h2>';
            //选择内容可以使页面、分类、菜单、侧边栏和自定义内容
            if($values['subtype'] == 'page'){
                $select = 'Select page';
                $entries = get_pages('title_li=&orderby=name');
            }else if($values['subtype'] == 'cat'){
                $select = 'Select category';
                $entries = get_categories('title_li=&orderby=name&hide_empty=0');
            }else if($values['subtype'] == 'menu'){
                $select = 'Select Menu in page left';
                $entries = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
            }else if($values['subtype'] == 'sidebar'){
                global $wp_registered_sidebars;
                $select = 'Select a special sidebar';
                $entries = $wp_registered_sidebars;
            }else{
                $select = 'Select...';
                $entries = $values['subtype'];
            }

            echo '<p><select class="postform" id="'. $values['id'] .'" name="'. $values['id'] .'"> ';
            echo '<option value="">'.$select .'</option>  ';

            foreach ($entries as $key => $entry){
                if($values['subtype'] == 'page'){
                    $id = $entry->ID;
                    $title = $entry->post_title;
                }else if($values['subtype'] == 'cat'){
                    $id = $entry->term_id;
                    $title = $entry->name;
                }else if($values['subtype'] == 'menu'){
                    $id = $entry->term_id;
                    $title = $entry->name;
                }else if($values['subtype'] == 'sidebar'){
                    $id = $entry['id'];
                    $title = $entry['name'];
                }else{
                    $id = $entry;
                    $title = $key;
                }

                if ($values['std'] == $id ){
                    $selected = "selected='selected'";
                }else{
                    $selected = "";
                }

                echo"<option $selected value='". $id."'>". $title."</option>";
            }

        echo '</select>';
        echo $values['desc'].'<br/></p>';
        echo '<br/>';
    }

    //编辑器
    function tinymce($values){
        if(isset($this->database_options[$values['id']]))
            $values['std'] = $this->database_options[$values['id']];

        echo '<h2>'.$values['name'].'</h2>';
        wp_editor( $values['std'], $values['id'] );
        //wp_editor( $values['std'], 'content', array('dfw' => true, 'tabfocus_elements' => 'sample-permalink,post-preview', 'editor_height' => 360) );
        //带配置参数
        /*wp_editor($meta_box['std'],$meta_box['name'].'_value', $settings = array(quicktags=>0,//取消html模式
        tinymce=>1,//可视化模式
        media_buttons=>0,//取消媒体上传
        textarea_rows=>5,//行数设为5
        editor_class=>"textareastyle") ); */
    }

}

/**
 * 统计订单收入总额.
 *
 * @since 2.0.0
 *
 * @param $in_effect
 *
 * @return int
 */
function ct_count_order_price($time='')
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $invites_table = $prefix.'tt_orders';
    if ($time=='day') {
        $sql = "SELECT SUM(order_total_price) FROM $invites_table WHERE `order_status`=4 AND `order_currency`='cash' AND `deleted`=0 AND `parent_id`<1 AND to_days(order_time) = to_days(now())";
    } elseif($time=='month') {
        $sql = "SELECT SUM(order_total_price) FROM $invites_table WHERE `order_status`=4 AND `order_currency`='cash' AND `deleted`=0 AND `parent_id`<1 AND DATE_FORMAT(order_time,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')";
    } else{
        $sql = "SELECT SUM(order_total_price) FROM $invites_table WHERE `order_status`=4 AND `order_currency`='cash' AND `deleted`=0 AND `parent_id`<1 AND DATE_FORMAT(order_time,'%Y-%m') = DATE_FORMAT(DATE_ADD(CURDATE(),INTERVAL -1 MONTH),'%Y-%m')";
    }
    $count = $wpdb->get_var($sql);
    if(!$count){
      $count = 0;
    }
    return $count;
}

/**
 * 获取分类下标签
 *
 * @since 2.0.0
 */
function ct_get_category_tags($args) {
	global $wpdb;
	$tags = $wpdb->get_results
	("
		SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name
		FROM
			$wpdb->posts as p1
			LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID
			LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id,

			$wpdb->posts as p2
			LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID
			LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id
		WHERE
			t1.taxonomy = 'category' AND p1.post_status = 'publish' AND terms1.term_id IN (".$args['categories'].") AND
			t2.taxonomy = 'post_tag' AND p2.post_status = 'publish'
			AND p1.ID = p2.ID
		ORDER by tag_name
	");
	$count = 0;

	if($tags) {
	  foreach ($tags as $tag) {
	    $mytag[$count] = get_term_by('id', $tag->tag_id, 'post_tag');
	    $count++;
	  }
	}
	else {
	  $mytag = NULL;
	}

	return $mytag;
}

/**
 * 文章付费资源快捷支付回调
 *
 * @since 2.0.0
 */
function post_pay_callback(){
    $post_id = $_POST['post_id'];
    $resource_seq = $_POST['seq'];
    $pay_type = $_POST['pay_type'];
    $user_id = get_current_user_id() ? get_current_user_id() : 0;
    $tt_vip_down_count = ct_get_option('tt_vip_down_count');
    $tt_user_down_count = (int) get_user_meta($user_id, 'tt_vip_down_count', true);
    if(is_user_logged_in() && ct_get_no_pay_order_count($user_id) >= $tt_vip_down_count && $tt_vip_down_count != 0 && !in_array($resource_seq, array("vip1", "vip2", "vip3", "donate","post-zs","credit","cash"))){
            $result_json = array(
					'status' => '201',
					'msg' => '未付款订单超过限制，请先付款或删除订单！'
				);
            echo json_encode($result_json);
	        exit;
        }
    if($tt_user_down_count >= ct_get_option('tt_user_down_count') && ct_get_option('tt_user_down_count') >0 && !in_array($resource_seq, array("vip1", "vip2", "vip3", "donate"))){
      $result_json = array(
					'status' => '201',
					'msg' => '超过限制购买次数，请次日再来！'
				);
            echo json_encode($result_json);
	        exit;
    }
    if($resource_seq =='vip1'){
      $data = ct_create_vip_order($user_id,1);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='vip2'){
      $data = ct_create_vip_order($user_id,2);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='vip3'){
      $data = ct_create_vip_order($user_id,3);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='credit'){
      $min_credit_price = ct_get_option('tt_hundred_min_credit_price',5);
      if ($_POST['count'] < $min_credit_price) {
                $result_json = array(
					'status' => '201',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => '最低' . $min_credit_price . '元起充'
				);
                  echo json_encode($result_json);
	              exit;
            }
      $data = ct_create_credit_charge_order($user_id, $_POST['count']);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='cash'){
      if(ct_get_option('tt_enable_no_cash',false)){
        $result_json = array(
					'status' => '201',
					'msg' => '创建订单失败，请刷新重试'
				);
            echo json_encode($result_json);
	        exit;
        }
      $data = ct_create_cash_charge_order($user_id, $_POST['count']);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='post-zs'){
      if(ct_get_option('tt_tencent_captcha', false)){
            if(empty($_POST['data']['tcaptcha_ticket']) || empty($_POST['data']['tcaptcha_randstr'])){
               $result_json = array(
					'status' => '201',
					'msg' => '验证失败！'
				);
            echo json_encode($result_json);
	        exit;
            }
            $ticket = $_POST['data']['tcaptcha_ticket'];
	        $randstr = $_POST['data']['tcaptcha_randstr'];

            $data = [
            "aid"=>ct_get_option('tt_tencent_captcha_id'),
            "AppSecretKey"=>ct_get_option('tt_tencent_captcha_sk'),
            "Ticket"=>$ticket,
            "Randstr"=>$randstr,
            "UserIP"=>$_SERVER["REMOTE_ADDR"]
            ];


            $url = "https://ssl.captcha.qq.com/ticket/verify?".http_build_query($data);
            $result = file_get_contents($url);
            $result = json_decode($result,true);
            if($result["response"] != 1){
             $result_json = array(
					'status' => '201',
					'msg' => '验证失败！'
				);
            echo json_encode($result_json);
	        exit;
            }
            }
      if($_POST['count']<0.1){
        $result_json = array(
					'status' => '201',
					'msg' => '创建订单失败，请输入打赏金额'
				);
            echo json_encode($result_json);
	        exit;
        }
      $product_name = '赞赏文章作者';
      $data = ct_create_admire_order($post_id, $product_name, $_POST['count']);
      $order_id = $data['order_id'];
      ct_update_order($order_id, array('user_message' => sanitize_text_field($_POST['data']['msg'])), array('%s'));
      $payAmount = $data['total_price'];
    }elseif($resource_seq =='donate'){
      $active_time = ct_get_option('tt_k_donate_active_time');
      $product_name = '捐赠获取免费资源下载权限'.$active_time.'小时';
      $data = ct_create_donate_order('-8', $product_name, 1);
      $order_id = $data['order_id'];
      $payAmount = $data['total_price'];
    }elseif($pay_type =='credit'){
      if(!is_user_logged_in()){
        $result_json = array(
					'status' => '201',
					'price' =>0,
					'qr' => '',
					'num' => 0,
					'msg' => '游客无法购买'.CREDIT_NAME.'商品，请先注册登录！'
				);
                  echo json_encode($result_json);
	              exit;
      }
      $data = ct_bought_post_resource($post_id, $resource_seq, true);
      $order_id = $data->data['data']['order_id'];
      $payAmount = $data->data['data']['total_price'] ? $data->data['data']['total_price'] : '0';
      $order = ct_get_order($order_id);
      if ($order->order_currency == 'credit') {
                $credits = (int) get_user_meta($user_id, 'tt_credits', true);
                if(in_array($order->order_status, array(2, 3, 4))){
                $result_json = array(
					'status' => '100',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => '支付成功,花费'.$payAmount.CREDIT_NAME.',剩余'.($credits - $payAmount).CREDIT_NAME
				);
                  echo json_encode($result_json);
	              exit;
                }else{
                $pay = ct_credit_pay($order->order_total_price, $order->product_name, true);
                if ($pay instanceof WP_Error){
                  $result_json = array(
					'status' => '201',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => CREDIT_NAME.'不足，请充值后购买！'
				);
                  echo json_encode($result_json);
	              exit;
                }
                if ($pay) {
                    // 更新订单支付状态和支付完成时间
                    ct_update_order($order_id, array('order_success_time' => current_time('mysql'), 'order_status' => 4), array('%s', '%d')); //TODO 确保成功
                    // 钩子 - 用于清理缓存等
                    // do_action('ct_order_status_change', $order_id); // 已在ct_update_order函数中包括
                    $result_json = array(
					'status' => '100',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => '支付成功,花费'.$payAmount.CREDIT_NAME.',剩余'.($credits - $payAmount).CREDIT_NAME
				);
                  echo json_encode($result_json);
	              exit;
                }
               }
            }
    }else{
      if(!is_user_logged_in() && ct_get_option('tt_enable_no_login_down', true)){
        $data = ct_no_login_bought_post_resource($post_id, $resource_seq);
      }else{
      $data = ct_bought_post_resource($post_id, $resource_seq, true);
      }
      $order_id = $data->data['data']['order_id'];
      $payAmount = $data->data['data']['total_price'] ? $data->data['data']['total_price'] : '0';
    }
	if($data){
        $order = ct_get_order($order_id);
        // 考虑是否使用现金余额
                if($pay_type ==='balance'){
                $use_balance = true;
                }
                $total = $order->order_total_price;
                if ($use_balance) {
                    $balance = ct_get_user_cash();
                    if($balance<$total){
                	$result_json = array(
					'status' => '101',
					'url' => add_query_arg(array('oid' => $order_id, 'spm' => wp_create_nonce('checkout')), ct_url_for('checkout')),
					'msg' => '余额不足，正在跳转到支付页，请稍等'
				);
                  echo json_encode($result_json);
	              exit;
                }
                    $pay_balance = ct_cash_pay(min($balance, $total), $order->product_name);
                    if ($pay_balance) {
                        $total = max(0, $total - $balance);
                    }

                if ($order->order_total_price - $total >= 0.01) {
		                    // 使用余额成功了, 把扣除余额后的剩余款项更新到订单
                            ct_update_order($order_id, array('order_total_price' => $total), array('%f'));
                        }
	            // 开始剩余款项支付
	            // 如果支付金额为0直接返回订单详情页面并更新订单为成功
                if ($total < 0.01) {
                    ct_update_order($order_id, array(
                        'order_success_time' => current_time('mysql'),
                        'order_status' => 4
                    ), array('%s', '%d'));
                    $result_json = array(
					'status' => '100',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => '支付成功,花费'.$payAmount.'元'
				);
                  echo json_encode($result_json);
	              exit;
                }
                }
                if ($order->order_total_price < 0.01) {
                    ct_update_order($order_id, array(
                        'order_success_time' => current_time('mysql'),
                        'order_status' => 4
                    ), array('%s', '%d'));
                    $result_json = array(
					'status' => '100',
					'price' =>$payAmount,
					'qr' => '',
					'num' => $order_id,
					'msg' => '支付成功,花费'.$payAmount.'元'
				);
                  echo json_encode($result_json);
	              exit;
                }
                if ($pay_type == 3) {
                    $result_json = array(
					'status' => '101',
					'url' => add_query_arg(array('oid' => $order_id, 'spm' => wp_create_nonce('checkout')), ct_url_for('checkout')),
					'msg' => '正在跳转到支付页，请稍等'
				);
                  echo json_encode($result_json);
	              exit;
                }
               if(wp_is_mobile()){
                 switch ($pay_type) {
                    case 'alipay' :
                        if(ct_get_option('tt_pay_channel')['kpay']){
                        $url = add_query_arg(array('oid' => $order_id, 'channel' => 'kpay_alipay'), ct_url_for('kpay'));
                        }elseif(ct_get_option('tt_pay_channel')['alipay']){
                        $url = add_query_arg(array('oid' => $order_id, 'spm' => wp_create_nonce('pay_gateway'), 'channel' => 'alipay'), ct_url_for('paygateway'));
                        }else{
                        $tt_default_pay_alipay = ct_get_option('tt_default_pay_alipay','aliqrpay');
                        if($tt_default_pay_alipay=='aliqrpay'){
                        $url = add_query_arg(array('oid' => $order_id, 'channel' => 'aliqrpay'), ct_url_for('aliqrpay'));
                        }elseif($tt_default_pay_alipay=='codepay'){
                          $url = add_query_arg(array('oid' => $order_id, 'channel' => 'alipay'), ct_url_for('codepay'));
                        }elseif($tt_default_pay_alipay=='xunhu'){
                          $url = add_query_arg(array('oid' => $order_id, 'channel' => 'alipay'), ct_url_for('xunhupay'));
                        }
                        }
                        $result_json = array(
					'status' => '101',
					'url' => $url,
					'msg' => '正在跳转到支付页，请稍等'
				);
                  echo json_encode($result_json);
	              exit;
                    case 'wxpay' :
                        if(ct_get_option('tt_pay_channel')['kpay']){
                        $url = add_query_arg(array('oid' => $order_id, 'channel' => 'kpay_wxpay'), ct_url_for('kpay'));
                        }elseif(ct_get_option('tt_pay_channel')['wxwappay'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') == false){
                        $url = add_query_arg(array('oid' => $order_id), ct_url_for('wxwappay'));
                        }elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && ct_get_option('tt_pay_channel')['wxjspay']){
                        $url = add_query_arg(array('oid' => $order_id), ct_url_for('wxjspay'));
                        }else{
                        $tt_default_pay_wxpay = ct_get_option('tt_default_pay_wxpay','wxpay');
                        if($tt_default_pay_wxpay=='wxpay'){
                        $url = add_query_arg(array('oid' => $order_id, 'channel' => 'wxpay'), ct_url_for('wxqrpay'));
                        }elseif($tt_default_pay_wxpay=='codepay'){
                          $url = add_query_arg(array('oid' => $order_id, 'channel' => 'wxpay'), ct_url_for('codepay'));
                        }elseif($tt_default_pay_wxpay=='xunhu'){
                          $url = add_query_arg(array('oid' => $order_id, 'channel' => 'wxpay'), ct_url_for('xunhupay'));
                        }
                        }
                       $result_json = array(
					'status' => '101',
					'url' => $url,
					'msg' => '正在跳转到支付页，请稍等'
				);
                  echo json_encode($result_json);
	              exit;

                    default :
                       $result_json = array(
					'status' => '101',
					'url' => add_query_arg(array('oid' => $order_id, 'spm' => wp_create_nonce('checkout')), ct_url_for('checkout')),
					'msg' => '正在跳转到支付页，请稍等'
				);
                  echo json_encode($result_json);
	              exit;
                }
               }
        $pay_img = ct_orders_pay_qr($pay_type,$order_id);
		if($pay_img){
                 if(is_array($pay_img)){
                   $total = $pay_img['payAmount'];
                   $pay_img = $pay_img['url'];
                 }
			    $result_json = array(
					'status' => '200',
					'price' =>$total,
					'qr' => $pay_img,
                    'seq' => $resource_seq,
					'num' => $order_id,
					'msg' => $msg
				);


			}else{
				$result_json = array(
					'status' => '201',
					'price' =>$total,
					'qr' => '',
					'num' => $order_id,
					'msg' => '获取二维码失败'
				);
			}
		}else{
			$result_json = array(
				'status' => '202',
				'msg' => '订单创建失败，请刷新重试！'
			);
		}
	echo json_encode($result_json);
	exit;
}
add_action( 'wp_ajax_post_pay', 'post_pay_callback');
add_action( 'wp_ajax_nopriv_post_pay', 'post_pay_callback');
function post_check_pay_callback(){
    $order_id = $_POST['order_id'];
    $order = ct_get_order($order_id);
    if($order && $order->order_status == 4){
      $result_json = array(
					'status' => '200',
                    'msg' => '支付成功'
				);
        setcookie('Cute_cache',1, time() + 3*24*60*60, '/', $_SERVER['HTTP_HOST'], false);
        setcookie('ttpay_'.$order->product_id, ct_encrypt($order_id, ct_get_option('tt_private_token')), time() + 3*24*60*60, '/', $_SERVER['HTTP_HOST'], false);
        }else{
      $result_json = array(
					'status' => '201'
				);
    }
	echo json_encode($result_json);
	exit;
}
add_action( 'wp_ajax_check_pay', 'post_check_pay_callback');
add_action( 'wp_ajax_nopriv_check_pay', 'post_check_pay_callback');
function getQrcode($url){
ob_start();
QRcode::png($url);
$image_data = ob_get_contents();
ob_end_clean();
$qr = "data:image/png;base64,". chunk_split(base64_encode($image_data));
ob_flush();
header("content-type:text/html; charset=utf-8");
return $qr;
}

/**
 * 设置各列表文章数量
 *
 * @since 2.0.0
 */
function custom_posts_per_page($query){
        if(is_admin()) return;
        if (ct_is_product_category()) {
            $query->set('posts_per_page',ct_get_option('tt_category_product_count', 12));
        } elseif (ct_is_product_tag()) {
            $query->set('posts_per_page',ct_get_option('tt_tag_product_count', 12));
        } elseif (is_archive() && isset($query->query['post_type']) && $query->query['post_type'] == 'product') {
            $query->set('posts_per_page',ct_get_option('tt_home_product_count', 12));
        } elseif (is_search() && $_GET['in_shop'] == 1) {
            $query->set('posts_per_page',ct_get_option('tt_search_product_count', 12));
        } elseif (is_search()) {
            $query->set('posts_per_page',ct_get_option('tt_search_post_count', 12));
        } elseif (is_home()) {
            $query->set('posts_per_page',ct_get_option('tt_home_post_count', 12));
        } elseif (is_category()) {
           $query->set('posts_per_page',ct_get_option('tt_category_post_count', 12));
        } elseif (is_tag()) {
            $query->set('posts_per_page',ct_get_option('tt_tag_post_count', 12));
        }

}
add_action('pre_get_posts','custom_posts_per_page');

/**
 * 小程序获取内容
 *
 * @since 2.0.0
 */
function get_content_callback(){
  $post_id = $_POST['post_id'];
  $type = $_POST['type'];
  if(!$post_id || !$type){
    return false;
  }
  global $wpdb;
  $table_name = $wpdb->prefix.'posts';
  $post = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE `ID`=%d", $post_id));
  $result_json = array();
  if($type == 'post'){
    $result_json['content'] = $post[0]->post_content;
    $result_json['download'] = ct_get_post_sale_resources($post_id,false);
  }elseif($type == 'product'){
    $result_json['content'] = ct_admin_get_product_pay_content($post_id,false);
  }
  echo json_encode($result_json);
  exit;
}
add_action( 'wp_ajax_get_content', 'get_content_callback');
add_action( 'wp_ajax_nopriv_get_content', 'get_content_callback');
function ct_admin_get_product_pay_content($product_id, $html = true)
{

    $pay_content = get_post_meta($product_id, 'tt_product_pay_content', true);
    $download_content = ct_get_product_download_content($product_id, $html);

    return $html ? sprintf(__('<div class="contextual-bg bg-paycontent"><span><i class="tico tico-paypal">&nbsp;</i>付费内容</span><p>%1$s</p><p>%2$s</p></div>', 'tt'), $download_content, $pay_content) : array('download_content' => $download_content, 'pay_content' => $pay_content);
}
/**
 * 判断是否评论
 *
 * @since 2.0.0
 */
function ct_user_is_comment($post_id){
        $email = null;
        $user_ID = (int) wp_get_current_user()->ID;
        if ($user_ID > 0) {
            $email = get_userdata($user_ID)->user_email;
        } elseif (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
            $email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);
        } else {
            return false;
        }
       // if (empty($email)) {
          //  return false;
        //}
        global $wpdb;
        $query = "SELECT `comment_ID` FROM {$wpdb->comments} WHERE `comment_post_ID`={$post_id} and `comment_approved`='1' and `comment_author_email`='{$email}' LIMIT 1";
        if ($user_ID > 0) {
            $query = "SELECT `comment_ID` FROM {$wpdb->comments} WHERE `comment_post_ID`={$post_id} and `comment_approved`='1' and `user_id`='{$user_ID}' LIMIT 1";
        }
        if ($wpdb->get_results($query)) {
            return true;
        } else {
            return false;
        }
    }
add_action('set_comment_cookies','coffin_set_cookies',10,3);
function coffin_set_cookies( $comment, $user, $cookies_consent){
	$cookies_consent = true;
	wp_set_comment_cookies($comment, $user, $cookies_consent);
}
/**
 * 统计文章付费资源购买次数
 *
 * @since 2.0.0
 */
function ct_count_post_orders($post_id,$seq)
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $seq = '%_'.$seq.'%';
    $sql = sprintf("SELECT COUNT(*) FROM $orders_table WHERE `order_id` LIKE '%s' AND `product_id` = '%d'",$seq,$post_id);
    $count = $wpdb->get_var($sql);
    if(ct_get_option('tt_enable_xujia_down_count', false)){
      $count = $count + substr($post_id, -2);
    }

    return (int) $count;
}
/**
 * 只获取一个分类
 *
 * @since 2.0.0
 */
function ct_get_first_category($post_id)
{
    $categorys = get_the_category_list(' ', '', $post->ID);
    preg_match("/<a .*?>.*?<\/a>/i",$categorys,$matches);
    return $matches[0];
}

/**
 * 聚合图床
 *
 * @since 2.0.0
 */
global $wb_uploader, $processed;
$wb_uploader = \Kuacg\WeiBoUploader::newInstance(ct_get_option('tt_k_weibo_image_user')['username'], ct_get_option('tt_k_weibo_image_user')['password']);
$processed = array();
function Base64EncodeImage($ImageFile) {
        if(file_exists($ImageFile) || is_file($ImageFile)){
            $base64_image = '';
            $image_info = getimagesize($ImageFile);
            $image_data = fread(fopen($ImageFile, 'r'), filesize($ImageFile));
            $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
            return $base64_image;
        }
        else{
            return false;
        }
}
function ct_install_juhe_img_table()
{
    global $wpdb;
    include_once ABSPATH.'/wp-admin/includes/upgrade.php';
    $table_charset = '';
    $prefix = $wpdb->prefix;
    $table = $prefix.'tt_juhe_image';
    $sql = "CREATE TABLE IF NOT EXISTS $table(
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
            `type` VARCHAR(25) NOT NULL DEFAULT '',
            `src` VARCHAR(255) NOT NULL DEFAULT '',
            `img` VARCHAR (255) NOT NULL DEFAULT '',
            `create_time` timestamp NOT NULL DEFAULT NOW(),
            PRIMARY KEY (`id`),
            UNIQUE KEY uniq_post_id_src(`post_id`,`src`)
           ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    maybe_create_table($table, $sql);
}
add_action('load-themes.php', 'ct_install_juhe_img_table');
function image_cdn_upload($type = 'ali', $file, $multipart = true)
    {
        global $wb_uploader;
        if($type=='ali'){
          return ali_image_cdn_upload($file,$multipart);
        }elseif($type=='sina'){
          try {
          $pid = $wb_uploader->upload($file, $multipart);
          $img = $wb_uploader->getImageUrl($pid);
          if(!$img) return $file;
          return $img;
          } catch (\Kuacg\WeiBoException $e) {
            echo "<!--ERROR[{$e->getMessage()}][$file]-->" . PHP_EOL;
        }
        }elseif(($type=='suning')){
          return suning_image_cdn_upload($file,$multipart);
        }elseif(($type=='blibli')){
          return blibli_image_cdn_upload($file,$multipart);
        }elseif(($type=='baidu')){
          return baidu_image_cdn_upload($file,$multipart);
        }elseif(($type=='jd')){
          return jd_image_cdn_upload($file,$multipart);
        }elseif(($type=='youku')){
          return youku_image_cdn_upload($file,$multipart);
        }elseif(($type=='360')){
          return qihu_image_cdn_upload($file,$multipart);
        }elseif(($type=='sm')){
          return sm_image_cdn_upload($file,$multipart);
        }elseif(($type=='qq')){
          return qq_image_cdn_upload($file,$multipart);
        }elseif(($type=='toutiao')){
          return toutiao_image_cdn_upload($file,$multipart);
        }
        $url = 'https://api.uomg.com/api/image.'.$type;
        if ($multipart) {
            if (class_exists('CURLFile')) {     // php 5.5
                $post['file'] = 'multipart';
                $post['Filedata'] = new CURLFile(realpath($file));
            } else {
                $post['file'] = 'multipart';
                $post['Filedata'] = '@' . realpath($file);
            }
        }else{
          $post['imgurl'] = $file;
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 60,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        $upload_json = json_decode($output, true);
        $code = $upload_json['code'];
            if ($code != 1) {
                return false;
            }
            $url = $upload_json['imgurl'];
            return $url;
}
function toutiao_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://mp.toutiao.com/upload_photo/?type=json';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['photo'] = new CURLFile(realpath($file));
        } else {
            $post['photo'] = '@' . realpath($file);
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        // 正则表达式提取返回结果中的 json 数据
         if ($upload_json['web_url']) {
                return $upload_json['web_url'].'.jpg';
            }
        return false;
}
function qq_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://om.qq.com/image/orginalupload';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['Filedata'] = new CURLFile(realpath($file));
        } else {
            $post['Filedata'] = '@' . realpath($file);
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        // 正则表达式提取返回结果中的 json 数据
         if ($upload_json['data']['url']) {
                return $upload_json['data']['url'];
            }
        return false;
}
function sm_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://sm.ms/api/v2/upload?inajax=1';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['smfile'] = new CURLFile(realpath($file));
            $post['file_id'] = 0;
        } else {
            $post['smfile'] = '@' . realpath($file);
            $post['file_id'] = 0;
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        // 正则表达式提取返回结果中的 json 数据
         if ($upload_json['data']['url']) {
                return $upload_json['data']['url'];
            }elseif($upload_json['images']){
           return $upload_json['images'];
         }
        return false;
}
function qihu_image_cdn_upload($img, $multipart = true)
    {
        $url = 'http://kuaichuan.360.cn/upload/img';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['img'] = new CURLFile(realpath($file));
        } else {
            $post['img'] = '@' . realpath($file);
        }
        // Curl 提交
        $cookies = ct_get_option('ct_360_cookies');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_COOKIE  => $cookies,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        // 正则表达式提取返回结果中的 json 数据
         if ($upload_json['data']['url']) {
                 $img_url=parse_url($upload_json['data']['url']);
                return "https://p.ssl.qhimg.com/".$img_url['path'];
            }
        return false;
}
function baidu_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://baijiahao.baidu.com/builderinner/api/content/file/upload';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['media'] = new CURLFile(realpath($file));
            $post['no_compress'] = "1";
            $post['id'] = "WU_FILE_0";
            $post['is_avatar'] = "0";
        } else {
            $post['media'] = '@' . realpath($file);
            $post['no_compress'] = "1";
            $post['id'] = "WU_FILE_0";
            $post['is_avatar'] = "0";
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $img_json = json_decode($output,true);
        // 正则表达式提取返回结果中的 json 数据
         if ($img_json['ret']['https_url']) {
                return $img_json['ret']['https_url'];
            }
        return false;
}
function blibli_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://api.vc.bilibili.com/api/v1/drawImage/upload';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['file_up'] = new CURLFile(realpath($file));
            $post['biz'] = "draw";
            $post['category'] = "daily";
            $post['build'] = "0";
            $post['mobi_app'] = "web";
        } else {
            $post['file_up'] = '@' . realpath($file);
            $post['biz'] = "draw";
            $post['category'] = "daily";
            $post['build'] = "0";
            $post['mobi_app'] = "web";
        }
        // Curl 提交
        $cookies = ct_get_option('ct_blibli_cookies');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_COOKIE  => $cookies,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $img_json = json_decode($output,true);
        // 正则表达式提取返回结果中的 json 数据
         if ($img_json['data']['image_url']) {
                return $img_json['data']['image_url'];
            }
        return false;
}
function ali_image_cdn_upload($img,$multipart = true)
    {
        global $wb_uploader;
        $url = 'https://kfupload.alibaba.com/mupload';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }
        if(filesize(realpath($file)) > 5242880){
          $pid = $wb_uploader->upload($file, true);
          if($unfile){
          unlink($unfile);
          }
          return $wb_uploader->getImageUrl($pid);
        }
        $size = getimagesize($file);
        if($size[0] > 5000 || $size[1] > 5000){
          $pid = $wb_uploader->upload($file, true);
          if($unfile){
          unlink($unfile);
          }
          return $wb_uploader->getImageUrl($pid);
        }
            if (class_exists('CURLFile')) {
                $post['name'] = time().'.png';
                $post['scene'] = 'scImageSearchNsRule';
                $post['file'] = new CURLFile(realpath($file));
            } else {
                $post['name'] = time().'.png';
                $post['scene'] = 'scImageSearchNsRule';
                $post['file'] = '@' . realpath($file);
            }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 60,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        $fs_url = $upload_json['fs_url'];
            if (!$fs_url) {
                return false;
            }
        $img_url = 'https://ae01.alicdn.com/kf/'.$fs_url;
        return $img_url;
}
function suning_image_cdn_upload($img, $multipart = true)
    {
        $url = 'http://review.suning.com/imageload/uploadImg.do';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }
        if (class_exists('CURLFile')) {
            $post['Filedata'] = new CURLFile(realpath($file));
            $post['omsOrderItemId'] = time();
            $post['custNum'] = 1;
            $post['deviceType'] = 1;
        } else {
            $post['Filedata'] = '@' . realpath($file);
            $post['omsOrderItemId'] = time();
            $post['custNum'] = 1;
            $post['deviceType'] = 1;
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        // 正则表达式提取返回结果中的 json 数据
        preg_match('/image.suning.cn\/uimg\/ZR\/share_order\/(\d+)/i',$output,$matches);
         if (!$matches[1] || strpos($matches[1],'ERROR') !== false) {
                return false;
            }
        $img_url = 'https://image.suning.cn/uimg/ZR/share_order/'.$matches[1].'.jpg';
        return $img_url;
}
function jd_image_cdn_upload($img, $multipart = true)
    {
        $url = 'https://imio.jd.com/uploadfile/file/post.do';
        if ($multipart) {
            $file = $img;
            $base64 = Base64EncodeImage($file);
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $base64 = Base64EncodeImage($file);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['s'] = $base64;
            $post['appId'] = "undefined";
            $post['aid'] = "undefined";
            $post['clientType'] = "wh5";
            $post['pin'] = "undefined";
        } else {
            $post['s'] = $base64;
            $post['appId'] = "undefined";
            $post['aid'] = "undefined";
            $post['clientType'] = "wh5";
            $post['pin'] = "undefined";
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        preg_match('/"(http.*jfs[^"]*)"/i',$output,$matches);
        // 正则表达式提取返回结果中的 json 数据
         if ($matches[1]) {
                $img_url=str_replace('http://','https://',$matches[1]);
                $img_url=str_replace('https://ddcdn.jd.com/ddimg/','https://img11.360buyimg.com/img/',$img_url);
                return $img_url;
            }
        return false;
}
function youku_image_cdn_upload($img, $multipart = true)
    {
        $url = 'http://you.163.com/xhr/file/upload.json';
        if ($multipart) {
            $file = $img;
        }else{
        $get_img = @file_get_contents($img);
        $filename=short_md5($img).'.png';
        $file = ABSPATH . '/wp-content/cache/' . $filename;
        @file_put_contents($file, $get_img);
        $unfile = $file;
        }
        if(!file_exists($file)){
          return false;
        }

        if (class_exists('CURLFile')) {
            $post['file'] = new CURLFile(realpath($file));
            $post['format'] = "json";
        } else {
            $post['file'] = '@' . realpath($file);
            $post['format'] = "json";
        }
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_TIMEOUT => 1800,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        if($unfile){
          unlink($unfile);
        }
        $upload_json = json_decode($output, true);
        // 正则表达式提取返回结果中的 json 数据
         if ($upload_json['data'][0]) {
                $img_url=str_replace('http://','https://',$upload_json['data'][0]);
                return $img_url;
            }
        return false;
}
add_filter('post_thumbnail_html', 'wp_image_to_juhe_content_img_replace');//特色图片
if (ct_get_option('ct_enable_juhe_image')) {
    // 每次显示文章时 查询数据库
    add_filter('the_content', 'wp_image_to_juhe_content_img_replace', 1);
}
function wp_image_to_juhe_content_img_replace($content, $show_query_num = true){
  global $post;
  if(get_post_meta($post->ID,'ct_post_enable_juhe_image',true)=='enable'){
    $before = get_num_queries();
    $pattern = '/(https?:)?\/\/([^\s]*).\.(jpg|jpeg|png|gif|bmp)/i';
    $content = preg_replace_callback($pattern, 'wp_image_to_juhe_match_callback', $content);
    if ($show_query_num) {
        $content .= PHP_EOL . "<!-- [聚合图床查询: " . (get_num_queries() - $before) . '] -->' . PHP_EOL;
    }
  }
    return $content;
}

function wp_image_to_juhe_match_callback($matches){
    $url = $matches[0];
    if (!$matches[1]) {
        $url = $_SERVER["REQUEST_SCHEME"] . ':' . $url;
    }
    $vm = JuheImageVM::getInstance($url);
    $data = $vm->modelData;
    return $data->url;
}
function wp_image_to_juhe_img_replace($url){
    global $wb_uploader, $wpdb, $post;
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    //检查数据库是否有
    $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE src = %s", $url));
    $link = $url;
    $type = ct_get_option('ct_juhe_image_type','ali');
    if (!$data || count($data) == 0) { //如果没有则上传
        $file = $url;
        $home_path = home_url('/');
        $multipart = false;// whether is local file or not
        if (strpos($url, $home_path) !== false) {
            $multipart = true;
            $file = ABSPATH . substr($file, strlen($home_path));
        }
        if (strpos($url, 'Timthumb.php') !== false) {
            $multipart = false;
            $file = $url;
        }
        //if($weibo = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_tt_weibo_image WHERE src = %s", $url))){
          //$file=$wb_uploader->getImageUrl($weibo[0]->pid);
        //}
        $img = image_cdn_upload($type, $file, $multipart);
        if(!$img){
          return $url;
        }
        $in = array(
                'post_id' => $post->ID,
                'type' => $type,
                'src' => $url,
                'img' => $img,
            );
            $wpdb->insert($table_name, $in);
            $juhe = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE src = %s", $url));
            $link = $juhe[0]->img;
    } elseif(post_id_arr_to($data, $post->ID)){
      $img = $data[0]->img;
      $in = array(
                'post_id' => $post->ID,
                'type' => $data[0]->type,
                'src' => $url,
                'img' => $img,
            );
      $wpdb->insert($table_name, $in);
      $link = $img;
    } else {
        $img = $data[0]->img;
        $link = $img;
    }
    if($type =='sina' && ct_get_option('ct_enable_fdl_juhe_image',false)){
     $link = 'https://image.baidu.com/search/down?tn=download&word=download&ie=utf8&fr=detail&url='.$link;
    }
    return $link;
}
function post_id_arr_to($arr, $post_id){
 foreach($arr as $post){
   if($post->post_id == $post_id){
     return false;
   }
 }
  return true;
}
add_filter('manage_posts_columns', 'customer_juhe_img_columns');
function customer_juhe_img_columns($columns) {
        $columns['juhe_img'] = '聚合图床';
        return $columns;
}
add_action('manage_posts_custom_column', 'customer_juhe_img_columns_value', 10, 2);
function customer_juhe_img_columns_value($column, $post_id) {
        if ($column == 'juhe_img') {
          $enable_juhe_image = get_post_meta($post_id,'ct_post_enable_juhe_image',true) == 'enable' ? '是' : '<a href="'.get_permalink($post_id).'?enable_juhe_img='.$post_id.'" target="_blank">打开</a>';
             echo $enable_juhe_image;
        }
        return;
}
if (isset($_GET['enable_juhe_img']) && current_user_can('edit_users')) {
  update_post_meta($_GET['enable_juhe_img'], 'ct_post_enable_juhe_image', 'enable');
}
function getHttpStatus($url) {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_NOBODY,1);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_TIMEOUT,5);
        curl_exec($curl);
        $re = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
        return  $re;
    }
function write_log($text){
    $file = CUTE_THEME_DIR.'/check_juhe_image.log';
    file_put_contents($file, $text.PHP_EOL, FILE_APPEND);
}
function array2object($array) {
    if (is_array($array)) {
        $obj = new StdClass();
        foreach($array as $key => $val) {
            $obj->$key = $val;
        }
    } else {
        $obj = $array;
    }
    return $obj;
}
function ct_daily_check_juhe_image($post_id = ''){
    global $wpdb;
    if(get_option('juhe_image_runlock') == 'true'){
      echo '正在运行中，请勿重复运行，可访问主题根目录查询日志';
    }else{
    update_option('juhe_image_runlock', 'true');
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    if(empty($post_id)){
    $rs = $wpdb->get_results("SELECT * FROM $table_name");
    }else{
    $rs = $wpdb->get_results("SELECT * FROM $table_name where post_id = $post_id");
    }
    write_log('循环检查开始：'.date("Y-m-d H:i:s", time()));
    return ct_daily_check_juhe_image_data($rs);
  }
}
add_action('ct_setup_common_img_event', 'ct_daily_check_juhe_image');
function ct_daily_check_juhe_image_data($array){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    $success = array();
    foreach ($array as $row) {
      $link = $row->img;
      $status = getHttpStatus($link);
      if($status == '301' || $status == '404'){
         $wpdb->query( "DELETE FROM $table_name WHERE img = '$row->img'" );
         $url = $row->src;
         $post_id = $row->post_id;
         $file = $url;
         $home_path = home_url('/');
         $type = ct_get_option('ct_juhe_image_type','ali');
         $multipart = false;// whether is local file or not
         if (strpos($url, $home_path) !== false) {
            $multipart = true;
            $file = ABSPATH . substr($file, strlen($home_path));
        }
        if (strpos($url, 'Timthumb.php') !== false) {
            $multipart = false;
            $file = $url;
        }
            $img = image_cdn_upload($type, $file, $multipart);
            $in = array(
                'post_id' => $post_id,
                'type' => $type,
                'src' => $url,
                'img' => $img,
            );
            $wpdb->insert($table_name, $in);
            write_log('修复完成：'.date("Y-m-d H:i:s", time()).'|'.$url);
            $ins = array(
                'post_id' => $post_id,
                'src' => $url,
                'img' => $img,
            );
            $ins = array2object($ins);
            array_push($success, $ins);
      }
    }
    write_log('本次检查结束：'.date("Y-m-d H:i:s", time()).'|共修复'.count($success).'张图片');
    if(!empty($success)){
    sleep(10);
    return ct_daily_check_juhe_image_data($success);
    }else{
       write_log('循环检查结束：'.date("Y-m-d H:i:s", time()));
       ct_cache_flush_daily();
       echo '修复程序运行完毕，请清理缓存';
       return update_option('juhe_image_runlock', 'false');
    }
}
/**
 * 删除文章时删除聚合图片
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */
function ct_delete_post_and_juhe_image($post_ID) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    $wpdb->query( "DELETE FROM $table_name WHERE post_id = $post_ID" );
}
add_action('before_delete_post', 'ct_delete_post_and_juhe_image');

/**
 * 自动替换媒体库图片的域名
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */
function get_attachment_post_id ($img_url) {
			global $wpdb;
			$post_id	= $wpdb->get_var("SELECT post_parent FROM $wpdb->posts WHERE guid = '{$img_url}'");
			$post_id	= $post_id?$post_id:'0';
	return $post_id;
}
function juhe_attachment_replace($url){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    //检查数据库是否有
    $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE src = %s", $url));
    $type = ct_get_option('ct_juhe_image_type','ali');
    if (!$data || count($data) == 0) { //如果没有则上传
      if(!ct_get_option('ct_enable_auto_media_juhe_image',false)) return $url;
      $file = $url;
        $home_path = home_url('/');
        $multipart = false;// whether is local file or not
        if (strpos($url, $home_path) !== false) {
            $multipart = true;
            $file = ABSPATH . substr($file, strlen($home_path));
        }
        if (strpos($url, 'Timthumb.php') !== false) {
            $multipart = false;
            $file = $url;
        }
        $img = image_cdn_upload($type, $file, $multipart);
        if(!$img){
          return $url;
        }
        $in = array(
                'post_id' => get_attachment_post_id ($url),
                'type' => $type,
                'src' => $url,
                'img' => $img,
            );
            $wpdb->insert($table_name, $in);
            $url = $img;
    } else {
        $url = $data[0]->img;
    }

	return $url;
}
function is_upload(){
  if(!isset($_SERVER["HTTP_REFERER"])){
    return false;
  }elseif(strpos($_SERVER['HTTP_REFERER'],'/wp-admin/') !==false || strpos($_SERVER['HTTP_REFERER'],'/me/') !==false){
    return true;
  }
  return false;
}
if (ct_get_option('ct_enable_juhe_image', false) && ct_get_option('ct_enable_media_juhe_image', false) && is_admin() && ($_SERVER['PHP_SELF'] == '/wp-admin/upload.php' || is_upload())) {
add_filter('wp_get_attachment_url', 'juhe_attachment_replace');
}
function juhe_attachment_replace_rest($url){
    global $wpdb;
    $pattern = '/(https?:)?\/\/([^\s]*)\/([^\s]*)\.(jpg|jpeg|png|gif|bmp)/i';
    preg_match($pattern,$url,$match);
    $prefix = $wpdb->prefix;
    $table_name = $prefix.'tt_juhe_image';
    //检查数据库是否有
    $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE img = %s", $match[0]));
    if (!$data || count($data) == 0) { //如果没有则上传
    } else {
        $url = preg_replace($pattern, $data[0]->src, $url);
    }

	return $url;
}
add_filter( 'image_send_to_editor', 'juhe_attachment_replace_rest');

/**
 * 限制分类图片限制
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */

function fulitu($content){
    global $post;
    $category = get_the_category($post->ID);
    $cat_ID = $category[0]->term_id;
    $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
    if( !is_null( $content ) && $term_meta['tax_free_img']) :
            $user_id = get_current_user_id();
            if( $user_id != $post->post_author && !user_can($user_id,'edit_others_posts') ){
                $member = new Member($user_id);
                $pattern = "/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>/i";
                preg_match_all($pattern, $content, $mat);
                $member_text = is_user_logged_in() ? ct_get_member_type_string($member->vip_type) : '游客';
                $currency = $term_meta['tax_img_currency']; // 0 - credit 1 - cash
                $price = $term_meta['tax_img_price'];
                $vip = $term_meta['tax_img_vip'];
                $count = $term_meta['tax_free_img_count'];
                $sales = ct_count_post_orders($post->ID,0);
                $currency = $currency == 1 ? 'cash' : 'credit';
                $currency_text = $currency == 'cash' ? '元' : CREDIT_NAME;
                if(($member->vip_type < $vip || $vip == 0) && !ct_check_bought_post_resources2($post->ID, '0') && count($mat[0])>$count && $term_meta['tax_free_img']) {
                    $i = 0;
                    $sy_img = count($mat[0])-$count;
                    foreach ($mat[0] as $arr){
                    $i++;
                    if($i > $count){
                      $content =   str_replace($arr,'',$content);
                    }
                    }
                    $new_content = $content;
                    $new_content .= '<div class="content-hide-tips pos-r"><i class="fa fa-lock"></i><div class="no-credit t-c">';
                    if($vip > 0){
                    $new_content .=  '<div class="mb10">仅限以下用户组阅读此隐藏内容</div>';
                    }
                    if($vip == 1){
                      $new_content .= '<span class="user-lv vip1 mr10">月费会员</span><span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span>';
                    }elseif($vip == 2){
                      $new_content .= '<span class="user-lv vip2 mr10">年费会员</span><span class="user-lv vip3 mr10">永久会员</span>';
                    }elseif($vip == 3){
                      $new_content .= '<span class="user-lv vip3 mr10">永久会员</span>';
                    }
                    $new_content .= '<div class="mb10"></div>';
                    if($price > 0 && $vip > 0){
                      $new_content .= '<div class="mb10">或支付 <font color="#FF0000">'.$price.'</font> '.$currency_text.'查看剩余'.$sy_img.'张写真</div><p class="t-c">已有 <font color="#FF0000">'.$sales.'</font> 人支付</p>';
                    }elseif($vip == 0){
                      $new_content .= '<div class="mb10">支付 <font color="#FF0000">'.$price.'</font> '.$currency_text.'查看剩余'.$sy_img.'张写真</div><p class="t-c">已有 <font color="#FF0000">'.$sales.'</font> 人支付</p>';
                    }else{
                      $new_content .= '<div class="mb10">请先开通对应会员</div>';
                    }
                    if(!is_user_logged_in() && ($price <= 0 || $currency == 'credit' || !ct_get_option('tt_enable_no_login_down', true))){
                      $button = '<button class="user-register user-login mr10">登录</button><button class="no-open-vip"><a href="'.ct_add_redirect(ct_url_for("signup"), ct_get_current_url()).'">立刻注册</a></button>';
                    }elseif(!is_user_logged_in() && ct_get_option('tt_enable_no_login_down', true) && $price > 0 && $currency == 'cash'){
                      $button = '<button class="user-register user-login mr10">登录</button><button class="user-register"><a class="buy-content" href="javascript:;" data-post-id="'.$post->ID.'" data-resource-seq="0" data-post-type="'.$currency.'">立即购买</a></button>';
                    }elseif(is_user_logged_in() && $price > 0){
                      $button = '<button class="open-vip mr10" data-resource-seq="vip1">加入会员</button><button class="user-register"><a class="buy-content" href="javascript:;" data-post-id="'.$post->ID.'" data-resource-seq="0" data-post-type="'.$currency.'">立即购买</a></button>';
                    }else{
                      $button = '<button class="open-vip mr10" data-resource-seq="vip1">加入会员</button>';
                    }
                    $new_content .= '<p class="fs12">'.$button.'</p></div> <span class="fs12 gray pos-a content-hide-text">您的用户组：<span class="user-lv guest">'.$member_text.'</span></span></div>';
                    return $new_content;
                }elseif($term_meta['tax_free_img']){
                    return '<div class="content-hide-tips pos-r"><i class="fa fa-unlock"></i><span class="fs12 gray pos-a content-hide-text">以下为专享内容：</span>'.$content.'</div>';
                }
            }
    endif;
    return $content;
}
add_filter('the_content', 'fulitu',1);
/**
 * cookie登录标识
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */
if(is_user_logged_in() && !isset($_COOKIE['Cute_cache'])){
  setcookie('Cute_cache',1, time() + 3*24*60*60, '/', $_SERVER['HTTP_HOST'], false);
}
/**
 * 同步前台头像
 *
 * @since 2.0.0
 * @param $post_ID
 * @return void
 */
add_filter('get_avatar', 'ct_get_admin_avatar', 10, 3);
function ct_get_admin_avatar($avatar, $id_or_email, $size){
 $default_avatar = Avatar::getDefaultAvatar('medium');
 if(is_object($id_or_email)) {
 if($id_or_email->user_id != 0) {
 $user = get_user_by('id',$id_or_email->user_id);
 $user_avatar = ct_get_avatar($user->ID, 'medium');
 if($user_avatar)
 return '<img src="'.$user_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 else
 return '<img src="'.$default_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';

 }elseif(!empty($id_or_email->comment_author_email)) {
 return '<img src="'.$default_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 }
 }else{
 if(is_numeric($id_or_email) && $id_or_email > 0){
 $user = get_user_by('id',$id_or_email);
 $user_avatar = ct_get_avatar($user->ID, 'medium');
 if($user_avatar)
 return '<img src="'.$user_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 else
 return '<img src="'.$default_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 }elseif(is_email($id_or_email)){
 $user = get_user_by('email',$id_or_email);
 $user_avatar = ct_get_avatar($user->ID, 'medium');
 if($user_avatar)
 return '<img src="'.$user_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 else
 return '<img src="'.$default_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="'.$user->display_name .'" />';
 }else{
 return '<img src="'.$default_avatar.'" class="avatar avatar-'.$size.' photo" width="'.$size.'" height="'.$size.'" alt="" />';
 }
 }
 return $avatar;
}

/*
==================================================
删除文章图片属性并自动添加
==================================================
*/
if(ct_get_option('tt_enable_k_post_img_title',true)){
add_filter( 'content_save_pre','remove_images_class' );
add_filter( 'post_thumbnail_html', 'fanly_remove_images_attribute', 10 );
add_filter( 'image_send_to_editor', 'fanly_remove_images_attribute', 10 );
add_filter( 'the_content','kuacg_image_alttitle');
}
function fanly_remove_images_attribute( $html ) {
	//$html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
    $html = preg_replace( '/class=\"[^\"]*\"/', "", $html );
    $html = preg_replace( '/width="(\d*)"/', "", $html );
    $html = preg_replace( '/height="(\d*)"/', "", $html );
	$html = preg_replace( '/alt="(\s*)"\s+/', "", $html );
	$html = preg_replace( '/  /', "", $html );
	return $html;
}
function remove_images_class( $content ) {
    $content = preg_replace( '/<img([^>]*)class="([^"]*)"([^>]*) alt="([^"]*)"([^>]*)>/i', '<img$3$5>', stripslashes($content) );
	return $content;
}
function kuacg_image_alttitle( $imgalttitle ){
        global $post;
        $category = get_the_category();
        $flname=$category[0]->cat_name;
        $btitle = get_bloginfo();
        $imgtitle = $post->post_title;
        $imgUrl = "<img\s[^>]*src=(\"??)([^\" >]*?)\\1[^>]*>";
        if(preg_match_all("/$imgUrl/siU",$imgalttitle,$matches,PREG_SET_ORDER)){
                if( !empty($matches) ){
                        for ($i=0; $i < count($matches); $i++){
                                $tag = $url = $matches[$i][0];
                                $j=$i+1;
                                $judge = '/title=/';
                                preg_match($judge,$tag,$match,PREG_OFFSET_CAPTURE);
                                if( count($match) < 1 )
                                $altURL = ' alt="'.$imgtitle.' '.$flname.' 第'.$j.'张" title="'.$imgtitle.' '.$flname.' 第'.$j.'张-'.$btitle.'" ';
                                $url = rtrim($url,'/ >');
                                $url .= $altURL.'>';
                                $imgalttitle = str_replace($tag,$url,$imgalttitle);
                        }
                }
        }
        return $imgalttitle;
}


/**
 * [_get_category_tags 获取文章标签 10条]
 * @Author   Dadong2g
 * @DateTime 2019-05-28T12:20:43+0800
 * @param    [type]                   $args [description]
 * @return   [type]                         [description]
 */
function _get_category_tags($args)
{
    global $wpdb;
    $tags = $wpdb->get_results
        ("
        SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name
        FROM
            $wpdb->posts as p1
            LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID
            LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
            LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id,

            $wpdb->posts as p2
            LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID
            LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
            LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id
        WHERE
            t1.taxonomy = 'category' AND p1.post_status = 'publish' AND terms1.term_id IN (" . $args['categories'] . ") AND
            t2.taxonomy = 'post_tag' AND p2.post_status = 'publish'
            AND p1.ID = p2.ID
        ORDER by tag_name LIMIT 10
    ");
    $count = 0;

    if ($tags) {
        foreach ($tags as $tag) {
            $mytag[$count] = get_term_by('id', $tag->tag_id, 'post_tag');
            $count++;
        }
    } else {
        $mytag = null;
    }

    return $mytag;
}

/**
 * [get_category_root_id description]
 * @Author   Dadong2g
 * @DateTime 2019-08-15T09:57:46+0800
 * @param    [type]                   $cat [description]
 * @return   [type]                        [通过子分类id获取父分类id]
 */
function get_category_root_id($cat)
{
    $this_category = get_category($cat); // 取得当前分类
    while ($this_category->category_parent) // 若当前分类有上级分类时，循环
    {
        $this_category = get_category($this_category->category_parent); // 将当前分类设为上级分类（往上爬）
    }
    return $this_category->term_id; // 返回根分类的id号
}


function get_category_deel($cat)
{

    $categories = get_terms('category', array('hide_empty' => 0,'parent' => 0));//获取所有主分类
    $get_term_children = get_term_children($cat_ID, 'category'); //获取当前分类的子分类
}


// 筛选条件 搜索框
//
function only_selected_category($query)
{
    //is_search判断搜索页面  !is_admin排除后台  $query->is_main_query()只影响主循环
    if (!is_admin() && $query->is_main_query()) {
        // 排序：
        $order = !empty($_GET['order']) ? $_GET['order'] : null;
        $cat = !empty($_GET['cat']) ? (int) $_GET['cat'] : null;
        $type = !empty($_GET['type']) ? (int) $_GET['type'] : null;
        $custom_meta_arr = !empty($_GET) ? $_GET : null;

        if ($order) {
            if ($order == 'hot') {
                $query->set( 'meta_key', 'views' );
                $query->set( 'orderby', 'meta_value_num');
                $query->set( 'order', 'DESC' );
            }else{
                $query->set('orderby', $order);
            }
        }
        //有cat值传入
        if ($cat) {
            $term_id = (int) $cat;
            $tax_query = array(
                array(
                    'taxonomy' => 'category', //可换为自定义分类法
                    'field'    => 'term_id',
                    'operator' => 'IN',
                    'terms'    => array($term_id),
                ),
            );
            $query->set('tax_query', $tax_query);
        }
        $custom_meta_query =  array();
        if ($type) {
            switch ($type) {
                case '1':
                    $_type_meta_key = 'tt_free_dl';
                    $_type_value = '';
                    $_type_compare = '!=';
                    break;
                case '2':
                    $_type_meta_key = 'tt_sale_dl2';
                    $_type_value = '';
                    $_type_compare = '!=';
                    break;
                case '3':
                    $s = '[ttvip';
                    break;
                case '4':
                    $s = '[tt_sale_content]';
                    break;
                case '5':
                    $s = '[ttcustom';
                    break;
                default:
                    break;
            }

            $type_meta_query =  array(
                array(
                    'key'     => $_type_meta_key,
                    'value'   => $_type_value,
                    'compare' => $_type_compare,
                )
            );
            array_push($custom_meta_query,$type_meta_query);
        }
           $cat_ID = $cat;
           $term_meta = get_option( "kuacg_taxonomy_$cat_ID" );
           $filters = explode(',',$term_meta['tax_filter']);
            foreach ($filters as $filter) {
                $_meta_key = $term_meta['tax_filter_ename'];
                if(array_key_exists($_meta_key,$custom_meta_arr) && $_GET[$_meta_key] != 'all'){
                     $opt_meta_query =  array(
                        array(
                            'key'     => 'post_filter',
                            'value'   => $_GET[$_meta_key],
                            'compare' => '=',
                        )
                    );
                    array_push($custom_meta_query,$opt_meta_query);
                }
            }
        $query->set('meta_query', $custom_meta_query);
        if($s){
          $query->set('s', $s);
        }

    }
    return $query;
}
add_filter('pre_get_posts', 'only_selected_category');

/**
 * Wordpress 禁止多个人登录同一用户帐号
 */
function pcl_user_has_concurrent_sessions() {
            return ( is_user_logged_in() && count( wp_get_all_sessions() ) > 1 );
    }
    //用户当前会话数组
    function pcl_get_current_session() {
            $sessions = WP_Session_Tokens::get_instance( get_current_user_id() );
            return $sessions->get( wp_get_session_token() );
    }
    //如果用户会话更新则销毁其他会话
    function pcl_disallow_account_sharing() {
            if ( ! pcl_user_has_concurrent_sessions() ) {
                    return;
            }
            $newest = max( wp_list_pluck( wp_get_all_sessions(), 'login' ) );
            $session = pcl_get_current_session();
            if ( $session['login'] === $newest ) {
                    wp_destroy_other_sessions();
            } else {
                    wp_destroy_current_session();
            }
    }
if(ct_get_option('tt_enable_one_login',false)){
add_action( 'init', 'pcl_disallow_account_sharing' );
}

/**
 * 数组按指定数量分组
 */
function splitArray($array, $enum){
    if(empty($array)) return array();

    //数组的总长度
    $allLength = count($array);

    //个数
    $groupNum = ceil($allLength/$enum);

    //开始位置
    $start = 0;


    //结果集
    $result = array();

    if($enum > 0){

        //被分数组中 能整除 分成数组中元素个数 的部分
        $firstLength = $enum * $groupNum;
        $firstArray = array();
        for($i=0; $i<$firstLength; $i++){
            if($array[$i]){
            array_push($firstArray, $array[$i]);
            unset($array[$i]);
            }
        }
        for($i=0; $i<$groupNum; $i++){

            //从原数组中的指定开始位置和长度 截取元素放到新的数组中
            $result[] = array_slice($firstArray, $start, $enum);

            //开始位置加上累加元素的个数
            $start += $enum;
        }
        //数组剩余部分分别加到结果集的前几项中
        $secondLength = $allLength - $firstLength;
        for($i=0; $i<$secondLength; $i++){
            array_push($result[$i], $array[$i + $firstLength]);
        }
    }
    return $result;
}
/**
 * 站长平台主动提交
 */
function zz_post_submit($links,$api='') {
if(!$api){
  $api = 'http://data.zz.baidu.com/urls?site='.home_url().'&token='.ct_get_option('tt_k_submit_token');
}
if(!is_array($links)){
  $links = array($links);
}
$ch = curl_init();
$options =  array(
    CURLOPT_URL => $api,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => implode("\n", $links),
    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
echo $result;
}


/**
 * 每日0点自动提交站长平台.
 *
 * @since 2.0.0
 *
 * @param $id
 *
 * @return bool
 */
function ct_auto_submit_baidu()
{
$myposts = get_posts('numberposts=-1');
$i=0;
foreach($myposts as $post ) {
$urls[]=get_permalink($post->ID);
 $i++;
}
$urls = splitArray($urls, 2000);
foreach($urls as $url ) {
 zz_post_submit($url);
}
}
if(ct_get_option('tt_k_submit_token') && ct_get_option('tt_enable_k_auto_submit_baidu',false)){
add_action('ct_setup_common_daily_event', 'ct_auto_submit_baidu');
}

// Fanly Submit
add_action('init', 'K_FanlySubmit', 100);
function K_FanlySubmit() { // 自定义文章类型
	$Fanly_type = ct_get_option('tt_enable_k_baidutjfl');//获取选项
	if ( is_array($Fanly_type) && $Fanly_type ) {
		foreach($Fanly_type as $type) {
			//add_action('save_'.$type, 'k_fanly_submit', 10, 2);
			add_filter('manage_'.$type.'_posts_columns', 'k_fanly_submit_add_post_columns');
			add_action('manage_'.$type.'s_custom_column', 'k_fanly_submit_render_post_columns', 10, 2);
		}
	}
}
add_action('post_updated', 'k_fanly_submit', 10, 3);
function k_fanly_submit($post_ID, $post_after, $post_before) {
	if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) return;
    if (wp_is_post_revision($post_ID) || wp_is_post_autosave($post_ID)) return;
	if( isset($_POST['Fanly_Submit_CHECK']) || ($post_after->post_status=='publish' && in_array(get_post_meta($post_ID,'Fanly_Submit',true), array('dailysubmit','linksubmit'))) ){
		//获取选项
		$Fanly = get_option('FanlySubmit');
        $Fanly['Site'] = home_url();
        $Fanly['Token'] = ct_get_option('tt_k_submit_token');
		$P_Fanly_Submit = isset($_POST['Fanly_Submit']) ? $_POST['Fanly_Submit'] : '';
		$Fanly_Submit = get_post_meta($post_ID,'Fanly_Submit',true);
		//$future = $post->post_status=='future' ? true :false;//定时文章
		//判断是否设置新增
		if( $Fanly_Submit!='-1' && $Fanly_Submit!='OK' ){
			if($P_Fanly_Submit=='dailysubmit'){update_post_meta($post_ID, 'Fanly_Submit', 'dailysubmit');}
			//else{update_post_meta($post_ID, 'Fanly_Submit', 0);}
			//if($future){update_post_meta($post_ID, 'Fanly_Submit_Future', 1);}
		}

		//判断文章状态与推送状态 返回/继续
		if( $post_after->post_status != 'publish' || $Fanly_Submit=='OK' )return;
		//执行
		if( ($P_Fanly_Submit && $post_after->post_status=='publish') || (in_array($Fanly_Submit,array('dailysubmit','linksubmit')) && !isset($_POST['Fanly_Submit_CHECK'])) ){
			$type = $P_Fanly_Submit=='dailysubmit' || ($Fanly_Submit=='dailysubmit'&&$P_Fanly_Submit=='') ? '&type=daily' : '';
			$api_url = 'http://data.zz.baidu.com/urls?site='.$Fanly['Site'].'&token='.$Fanly['Token'].$type;
			$link = $Fanly['Site']==home_url() ? get_permalink($post_ID) : str_replace(home_url(),$Fanly['Site'],get_permalink($post_ID));

			$cambrian_re = wp_remote_post($api_url, array(
				'headers'	=> array('Accept-Encoding'=>'','Content-Type'=>'text/plain'),
				'timeout'	=> 10,
				'sslverify'	=> false,
				'blocking'	=> true,
				'body'		=> $link
			));

			if ( is_wp_error( $cambrian_re ) ) {
				update_post_meta($post_ID, 'Fanly_Submit', '-1');
				$Fanly['msg'] = $cambrian_re->get_error_message();//错误信息
				update_option('FanlySubmit', $Fanly);//更新选项
			} else {
				//若同步成功，则给自定义栏目Fanly_Submit
				$res = json_decode($cambrian_re['body'], true);
				if($res['success']==1){
					if($type){//快速收录
						$Fanly['daily'] = $res['remain_daily'].'|'.date('Ymd');
					}else{
						$Fanly['link'] = $res['remain'].'|'.date('Ymd');
					}
					update_post_meta($post_ID, 'Fanly_Submit', 'OK');// OK 成功
				}elseif($res['remain']==0||$res['success']==0){//当天剩余的可推送url条数
					if($res['success_daily']==0){//当快速收录为0时，自动用普通收录重试
						$linksubmit_re = wp_remote_post('http://data.zz.baidu.com/urls?site='.$Fanly['Site'].'&token='.$Fanly['Token'], array('timeout'=>10,'sslverify'=>false,'blocking'=>true,'body'=>$body));
						$linksubmit_json = json_decode($linksubmit_re['body'], true);
						if($linksubmit_json['success']==1){
							update_post_meta($post_ID, 'Fanly_Submit', 'OK');// OK 成功
						}
					}
					$Fanly['link'] = $res['remain'].'|'.date('Ymd');
				}else{//未知错误 提交失败
					update_post_meta($post_ID, 'Fanly_Submit', '-1');
					$Fanly['err']=$res;//调试
				}
				update_option('FanlySubmit', $Fanly);//更新选项
			}
		}
	}
}

//获取当前文章类型
function k_fanly_submit_get_post_type() {
  global $post, $typenow, $current_screen;
  if ( $post && $post->post_type ) {return $post->post_type;
  } elseif ( $typenow ) {return $typenow;
  } elseif ( $current_screen && $current_screen->post_type ) {return $current_screen->post_type;
  } elseif ( isset( $_REQUEST['post_type'] ) ) {return sanitize_key( $_REQUEST['post_type'] );
  } elseif ( isset( $_REQUEST['post'] ) ) {return get_post_type( $_REQUEST['post'] );
  } return 'post';
}
//默认数据
add_action('admin_init', 'k_fanly_submit_default_options');
function k_fanly_submit_default_options(){
	$Fanly = get_option('FanlySubmit');//获取选项
    $Fanly_type = ct_get_option('tt_enable_k_baidutjfl');//获取选项
	if( $Fanly == '' ){
		$Fanly = array(//设置默认数据
			'Types'		=> $Fanly_type,
			'Token'	=> ct_get_option('tt_k_submit_token'),
			'Default'	=> 'true',
		);
		update_option('FanlySubmit', $Fanly);//更新选项
	}
}

//同步
add_action( 'admin_menu', 'k_fanly_submit_create' );
function k_fanly_submit_create(){
	$Fanly_type = ct_get_option('tt_enable_k_baidutjfl');//获取选项
	if(is_array($Fanly_type) && in_array(k_fanly_submit_get_post_type(),$Fanly_type)){
		add_action( 'post_submitbox_misc_actions', 'k_fanly_submit_to_publish_metabox' );//同步选项
	}
}
add_action( 'add_meta_boxes', 'k_fanly_submit_register_meta_boxes' );
function k_fanly_submit_register_meta_boxes() {
	global $post_id;
	if(	class_exists( 'Classic_Editor' ) && (
		(get_option('classic-editor-replace')=='classic' && get_option('classic-editor-allow-users')=='disallow') ||
		(get_option('classic-editor-allow-users')=='allow' && get_post_meta($post_id ,'classic-editor-remember',true)=='classic-editor')
	))return;
	$Fanly_type = ct_get_option('tt_enable_k_baidutjfl');//获取选项
	if(!is_array($Fanly_type))return;
	add_meta_box(
		'fanly-submit-meta-box',
		'Fanly Submit',
		'fanly_submit_to_publish_metabox',
		$Fanly_type,
		'normal',
		'high',
		array(
			'__block_editor_compatible_meta_box' => true,
		)
	);
}
//同步选项
function k_fanly_submit_to_publish_metabox() {
	$Fanly = get_option('FanlySubmit');//获取选项
    $Fanly['Token'] = ct_get_option('tt_k_submit_token');
    $Fanly['Default'] = 'true';
	if($Fanly['Token']=='')return;
    global $post,$post_ID;
	$fanly_submit	= get_post_meta($post_ID,'Fanly_Submit',true);
	$remain_d		= explode('|',@$Fanly['daily']);//快速收录
	$remain_l		= explode('|',$Fanly['link']);//普通收录
	$checked		= ($Fanly['Default']=='true' && $fanly_submit=='') || $fanly_submit=='dailysubmit' || $fanly_submit=='linksubmit' ? 'checked="checked"' : '';

	if($fanly_submit=='OK'){//成功
		$z = @$remain_d[1]==date('Ymd') ? '['.$remain_d[0].']' : '';
		$input = '
			<input id="Fanly_Submit" type="checkbox" checked="checked" disabled>
			<label for="Fanly_Submit" class="selectit">成功'.$z.'</label>
		';
	}elseif( strtotime(date($post->post_date))+24*60*60 <= time() && $post->post_status=='publish'){//周级收录/文章发布已经超过二十四小时
		if($remain_l[0]==0 && $remain_l[1]==date('Ymd')){
			$input = '<label for="Fanly_Submit" class="selectit">上限<a style="font-weight:bold;color:#0066FF;text-decoration:none;" href="javascript:;" title="超过提交配额数量">?</a></label>';
		}else{
			$repost_text = $fanly_submit=='-1' ? '失败重试' : '普通';
			$input = '
			<label class="selectit"><input name="Fanly_Submit" type="checkbox" value="linksubmit" '.$checked.'>'.$repost_text.'</label>';
		}
	}else{
		if(@$remain_d[0]==0 && @$remain_d[1]==date('Ymd')){//快速收录
			$input = '
			<label class="selectit"><input name="Fanly_Submit" type="checkbox" value="dailysubmit" '.$checked.'>上限</label>';
		}else{
			$z = @$remain_d[1]==date('Ymd') ? '['.$remain_d[0].']' : '';
			$realtime_text = $fanly_submit=='-1' ? '重试' : '快速';
			$input = '
				<label class="selectit"><input name="Fanly_Submit" type="checkbox" value="dailysubmit" '.$checked.'>'.$realtime_text.$z.'</label>
			';
		}
		if(@$remain_l[0]==0 && @$remain_l[1]==date('Ymd')){//普通收录
			$input .= '

			<label class="selectit"><input name="Fanly_Submit" type="checkbox" value="linksubmit" '.$checked.'>上限</label>';
		}else{
			$batch_text = $fanly_submit=='-1' ? '重试' : '普通';
			$input .= '
			<label class="selectit"><input name="Fanly_Submit" type="checkbox" value="linksubmit">'.$batch_text.'</label>';
		}
	}
	echo '<div class="misc-pub-section misc-pub-post-status"><input name="Fanly_Submit_CHECK" type="hidden" value="true">百度收录：<span id="submit-span">'.$input.'</span></div>';
}

// 文章列表字段
function k_fanly_submit_add_post_columns($columns) {
    $columns['Fanly_Submit'] = '收录提交';
    return $columns;
}
function k_fanly_submit_render_post_columns($column_name, $id) {
    switch ($column_name) {
		case 'Fanly_Submit':
			echo get_post_meta( $id, 'Fanly_Submit', TRUE)=='OK' ? '提交成功' : (get_post_meta( $id, 'Fanly_Submit', TRUE)=='-1' ? '提交失败' : '未提交'); //数据提交
			break;
    }
}


// 获取文章剩余图片数量
function ct_get_post_img_count($content){
    $pattern = "/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>/i";
    preg_match_all($pattern, $content, $mat);
    return count($mat[0]);
}

// 获取奖品名称
function ct_get_lottery_name($id){
    $lotterys = array(
            '0' => __('谢谢参与', 'tt'),
            '1' => CREDIT_NAME,
            '2' => __('月费会员', 'tt'),
            '3' => __('年费会员', 'tt'),
            '4' => __('永久会员', 'tt')
        );
    foreach($lotterys as $lottery => $name){
      if($id == $lottery){
        return $name;
      }
    }
}

// 奖品发放
function ct_lottery_give($id){
    $id = $id+1;
    $user_id = get_current_user_id();
    $lottery = ct_get_option('ct_credit_lottery_'.$id);
    $lottery_count = ct_get_option('ct_credit_lottery_count_'.$id);
    switch ($lottery) {
            case 1:
                return ct_update_user_credit($user_id, $lottery_count, sprintf(__('抽奖活动奖励获得%1$d%2$s', 'tt'), $lottery_count,CREDIT_NAME), true);
            case 2:
                delete_user_meta($user_id, 'tt_vip_down_count');
                return ct_add_or_update_member($user_id, Member::MONTHLY_VIP);
            case 3:
                delete_user_meta($user_id, 'tt_vip_down_count');
                return ct_add_or_update_member($user_id, Member::ANNUAL_VIP);
            case 4:
                delete_user_meta($user_id, 'tt_vip_down_count');
                return ct_add_or_update_member($user_id, Member::PERMANENT_VIP);
        }
  return false;
}


/**
 * 创建赞赏订单.
 *
 * @since 2.0.0
 *
 * @param $product_id
 * @param string $product_name
 * @param int    $order_quantity
 * @param int    $parent_id
 *
 * @return bool|array
 */
function ct_create_admire_order($product_id, $product_name = '', $order_price = 2, $parent_id = 0)
{
    $user_id = get_current_user_id() ? get_current_user_id() : '0';
    $order_id = 'Z'.ct_generate_order_num();
    $order_time = current_time('mysql');
    $currency = 'cash';

    global $wpdb;
    $prefix = $wpdb->prefix;
    $orders_table = $prefix.'tt_orders';
    $insert = $wpdb->insert(
        $orders_table,
        array(
            'parent_id' => $parent_id,
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'order_time' => $order_time,
            'order_price' => $order_price,
            'order_currency' => $currency,
            'order_quantity' => 1,
            'order_total_price' => $order_price,
            'user_id' => $user_id,
        ),
        array(
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%f',
            '%s',
            '%d',
            '%f',
            '%d',
        )
    );
    if ($insert) {
        if ($currency == 'cash') {
            do_action('ct_order_status_change', $order_id);
        }

        return array(
            'insert_id' => $wpdb->insert_id,
            'order_id' => $order_id,
            'total_price' => $order_price,
        );
    }

    return false;
}

function post_check_wx_login_callback(){
    $key = $_POST['key'];
    $_data_transient_key = md5('tt_oauth_temp_data_' . $key);
    $oauth_data_cache = get_transient($_data_transient_key);
    $oauth_data = (array)maybe_unserialize($oauth_data_cache);
    if($oauth_data['type']==='new'){
      $user_id = $oauth_data['data'];
      $url = $oauth_data['url'];
      wp_set_current_user( $user_id );
      wp_set_auth_cookie( $user_id );
      setcookie('Cute_cache',1, time() + 3*24*60*60, '/', $_SERVER['HTTP_HOST'], false);
      delete_transient($_data_transient_key);
      $result_json = array(
					'status' => '200',
                    'msg' => '登录成功',
                    'url' => $url,
				);
        }elseif($oauth_data['type']==='key'){
         $url = $oauth_data['url'];
         $result_json = array(
					'status' => '200',
                    'msg' => '登录成功',
                    'url' => $url,
				);
    }else{
      $result_json = array(
					'status' => '201'
				);
    }
	echo json_encode($result_json);
	exit;
}
add_action( 'wp_ajax_check_wx_login', 'post_check_wx_login_callback');
add_action( 'wp_ajax_nopriv_check_wx_login', 'post_check_wx_login_callback');

function ct_get_redirect(){
if( isset($_GET['redirect']) ) return urldecode($_GET['redirect']);
        if( isset($_GET['redirect_uri']) ) return urldecode($_GET['redirect_uri']);
        if( isset($_GET['redirect_to']) ) return urldecode($_GET['redirect_to']);
        if( isset($_SERVER['HTTP_REFERER']) ) return urldecode($_SERVER['HTTP_REFERER']);
        return home_url();
}



function ct_get_zs_user(){
  global $post,$wpdb;
  $prefix = $wpdb->prefix;
  $order_table = $prefix.'tt_orders';
  $orders = $wpdb->get_results("SELECT * FROM $order_table WHERE `product_id`=$post->ID and `order_id` LIKE '%Z%' and `parent_id`=0 and `order_status`=4 and `deleted`=0");
  $count = count($orders);
  $users = array();
  $i = 1;
  foreach($orders as $order){
    $i++;
    $user = get_user_by('id', $order->user_id);
    $_user['ID'] = $user->ID;
    $_user['display_name'] = $user->display_name;
    $_user['avatar'] = ct_get_avatar($user);
    $_user['money'] = $order->order_price;
    $_user['link'] = get_author_posts_url($user->ID, $user->user_nicename);
    $users[] = $_user;
    if($i>5) break;
  }
  $result_json = array(
					'count' => $count,
                    'users' => $users,
				);
  return $result_json;
}

function ashu_add_page($title,$slug,$page_template=''){
    $allPages = get_pages();//获取所有页面
    $exists = false;
    foreach( $allPages as $page ){
        //通过页面别名来判断页面是否已经存在
        if( strtolower( $page->post_name ) == strtolower( $slug ) ){
            $exists = true;
        }
    }
    if( $exists == false ) {
        $new_page_id = wp_insert_post(
            array(
                'post_title' => $title,
                'post_type'     => 'page',
                'post_name'  => $slug,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => '本页面为Cute主题自动创建，可修改内容或删除！',
                'post_status' => 'publish',
                'post_author' => 1,
                'menu_order' => 0
            )
        );
        //如果插入成功 且设置了模板
        if($new_page_id && $page_template!=''){
            //保存页面模板信息
            update_post_meta($new_page_id, '_wp_page_template',  $page_template);
        }
    }
}
function ashu_add_pages() {
	global $pagenow;
	//判断是否为激活主题页面
	if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ){
		ashu_add_page('关于我们','about','default'); //页面标题ASHU_PAGE 别名ashu-page  页面模板page-ashu.php
		ashu_add_page('联系留言','guestbook','default');
        ashu_add_page('购买流程','gmlc','default');
        ashu_add_page('支付方式','zffs','default');
        ashu_add_page('售后服务','shfw','default');
        ashu_add_page('投稿有奖','tgyj','default');
        ashu_add_page('广告合作','business','default');
        ashu_add_page('友情链接','links','core/templates/page/tpl.Page.Links.php');
        ashu_add_page('主题修改','ztxg','default');
        ashu_add_page('安装调试','azts','default');
        ashu_add_page('环境搭建','hjdj','default');
        ashu_add_page('开通VIP','get_vip','core/templates/page/tpl.Page.Vip.php');
	}
}
add_action( 'load-themes.php', 'ashu_add_pages' );


// 输入密码查看文章内容
function password_protected_post($atts, $content=null){
    extract(shortcode_atts(array('key'=>null), $atts));
    if(isset($_POST['password_key']) && $_POST['password_key']==$key){
        return '
			<div class="password_protected_post_content">'.$content.'</div>
		';
    }elseif(isset($_POST['password_key']) && $_POST['password_key']!=$key){
        return '
			<script>
				alert("密码错误，请仔细核对密码后重试！！！");
				window.location.href="'.get_permalink().'";
			</script>
		';

	}else{
        return '
			<form class="password_protected_post_form" action="'.get_permalink().'" method="post">
			<input type="password" id="password_key" name="password_key" size="20" placeholder="请输入密码查看隐藏内容"/>
			<input type="submit" value="确    定" />
			</form>
		';
    }
}
add_shortcode('pwd_protected_post','password_protected_post');
// 输入密码查看文章内容快捷按钮
function appthemes_add_pwd_protected_post() {
	if (wp_script_is('quicktags')){
?>
    <script type="text/javascript">
        QTags.addButton( 'pwd_protected_post', '文章密码保护', '[pwd_protected_post key="保护密码"]','[/pwd_protected_post]' );
    </script>
<?php
    }
}
add_action('admin_print_footer_scripts', 'appthemes_add_pwd_protected_post' );