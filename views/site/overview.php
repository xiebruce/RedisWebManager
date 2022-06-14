<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-22
 * Time: 03:22
 */
	
$title = 'Redis Manager';
if($server_ip=='127.0.0.1'){
	$this->title = '[local]-'.$title;
}else{
	$this->title = '[remote]-'.$title;
}

?>
<style>
	.col-one {
		font-weight: bold;
		/*width: 10%;*/
	}
	/*.col-two {
		width: 40%;
	}*/
</style>
<ul id="myTab" class="nav nav-tabs">
	<?php if(!empty($info)):?>
	<?php
		$i = 0;
		foreach($info as $key=>$val):?>
		<?php if($key=='Keyspace'):?>
			<li class="dropdown">
				<a href="#" id="<?=$key?>" class="dropdown-toggle" data-toggle="dropdown">
					<?=$key?><b class="caret"></b>
				</a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="<?=$key?>">
					<?php if(!empty($info)):?>
					<?php foreach($val as $key2=>$val2):?>
					<li>
						<a href="#<?=$key2?>" tabindex="-1" data-toggle="tab"><?=$key2?></a>
					</li>
					<?php endforeach;?>
					<?php endif;?>
				</ul>
			</li>
		<?php else:?>
			<li<?= $i==0 ? ' class="active"' : ''?>>
				<a href="#<?=$key?>" data-toggle="tab"><?=$key?></a>
			</li>
		<?php $i++; endif;?>
	<?php endforeach;?>
	<?php endif;?>
</ul>

<div id="myTabContent" class="tab-content">
	<?php if(!empty($info)):?>
	<?php
		$j = 0;
		foreach($info as $key=>$val):?>
			<?php if($key=='Keyspace'):?>
				<?php if(!empty($val)):?>
					<?php foreach($val as $key2=>$val2):?>
						<div class="tab-pane" id="<?=$key2?>">
							<div class="table-responsive">
								<table class="table table-hover table-striped table-bordered table-condensed">
									<thead>
										<colgroup>
											<col style="width:10%">
											<col style="width:90%">
										</colgroup>
									</thead>
									<tbody>
									<?php if(!empty($val2)):?>
										<?php foreach ($val2 as $key5=>$val5):?>
											<tr>
												<td class="col-one""><?=$key5?></td>
												<td class="col-two"><?=$val5?></td>
											</tr>
											<?php endforeach;?>
										<?php endif;?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endforeach;?>
				<?php endif;?>
			<?php else:?>
				<?php
				$serverInfo = [];
				if(!empty($val)){
					$eleCount = count($val);
					if($eleCount % 2 == 1){
						$val[''] = '';
					}
					$serverInfo = array_chunk($val, 2, true);
				}
				?>
				<div class="tab-pane<?=$j==0?' in active':''?>" id="<?=$key?>">
					<div class="table-responsive">
						<table class="table table-hover table-striped table-bordered table-condensed">
							<thead>
								<colgroup>
									<col style="width:10%">
									<col style="width:40%">
									<col style="width:10%">
									<col style="width:40%">
								</colgroup>
							</thead>
							<tbody>
							<?php if(!empty($serverInfo)):?>
								<?php foreach ($serverInfo as $key3=>$val3):?>
									<tr>
										<?php foreach ($val3 as $key4=>$val4):?>
											<td class="col-one""><?=$key4?></td>
											<td class="col-two"><?=$val4?></td>
										<?php endforeach;?>
									</tr>
								<?php endforeach;?>
							<?php endif;?>
							</tbody>
						</table>
					</div>
				</div>
			<?php $j++; endif;?>
	<?php endforeach;?>
	<?php endif;?>
</div>
