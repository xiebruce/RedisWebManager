<?php
/**
 * Created by PhpStorm.
 * User: Bruce
 * Date: 06/04/2017
 * Time: 17:14
 */

$title = 'Redis Manager';
if($server_ip=='127.0.0.1'){
    $this->title = '[local]-'.$title;
}else{
    $this->title = '[remote]-'.$title;
}

$controller = $this->context->id;
$action = $this->context->action->id;

$quickSearch = Yii::$app->params['quickSearch'];
$valDisplayType = Yii::$app->params['valDisplayType'] ?? 'popup';
?>

<style type="text/css">
	.container {
		font-size: 14px;
	}
    @-moz-document url-prefix() {
        fieldset { display: table-cell; }
    }
    th,td{
        vertical-align: middle !important;
    }
    #key-list{
        /*margin:0 200px 0 200px;*/
    }
	#key-list .first-row{
		font-weight: bold;
	}
    .table-hover > tbody > tr:hover > td,
    .table-hover > tbody > tr:hover > th {
        background-color: #f5f4e5;
    }
    .keyword{
        width:300px;
        margin-right:10px;
    }
    .underline{
        text-decoration: underline;
    }
    .pointer{
        cursor:pointer;
    }
    .cursor-not-allowed {
	    cursor: not-allowed;
    }
    .redis-value{
        display: none;
    }
    .quick-search{
        margin-right:5px;
    }
    .red{
        color: #ff0000;
    }
    .cursor-default{
        cursor:default;
    }
    .view-in-new-page{
        margin-right:5px;
    }
	#redis-value-modal{
		word-break: break-all;
		word-wrap: break-word;
	}
	#redis-value-modal .modal-dialog{
		width: <?=Yii::$app->params['modalWidth']??''?>;
	}
	.key-name{
		text-decoration: underline;
		color: #337ab7;
		cursor: pointer;
	}
</style>

<script src="/plugins/progress-bar/progress.js"></script>
<link href="/plugins/progress-bar/ui.progress-bar.css" type="text/css" rel="Stylesheet">

<div class="table-responsive" id="key-list">
	<table class="table table-hover table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th colspan="3">
				Redis Server Info:
				<span style="font-weight: normal;">
                        <?php if($code==0):?>
	                        <span class="underline">server ip</span>：<span><?=$server_ip?></span> |
                            <span class="underline">redis_version</span>: <span class="red"><?=$info['Server']['redis_version']?></span> |
                            <span class="underline">os</span>: <span class="red"><?=$info['Server']['os']?></span> |
                            <span class="underline">redis_mode</span>: <span class="red"><?=$info['Server']['redis_mode']?></span> |
                            <span class="underline">used_memory</span>: <span class="red"><?=$info['Memory']['used_memory_human']?></span> |
                            <select name="db">
		                        <?php for($i=0;$i<$databaseCount;$i++):?>
			                        <option value="<?=$i?>"<?=$db==$i?' selected':''?>>db <?=$i?></option>
		                        <?php endfor;?>
                            </select> |
                            <a href="javascript:void(0);" class="flush-db" title="Click to flush current db">FlushDB</a> |
                            <a href="javascript:void(0);" class="flush-all" title="Click to flush all db">FlushAll</a>
                        <?php else:?>
	                        <span class="underline">server ip</span>：<span><?=$serverIp?></span> |
                            <span class="red"><?=$error_msg?></span>
                        <?php endif;?>
                    </span>
			</th>
		</tr>
		<tr>
			<th colspan="3">
				<form id="search-form">
					<input type="input" name="keyword" placeholder="Please enter keyword" class="form-control pull-left keyword" value="<?=$keyword?>">
					<button type="submit" class="btn btn-primary pull-left search-btn">Search</button>
				</form>
			</th>
		</tr>
		<tr>
			<th colspan="3">
				<span>Quick Search:</span>
				<span>
					<?php if(!empty($quickSearch)):?>
						<?php foreach($quickSearch as $val):?>
							<a href="<?=$val?>" class="quick-search"><?=$val?></a>
						<?php endforeach;?>
					<?php endif;?>
				</span>
			</th>
		</tr>
		</thead>
		<tbody>
		<tr class="first-row">
			<td class="col-xs-1 text-center">
				<label>
					<input type="checkbox" class="select-all">
				</label>
			</td>
			<td class="text-center cursor-default">Key Name (Click to preview value)</td>
			<td class="col-xs-1 text-center cursor-default">Operation</td>
		</tr>
		<!-- key list row insert here (after .first-row) -->
		<tr>
			<td class="text-center">
				<label>
					<input type="checkbox" class="select-all">
				</label>
			</td>
			<td style="font-weight: bold;">
				Total Count: <span class="red"><?=$count?></span> keys
			</td>
			<td class="text-center">
				<button type="button" class="btn btn-danger batch-delete">Batch Del</button>
			</td>
		</tr>
		<!--<tr>
			<td colspan="3" class="text-center" style="height:50px;">no result</td>
		</tr>-->
		</tbody>
	</table>
