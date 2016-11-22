<?php
namespace Etobi\Devmagic\Domain\Model\File;

use Etobi\Devmagic\Domain\Model\Model;

class ConfigurationTcaFile extends AbstractFile
{
    /**
     * @var string
     */
    protected $path = 'Configuration/TCA/__TABLENAME__.php';

    /**
     * @var Model
     */
    protected $model;

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $absolutePath = parent::getPath();
        return str_replace('__TABLENAME__', $this->model->getTableName(), $absolutePath);
    }
}
