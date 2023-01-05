<?php
/**
 * メインページのコントローラ
 *
 * Macacaにおける処理のコアとなります。
 * 引数の監理やMVCの紐付けを行います。
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */

$argv = $this->getARGV();
$view_engine = $this->getViewEngine();

# setString(文字列)で保持したデータはgetString()で読み出すことができる。
# StringはFoobarのように任意の文字列に置換可能。
$view_engine['templateengine']->setString("main");