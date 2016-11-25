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
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $labelPropertyName;

    /**
     * @var bool
     */
    protected $hideTable = false;

    /**
     * @var array
     */
    protected $config = array();

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
        $explodedNamespace = GeneralUtility::revExplode('\\', $this->className, 2);
        $this->name = $explodedNamespace[1];
        $this->classSchema = $this->buildClassSchema($this->className);
        $this->dataMap = $this->buildDataMap($this->className);

        $this->parseDevmagicTags();
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
     * @return string
     */
    public function getTitle()
    {
        return $this->config['tca']['ctrl']['title'] ?: $this->name;
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
     * @param string $className
     * @return ClassSchema
     * @throws \Exception
     */
    private function buildClassSchema($className)
    {
        if (!class_exists($className)) {
            throw new \Exception('Class "' . $className . '" does not exist', 1454585729);
        }

        $classSchema = $this->reflectionService->getClassSchema($className);

        if ($classSchema->getModelType() !== ClassSchema::MODELTYPE_ENTITY) {
            throw new \Exception('Class "' . $className . '" must be of entity model type', 1454585947);
        }

        return $classSchema;
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
        $classSchema = $this->getClassSchema();
        foreach ($classSchema->getProperties() as $name => $schema) {
            if (!in_array($name, $this->propertyNamesToIgnore)) {

                if (!$this->labelPropertyName) {
                    $this->labelPropertyName = $name;
                }

                /** @var ModelProperty $property */
                $property = $this->objectManager->get(ModelProperty::class);
                $property->setModel($this);
                $property->setName($name);
                $property->setSchema($schema);
                $this->properties[$name] = $property;
            }
        }
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getLabelPropertyName()
    {
        return $this->labelPropertyName ?: $this->config['tca']['ctrl']['label'] ?: 'uid';
    }

    /**
     * @return boolean
     */
    public function isHideTable()
    {
        return $this->hideTable;
    }

    private function parseDevmagicTags()
    {
        $tags = $this->reflectionService->getClassTagValues($this->getClassName(), 'devmagic');
        foreach ($tags as $tag) {
            list($key, $value) = GeneralUtility::trimExplode('=', $tag, 2);
            $config = $this->parseDevmagicTag($key, $value);

            $this->config = \TYPO3\CMS\Extbase\Utility\ArrayUtility::arrayMergeRecursiveOverrule($this->config, $config);
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
}