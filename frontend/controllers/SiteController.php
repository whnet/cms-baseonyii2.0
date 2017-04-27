<?php
namespace frontend\controllers;


use Yii;
use frontend\controllers\BaseController;
use common\tools\htmls;





class SiteController extends BaseController
{

    public function  actionTest()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $file = __DIR__ . "/1.docx";
        $word = \PhpOffice\PhpWord\IOFactory::load($file);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($word, 'HTML');
        $objWriter->save('helloWorld.html');

    }
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCaseList($pid)
    {
        $info = htmls::getAr(['rec'=>1], '3', ['listorder' => SORT_DESC]);
       return $this->render('case_list', [
           'site'=> htmls::site(),
           'list'=>  $info['list'],
           'pages'=>  $info['pages'],
           'bread'=>htmls::getBread($pid),
       ]);
    }
    public function actionArticleList($pid)
    {
        $info =htmls::getAr(['rec'=>1, 'pid'=>$pid], '3', ['listorder' => SORT_DESC]);
           return $this->render('article_list', [
               'site'=> htmls::site(),
               'list'=>  $info['list'],
               'pages'=>  $info['pages'],
               'bread'=>htmls::getBread($pid),
           ]);
    }
    public function actionArticleContent()
    {
        $info = htmls::ar();
        return $this->render('article_content',
            [
                'info'=>$info,
                'site'=> htmls::site(),
                'bread'=>htmls::getBread($info['pid']),
            ]
        );
    }
    public function actionPage($pid)
    {

        return $this->render('page', [
            'info' => htmls::page($pid),
            'bread'=>htmls::getBread($pid),
        ]);
    }



}
