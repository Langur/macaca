<?php
/**
 * MacacaのTemplateEngineクラス
 *
 * Macacaにおける処理のコアとなります。
 * 引数の監理やMVCの紐付けを行います。
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */

namespace TemplateEngine;

/**
 * TemplateEngineクラス
 *
 * Macacaにおける処理のコアとなります。
 * 引数の監理やMVCの紐付けを行います。
 * 
 * @category TemplateEngine
 * @package  TemplateEngine
 */
class TemplateEngine
{
    // 各種パラメータの保持
    private $parameter;
    public $template_dir = '';
    public $compile_dir = '';

    public function __construct()
    {
        $this->parameter = [];
        $this->parameter['flag'] = 0;
        $this->parameter['PathView'] = __ABSPATH__ . 'view';
	$this->template_dir = '';
    }

    // クラスを拡張するための処理
    public function __call($name, $args)
    {
        if (strncmp($name, 'get', 3) === 0) {
            return $this->get(substr($name, 3), reset($args));
        } 
        elseif (strncmp($name, 'set', 3) === 0) {
            return $this->set(substr($name, 3), reset($args));
        }
        elseif (strncmp($name, 'exec', 4) === 0) {
            return $this->exec(substr($name, 4), reset($args));
        }
        else {
        }

        throw new \BadMethodCallException('Method "' . $name . '" does not exist.');
    }

    // クラスを拡張するための処理
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    // クラスを拡張するための処理
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->parameter)) {
              return $this->parameter[$key];
        }

        return $default;
    }

    // クラスを拡張するための処理
    public function set($key, $value)
    {
        $this->parameter[$key] = $value;
    }

    // クラスを拡張するための処理
    public function exec($key, $func = null)
    {
        $func();
    }

    /**
     * Viewを表示する。
     *
     * @param int $value
     */
    public function display($value)
    {
        if (file_exists($value)) {
            include($value);
	}
    }
}
