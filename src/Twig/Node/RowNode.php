<?php

namespace K7\TwigSpreadsheetBundle\Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;

/**
 * Class RowNode.
 */
#[YieldReady]
class RowNode extends BaseNode
{
    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->startRow(')
                ->subcompile($this->getNode('index'))
            ->raw(');'.\PHP_EOL)
            ->subcompile($this->getNode('body'))
            ->addDebugInfo($this)
            ->write(self::CODE_INSTANCE.'->endRow();'.\PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedParents(): array
    {
        return [
            SheetNode::class,
        ];
    }
}
