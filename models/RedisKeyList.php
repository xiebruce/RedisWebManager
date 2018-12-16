<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\Pagination;

/**
 * This is the model class for table "redis_key_list".
 *
 * @property int $id
 * @property string $keyname
 * @property string $created_at
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
            [['keyname', 'created_at'], 'required'],
            [['created_at'], 'safe'],
            [['keyname'], 'string', 'max' => 100],
        ];
    }
	
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',// 自己根据数据库字段修改
				'updatedAtAttribute' => false, // 自己根据数据库字段修改, // 自己根据数据库字段修改
				//'value'   => new Expression('NOW()'),
				'value'   => function(){
					return date('Y-m-d H:i:s',time());
				},
			],
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
        ];
    }
	
	public static function batchInsert(array $data){
		$connection = \Yii::$app->db;
		$insertedRows = $connection->createCommand()->batchInsert(static::tableName(),['keyname', 'created_at'], $data)->execute();
		return $insertedRows;
	}
	
	public static function getOnePage($queryParams){
		$obj = static::find();
		$pagination1 = new Pagination([
			'totalCount'=>$obj->count(),
			'pageSize'=>Yii::$app->params['pageSize'] ?? 10,
			'params'=>$queryParams,
		]);
		$res = $obj->offset($pagination1->offset)->limit($pagination1->limit)->asArray()->all();
		return [
			'pageCount' => $pagination1->pageCount,
			
		];
	}
}
