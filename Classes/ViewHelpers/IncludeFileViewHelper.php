<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class IncludeFileViewHelper.
 */
class IncludeFileViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('path', 'string', 'Path to file', true);
        $this->registerArgument('compress', 'bool', 'Enable compression', false, true);
        $this->registerArgument('library', 'bool', 'Is library file', false, false);
        $this->registerArgument('exclude', 'bool', 'Exclude', false, false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): void
    {
        $path = $arguments['path'];
        $compress = $arguments['compress'];
        $isLibrary = $arguments['library'];
        $exclude = $arguments['exclude'];

        if (TYPO3_MODE === 'FE' && !empty($path)) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

            // JS
            if (strtolower(substr($path, -3)) === '.js') {
                if ($isLibrary) {
                    $pageRenderer->addJsFooterLibrary(
                        $path,
                        $path,
                        null,
                        $compress,
                        false,
                        '',
                        $exclude
                    );
                } else {
                    $pageRenderer->addJsFooterFile($path, null, $compress, false, '', $exclude);
                }

                // CSS
            } elseif (strtolower(substr($path, -4)) === '.css') {
                if ($isLibrary) {
                    $pageRenderer->addCssLibrary($path, 'stylesheet', 'all', '', $compress, false, '', $exclude);
                } else {
                    $pageRenderer->addCssFile($path, 'stylesheet', 'all', '', $compress, false, '', $exclude);
                }
            }
        }
    }
}
