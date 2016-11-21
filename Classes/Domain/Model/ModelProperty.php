<?php
namespace Etobi\Devmagic\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModelProperty
{

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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->key = GeneralUtility::camelCaseToLowerCaseUnderscored($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getTcaColumnType() {
        switch ($this->type) {
            case 'string':
                return 'InputString';
            default:
                return 'InputString';
        }
    }
}