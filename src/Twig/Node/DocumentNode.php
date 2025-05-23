<?php

namespace K7\TwigSpreadsheetBundle\Twig\Node;

use K7\TwigSpreadsheetBundle\Wrapper\PhpSpreadsheetWrapper;
use Twig\Attribute\YieldReady;
use Twig\Compiler;

/**
 * Class DocumentNode.
 */
#[YieldReady]
class DocumentNode extends BaseNode
{
    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write("ob_start();\n")
            ->write(self::CODE_INSTANCE.' = new '.PhpSpreadsheetWrapper::class.'($context, $this->env, ')
                ->repr($this->attributes)
            ->raw(');'.\PHP_EOL)
            ->write(self::CODE_INSTANCE.'->startDocument(')
                ->subcompile($this->getNode('properties'))
            ->raw(');'.\PHP_EOL)
            ->subcompile($this->getNode('body'))
            ->addDebugInfo($this)
            ->write("ob_end_clean();\n")
            ->write('yield from '.self::CODE_INSTANCE.'->yieldDocument();'.\PHP_EOL)
            ->write(self::CODE_INSTANCE.'->endDocument();'.\PHP_EOL)
            ->write('unset('.self::CODE_INSTANCE.');'.\PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedParents(): array
    {
        return [];
    }
}
