<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;
use kartik\file\FileInput;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '微信设置';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="wrapper wrapper-content">
    <div class="user-create">
        <div class="ibox-content">
            <h1><?= Html::encode($this->title) ?></h1>
            <hr/>

            <div class="user-form">
                <?php $form = ActiveForm::begin(); ?>
                <?= $form->field($model, 'ak')->textInput(['maxlength' => true, 'value'=>isset($info['ak'])?$info['ak']:'' ]) ?>
                <?= $form->field($model, 'sk')->textInput(['maxlength' => true, 'value'=>isset($info['sk'])?$info['sk']:'' ]) ?>
                <?= $form->field($model, 'hk')->textInput(['maxlength' => true, 'value'=>isset($info['hk'])?$info['hk']:'' ]) ?>
                <?= $form->field($model, 'hs')->textInput(['maxlength' => true, 'value'=>isset($info['hs'])?$info['hs']:'' ]) ?>


                <?php
                if( Yii::$app->getSession()->hasFlash('error') ) {
                    echo Alert::widget([
                        'options' => [
                            'class' => 'alert alert-danger',
                        ],
                        'body' => Yii::$app->getSession()->getFlash('error'),
                    ]);
                }
                ?>
                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ? '新增' : '更新', ['class' => $model->isNewRecord ? 'btn btn-primary' : 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
