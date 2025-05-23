<?php

namespace K7\TwigSpreadsheetBundle\Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;

/**
 * Class DrawingNode.
 */
#[YieldReady]
class DrawingNode extends BaseNode
{
    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this)
            ->write(self::CODE_FIX_CONTEXT)
            ->write(self::CODE_INSTANCE.'->startDrawing(')
                ->subcompile($this->getNode('path'))->raw(', ')
                ->subcompile($this->getNode('properties'))
            ->raw(');'.\PHP_EOL)
            ->write(self::CODE_INSTANCE.'->endDrawing();'.\PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedParents(): array
    {
        return [
            SheetNode::class,
            AlignmentNode::class,
        ];
    }
}
