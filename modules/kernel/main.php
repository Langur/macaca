<?php
/**
 * MacacaのKernelにおけるオブジェクトに対する処理
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */

namespace Kernel;

/**
 * Kernelオブジェクトへの初期化処理。
 *
 * @param Kernel $kernel
 * @param array $param
 * @return void
 */
function init($kernel, $param = null) {
    /**
     * セッションを保存。
     */
    $kernel->setSession($_SESSION);

    /**
     * Subディレクトリの設定。
     *
     * @param array $param
     * @return string | null
     */
    $kernel->setSubDir($param);

    /**
     * URIの抽出。
     *
     * @param array $param
     * @return string
     */
    $kernel->setURI($param);

    /**
     * 引数の抽出。
     *
     * @param void
     * @return array
     */
    $kernel->setARGV();
    
    /**
     * メソッドの抽出。
     *
     * @param Kernel $kernel
     * @return string
     */
    $kernel->setMethod();
    
    /**
     * 引数の数を抽出。
     *
     * @param Kernel $kernel
     * @return int
     */
    $kernel->setARGC();

    /**
     * アクセスされたポート番号を抽出。
     */
    $kernel->setServerPort(intval($_SERVER['SERVER_PORT']));


    /**
     * 各種パスの抽出。
     */
    $kernel->setPathRoot(substr(__ABSPATH__, 0, -1) . (isset($param['root_directory']) ? $param['root_directory'] : ''));

    $kernel->setPathView($kernel->convertDirectoryPath($param['path_site_key'], 'view'));

    $kernel->setPathViewc($kernel->convertDirectoryPath($param['path_site_key'], 'viewc'));

    $kernel->setPathControler($kernel->convertDirectoryPath($param['path_site_key'], 'controler'));

    $kernel->setPathErrorView($kernel->convertDirectoryPath($param['path_error_key'], 'view'));

    $kernel->setPathErrorViewc($kernel->convertDirectoryPath($param['path_error_key'], 'viewc'));

    $kernel->setRootDirectory((isset($param['root_directory'])) ? $param['root_directory'] : null);
}
