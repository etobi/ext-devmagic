<?php
namespace Etobi\Devmagic\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;

class ModelProperty
{

    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     * @inject
     */
    protected $reflectionService;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $elementType;

    /**
     * @var bool
     */
    protected $lazy;

    /**
     * @var string
     */
    protected $cascade;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Model
     */
    protected $relationModel;

    /**
     * @var \Etobi\Devmagic\Service\BuildService
     * @inject
     */
    protected $buildService;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->key = GeneralUtility::camelCaseToLowerCaseUnderscored($name);

        $this->parseDevmagicTags();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setSchema($schema) {
        $this->schema = $schema;
        $this->type = $this->schema['type'];
        $this->elementType = $this->schema['elementType'];
        $this->lazy = $this->schema['lazy'];
        $this->cascade = $this->schema['cascade'];
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getElementType()
    {
        return $this->elementType;
    }

    /**
     * @param string $elementType
     */
    public function setElementType($elementType)
    {
        $this->elementType = $elementType;
    }

    /**
     * @return boolean
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @param boolean $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = $lazy;
    }

    /**
     * @return string
     */
    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * @param string $cascade
     */
    public function setCascade($cascade)
    {
        $this->cascade = $cascade;
    }

    /**
     * @return string
     */
    public function getTcaColumnPartialName() {
        if ($this->config['tca']['config']['type']) {
            return ucfirst(strtolower(trim($this->config['tca']['config']['type'])));
        }
        switch ($this->type) {
            case 'boolean':
                return 'Check';

            case 'TYPO3\CMS\Extbase\Domain\Model\FileReference':
                return 'File';

            case 'string':
                return 'Input';

            case 'DateTime':
                return 'Input_DateTime';

            case 'array':
            case 'TYPO3\CMS\Extbase\Persistence\ObjectStorage':
                if ($this->elementType == 'TYPO3\CMS\Extbase\Domain\Model\FileReference') {
                    return 'Files';
                }

                if ($this->isRelation() && $this->getRelationModel()->getTableName() == 'sys_category') {
                    return 'Select_SysCategory';
                }

                return 'Group_Db';

            default:
                if ($this->isRelation()) {
                    return 'Select_One';
                }
                return 'Input';
        }
    }

    public function getRelationMMTable()
    {
        if ($this->getTcaColumnPartialName() != 'Group_Db' && $this->getTcaColumnPartialName() != 'Inline') {
            return null;
        }
        list($_, $table1) = GeneralUtility::revExplode('_', $this->model->getTableName(), 2);
        list($_, $table2) = GeneralUtility::revExplode('_', $this->getRelationModel()->getTableName(), 2);
        return 'tx_' .
            str_replace('_', '', $this->model->getExtensionKey()) .
            '_' .
            $table1 .
            '_' .
            $table2 .
            '_mm';
    }

    public function getSqlColumnDefinition()
    {
        $tcaColumnPartialName = $this->getTcaColumnPartialName();
        switch ($tcaColumnPartialName) {
            case 'Text':
                $sql = 'text NOT NULL';
                break;
            case 'Select_SysCategory':
            case 'Select_One':
            case 'Group_Db':
            case 'Inline':
            case 'Files':
            case 'File':
            case 'Input_DateTime':
                $sql = 'int(11) unsigned DEFAULT \'0\' NOT NULL';
                break;
            case 'Check':
                $sql = 'tinyint(1) unsigned DEFAULT \'0\' NOT NULL';
                break;
            default:
                $sql = 'varchar(255) DEFAULT \'\' NOT NULL';
                break;
        }
        return $sql;
    }

    /**
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return bool
     */
    private function isRelation()
    {
        if (class_exists($this->elementType ?: $this->type)) {
            $classSchema = $this->reflectionService->getClassSchema($this->elementType ?: $this->type);
            return ($classSchema->getModelType() == ClassSchema::MODELTYPE_ENTITY);
        } else {
            return false;
        }
    }

    /**
     * @return Model
     */
    public function getRelationModel()
    {
        if (!$this->isRelation()) {
            return null;
        }
        if (!$this->relationModel) {
            $this->relationModel = $this->buildService->getModelForClassname($this->elementType ?: $this->type);
        }
        return $this->relationModel;
    }

    private function parseDevmagicTags()
    {
        if ($this->reflectionService->isPropertyTaggedWith($this->model->getClassName(), $this->name, 'devmagic')) {
            $tags = $this->reflectionService->getPropertyTagValues($this->model->getClassName(), $this->name, 'devmagic');
            foreach ($tags as $tag) {
                list($key, $value) = GeneralUtility::trimExplode('=', $tag, false, 2);
                $config = $this->parseDevmagicTag($key, $value);

                $this->config = \TYPO3\CMS\Extbase\Utility\ArrayUtility::arrayMergeRecursiveOverrule($this->config, $config);

            }
            $this->label = trim($this->config['tca']['label']);
        }
    }

    private function parseDevmagicTag($key, $value)
    {
        if (strpos($key, '.') !== false) {
            list($firstKey, $remainingKeys) = GeneralUtility::trimExplode('.', $key, true, 2);
            $config[$firstKey] = $this->parseDevmagicTag($remainingKeys, $value);
        } else {
            $config[$key] = trim($value);
        }
        return $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getTabLabel() {
        return $this->config['tab'] ?: '';
    }

    /**
     * @return string
     */
    public function getTabKey() {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->getTabLabel()));
    }
}
