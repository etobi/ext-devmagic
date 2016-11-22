<?php
namespace Etobi\Devmagic\Domain\Model\File;

use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFile
{
    /**
     * @var string
     */
    protected $templateRootPaths = 'EXT:devmagic/Resources/Private/Templates';

    /**
     * @var string
     */
    protected $partialRootPaths = 'EXT:devmagic/Resources/Private/Partials';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * @param string $extensionName
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    public function write()
    {
        $content = $this->renderContent();
        $path = $this->getPath();
        if (file_exists($path)) {
//            throw new \Exception('TCA target file ' . $path . ' already exists', 1479741882);
        }
        GeneralUtility::mkdir_deep(dirname($path));
        file_put_contents($path, $content);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $path = 'EXT:' . $this->extensionName . '/' . $this->path;
        $absolutePath = GeneralUtility::getFileAbsFileName($path);
        return $absolutePath;
    }

    /**
     * @return string
     */
    public function renderContent()
    {
        $content = $this->renderView($this->path . '.txt', array(
                'file' => $this
        ));
        return $content;
    }

    /**
     * @param string $templateName
     * @param array $variables
     * @return string
     */
    protected function renderView($templateName, $variables)
    {
        /** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
        $view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setFormat('txt');
        $templateFilepath = $this->getFirstExistingFileInPaths($templateName);
        $view->setTemplatePathAndFilename($templateFilepath);
        $view->setPartialRootPaths($this->resolvePartialRootPaths());
        $view->assignMultiple($variables);
        return $view->render();
    }

    /**
     * Resolves the defined template path(s) to absolute paths.
     *
     * @return array
     */
    private function resolvePartialRootPaths()
    {
        return $this->explodeRootPaths($this->partialRootPaths);
    }

    /**
     * Resolves the defined template path(s) to absolute paths.
     *
     * @return array
     */
    private function resolveTemplateRootPaths()
    {
        return $this->explodeRootPaths($this->templateRootPaths);
    }

    /**
     * Resolves the defined template path(s) to absolute paths.
     *
     * @return array
     */
    private function explodeRootPaths($rootPaths)
    {
        $rootPaths = GeneralUtility::trimExplode(',', $rootPaths);

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
