<?php

/**
 * Created by PhpStorm.
 * User: yanli
 * Date: 2017/2/26
 * Time: 14:41
 */
namespace  common\tools;
use backend\models\adds\banner\BannerModel;
use backend\models\ArticleModel;
use backend\models\options\OptionsModel;
use backend\models\PageModel;
use backend\models\TermModel;
use Yii;
use yii\data\Pagination;


class htmls
{
    /*
     * 网站设置信息
     * */
    public static function site()
    {
        $info = OptionsModel::find()->where(['type'=>'website'])->asArray()->one();
        $value = json_decode($info['value'], true);

        return $value;
    }
    /*
     * 显示导航
     * */
    public static function nav($pid)
    {
      $info = TermModel::find()->where(['pid'=>$pid])->asArray()->all();
      return $info;
    }
    /*
     * 显示碎片类别
     * $name string
     * return string
     * */
    public static function BannerType($name)
    {
            $info = OptionsModel::find()->where(['type' => 'bannertype'])->one();
            $value = json_decode($info['value'], true);
            $flip = array_flip($value['mingzi']);
            $infos = $value['text'][$flip[$name]];
        return $infos;
    }

    /*
     * 前台碎片化调用
     * @tablename = 'banner'
     * @ return array
     * */
    public static function getPiece($type)
    {
        $info = BannerModel::find()->where(['type' => $type])->asArray()->all();
        return $info;
    }
    /*
     * 获取文章相关信息，推荐，置顶，分页等
     * @type : rec, status, stick
     * @limit : int
     * @order : DESC or ASC
     * */
    public static function getAr($where=[], $size='', $orderBy = ['listorder' => SORT_DESC] )
    {
        $data = ArticleModel::find()->where($where);
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => $size]);
        $list = $data
                ->asArray()
                ->orderBy($orderBy)
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
       return ['list' =>$list, 'pages'=>$pages];
    }

    /*
     * 获取指定id文章内容
     * */

    public static  function ar()
    {
        $id = $_GET['id'];
        $model =ArticleModel::findOne($id);
        $hits = $model['hits'];
        $model-> hits = $hits + 1;
        $model->save();
        $info =  ArticleModel::find()->where(['id'=>$id])->one();
        return $info;
    }
    /*
     * 面包屑
     * */
    public static  function getBread($id)
    {
        $arr = TermModel::find()->asArray()->all();
        $info = self::bread($arr, $id);
        $list = [];
        for($i=0;$i<=count($info)-1;$i++){
            $list[] = $info[$i]['text'];
        }
        $array = array_reverse($list);
        return $array;

    }
      public static  function bread($arr,$id){
            static $list;
            foreach($arr as $v){
                if($v['id'] == $id){
                    $list[] =$v;
                    self::bread($arr,$v['pid']);
                }
            }
            return $list;
        }

        public static function page($pid)
        {
          $info = PageModel::find()->asArray()->where(['pid'=>$pid])->one();
          return $info;
        }


}