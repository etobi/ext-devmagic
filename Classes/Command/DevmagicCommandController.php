<?php
namespace Etobi\Devmagic\Command;

use Etobi\Devmagic\Domain\Model\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

class DevmagicCommandController extends CommandController
{

    /**
     * @var \Etobi\Devmagic\Service\BuildService
     * @inject
     */
    protected $buildService;

    /**
     * @param string $extensionName
     * @param string $modelClassName
     * @throws \Exception
     */
    public function buildTcaFromModelCommand($extensionName, $modelClassName)
    {
        $this->outputLine('Build TCA from model');
        $this->outputLine('Extension: ' . $extensionName);
        $this->outputLine('Model: ' . $modelClassName);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        /** @var Model $model */
        $model = $this->objectManager->get(\Etobi\Devmagic\Domain\Model\Model::class, $modelClassName);
        $content = $this->buildService->buildTcaForModel($model);

        $targetPath = 'EXT:' . $extensionName . '/Configuration/TCA/' . $model->getTableName() . '.php';
        $this->outputLine('Target file: ' . $targetPath);
        $targetAbsolutePath = GeneralUtility::getFileAbsFileName($targetPath);
        if (file_exists($targetAbsolutePath)) {
//            throw new \Exception('TCA target file ' . $targetPath . ' already exists', 1479741882);
        }

        GeneralUtility::mkdir_deep(dirname($targetAbsolutePath));
        file_put_contents($targetAbsolutePath, $content);
    }


    // TODO buildLocallangDbXlf
    // TODO buildExtTablesSql
    // TODO buildModelFromYaml
    // TODO buildExtension
}