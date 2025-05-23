<?php

namespace K7\TwigSpreadsheetBundle\Twig\Node;

use K7\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use Twig\Node\Node;

/**
 * Class BaseNode.
 */
abstract class BaseNode extends Node
{
    /**
     * @var string
     */
    public const CODE_FIX_CONTEXT = '$context = '.PhpSpreadsheetWrapper::class.'::fixContext($context);'.\PHP_EOL;

    /**
     * @var string
     */
    public const CODE_INSTANCE = '$context[\''.PhpSpreadsheetWrapper::INSTANCE_KEY.'\']';

    /**
     * @return string[]
     */
    abstract public function getAllowedParents(): array;
}
