<?php
/**
 * Created by PhpStorm.
 * User: Bruce
 * Date: 13/04/2017
 * Time: 16:37
 */
$this->title = 'Redis Manager';
$controller = $this->context->id;
$action = $this->context->action->id;
?>
<style>
    body{
        /*background: #fff3d3;*/
    }
    .container{
        max-width: 90%;
        font-size:14px;
        margin:20px auto 0 auto;
    }
    .container .key-type{
        height:40px;
        line-height: 40px;
    }
    .container .value{
        /*border:1px solid #ccc;*/
    }
    .keyword{
        max-width:300px;
        margin-right:5px;
    }
</style>


<div class="container">
    <div>
        <a href="/?keyword=<?=$keyword?>">Return To List</a>
    </div>
    <form class="key-type">
        <input type="input" name="specified_key" placeholder="Please enter compplete key" class="form-control pull-left keyword" value="<?=$specified_key?>">
        <input type="hidden" name="keyword" value="<?=$keyword?>">
        <button type="submit" class="btn btn-primary pull-left search-btn">Search</button>
    </form>
    <div class="key-type">Key Type: <?=$type?></div>
    <div class="value">
        <pre>
            <?php print_r($value)?>
        </pre>
    </div>
</div>
