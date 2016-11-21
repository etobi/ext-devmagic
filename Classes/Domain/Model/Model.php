<?php
namespace Etobi\Devmagic\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;

class Model
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     * @inject
     */
    protected $dataMapper;

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

    protected $propertyNamesToIgnore = array(
            'uid',
            'pid',
            '_localizedUid',
            '_languageUid',
            '_versionedUid',
    );

    /**
     * @var string
     */
    protected $extensionKey;

    /**
     * @var ClassSchema
     */
    protected $classSchema;

    /**
     * @var DataMap
     */
    protected $dataMap;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    public function initializeObject()
    {
        $explodedNamespace = GeneralUtility::trimExplode('\\', $this->className, true, 3);
        $this->extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($explodedNamespace[1]);
        $this->classSchema = $this->buildClassSchema($this->className);
        $this->dataMap = $this->buildDataMap($this->className);
    }

    /**
     * @return string
     */
    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    /**
     * @return ClassSchema
     */
    public function getClassSchema()
    {
        return $this->classSchema;
    }

    /**
     * @return DataMap
     */
    public function getDataMap()
    {
        return $this->dataMap;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->dataMap->getTableName();
    }

    /**
     * return array
     */
    public function getProperties()
    {
        if (!$this->properties) {
            $this->buildPropertiesArray();
        }

        return $this->properties;
    }


    /**
     * @param string $modelClassName
     * @return ClassSchema
     * @throws \Exception
     */
    private function buildClassSchema($modelClassName)
    {
        if (!class_exists($modelClassName)) {
            throw new \Exception('Class "' . $modelClassName . '" does not exist', 1454585729);
        }

        $schema = $this->reflectionService->getClassSchema($modelClassName);

        if ($schema->getModelType() !== ClassSchema::MODELTYPE_ENTITY) {
            throw new \Exception('Class "' . $modelClassName . '" must be of entity model type', 1454585947);
        }

        return $schema;
    }

    /**
     * @param string $className
     * @return DataMap
     */
    private function buildDataMap($className)
    {
        return $this->dataMapper->getDataMap($className);
    }

    private function buildPropertiesArray()
    {
        $this->properties = array();
        foreach ($this->getClassSchema()->getProperties() as $name => $schema) {
            if (!in_array($name, $this->propertyNamesToIgnore)) {
                /** @var ModelProperty $property */
                $property = $this->objectManager->get(ModelProperty::class);
                $property->setName($name);
                $property->setSchema($schema);
                $this->properties[$name] = $property;
            }
        }
    }
}