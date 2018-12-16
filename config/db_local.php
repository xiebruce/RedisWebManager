<?php

return [
	'class' => 'yii\db\Connection',
    /*
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '123',
    'charset' => 'utf8',*/
    
    'dsn' => 'sqlite:@app/runtime/sqlite3/database.db',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];