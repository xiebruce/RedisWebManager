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
    .keyword{
        max-width:300px;
        margin-right:5px;
    }
	.errMsg{
		color: #FF0000;
	}
</style>

<div class="table-responsive" id="key-list">
	<div class="container">
		<table class="table table-hover table-striped table-bordered table-condensed">
			<thead>
				<tr>
					<th><a href="/?keyword=<?=$keyword?>">Return To List</a></th>
				</tr>
				<tr>
					<td>
						<form class="key-type">
							<input type="input" name="specified_key" placeholder="Please enter compplete key" class="form-control pull-left keyword" value="<?=$specified_key?>">
							<input type="hidden" name="keyword" value="<?=$keyword?>">
							<button type="submit" class="btn btn-primary pull-left search-btn">Search</button>
						</form>
					</td>
				</tr>
				<tr>
					<th>
						
						<?php if($code==0):?>
							<div>KeyType : <?=$key_type?></div>
							<?php if($value_type && $value_type!='unknow'):?>
								<div>valueType : <?=$value_type?></div>
							<?php endif;?>
							<div>TTL : <?=$ttl?></div>
						<?php elseif($code==-1):?>
							<div class="errMsg"><?=$errMsg?></div>
						<?php endif;?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if($code==0):?>
				<tr>
					<td>
						<div>
				            <?php
					            // \yii\helpers\VarDumper::dump($value, 10, true);
					            echo \yii\helpers\VarDumper::dumpAsString($value, 10, true);
				            ?>
				        </div>
					</td>
				</tr>
				<?php endif;?>
			</tbody>
		</table>
	</div>
</div>
