<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-22
 * Time: 03:40
 */
	
$title = 'Redis Manager';
if($server_ip=='127.0.0.1'){
	$this->title = '[local]-'.$title;
}else{
	$this->title = '[remote]-'.$title;
}

?>
<style>
	.auto-complete {
		display: none;
	}
	select.auto-complete {
		margin: 0;
		padding: 0;
	}
	select.auto-complete option {
		padding: 5px 5px 5px 10px;
		margin: 0;
		border-bottom: 1px solid #CCCCCC;
	}
	.server-info {
		font-size: 16px;
		color: #FFFFFF;
		min-height: 40px;
		line-height: 40px;
		background: #555555;
		margin-bottom: 10px;
		border-radius: 4px;
		padding: 0 10px;
	}
	.server-info .db {
		background: #555555;
	}
</style>
<div class="table-responsive">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="server-info">
					<span><?='server ip:'.$server_ip. ' port:'.$port.' db:'?></span>
					<select class="db" name="db">
						<?php for($i=0;$i<$databaseCount;$i++):?>
							<option value="<?=$i?>"<?=$db==$i?' selected':''?>>db <?=$i?></option>
						<?php endfor;?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<form id="cmd-form">
					<input type="text" class="form-control" id="command" placeholder="Please input redis command..." autocomplete="off">
					<select class="form-control auto-complete" multiple="multiple">

					</select>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<br>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 output">
			
			</div>
		</div>
	</div>
</div>

<script>
	function set_text_value_position(tobj, spos){
		if(spos<0)
			spos = tobj.value.length;
		if(tobj.setSelectionRange){ //兼容火狐,谷歌
			setTimeout(function(){
				tobj.setSelectionRange(spos, spos);
				tobj.focus();
			}, 0);
		}else if(tobj.createTextRange){ //兼容IE
			var rng = tobj.createTextRange();
			rng.move('character', spos);
			rng.select();
		}
	}
	
	var commands = <?=$commands?>;
	
	$(document).ready(function (){
		$('#command').focus();
		
		var inputFocus = true;
		$('#command').on('focus', function (){
			inputFocus = true;
		});
		$('#command').on('blur', function (){
			inputFocus = false;
		});
		
		var preSelIndex = undefined;
		$(document).on('keyup', function (e){
			if(e.keyCode==38 || e.keyCode==40){
				$('#cmd-form .auto-complete').focus().find('option:first').attr('selected','selected');
				if(e.keyCode==38 && preSelIndex==0){
					$('#command').focus();
				}
				if(e.keyCode == 40 && inputFocus){
					var opts = [];
					for (var i in commands) {
						opts.push('<option><div class="cmd-hints">'+commands[i]+'</div></option>');
					}
					$('#cmd-form .auto-complete').html(opts.join('')).attr('size', 15).show();
				}
				preSelIndex = $('.auto-complete option:selected').index();
				return false;
			}
			
			if(e.keyCode==13){
				if(!inputFocus){
					var selOpt = $('.auto-complete option:selected').html();
					if(selOpt != undefined) {
						$('#command').val(selOpt);
					}
					$('#command').focus();
					set_text_value_position($('#command').get(0), -1);
				}else{
					$('.auto-complete').hide();
					return false;
				}
			}
			
			var command = $('#command').val().trim().toUpperCase();
			
			var options = [];
			if(command!=''){
				for (var i in commands) {
					var pos = commands[i].indexOf(command);
					if(pos > -1 && pos < 2){
						options.push('<option><div class="cmd-hints">'+commands[i]+'</div></option>');
					}
				}
			}
			
			if(options.length){
				var size = options.length > 15 ? 15 : options.length;
				$('#cmd-form .auto-complete').html(options.join('')).attr('size', size).show();
			}else{
				$('#cmd-form .auto-complete').empty().hide();
			}
		});
		
		$('body').on('click', function (e){
			var targetEle = e.target.tagName;
			if(targetEle != 'SELECT' && targetEle != 'OPTION' && targetEle != 'INPUT'){
				$('.auto-complete').empty().hide();
			}else{
				if(targetEle=='OPTION'){
					var clickedOpt = $(e.target).val();
					$('#command').val(clickedOpt);
				}
			}
		});
		
		//Select db
		$('select[name="db"]').on('change', function (){
			var db = $(this).val();
			var link = window.location.href
			if(link.indexOf('db=')>-1){
				link = link.replace(/db=(\d+)/, 'db='+db);
			}else{
				if(window.location.search){
					link += '&db='+db;
				}else{
					link += '?db='+db;
				}
			}
			window.location.href = link;
		});
		
		var csrfToken = $('meta[name="csrf-token"]').attr('content');
		$('#cmd-form').on('submit', function (){
			$.ajax({
				type:'post',
				url:'',
				data:{
					cmd:$('#command').val(),
					_csrf:csrfToken,
				},
				dataType:'json',
				success:function (response){
					var content = '';
					if(response.code == 0){
						content = response.content;
					}else{
						content = response.msg;
					}
					$('.output').html(content);
					
				},
				error:function (error){
					console.log(error);
				}
			});
			return false;
		});
	})
</script>