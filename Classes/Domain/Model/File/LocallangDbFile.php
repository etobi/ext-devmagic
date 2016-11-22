<?php
namespace Etobi\Devmagic\Domain\Model\File;

use Etobi\Devmagic\Domain\Model\Model;

class LocallangDbFile extends AbstractFile
{
    /**
     * @var string
     */
    protected $path = 'Resources/Private/Language/locallang_db.xlf';

    /**
     * @var array
     */
    protected $models = [];

    /**
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * @param array $models
     */
    public function setModels($models)
    {
        $this->models = $models;
    }

    /**
     * @param Model $model
     */
    public function addModel($model)
    {
        $this->models[] = $model;
    }

}
