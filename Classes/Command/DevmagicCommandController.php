<?php
namespace Etobi\Devmagic\Command;

use Etobi\Devmagic\Service\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

class DevmagicCommandController extends CommandController {

	protected $templateRootPaths = 'EXT:devmagic/Resources/Private/Templates/Devmagic';

	/**
	 * @param string $modelClassName
	 * @throws \Exception
	 */
	public function buildTcaFromModelCommand($modelClassName) {
		$this->outputLine('Build TCA from model');
		$this->outputLine('Model: ' . $modelClassName);

		/** @var Model $model */
		$model = $this->objectManager->get('Etobi\Devmagic\Service\Model', $modelClassName);

		$this->buildRepository($model);
		$this->buildTCA($model);
	}


	/**
	 * @param Model $model
	 */
	private function buildRepository($model) {
		if ($model->getClassSchema()->isAggregateRoot()) {
			$this->outputLine('Repository not found.');
			// TODO create repository?
		}
	}

	/**
	 * @param Model $model
	 */
	private function buildTCA($model) {
		foreach ($model->getProperties() as $name => $property) {
			$this->outputLine($name);
		}
		$tca = $this->renderView('ModelTCA', array(
			'model' => $model
		));

		var_dump($tca);
	}

	/**
	 * @param string $templateName
	 * @param array $variables
	 * @return string
	 */
	private function renderView($templateName, $variables) {
		/** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
		$view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
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
	protected function resolveTemplateRootPaths() {
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
	private function getFirstExistingFileInPaths($fileName) {
		foreach ($this->resolveTemplateRootPaths() as $path) {
			$path = rtrim($path, '/') . '/';
			if (file_exists($path . $fileName)) {
				return $path . $fileName;
			}
		}
		return NULL;
	}
}