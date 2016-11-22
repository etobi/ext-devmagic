<?php
namespace Etobi\Devmagic\Service;

use Etobi\Devmagic\Domain\Model\File\ConfigurationTcaFile;
use Etobi\Devmagic\Domain\Model\File\ExtTablesSqlFile;
use Etobi\Devmagic\Domain\Model\File\LocallangDbFile;
use Etobi\Devmagic\Domain\Model\Model;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BuildService implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    protected $models = [];

    /**
     * @param string $extensionName
     * @return array
     */
    public function buildFilesFromModels($vendorName, $extensionName)
    {
        $files = [];
        $modelClassNames = $this->getClassesInNamespace(
                '\\' . $vendorName .
                '\\' . GeneralUtility::underscoredToUpperCamelCase($extensionName) .
                '\\Domain\\Model');

        /** @var LocallangDbFile $locallandDbFile */
        $locallandDbFile = $this->objectManager->get(LocallangDbFile::class);
        $locallandDbFile->setExtensionName($extensionName);
        $files[] = $locallandDbFile;

        /** @var ExtTablesSqlFile $extTablesSql */
        $extTablesSql = $this->objectManager->get(ExtTablesSqlFile::class);
        $extTablesSql->setExtensionName($extensionName);
        $files[] = $extTablesSql;

        foreach ($modelClassNames as $modelClassName) {
            $model = $this->getModelForClassname($modelClassName);
            /** @var ConfigurationTcaFile $tcaFile */
            $tcaFile = $this->objectManager->get(ConfigurationTcaFile::class);
            $tcaFile->setExtensionName($extensionName);
            $tcaFile->setModel($model);
            $files[] = $tcaFile;

            $locallandDbFile->addModel($model);
            $extTablesSql->addModel($model);
        }

        return $files;
    }

    /**
     * @param string $namespace
     * @return array
     */
    public function getClassesInNamespace($namespace)
    {
        list($vendor, $extensionNamespace, $fragments) = GeneralUtility::trimExplode('\\', $namespace, true, 3);
        $extensionName = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionNamespace);

        $files = scandir(GeneralUtility::getFileAbsFileName('EXT:' . $extensionName . '/Classes/' . str_replace('\\', '/', $fragments)));

        $classes = array_map(function ($file) use ($namespace) {
            return $namespace . '\\' . str_replace('.php', '', $file);
        }, $files);

        return array_filter($classes, function ($possibleClass) {
            return class_exists($possibleClass);
        });
    }

    /**
     * @param string $className
     * @return Model
     */
    public function getModelForClassname($className)
    {
        if (!$this->models[$className]) {
            $this->models[$className] = $this->objectManager->get(\Etobi\Devmagic\Domain\Model\Model::class, $className);
        }
        return $this->models[$className];
    }
}