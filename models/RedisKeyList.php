<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "redis_key_list".
 *
 * @property int $id
 * @property string $keyname
 * @property int $created_at
 * @property int $db
 */
class RedisKeyList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'redis_key_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keyname', 'created_at', 'db'], 'required'],
            [['created_at', 'db'], 'integer'],
            [['keyname'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'keyname' => Yii::t('app', 'Keyname'),
            'created_at' => Yii::t('app', 'Created At'),
            'db' => Yii::t('app', 'Db'),
        ];
    }
	
	public static function batchInsert(array $data){
		$connection = \Yii::$app->db;
		$insertedRows = $connection->createCommand()->batchInsert(static::tableName(),['keyname', 'created_at', 'db'], $data)->execute();
		return $insertedRows;
	}
}
