<?php
declare(strict_types=1);
namespace Wwwision\Neos\TaskModule\ViewHelpers\Format;

use Lorisleiva\CronTranslator\CronParsingException;
use Lorisleiva\CronTranslator\CronTranslator;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

final class CronExpressionViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'the cron expression to render', true);
    }

    public function render()
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null) {
            return null;
        }
        try {
            return CronTranslator::translate($value);
        } catch (CronParsingException $e) {
            return $value;
        }
    }
}
