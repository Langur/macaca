<!doctype html>
<html lang="ja">
<?php
/**
 * メインページのテンプレート
 * 
 * @copyright Copyright (c) 2017-2023 Akihisa ONODA
 * @license https://github.com/Langur/macaca/blob/master/LICENSE MIT
 * @link https://github.com/Langur/macaca#readme
 * @author Akihisa ONODA <akihisa.onoda@osarusystem.com>
 */
if (SITE_USE_OGP) {
    echo('    <head prefix="og: http://ogp.me/ns#">' . "\n");
}
else {
    echo('    <head>' . "\n");
}
?>
    <meta charset="utf-8">
    <title><?php
if (!empty($this->title)) {
    echo($this->title . ' | ');
}
echo(SITE_NAME);
?></title>
<?php
if (!empty($this->keywords)) {
    echo('    <meta name="keywords" content="' . $this->keywords . ',' . SITE_NAME . '">' . "\n");
}
else {
    echo('    <meta name="keywords" content="' . SITE_NAME . '">' . "\n");
}
if (!empty($this->description)) {
    echo('    <meta name="description" content="' . $this->description . '">' . "\n");
}
else {
    echo('    <meta name="description" content="' . SITE_NAME . '">' . "\n");
}
?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
<?php
if (SITE_USE_OGP) {
    echo('    <meta property="og:type" content="website">' . "\n");
    if (!empty($this->description)) {
        echo('    <meta property="og:description" content="' . $this->description . '">' ."\n");
    }
    else {
        echo('    <meta property="og:description" content="' . SITE_NAME . '">' . "\n");
    }
    echo('    <meta property="og:url" content="' . BASE_URL . '">' . "\n");
    if (defined('SITE_CACHE')) {
        echo('    <meta property="og:image" content="' . BASE_URL . 'images/thumbnail.png?' . SITE_CACHE . '">' . "\n");
    }
    else {
        echo('    <meta property="og:image" content="' . BASE_URL . 'images/thumbnail.png">' . "\n");
    }
    echo('    <meta property="og:site_name" content="' . SITE_NAME . '">' . "\n");
    if (!empty($this->title)) {
        echo('    <meta property="og:title" content="' . $this->title . '">' ."\n");
    }
    else {
        echo('    <meta property="og:title" content="' . SITE_NAME . '">' ."\n");
    }
    if (defined('SITE_EMAIL')) {
        echo('    <meta property="og:email" content="' . SITE_EMAIL . '">' ."\n");
    }
}

if (defined('SITE_CACHE')) {
    echo('    <link rel="stylesheet" href="' . BASE_URL . 'css/common.css?' . SITE_CACHE . '" media="all">' ."\n");
} else {
    echo('    <link rel="stylesheet" href="' . BASE_URL . 'css/common.css" media="all">' ."\n");
}
?>
</head>

<body>
<?php
echo($this->getString());
?><br>
</body>
</html>