<?php
namespace Etobi\Devmagic\Service;

use Etobi\Devmagic\Domain\Model\Model;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BuildService implements SingletonInterface
{

    protected $templateRootPaths = 'EXT:devmagic/Resources/Private/Templates/Devmagic';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @param Model $model
     * @return string
     */
    public function buildTcaForModel($model)
    {
        $tcaContent = $this->renderView('Model/Tca', array(
                'model' => $model
        ));
        return $tcaContent;
    }

    /**
     * @param string $templateName
     * @param array $variables
     * @return string
     */
    private function renderView($templateName, $variables)
    {
        /** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
        $view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setFormat('txt');
        $templateFilepath = $this->getFirstExistingFileInPaths($templateName . '.txt');
        $view->setTemplatePathAndFilename($templateFilepath);
        $view->assignMultiple($variables);
        return $view->render();
    }

    /**
     * Resolves the defined template path(s) to absolute paths.
     *
     * @return array
     */
    private function resolveTemplateRootPaths()
    {
        $rootPaths = GeneralUtility::trimExplode(',', $this->templateRootPaths);

        foreach ($rootPaths as &$path) {
            $path = GeneralUtility::getFileAbsFileName($path);
        }

        return $rootPaths;
    }

    /**
     * Checks the list of folders if they contain a file with the given name and returns the first existing file’s path.
     *
     * @param string $fileName
     * @return NULL|string
     */
    private function getFirstExistingFileInPaths($fileName)
    {
        foreach ($this->resolveTemplateRootPaths() as $path) {
            $path = rtrim($path, '/') . '/';
            if (file_exists($path . $fileName)) {
                return $path . $fileName;
            }
        }
        return null;
    }
}