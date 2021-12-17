<?php
declare(strict_types=1);
namespace Wwwision\Neos\TaskModule\ViewHelpers\Format;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

final class JsonViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'the JSON string to render', true);
    }

    public function render()
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();
        if ($value === null) {
            return null;
        }
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
