<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:00
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\viewRenderer\DetailView;
use execut\actions\action\ModelsFinder;
use execut\actions\action\Response;
use yii\base\Event;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Session;
use yii\web\UploadedFile;

class Edit extends Form
{
    public $modelClass = null;
    public $relations = [];
    public $additionalAttributes = [];
    public $requestType = self::MIXED;
    public $scenario = null;
    public $createFormLabel = 'Create';
    public $editFormLabel = 'Edit';
    public $urlParamsForRedirectAfterSave = [];

    public $isTrySaveFromGet = false;
    public $templateSuccessMessage = null;
    public $mode = 'edit';
    protected function _run() {
        $actionParams = $this->actionParams;
        if ($this->actionParams && (!empty($actionParams->get['id']) || !empty($actionParams->post['id']))) {
            $mode = $this->mode;
        } else {
            $mode = 'edit';
        }

        $model = $this->getModel();
        if ($this->scenario !== null) {
            $model->setScenario($this->scenario);
        }

        $result = parent::loadAndValidateForm();
        if (is_array($result)) {
            return $this->getResponse([
                'content' => $result
            ]);
        }

        $flashes = [];
        if ($result === true && $this->isSave()) {
            if ($model->isNewRecord) {
                $operation = 'created';
            } else {
                $operation = 'updated';
            }

            $operation = $this->translate($operation);

            $model->save();
            if ($this->templateSuccessMessage !== false) {
                $parts = [
                    '{id}' => $model->id,
                    '{operation}' => $operation,
                ];

                $template = $this->getTemplateSuccessMessage();
                $flashes['kv-detail-success'] = strtr($template, $parts);
            }

            $result = $this->redirectAfterSave();
            if ($result === false) {
                if ($this->actionParams->isAjax) {
                    return $this->getResponse([
                        'format' => \yii\web\Response::FORMAT_JSON,
                        'content' => [],
                    ]);
                }

                $result = [
                    'mode' => $mode,
                    'model' => $model
                ];
            }
        } else {
            $session = \Yii::$app->session;
            if (!empty($model->errors)) {
                $session->setFlash('kv-detail-error', Html::errorSummary($model, [
                    'encode' => false,
                ]));
//                $flashes['kv-detail-danger'] = Html::errorSummary($model);
            }

            $result = [
                'mode' => $mode,
                'model' => $model
            ];
        }

        if (\yii::$app->has('db') && $t = \yii::$app->db->transaction) {
            while ($t->getIsActive()) {
                $t->commit();
            }
        }

        $response = $this->getResponse([
            'flashes' => $flashes,
            'content' => $result,
        ]);

        return $response;
    }

    public function getIsValidate()
    {
        return $this->isSave() && parent::getIsValidate(); // TODO: Change the autogenerated stub
    }

    protected function isSave() {
        $get = $this->actionParams->get;
        unset($get['id']);

        return (!empty($get) && $this->isTrySaveFromGet) || (!$this->isTrySaveFromGet);
    }

    protected function getHeading() {
        if ($this->model->isNewRecord) {
            return $this->getCreateFormLabel();
        } else {
            return $this->getEditFormLabel($this->model);
        }
    }

    protected function getCreateFormLabel() {
        $m  = $this->createFormLabel;
        $t = $this->translate($m);

        return $t;
    }

    protected function getEditFormLabel() {
        $editFormLabel = $this->editFormLabel;
        if (is_callable($editFormLabel)) {
            return $editFormLabel($this->model);
        }

        return \yii::t('execut.actions', $editFormLabel);
    }

    public function getDefaultViewRendererConfig() {
        return [
            'class' => DetailView::className(),
            'uniqueId' => $this->uniqueId,
            'heading' => $this->getHeading(),
            'action' => $this->getFormAction(),
        ];
    }

    protected function getFormAction() {
        $params = $this->getUrlParams();

        return $params;
    }

    /**
     * @param $model
     */
    protected function redirectAfterSave()
    {
        if ($this->urlParamsForRedirectAfterSave === false) {
            return false;
        }

        $data = $this->actionParams->getData();
        $params = $this->getUrlParams();

        if (is_callable($this->urlParamsForRedirectAfterSave)) {
            $urlParamsForRedirectAfterSave = $this->urlParamsForRedirectAfterSave;
            $params = $urlParamsForRedirectAfterSave($params);
        } else {
            $params = ArrayHelper::merge($this->urlParamsForRedirectAfterSave, $params);
            if (!empty($params[1])) {
                unset($params[1]);
            }

            if (!empty($data['save'])) {
                $params = [
                    str_replace('/update', '/index', $this->getUniqueId()),
                ];
            }
        }

        $result = \yii::$app->response->redirect($params);

        return $result;
    }

    /**
     * @param $model
     * @return array
     */
    protected function getUrlParams(): array
    {
        $model = $this->model;
        $params = [
            $this->actionParams->uniqueId,
            'id' => $model->id
        ];

        foreach ($this->additionalAttributes as $attribute) {
            $params[$attribute] = $model->$attribute;
        }
        return $params;
    }

    /**
     * @param $m
     * @return string
     */
    protected function translate($m): string
    {
        $m = \yii::t('execut.actions', $m);

        return $m;
    }

    /**
     * @return string
     */
    protected function getTemplateSuccessMessage(): string
    {
        if ($this->templateSuccessMessage !== null) {
            return $this->templateSuccessMessage;
        }

        $template = $this->translate('Record') . ' #{id} ' . $this->translate('is successfully') . ' {operation}';

        return $template;
    }
}