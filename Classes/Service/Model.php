<?php
namespace Etobi\Devmagic\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;

class Model {

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
	 * @param string $className
	 */
	public function __construct($className) {
		$this->className = $className;
	}

	public function initializeObject() {
		$explodedNamespace = GeneralUtility::trimExplode('\\', $this->className, true, 3);
		$this->extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($explodedNamespace[1]);
		$this->classSchema = $this->buildClassSchema($this->className);
		$this->dataMap = $this->buildDataMap($this->className);
	}

	/**
	 * @return string
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * @return ClassSchema
	 */
	public function getClassSchema() {
		return $this->classSchema;
	}

	/**
	 * @return DataMap
	 */
	public function getDataMap() {
		return $this->dataMap;
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->dataMap->getTableName();
	}

	/**
	 * return array
	 */
	public function getProperties() {
		$properties = array();
		var_dump($properties);
		foreach ($this->getClassSchema()->getProperties() as $name => $schema) {
			if (!in_array($name, $this->propertyNamesToIgnore)) {
				$key = GeneralUtility::camelCaseToLowerCaseUnderscored($name);
				$properties[$key] = $schema;
				$properties[$key]['name'] = $name;
				$properties[$key]['key'] = $key;
			}
		}

		return $properties;
	}


	/**
	 * @param string $modelClassName
	 * @return ClassSchema
	 * @throws \Exception
	 */
	private function buildClassSchema($modelClassName) {
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
	private function buildDataMap($className) {
		return $this->dataMapper->getDataMap($className);
	}
}