</div>
<div class="text-center">
	<button class="btn btn-primary load-more">Load More</button>
</div>

<!-- Modal popup -->
<div class="modal fade" id="redis-value-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel">
					<!-- Insert title here -->
				</h4>
			</div>
			<div class="modal-body">
				<!-- Insert display content here -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<!--<button type="button" class="btn btn-primary">Submit</button>-->
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal -->
</div>

<!--<a href="#" data-toggle="popover" title="Example popover">
	popover
</a>-->
<script>
	var controller = '<?=$controller?>';
	var action = '<?=$action?>';
	var db = $('select[name="db"]').val();
	
	function getKeyList(iterator, keyword, operation){
		if(operation=='index' || operation=='search'){
			$('#key-list .inserted-row').remove();
			setTimeout(function (){
				if(!$('#key-list .progress-row').length && !$('#key-list .inserted-row').length){
					var progressRow = '';
					progressRow += '<tr class="progress-row">';
					progressRow += '<td colspan="3">';
					progressRow += '<div id="progress_bar" class="ui-progress-bar ui-container">';
					progressRow += '<div class="ui-progress" style="width: 0%;">';
					progressRow += '<span class="ui-label" style="display: block;">0%</span>';
					progressRow += '</div>';
					progressRow += '</div>';
					progressRow += '</td>';
					progressRow += '</tr>';
					$('#key-list .first-row').after(progressRow);
					getSearchProgress();
				}
			}, 800);
		}
		
		$.ajax({
			type: 'get',
			url: '/'+controller+'/get-key-list',
			data: {
				db:db,
				iterator: iterator,
				keyword: keyword,
			},
			dataType: 'json',
			success: function (response){
				if(response.code == 0){
					var keys = response.keys;
					var row = '';
					if(keys.length){
						var lastrow = '';
						var url = window.location.origin + '/' +controller + '/view-redis-value';
						if(window.location.search==''){
							url += '?';
						}else{
							url += window.location.search + '&';
						}
						for(var i=0;i<keys.length;i++){
							lastrow = i==keys.length - 1 ? ' last-row' : '';
							row += '<tr class="inserted-row'+lastrow+'">';
							row += '<td class="text-center col-xs-1">';
							row += 		'<input type="checkbox" name="keys[]" value="'+keys[i]+'">';
							row += 	'</td>';
							row += 	'<td class="col-xs-9">';
							row += 	'<span class="key-name" title="click to preview value of key: '+keys[i]+'">'+keys[i]+'</span>';
							row += 		'<div class="redis-value"></div>';
							row += 		'</td>';
							row += 		'<td class="text-center col-xs-2">';
							row += 		'<a href="'+url+'specified_key=' + keys[i] + '" title="Click to view value in new page" class="btn btn-info view-in-new-page">View</a>';
							row += 		'<button type="button" class="btn btn-danger delete" title="Click to delete key" key="'+keys[i]+'">Delete</button>';
							row += 	'</td>';
							row += '</tr>';
						}
					}else{
						// keys为空
						row += '<tr class="inserted-row"><td colspan="3" class="text-center" style="height:50px;">no result</td></tr>';
					}
					
					switch (operation){
						case 'index':
						case 'search':
							if($('#key-list .progress-row').length){
								$('#progress_bar .ui-progress').css('width','100%');
								$('#progress_bar .ui-progress .ui-label').html('100%');
								setTimeout(function (){
									$('#key-list .progress-row').hide(function (){
										$(this).remove();
										$('#key-list .first-row').after(row);
									});
								}, 100);
							}else{
								$('#key-list .first-row').after(row);
							}
							break;
						case 'loadmore':
							$('#key-list .last-row').after(row);
							$('.load-more').attr('isclick', 0);
					}
					$('#key-list .inserted-row').fadeIn(500);
					$('.load-more').data('iterator', response.iterator);
					if(response.iterator==0){
						$('.load-more').removeClass('btn-primary').addClass('disabled');
					}
				}else{
					console.log(response);
				}
			}
		});
	}
	
	//update search progress
	function getSearchProgress(){
		var db = $('select[name="db"]').val();
		$.ajax({
			type: 'get',
			url: '/'+controller+'/search-progress',
			data:{
				db:db,
			},
			success:function (response){
				if($('.progress-row').length){
					$('#progress_bar .ui-progress').css('width',response);
					$('#progress_bar .ui-progress .ui-label').html(response);
					setTimeout(function (){
						getSearchProgress();
					}, 1000);
				}
			}
		});
	}
	
    $(document).ready(function (){
    	// load first page
	    var keyword = $('#search-form input[name="keyword"]').val().trim();
        getKeyList(0, keyword, 'index');
        
        // Click quick search key
        $('.quick-search').on('click', function (e){
	        $('#search-form input[name="keyword"]').val($(this).html());
	        $('#search-form .search-btn').click();
	        e.preventDefault();
        });
        
        //loadmore
        $('.load-more').on('click', function (){
        	if($(this).attr('isclick')==1){
        		return false;
	        }
	        $(this).attr('isclick', 1);
        	if($(this).hasClass('disabled')){
        		return false;
	        }
	        var iterator = $('.load-more').data('iterator');
	        var keyword = $('#search-form input[name="keyword"]').val().trim();
	        getKeyList(iterator, keyword, 'loadmore');
        });
	
        // search
	    $('#search-form').on('submit', function (){
		    var keyword = $('#search-form input[name="keyword"]').val().trim();
		    var history_url = window.location.href;
		    if(history_url.indexOf('keyword=')>-1){
			    history_url = history_url.replace(/([&|?])(keyword=[^&]*)(&{0,1}.*)/, '$1keyword='+keyword+'$3');
		    }else{
			    if(window.location.search){
				    history_url += '&keyword='+keyword;
			    }else{
				    history_url = window.location.origin + '?keyword='+keyword;
			    }
		    }
		    window.history.pushState({}, null, history_url);
		    
		    getKeyList(0, keyword, 'search');
		    return false;
	    });
        
        var controller = '<?=$controller?>';
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Delete key
        $('#key-list').on('click','.delete',function (){
            var $this = $(this);
            $this.parents('tr').addClass('danger');
            if(!confirm('Confirm to delete ?')){
                $this.parents('tr').removeClass('danger');
                return false;
            }

            if($this.attr('isclick')==1){
                return false;
            }
	        $this.attr('isclick',1);
            
            var key = $this.attr('key');
	        var db = $('select[name="db"]').val();
            $.ajax({
                type:'post',
                url:'/'+controller+'/del-redis-key',
                data:{
                    keys:key,
                    db:db,
	                _csrf:csrfToken,
                },
                dataType:'json',
                success:function (responseText){
                    if(responseText.code==0){
                        $this.parents('tr').removeClass('danger').addClass('success');
                        $this.parents('tr').slideUp(300);
                    }else{
                        alert(responseText.msg);
                        $this.attr('isclick',0);
                    }
                }
            });
        });
	
        // Preview value of the key
	    // $('#key-list').on('click','.delete',function (){
        $('#key-list').on('click','.key-name', function (){
            var $this = $(this);
	        <?php if($valDisplayType=='inline'):?>
            if($this.next().is(':visible')){
	            $this.next().empty().hide();
                return false;
            }
            <?php endif;?>
            if($this.attr('isclick')==1){
                return false;
            }
            var key = $this.html();
            $this.attr('isclick',1);
	        var db = $('select[name="db"]').val();
            $.ajax({
                type:'get',
                url:'/'+controller+'/get-redis-val',
                data:{
                    key:key,
	                db:db,
	                // _csrf:csrfToken,
                },
                dataType:'json',
                success:function (responseText){
	                var reloadUrl = window.location.href.replace(/[&|?]page=\d+&per-page=\d+/,'');
                	<?php if($valDisplayType=='inline'):?>
		                if(responseText.code==0){
			                var str = '';
			                str += '------------------------------------<br>';
			                str += 'keyType => '+responseText.key_type+"<br>";
			                if(responseText.value_type!==''){
				                str += 'valueType => '+responseText.value_type+"<br>";
			                }
			                str += 'TTL => '+responseText.ttl+"<br>";
			                str += '------------------------------------<br>';
			                str += responseText.value;
			                $this.next('.redis-value').html(str);
			                $this.next('.redis-value').show();
		                }else if(responseText.code == -1){
			                var str = responseText.errMsg;
			                str += ' <a href="'+reloadUrl+'">Reload</a>';
			                $this.parent().html(str);
		                }
	                <?php else:?>
		                if(responseText.code==0){
			                var str = '';
			                str += 'KeyType => '+responseText.key_type+"<br>";
			                if(responseText.value_type!==''){
				                str += 'ValueType => '+responseText.value_type+"<br>";
			                }
			                str += 'TTL => '+responseText.ttl+"<br>";
			                if(responseText.key_type==null && responseText.value_type==null){
				                str += responseText.errMsg + "\n";
			                }
			                str += "----------------------------------------------------<br>";
			                str += responseText.value;
		                	$('#redis-value-modal .modal-title').html(key);
		                	$('#redis-value-modal .modal-body').html(str);
			                // $('#redis-value-modal').modal('handleUpdate');
			                $('#redis-value-modal').modal('show');
		                }else if(responseText.code == -1){
			                var str = responseText.errMsg;
			                str += ' <a href="'+reloadUrl+'">Reload</a>';
			                $this.parent().html(str);
		                }
	                <?php endif;?>
                    $this.attr('isclick',0);
                }
            });
        });

        // Select all
        $('.select-all').on('click', function (e){
            $('#key-list table input[type="checkbox"]').prop('checked',$(this).prop('checked'));
            e.stopPropagation();
        });

        // click row to check the checkbox
        $('#key-list table tbody').on('click', 'tr', function (e){
            var checkbox_obj = $(this).find('input[type="checkbox"]');
            if(checkbox_obj.length){
                if(checkbox_obj.hasClass('select-all')){
                    checkbox_obj.click();
                }else{
                	if(e.target.tagName != 'INPUT' && e.target.tagName != 'BUTTON') {
		                var checked = !checkbox_obj.prop('checked');
		                checkbox_obj.prop('checked',checked);
		                if(!checked){
			                $('.select-all').prop('checked',false);
		                }else{
			                $('#key-list table tr input[type="checkbox"]:not(".select-all")').each(function (e){
				                checked = checked && $(this).prop('checked');
			                });
			                if(checked){
				                $('.select-all').prop('checked',true);
			                }
		                }
	                }
                }
            }else{
                console.log('something is error');
            }
        });

        // stop anchor from bubbling
        $('#key-list table tr input[type="checkbox"],#key-list table tr a').on('click', function (e){
            e.stopPropagation();
        });

        // Batch delete
        $('.batch-delete').on('click',function (e){
            e.stopPropagation();
            var $this = $(this);
            if($this.attr('isclick')==1){
                return false;
            }

            var keys_obj = $('#key-list table input[type="checkbox"]:checked:not(".select-all")');
            var selected_row = keys_obj.length;
            if(selected_row==0){
                alert('Please select row');
                return false;
            }

            if(!confirm('confirm to delete '+selected_row+' keys ?')){
                return false;
            }

            var keys = keys_obj.serialize();
	        var db = $('select[name="db"]').val();
            if(keys!=null && keys!=''){
                $this.attr('isclick',1);
                $.ajax({
                    type:'post',
                    url:'/'+controller+'/del-redis-key',
                    data:keys + '&db='+db+'&_csrf='+csrfToken,
                    dataType:'json',
                    success:function (responseText){
                        if(responseText.code==0){
                            keys_obj.each(function (){
                                $(this).parents('tr').remove();
                            });
                            if($('#key-list table input[type="checkbox"]:checked:not(".select-all")').length==0){
                                $(".select-all").prop('checked',false);
                            }
                        }else{
                            alert('error, please contact Bruce');
                        }
                        $this.attr('isclick',0);
                    }
                });
            }else{
                alert('error, please contact Author');
            }
        });

        // flushDB or flushAll
        $('.flush-db,.flush-all').on('click', function (){
            if($(this).attr('isclick')==1){
                return false;
            }
            var password = prompt('Flush redis database is extremely dangers, please enter password:');
            if(!password){
                return false;
            }
            var flush_type = $(this).attr('class');
            var db = $('select[name="db"]').val();
            $.ajax({
                type:'post',
                url:'/'+controller+'/flush-db',
                data:{
                	db:db,
                    flush_type:flush_type,
                    password:password,
	                _csrf:csrfToken,
                },
                dataType:'json',
                success:function (responseText){
                    if(responseText.code==0){
                        alert('flush succeed!');
                        window.location.reload();
                    }else{
                        alert('error');
                    }
	                $(this).attr('isclick', '0');
                },
            });
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
    });
</script>