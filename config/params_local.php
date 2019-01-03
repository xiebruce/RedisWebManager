<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-13
 * Time: 16:52
 */
	
return [
	'flushPwd' => 'fxfzaS4lBoV3',
	'quickSearch' => ['aaa', 'bbb', 'ccc'],
	'pageSize' => 10,
	//if pageSize is set to 10 and search keyword is not empty, redis can't assure that you can fetch 10 keys back, because redis fetch 10 keys first and than match the keyword from the already fetched keys, let's say there are 3 items in the 10 keys matched your keyword, it will return only 3 keys, not 10, to solve this problem, I loop to fetch keys until the keys amount is equal to 10 or more than 10, but I need you tell me how many keys would you like to fetch per loop(set scanPageSize value), if you set to 1, if means the program will loop at least 10 times to get 10 keys if it's lucky enough that each key fetch from redis is match your keyword, but if not lucky enough, if may loop 100 times or more to get 10 keys that would match your keyword, if you set to 10, may be you can get 10 keys within 2 loop, or 3, or 4, ..., cause this is depends on the amount of keys that would match your keyword, for example loop 1 matched 2 keys, loop 2 matched 4 keys, and loop 3 match 5 keys, so 2+4+5=11>10, then the loop will stop and return 11 keys. As you can see, the smaller scanPageSize you set, the more times would be take, and the more time you need to spend, and the more resource need on the server, so set this value to a proper value, if you not setting it, it will be the same as pageSize.
	'scanPageSize' => 10,
	//as the above says, if you have 1 million keys in your redis but you type a wrong keyword that didn't match any of these keys, if while loop dbsize()/scanPageSize times, so be aware to set this option properly.
	'maxLoop' => 100,
	//two value: popup / inline
	'valDisplayType' => 'popup',
	// only use this option where valDisplayType sets to 'popup'
	// popup modal width(css), e.g.: 50%, 500px, 50em, 70rem or empty string
	'modalWidth' => '50%',
];