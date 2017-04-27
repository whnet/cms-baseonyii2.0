<?php

namespace backend\models;

use Yii;
use backend\models\AuthAssignment;
use yii\base\model;
use yii\db\Query;

class ShouyinModel extends \yii\db\ActiveRecord
{
    public $_lastError = "";
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shouyin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cashier'], 'required'],
            [['title','message', 'wangfee','goodsfee','gamefee','canbafee','otherfee','givefee', 'start_time', 'end_time'],'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'cashier' => '收银员',
            'wangfee' => '网费收入',
            'gamefee' => '游戏收入',
            'goodsfee' => '商品收入',
            'canbafee' => '餐吧收入',
            'otherfee' => '其他收入',
            'givefee' => '奖励网费',
            'message' => '值班备注',
            'start_time' => '上班时间',
            'end_time' => '下班时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    //创建数据
   public function create()
   {

       //事务
       $transaction = Yii::$app->db->beginTransaction();
       try{
           $this->setAttributes($this->attributes);
           if(!$this->save()){
               throw new \Exception('保存失败');
           }
           $transaction->commit();
           return true;
       }catch(\Exception $e){
           $transaction->rollBack();
           $this->_lastError = $e->getMessage();
           return false;
       }
   }






}
