<?php
	class ClosestDealers {
		public function main() {echo "string";
		$eid = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Utility\EidUtility');
$eid->connectDB();

			/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $TSFE */
			$TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
			 
			// Initialize Language
			\TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
			 
			// Initialize FE User.
			$TSFE->initFEuser();
			 
			// Important: no Cache for Ajax stuff
			$TSFE->set_no_cache();
			//$TSFE->checkAlternativeIdMethods();
			$TSFE->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			$TSFE->determineId();
			$TSFE->initTemplate();
			$TSFE->getConfigArray();
			\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->loadCachedTca();
			$TSFE->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			$TSFE->settingLanguage();
			$TSFE->settingLocale();

			$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			$repo = $objectManager->get('PXA\\PxaDealers\\Domain\\Repository\\DealersRepository');
			$query = $repo->createQuery();
			$query->getQuerySettings()->setStoragePageIds(array(6084));
			\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($query->execute());
  			die('test');
  			return $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pxaexternallogin.']['settings.'];


		}
	}

	$test = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('ClosestDealers');
	$test->main();
?>