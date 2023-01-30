<?php
/**
 * MacacaのKernelクラス
 *
 * Macacaにおける処理のコアとなります。
 * 引数の監理やMVCの紐付けを行います。
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */

namespace Kernel;

define('ALL_BITS', -1);

// フラグ管理用の定義
define('KERNEL_FLAG_CONTROLER_IS_UNNECESSARY', 1 << 0);
define('KERNEL_FLAG_URI_IS_ARTICLES',          1 << 1);

// URI分離のための定義
define('KERNEL_PARAM_DIVIDE_URI',                   2);
define('KERNEL_PARAM_DIVIDE_URI_PATH',              0);
define('KERNEL_PARAM_DIVIDE_URI_PARAM',             1);

// QUERY分離のための定義
define('KERNEL_PARAM_DIVIDE_QUERY',                 2);
define('KERNEL_PARAM_DIVIDE_QUERY_NAME',            0);
define('KERNEL_PARAM_DIVIDE_QUERY_VALUE',           1);

// ファイル読出しのための定義
define('KERNEL_READ_BYTES',                      1024);

// ファイルの拡張子指定のための定義
define('KERNEL_PARAM_DIVIDE_FILENAME_EXTENTION',    1);

/**
 * EnumViewEngineクラス
 *
 * ViewEngineの種別をEnum的に扱うためのクラス。
 * 
 * @category Enum
 * @package  Kernel
 */
class EnumViewEngine
{
    const NONE = 0;
    const RAW = 1;
    const TEMPLATEENGINE = 2;
}


/**
 * Kernelクラス
 *
 * Macacaにおける処理のコアとなります。
 * 引数の監理やMVCの紐付けを行います。
 * 
 * @category Kernel
 * @package  Kernel
 */
class Kernel
{
    // 各種パラメータの保持
    public $parameter;
    
    // コントローラを使用しないファイルの拡張子を指定
    public $controlerless_filename_extension = ['css', 'js', 'jpg', 'png'];

    // HTTPのステータスコード
    public $http_status_code = array(
                                    200 => 'OK',
                                    201 => 'Created',
                                    202 => 'Accepted',
                                    204 => 'No Content',
                                    300 => 'Multiple Choices',
                                    301 => 'Moved Permanently',
                                    302 => 'Moved Temporarily',
                                    304 => 'Not Modified',
                                    400 => 'Bad Request',
                                    401 => 'Unauthorized',
                                    403 => 'Forbidden',
                                    404 => 'Not Found',
                                    500 => 'Internal Server Error',
                                    501 => 'Not Implemented',
                                    502 => 'Bad Gateway',
                                    503 => 'Service Unavailable',
                                    506 => 'Variant Also Negotiates',
                               );


