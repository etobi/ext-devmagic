<?php
namespace Etobi\Devmagic\Domain\Model\File;

use Etobi\Devmagic\Domain\Model\Model;

class ExtTablesSqlFile extends AbstractFile
{
    /**
     * @var string
     */
    protected $path = 'ext_tables.sql';

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
