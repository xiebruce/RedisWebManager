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
    .container {
	    max-width: 99%;
	    font-size: 14px;
    }
    .container .key-type{
        height:40px;
        line-height: 20px;
	    margin-bottom: 10px;
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
    <div class="key-type">
	    <div>KeyType : <?=$key_type?></div>
	    <?php if($value_type):?>
		    <div>valueType : <?=$value_type?></div>
	    <?php endif;?>
    </div>
    <div class="value">
        <pre>
            <?php
	            $str = print_r($value, true);
	            echo $str;
            ?>
        </pre>
    </div>
</div>