    public function __construct()
    {
        $this->parameter = [];
        $this->parameter['flag'] = 0;
        $this->parameter['PathControler'] = __ABSPATH__ . 'controler';
        $this->parameter['PathView'] = __ABSPATH__ . 'view';
        $this->parameter['PathViewc'] = __ABSPATH__ . 'view_c';
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
     * Subディレクトリの設定。
     *
     * @param array $param
     * @return string | null
     */
    public function setSubDir($param)
    {
        $this->parameter['SubDir'] = (isset($param['sub_dir'])) ? $param['sub_dir'] : null;
    }

    /**
     * URIの抽出。
     *
     * @param array $param
     * @return string
     */
    public function setURI($param)
    {
        $uri = explode('?',
                       $_SERVER["REQUEST_URI"],
                       KERNEL_PARAM_DIVIDE_URI)[KERNEL_PARAM_DIVIDE_URI_PATH];
        if (isset($param['sub_dir'])) {
            $uri = preg_replace("/^\\" . $param['sub_dir'] ."/", '', $uri);
        }
        $this->parameter['URI'] = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 引数の抽出。
     *
     * @param void
     * @return array
     */
    public function setARGV()
    {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            if ($_SERVER['QUERY_STRING'] === "") {
                $argv = array();
            } else {
                $ary = explode('&', $_SERVER['QUERY_STRING']);
                foreach ($ary as $equation) {
                    $parameters = explode('=', $equation, KERNEL_PARAM_DIVIDE_QUERY);
                    // 先勝ちで値を格納する
		    if (!isset($argv[$parameters[KERNEL_PARAM_DIVIDE_QUERY_NAME]]) &&
		        count($parameters) > 1) {
                        $argv[$parameters[KERNEL_PARAM_DIVIDE_QUERY_NAME]] = $parameters[KERNEL_PARAM_DIVIDE_QUERY_VALUE];
                    }
                }
            }
        } else {
            $argv = $_POST;
        }
        $this->parameter['ARGV'] = isset($argv) ? $argv : array();
    }

    /**
     * メソッドの抽出。
     *
     * @param Kernel $kernel
     * @return string
     */
    public function setMethod()
    {
        // '_method'でメソッド名が定義されていたら当該メソッドとして扱う
        $method = (isset($this->parameter['ARGV']['_method'])) ? $this->parameter['ARGV']['_method'] : null;
        if (is_null($method)) {
            $method = $_SERVER['REQUEST_METHOD'];
        } else {
            $method = strtoupper(htmlspecialchars($method, ENT_QUOTES, 'UTF-8'));
        }
        
        $this->parameter['Method'] = $method;
    }

    /**
     * 引数の数を抽出。
     *
     * @param Kernel $kernel
     * @return int
     */
    public function setARGC()
    {
        $this->parameter['ARGC'] = count($this->getARGV());
    }

    /**
     * フラグのビットを立てる。
     *
     * @param int $value
     * @param int $bits
     * @return int | null
     */
    public function setBits($value, $bits)
    {
        return is_int($value) ? ($value | $bits) : null;
    }

    /**
     * フラグのビットを削除する。
     *
     * @param int $value
     * @param int $bits
     * @return int | null
     */
    public function unsetBits($value, $bits)
    {
        return is_int($value) ? ($value & (ALL_BITS ^ $bits)) : null;
    }

    /**
     * コントローラを探す。
     *
     * @param void
     * @return int | null
     */
    public function findControler()
    {
        $controler = '';

        if (mb_substr($this->getURI(), -1) !== '/') {
            // URIがファイルを示していればそのまま処理する。
            $ary = explode('/', $this->getURI());
            $ary_keys = array_keys($ary);
            $ary_last_key = end($ary_keys);
            $file = explode('.', $ary[$ary_last_key]);
            if (count($file) > 1) {
               $file_keys = array_keys($file);
               $file_extension_key = end($file_keys);
               if (in_array($file[$file_extension_key],
                            $this->controlerless_filename_extension)) {
                   // コントローラを使用しないファイルであれば、
                   // KERNEL_FLAG_CONTROLER_IS_UNNECESSARYフラグを立てる
                   $this->set('flag',
                              $this->setBits($this->get('flag'),
                                             KERNEL_FLAG_CONTROLER_IS_UNNECESSARY));
               } else {
                   // コントローラを使用するファイルであれば、
                   // 拡張子をPHPに変更する
                   $file[$file_extension_key] = 'php';
                   $this->set('flag',
                              $this->unsetBits($this->get('flag'),
                                               KERNEL_FLAG_CONTROLER_IS_UNNECESSARY));
               }
            } else {
               // URIが拡張子を持っていなければ、当該名のコント
               // ローラとして扱う
               $file[KERNEL_PARAM_DIVIDE_FILENAME_EXTENTION] = 'php';
               $this->set('flag',
                          $this->unsetBits($this->get('flag'),
                                           KERNEL_FLAG_CONTROLER_IS_UNNECESSARY));
            }

            if (($this->get('flag') & KERNEL_FLAG_CONTROLER_IS_UNNECESSARY) === 0) {
	        // コントローラを必要とするのであれば、コントローラのファイル名を
		// 再生成する
                $ary[$ary_last_key] = implode('.', $file);
                $controler = implode('/', $ary);
            } else {
                $controler = null;
            }
        } else {
            // URIがディレクトリを示していればindexを参照したと判断して処理する。
            $controler = $this->getURI() . 'index.php';
        }

	// ルートディレクトリが異なる場合はコントローラのパスを補正する。
        if (!empty($this->getRootDirectory())) {
            $controler = $this->convertRootUri($controler);
        }
	
	// 最終的なコントローラを決定する。
        if (($this->get('flag') & KERNEL_FLAG_CONTROLER_IS_UNNECESSARY) === 0 &&
            file_exists($this->getPathControler() . $controler)) {
            $this->setControler($this->getPathControler() . $controler);
        } else {
            $this->setControler(null);
        }
    }

    /**
     * コントローラを実行する。
     *
     * @param void
     * @return void
     */
    public function execControler()
    {
        $this->findControler();

        if (!is_null($this->getControler())) {
            include($this->getControler());
        }
    }

    public function findView()
    {
        $view = '';

        if (mb_substr($this->getURI(), -1) !== '/') {
            $ary = explode('/', $this->getURI());
            $ary_keys = array_keys($ary);
            $ary_last_key = end($ary_keys);
            $file = explode('.', $ary[$ary_last_key]);
            if (count($file) > 1) {
               $file_keys = array_keys($file);
               $file_extension_key = end($file_keys);
               $this->setViewFilenameExtention($file[$file_extension_key]);
               switch ($file[$file_extension_key]) {
                   case 'html':
                   case 'htm':
                   case 'tpl':
                       $file[$file_extension_key] = 'tpl';
                       $this->setEngine(EnumViewEngine::TEMPLATEENGINE);
                       break;
                   case 'php':
                       $this->setEngine(EnumViewEngine::NONE);
                       break;
                   default:
                       $this->setEngine(EnumViewEngine::RAW);
                       break;
               }
            } else {
               $file[1] = 'tpl';
               $this->setViewFilenameExtention($file[1]);
               $this->setEngine(EnumViewEngine::TEMPLATEENGINE);
            }

            if ($this->getEngine() !== EnumViewEngine::NONE) {
                $ary[$ary_last_key] = implode('.', $file);
                $view = implode('/', $ary);
            } else {
                $view = null;
            }
        }
        else {
            $view = $this->getURI() . 'index.tpl';
            $this->setViewFilenameExtention('tpl');
            $this->setEngine(EnumViewEngine::TEMPLATEENGINE);
        }

        if (!empty($this->getRootDirectory())) {
            $view = $this->convertRootUri($view);
        }

        if ($this->getEngine() === EnumViewEngine::RAW &&
            file_exists($this->getPathRoot() . $view)) {
            $this->setView($this->getPathRoot() . $view);
        } elseif (file_exists($this->getPathView() . $view)) {
            $this->setView($this->getPathView() . $view);
        } else {
            $this->setView(null);
            $this->setEngine(EnumViewEngine::NONE);
        }
    }

    public function execViewEngine()
    {
        $this->findView();
        if (is_null($this->getView())) {
            if ($this->getEngine() === EnumViewEngine::NONE &&
                $this->getViewFilenameExtention() !== 'php') {
                $this->execViewError(404);
                exit;
            }
            return;
        }
        switch ($this->getEngine()) {
            case EnumViewEngine::NONE:
                if ($this->setViewFilenameExtention !== 'php') {
                    $this->execViewError(404);
                    exit;
                }
                break;
            case EnumViewEngine::RAW:
                $this->execViewEngineRaw();
                break;
            case EnumViewEngine::TEMPLATEENGINE:
                $this->getViewEngine()['templateengine']->display($this->getView());
                break;
            default:
                break;
        }
    }

    public function execViewEngineRaw()
    {
        $content_type = '';
        switch ($this->getViewFilenameExtention()) {
            case 'txt':
                $content_type = 'text/plain';
                break;
            case 'csv':
                $content_type = 'text/csv';
                break;
            case 'css':
                $content_type = 'text/css';
                break;
            case 'js':
                $content_type = 'text/javascript';
                break;
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'xls':
            case 'xlsx':
                $content_type = 'application/vnd.ms-excel';
                break;
            case 'ppt':
            case 'pptx':
                $content_type = 'application/vnd.ms-powerpoint';
                break;
            case 'doc':
            case 'docx':
                $content_type = 'application/vnd.msword';
                break;
            case 'jpg':
            case 'jpeg':
            case 'JPG':
            case 'JPEG':
                $content_type = 'image/jpeg';
                break;
            case 'png':
            case 'PNG':
                $content_type = 'image/png';
                break;
            case 'gif':
            case 'GIF':
                $content_type = 'image/gif';
                break;
            case 'bmp':
            case 'BMP':
                $content_type = 'image/bmp';
                break;
            case 'zip':
                $content_type = 'application/zip';
                break;
            case 'lzh':
                $content_type = 'application/x-lzh';
                break;
            case 'tar':
            case 'gz':
                $content_type = 'application/x-tar';
                break;
            case 'mp3':
                $content_type = 'audio/mpeg';
                break;
            case 'mp4':
                $content_type = 'audio/mp4';
                break;
            case 'mpeg':
            case 'mpg':
                $content_type = 'video/mpeg';
                break;
            default:
                $content_type = 'application/octet-stream';
                break;
        }
        header('Content-Type: ' . $content_type);

        $fp = fopen($this->getView(), 'r');
        for ($offset = 0; ($data = stream_get_contents($fp, KERNEL_READ_BYTES, $offset)) != false; $offset += KERNEL_READ_BYTES) {
            print($data);
        }
        fclose($fp);
    }

    public function execViewError($code) {
        if (isset($this->http_status_code[$code])) {
            header("HTTP/1.0 $code " . $this->http_status_code[$code]);
            if (file_exists(ERROR_VIEW_PATH . "/{$code}.tpl") &&
                isset($this->getViewEngine()['Error'])) {
		$uri = $this->getViewEngine()['Error']->template_dir . "{$code}.tpl";
                $this->getViewEngine()['Error']->display($uri);
            } else {
                echo($this->http_status_code[$code] . '.');
            }
        } else {
            header("HTTP/1.0 400 Bad Request");
            if (file_exists(ERROR_VIEW_PATH . '/400.tpl') &&
                isset($this->getViewEngine()['Error'])) {
		$uri = $this->getViewEngine()['Error']->template_dir . '400.tpl';
                $this->getViewEngine()['Error']->display($uri);
            } else {
                echo('Bad Request.');
            }
        }
    }

    public function convertDirectoryPath($key, $kind) {
            if (empty($kind) || empty($key)) {
            return '';
        }
        
        switch ($kind) {
            case 'view':
                $result = __ABSPATH__ . 'view/' . $key;
                break;
            case 'viewc':
                $result = __ABSPATH__ . 'view_c/' . $key;
                break;
            case 'controler':
                $result = __ABSPATH__ . 'controler/' . $key;
                break;
            default:
                $result = '';
                break;
        }

        return $result;
    }

    public function convertRootUri($uri) {
        return (!empty($uri)) ? implode('', explode($this->getRootDirectory(), $uri, 2)) : '';
    }
}
