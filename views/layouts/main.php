<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
	
	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
	<link rel="icon" href="/images/redis-ico.png" sizes="192x192">
	<style>
		.navbar-brand{
			padding: 0 !important;
		}
		.navbar-brand > img.redis-logo{
			height: 50px;
			float: left;
		}
		.brand-name{
			padding: 15px 5px;
			float: left;
		}
		.navbar{
			margin-bottom: 0;
		}
	</style>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '<img class="redis-logo" src="/images/redis-ico.png"><div class="brand-name" title="RedisWebManager">'.Yii::$app->name.'</div>',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
	    'options' => ['class' => 'navbar-nav navbar-left'],
	    'items' => [
		    ['label' => 'Overview', 'url' => ['/site/overview']],
		    ['label' => 'Redis-cli', 'url' => ['/site/redis-cli']],
		    // ['label' => 'Contact', 'url' => ['/site/contact']],
	    ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Bruce Xie <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
