<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class GridView
 * @package execut\actions\action
 * @property Model $filter
 */
class File extends Adapter
{
    public $modelClass = null;
    public $model = null;
    public $dataAttribute = 'data';
    public $extensionIsRequired = true;
    protected function _run() {
        $attributes = $this->actionParams->get;
        $class = $this->modelClass;
        unset($attributes['r']);

        if ($this->extensionIsRequired && empty($attributes['extension'])) {
            throw new NotFoundHttpException('Extension required');
        }

        if (!empty($attributes['dataAttribute'])) {
            $dataAttribute = $attributes['dataAttribute'];
            unset($attributes['dataAttribute']);
        } else {
            $dataAttribute = $this->dataAttribute;
        }

        $selectedAttributes = array_merge([
            $dataAttribute,
            'mime_type',
        ], array_keys($attributes));
        $result = $class::find()->select($selectedAttributes)->andWhere($attributes)->one();
        if (!$result) {
            throw new NotFoundHttpException('File by url "' . \yii::$app->request->getUrl() . '" not found');
        }

        if ($this->extensionIsRequired && strtolower($result->extension) !== $attributes['extension']) {
            throw new NotFoundHttpException('File extension is wrong');
        }

        $this->model = $result;

        $response = \Yii::$app->getResponse();
        if (strpos($result->mime_type, 'image/') === 0) {
            $response->headers->set('Content-Type', $result->mime_type);
        } else {
            $response->setDownloadHeaders($result->name, $result->mime_type);
        }

        $response = $this->getResponse([
            'format' => Response::FORMAT_RAW,
            'content' => stream_get_contents($result->{$dataAttribute}),
        ]);

        return $response;
    }
}