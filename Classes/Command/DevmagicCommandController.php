<?php
namespace Etobi\Devmagic\Command;

use Etobi\Devmagic\Domain\Model\File\AbstractFile;
use Etobi\Devmagic\Domain\Model\Model;
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
    public function buildFromModelsCommand($vendorName, $extensionName)
    {
        $this->outputLine('Build TCA from model');
        $this->outputLine('Extension: ' . $vendorName . ' ' . $extensionName);

        $files = $this->buildService->buildFilesFromModels($vendorName, $extensionName);
        /** @var AbstractFile $file */
        foreach ($files as $file) {
            $this->outputLine($file->getPath());
            $file->write();
        }
    }


    // TODO buildLocallangDbXlf
    // TODO buildExtTablesSql
    // TODO buildModelFromYaml
    // TODO buildExtension
}