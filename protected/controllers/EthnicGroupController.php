<?php

class EthnicGroupController extends BaseController
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'roles'=>array('TaskViewEthnic'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'roles'=>array('TaskManageEthnic'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new EthnicGroup;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['EthnicGroup']))
		{
			$model->attributes=$_POST['EthnicGroup'];
			if($model->save())
				$this->redirect($this->createUrl(array('view','id'=>$model->id)));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['EthnicGroup']))
		{
			$model->attributes=$_POST['EthnicGroup'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
	    $criteria = new CDbCriteria();
	    $criteria->together = false;
	    $criteria->order = 'name';

	    if (isset($_POST['search-term'])){
	        $criteria->addSearchCondition('LOWER(name)', strtolower($_POST['search-term']), true, 'OR');
            $criteria->addSearchCondition('LOWER(code)', strtolower($_POST['search-term']), true, 'OR');
            $criteria->addSearchCondition('LOWER(display_order)', strtolower($_POST['search-term']), true, 'OR');
        }
		$dataProvider=new CActiveDataProvider('EthnicGroup',array('criteria'=>$criteria));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return EthnicGroup the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=EthnicGroup::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param EthnicGroup $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='ethnic-group-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}