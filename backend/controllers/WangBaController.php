<?php

namespace backend\controllers;
use backend\models\AuthItem;
use backend\models\Menu;
use backend\models\OptionModel;
use yii\data\Pagination;
use backend\models\ShouyinModel;
use backend\models\ZhichuModel;
use backend\models\WagesModel;
use backend\models\CaigouModel;
use backend\models\AssetModel;
use backend\models\ZhuangxiuModel;
use backend\models\AuthAssignment;
use yii\web\NotFoundHttpException;
use Yii;

class WangBaController extends \yii\web\Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionShouyin()
    {
        return $this->render('shouyin');
    }
    public function actionCaigou()
    {
        return $this->render('caigou');
    }

    //收银人员管理
	public function actionShouYinIndex()
	{
		return $this->render('shouyin_index');
	}	

	//收银列表
	public function actionShouYinFeeList()
	{
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限

        $data = ShouyinModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render(
		    'shouyin_feelist',
            ['list'=>$list,
                'pages'=>$pages,
                'item' => $item_one,
                'nowtime'=>time(),
            ]);
	}
	//删除指定列表
    public function actionShouYinFeeDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("shouyin", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['shou-yin-fee-list']);

    }
	//查看具体的
	public function actionShouYinFeeDetail()
	{
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = ShouyinModel::findOne($id);
        $info = ShouyinModel::find()->where(['id' => $id])->one();

       //判断是否超过更新时间
        if( (time() - $info['created_at']) >7200){
            return $this->redirect(['shou-yin-fee-list']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
            }else{
                $value = json_encode($post['type']);

            }
            $model->sums = $post["ShouyinModel"]['wangfee'] + $post["ShouyinModel"]['goodsfee'] + $post["ShouyinModel"]['gamefee'];
            $model->value = $value;
            $model->updated_at = time();
            $model->save($post);
            return $this->redirect(['shou-yin-fee-list']);
            }else{

            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'ShouyinModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取储存的字段值
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('shouyin_feedetail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }




	}
	//收银收入新增
	public function actionShouYinFeecreate()
	{

        $model = new ShouyinModel();
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        if( $model->load(Yii::$app->request->post())){

            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
            }else{
                $value = json_encode($post['type']);
            }

            $model->created_at = time();
            $model->sums = $post["ShouyinModel"]['wangfee'] + $post["ShouyinModel"]['goodsfee'] + $post["ShouyinModel"]['gamefee'];
            $model->value = $value;

            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['shou-yin-fee-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'ShouyinModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
              foreach($ziduan as $k=>$v){
                 $names[] = $v;
              }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('shouyin_feecreate', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
	}

	//收银人员管理 END



    //采购经理列表
    public function actionCaiGouFeeList()
    {
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        $data = CaigouModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('caigou_feelist',['list'=>$list,'pages'=>$pages]);
    }
    //删除指定列表
    public function actionCaiGouFeeDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("caigou", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['cai-gou-fee-list']);

    }
    //查看具体的
    public function actionCaiGouFeeDetail()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = CaigouModel::findOne($id);
        $info = CaigouModel::find()->where(['id' => $id])->one();
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
                $model->sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model->sums =  array_sum($post['type']);
            }
            $model->updated_at = time();
            $model->value = $value;
            $model->save($post);
            return $this->redirect(['cai-gou-fee-list']);
        }else{
            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'CaigouModel'])->one();
            $ziduan = json_decode($data["value"],true);
            $names =[];
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取储存的字段值
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('caigou_feedetail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }

    }
    //收银收入新增
    public function actionCaiGouFeecreate()
    {
        $model = new CaigouModel();
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        if( $model->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
                $model-> sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model-> sums = array_sum($post['type']);
            }
            $model->created_at = time();
            $model-> value = $value;
            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['cai-gou-fee-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'CaigouModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('caigou_feecreate', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
    }

    //采购管理 END
    //日常支出管理
    public function actionZhichuList(){
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        $data = ZhichuModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render(
            'zhichu_list',
            [
                'list'=>$list,
                'pages'=>$pages,
                'nowtime'=>time()
            ]);
    }
    //新增日常支出
    public function actionZhichucreate()
    {
        $model = new ZhichuModel();
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        if( $model->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
                $model-> sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model-> sums = array_sum($post['type']);
            }
            $model->created_at = time();
            $model-> value = $value;
            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['zhichu-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'ZhichuModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('zhichu_create', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
    }
    public function actionZhichuDetail()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = ZhichuModel::findOne($id);
        $info = ZhichuModel::find()->where(['id' => $id])->one();
        //判断是否超过更新时间
        if( (time() - $info['created_at']) >7200){
            return $this->redirect(['zhichu-list']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
                $model->sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model->sums =  array_sum($post['type']);
            }
            $model->updated_at = time();
            $model->value = $value;
            $model->save($post);
            return $this->redirect(['zhichu-list']);
        }else{
            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'ZhichuModel'])->one();
            $ziduan = json_decode($data["value"],true);
            $names =[];
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('zhichu_detail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }

    }
    public function actionZhichuDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("rczhichu", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['zhichu-list']);

    }
    //日常支出管理END

    //工资管理
    public function actionGongziList(){
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        $data = WagesModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render(
            'gongzi_list',
            [
                'list'=>$list,
                'pages'=>$pages,
                'nowtime'=>time()
            ]);
    }
    //新增日常支出
    public function actionGongzicreate()
    {
        $model = new WagesModel();
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        if( $model->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
                $model-> sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model-> sums = array_sum($post['type']);
            }
            $model->created_at = time();
            $model-> value = $value;
            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['gongzi-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'WagesModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('gongzi_create', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
    }
    public function actionGongziDetail()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = WagesModel::findOne($id);
        $info = WagesModel::find()->where(['id' => $id])->one();
        //判断是否超过更新时间
        if( (time() - $info['created_at']) >7200){
            return $this->redirect(['zhichu-list']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
                $model->sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model->sums =  array_sum($post['type']);
            }
            $model->updated_at = time();
            $model->value = $value;
            $model->save($post);
            return $this->redirect(['gongzi-list']);
        }else{
            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'WagesModel'])->one();
            $ziduan = json_decode($data["value"],true);
            $names =[];
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取储存的字段值
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('gongzi_detail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }

    }
    public function actionGongziDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("wages", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['gongzi-list']);

    }
    //工资管理END


    //年度固定资产支出
    public function actionAssetList(){
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        $data = AssetModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render(
            'asset_list',
            [
                'list'=>$list,
                'pages'=>$pages,
                'nowtime'=>time()
            ]);
    }
    //新增日常支出
    public function actionAssetcreate()
    {
        $model = new AssetModel();
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        if( $model->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
                $model-> sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model-> sums = array_sum($post['type']);
            }
            $model->created_at = time();
            $model-> value = $value;
            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['asset-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'AssetModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('asset_create', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
    }
    public function actionAssetDetail()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = AssetModel::findOne($id);
        $info = AssetModel::find()->where(['id' => $id])->one();
        //判断是否超过更新时间
        if( (time() - $info['created_at']) >7200){
            return $this->redirect(['asset-list']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
                $model->sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model->sums =  array_sum($post['type']);
            }
            $model->updated_at = time();
            $model->value = $value;
            $model->save($post);
            return $this->redirect(['asset-list']);
        }else{
            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'AssetModel'])->one();
            $ziduan = json_decode($data["value"],true);
            $names =[];
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取储存的字段值
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('asset_detail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }

    }
    public function actionAssetDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("asset", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['asset-list']);

    }
    //固定资产END
    //
    //
    // 装修支出
    public function actionZhuangxiuList(){
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        $data = ZhuangxiuModel::find();
        $pages = new Pagination(['totalCount' =>$data->count(), 'pageSize' => '5']);
        $list = $data->asArray()->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render(
            'zx_list',
            [
                'list'=>$list,
                'pages'=>$pages,
                'nowtime'=>time()
            ]);
    }
    //新增日常支出
    public function actionZhuangxiucreate()
    {
        $model = new ZhuangxiuModel();
        //权限
        $model_auth = new AuthItem();
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        $item_one = [];
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        //权限
        if( $model->load(Yii::$app->request->post())){
            $post = Yii::$app->request->post();
            if(empty($post['type'])){
                $value = json_encode([]);
                $model-> sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model-> sums = array_sum($post['type']);
            }
            $model->created_at = time();
            $model-> value = $value;
            if( !$model->create() ){
                Yii::$app->session->setFlash('warning',$model->_lastError);
            }else{
                return $this->redirect(['zhuangxiu-list']);
            }
        }else{

            //获取自定义字段增加
            $data = OptionModel::find()->asArray()->where(['name' =>'ZhuangxiuModel'])->one();
            $ziduan = json_decode($data["value"],true);
            if(!empty($ziduan)){
                $names = [];
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取自定义字段END

            return $this->render('zx_create', [
                'model' => $model,
                'model_auth' => $model_auth,
                'item' => $item_one,
                'names' => $names,
            ]);
        }
    }
    public function actionZhuangxiuDetail()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }


        $id = Yii::$app->request->get('id');
        $model = ZhuangxiuModel::findOne($id);
        $info = ZhuangxiuModel::find()->where(['id' => $id])->one();
        //判断是否超过更新时间
        if( (time() - $info['created_at']) >7200){
            return $this->redirect(['zhuangxiu-list']);
        }
        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();

            if(empty($post['type'])){
                $value = json_encode([]);
                $model->sums = 0;
            }else{
                $value = json_encode($post['type']);
                $model->sums =  array_sum($post['type']);
            }
            $model->updated_at = time();
            $model->value = $value;
            $model->save($post);
            return $this->redirect(['zhuangxiu-list']);
        }else{
            //获取自定义字段,编辑
            $data = OptionModel::find()->asArray()->where(['name' =>'ZhuangxiuModel'])->one();
            $ziduan = json_decode($data["value"],true);
            $names =[];
            if(!empty($ziduan)){
                foreach($ziduan as $k=>$v){
                    $names[] = $v;
                }
            }else{
                $names[] = [];
            }
            //获取储存的字段值
            $hasnames = json_decode($info["value"],true);
            //获取自定义字段,编辑END

            return $this->render('zx_detail',[
                'model' => $info,
                'item' => $item_one,
                'names' => $names,
                'hasnames' => $hasnames,
            ]);
        }

    }
    public function actionZhuangxiuDel()
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getRoles();
        $itemArr =array();
        foreach($item as $v){
            $itemArr[] .= $v->name;
        }
        foreach($itemArr as $key=>$value)
        {
            $item_one[$value]=$value;
        }
        $id = Yii::$app->request->get('id');
        $connection=Yii::$app->db;
        $transaction=$connection->beginTransaction();
        try
        {
            $connection->createCommand()->delete("zhuangxiu", "id = '$id'")->execute();
            $transaction->commit();
        }
        catch(Exception $ex)
        {
            $transaction->rollBack();
        }
        return $this->redirect(['zhuangxiu-list']);

    }
    //固定资产END


}
