<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/13/19
 * Time: 12:00 PM
 */

namespace execut\actions\models;


use detalika\goods\models\MergeRelation;
use detalika\goods\models\MergeRelations;
use execut\crudFields\fields\Field;
use yii\base\Model;

class MassDelete extends Model
{
    public $owner = null;
    protected $_deleteRelationsModels = null;
    public $deleteErrors = [];

    public function load($data, $formName = null)
    {
        if ($formName === null) {
            $formName = $this->formName();
        }

        if (!empty($data[$formName]) && !empty($data[$formName]['deleteRelationsModels'])) {
            $this->setDeleteRelationsModels($data[$formName]['deleteRelationsModels']);
        }

        return parent::load($data, $formName); // TODO: Change the autogenerated stub
    }

    public function setDeleteRelationsModels($modelsData) {
        $models = $this->getDefaultDeleteRelationsModels();
        foreach ($modelsData as $key => $modelsDatum) {
            $models[$key]->load($modelsDatum, '');
        }

        $this->_deleteRelationsModels = $models;

        return $this;
    }

    public function getDeleteRelationsModels() {
        if ($this->_deleteRelationsModels !== null) {
            return $this->_deleteRelationsModels;
        }

        return $this->_deleteRelationsModels = $this->getDefaultDeleteRelationsModels();
    }

    public function getDefaultDeleteRelationsModels() {
        $result = [];
        foreach ($this->owner->getRelations() as $relation) {
            $result[$relation->name] = $relation->getDeleteModel();
        }

        return $result;
    }

    public function getCount() {
        return $this->getQuery()->count();
    }

    public function getQuery() {
        $dp = $this->owner->search();

        return $dp->query;
    }

    public function attributeLabels()
    {
        return [
            'deleteRelationsModels' => 'Удалить связанные записи:',
            'count' => 'Количество удаляемых записей',
        ];
    }

    public function rules()
    {
        return [
            ['deleteRelationsModels', 'safe'],
        ];
    }

    public function delete() {
        $result = 0;
        foreach ($this->getQuery()->batch(10000) as $models) {
            foreach ($models as $model) {
                $mergeRelation = new MergeRelations();
                $mergeRelation->article = $model;
                $relations = [];
                foreach ($this->getDeleteRelationsModels() as $name => $deleteRelationsModel) {
                    if ($deleteRelationsModel->is_delete) {
                        $relations[$name] = [
                            'action_id' => MergeRelation::ACTION_DELETE,
                        ];
                    }
                }
                $mergeRelation->relations = $relations;
                if (!$mergeRelation->validate()) {
                    foreach ($mergeRelation->errors as $errors) {
                        foreach ($errors as $error) {
                            $this->deleteErrors[] = [
                                'model' => $model,
                                'error' => $error
                            ];
//                            $this->addError('deleteErrors', $error);
                        }
                    }
                } else {
                    if ($mergeRelation->delete()) {
                        $result++;
                    }
                }
            }
//            $mergeRelation->delete();
        }

        return $result;
    }
}