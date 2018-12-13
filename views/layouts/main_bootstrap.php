<?php
use yii\helpers\Html;
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1 user-scalable=no">
        <meta charset="utf-8">
	    <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <script src="/js/jquery.min.js"></script>
        <script src="/bootstrap-3.3.7-dist/js/bootstrap.js"></script>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="/bootstrap-3.3.7-dist/css/bootstrap.min.css">
        <!-- Optional theme -->
        <link rel="stylesheet" href="/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
	    <link rel="icon" href="/images/redis-ico.png" sizes="192x192">
	    <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <?=$content?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>