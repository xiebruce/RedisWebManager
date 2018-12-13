<?php
/**
 * Created by PhpStorm.
 * User: Bruce
 * Date: 06/04/2017
 * Time: 17:14
 */

$title = 'Redis Manager';
if($visitor_ip=='127.0.0.1'){
    $this->title = '[local]-'.$title;
}else{
    $this->title = '[remote]-'.$title;
}

$controller = $this->context->id;
$action = $this->context->action->id;
$link = '/'.$controller.'/'.$action.'?keyword=';
$queryString = Yii::$app->request->queryString;
$queryString = $queryString ? $queryString.'&' : '';

$quickSearch = Yii::$app->params['quickSearch'];
?>

<style type="text/css">
    @-moz-document url-prefix() {
        fieldset { display: table-cell; }
    }
    th,td{
        vertical-align: middle !important;
    }
    #key-list{
        /*margin:0 200px 0 200px;*/
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
    .redis-value{
        max-width:300px;
        min-height:100px;
        /*border:1px solid #ccc;*/
        /*position: relative;*/

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
</style>

<form class="table-responsive" id="key-list" action="">
    <div class="container">
        <table class="table table-hover table-striped table-bordered table-condensed">
            <thead>
            <tr>
                <th colspan="3">
                    Redis Server Info:
                    <span style="font-weight: normal;">
                        <?php if($code==0):?>
                            <span class="underline">server ip</span>：<span><?=$visitor_ip?></span> |
                            <span class="underline">redis_version</span>: <span class="red"><?=$info['redis_version']?></span> |
                            <span class="underline">os</span>: <span class="red"><?=$info['os']?></span> |
                            <span class="underline">redis_mode</span>: <span class="red"><?=$info['redis_mode']?></span> |
                            <span class="underline">used_memory</span>: <span class="red"><?=$info['used_memory_human']?></span> |
                            <select name="db">
		                        <?php for($i=0;$i<$databaseCount;$i++):?>
		                        <option value="<?=$i?>"<?=$db==$i?' selected':''?>>db <?=$i?></option>
	                            <?php endfor;?>
                            </select>
                            <?php if($delete_auth):?>
                            | <a href="javascript:void(0);" class="flush-db" title="Click to flush current db">FlushDB</a> |
                            <a href="javascript:void(0);" class="flush-all" title="Click to flush all db">FlushAll</a>
                            <?php endif;?>
                        <?php else:?>
                            <span class="underline">server ip</span>：<span><?=$visitor_ip?></span> |
                            <span class="red"><?=$error_msg?></span>
                        <?php endif;?>
                    </span>
                </th>
            </tr>
            <tr style="height: 60px;font-size: 20px;">
                <th class="text-center cursor-default" colspan="3">Redis Manager</th>
            </tr>
            <tr>
                <th colspan="3">
                    <div>
                        <input type="input" name="keyword" placeholder="Please enter keyword" class="form-control pull-left keyword" value="<?=$keyword?>">
                        <button type="submit" class="btn btn-primary pull-left search-btn">Search</button>
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="3">
                    Quick Search:
	                <?php if(!empty($quickSearch)):?>
	                <?php foreach($quickSearch as $val):?>
		                <a href="<?=$link.$val?>" class="quick-search"><?=$val?></a>
	                <?php endforeach;?>
	                <?php endif;?>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr style="font-weight: bold;">
                <td class="col-xs-1 text-center">
                    <label>
                        <input type="checkbox" class="select-all">
                    </label>
                </td>
                <td class="text-center cursor-default">Key Name (Click to preview value)</td>
                <td class="col-xs-1 text-center cursor-default">Operation</td>
            </tr>
            <?php if($keys):?>
                <?php foreach($keys as $key):?>
                    <tr>
                        <td class="text-center col-xs-1">
                            <input type="checkbox" name="keys[]" value="<?=$key?>">
                        </td>
                        <td class="col-xs-9">
                            <a tabindex="0" class="key-name" role="button" data-toggle="popover" data-container="body" title="click to preview value" data-content="" data-placement="right"><?=$key?></a>
                        </td>
                        <td class="text-center col-xs-2">
                            <a href="/<?=$controller?>/view-redis-value?<?=$queryString?>specified_key=<?=$key?>" title="Click to view value in new page" class="btn btn-info view-in-new-page">View</a>
                            <?php if($delete_auth):?>
                            <button type="button" class="btn btn-danger delete" title="Click to delete key" key="<?=$key?>">Delete</button>
                            <?php endif;?>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <td class="text-center">
                        <label>
                            <input type="checkbox" class="select-all">
                        </label>
                    </td>
                    <td style="font-weight: bold;">
                        Total Count: <span class="red"><?=$count?></span> keys
                        <?php if($keyword):?>
                            , Match Count: <span class="red"><?=$match_count_real ? $match_count : 'more than '.$match_count?></span>
                        <?php endif;?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger batch-delete">Batch Del</button>
                    </td>
                </tr>
            <?php else:?>
                <tr>
                    <td colspan="3" class="text-center" style="height:50px;">no result</td>
                </tr>
            <?php endif;?>
            </tbody>
        </table>
    </div>
</form>
<div class="text-center">
    <?php
    if($pagination){
        echo \yii\widgets\LinkPager::widget([
            'pagination' => $pagination,
        ]);
    }
    ?>
</div>

<script>
    $(document).ready(function (){
        //tool tip
        /*$('[data-toggle="popover"]').popover();
        $('a[role="tooltip"]').click(function (e){
            e.stopPropagation();
        });*/
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
            var key = $this.attr('key');
            $this.attr('isclick',1);
            $.ajax({
                type:'post',
                url:'/'+controller+'/del-redis-key',
                data:{
                    keys:key,
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
        $('.key-name').click(function (){
            var $this = $(this);
            if($this.attr('isclick')==1){
                return false;
            }
            var key = $this.html();
            $this.attr('isclick',1);
            $.ajax({
                type:'post',
                url:'/'+controller+'/get-redis-val',
                data:{
                    key:key,
	                _csrf:csrfToken,
                },
                dataType:'json',
                success:function (responseText){
                    if(responseText.code==0){
                        /*$this.attr({
                            'title':'type: '+responseText.type,
                            'data-content':responseText.value
                        })*/
                        var str = '';
                        if(responseText.value_type=='object'){
                            str = 'Object type data convert to array in order to show content';
                        }
                        alert('key type => '+responseText.key_type+"\n"+"value type => "+responseText.value_type+"\n-------------------------------------------------------------------"+str+"\n"+responseText.value);
                    }
                    $this.attr('isclick',0);
                }
            });
        });

        // Select all
        $('.select-all').click(function (e){
            $('#key-list table input[type="checkbox"]').prop('checked',$(this).prop('checked'));
            e.stopPropagation();
        });

        // click row to check the checkbox
        $('#key-list table tbody tr').on('click', function (){
            var checkbox_obj = $(this).find('input[type="checkbox"]');
            if(checkbox_obj.length){
                if(checkbox_obj.hasClass('select-all')){
                    checkbox_obj.click();
                }else{
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
            }else{
                console.log('something is error');
            }
        });

        // Click check box
        $('#key-list table tr input[type="checkbox"]:not(".select-all")').on('click',function (){
            var checked = $(this).prop('checked');
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
        });

        // stop anchor from bubbling
        $('#key-list table tr input[type="checkbox"],#key-list table tr a').click(function (e){
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
            if(keys!=null && keys!=''){
                $this.attr('isclick',1);
                $.ajax({
                    type:'post',
                    url:'/'+controller+'/del-redis-key',
                    data:keys + '&_csrf='+csrfToken,
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
        $('.flush-db,.flush-all').click(function (){
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
		    	link += '&db='+db;
		    }
		    // console.log(db, link);
		    window.location.href = link;
	    });
    });
</script>