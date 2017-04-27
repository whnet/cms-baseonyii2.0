<?php

/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = '文章列表';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>文章列表</h5>
                    <div class="ibox-tools">
                        <a href="<?=Url::toRoute(['article/add'])?>" class="btn btn-primary btn-xs">创建文章</a>
                    </div>
                </div>
                <div class="ibox-content">

                    <div class="project-list">

                        <table class="table table-hover">
                            <tbody>
                            <?php foreach($list as $v):?>
                                <tr>
                                    <td class="project-status">
                                            <span class="label label-primary"><?=$v['listorder']?>
                                    </td>
                                    <td class="project-title">
                                        <a href=""><?=$v['title']?></a>
                                    </td>
                                    <td class="project-completion">
                                            <a href="" class="label <?php if($v['status'] == 1):?>label-primary<?php else:?>label-default<?php endif;?>">开启</a>
                                            <a href="" class="label <?php if($v['stick'] == 1):?>label-primary<?php else:?>label-default<?php endif;?>">置顶</a>
                                            <a href="" class="label <?php if($v['rec'] == 1):?>label-primary<?php else:?>label-default<?php endif;?>">推荐</a>
                                    </td>
                                    <td class="project-actions">
                                        <a href="<?=Url::toRoute(['article/edit','id'=>$v['id']])?>" class="btn btn-white btn-sm">
                                            <i class="fa fa-folder"></i> 查看 </a>
                                        <a href="<?=Url::toRoute(['article/del','id'=>$v['id']])?>" class="btn btn-white btn-sm">
                                            <i class="fa fa-pencil"></i> 删除 </a>
                                    </td>
                                </tr>
                            <?php endforeach;?>


                            </tbody>
                        </table>
                        <!--分页-->
                        <div class="f-r">
                            <?= LinkPager::widget([
                                'pagination'=>$pages,
                                'firstPageLabel' => '首页',
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'lastPageLabel' => '末页',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

