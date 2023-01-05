<?php
/**
 * Macacaのmain処理
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */

/*
 * 初期化
 * -------------- */
if (!defined('LOADED_CONFIG')) {
    session_start();
    include_once('../config.php');
}

# URI
# protocol://***.***.***/ の形式で記載すること
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/'); 

$kernel = new Kernel\Kernel();
$param = array('root_directory' => ROOT_URI,
               'path_site_key' => 'sample',
               'path_error_key' => 'error');
Kernel\init($kernel, $param);

/*
 * Object生成
 * -------------- */
$view_engine['templateengine']               = new TemplateEngine\TemplateEngine;
$view_engine['templateengine']->template_dir = $kernel->getPathView() . '/';
$view_engine['Error']                        = new TemplateEngine\TemplateEngine;
$view_engine['Error']->template_dir          = $kernel->getPathErrorView() . '/';
$kernel->setViewEngine($view_engine);

$_SERVER['SERVER_SOFTWARE'] = 'Macaca';
$_SERVER['SERVER_SIGNATURE'] = "<address>" . $_SERVER['SERVER_SOFTWARE'] . " at " . $_SERVER['SERVER_NAME'] . " Port " . $_SERVER['SERVER_PORT'] . "</address>";

$parser = array();
$kernel->setParser($parser);

/*
 * Controler実行
 * -------------- */
$kernel->execControler();

/*
 * View実行
 * -------------- */
$kernel->execViewEngine();
