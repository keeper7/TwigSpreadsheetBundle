<?php

namespace K7\TwigSpreadsheetBundle\Twig\TokenParser;

use K7\TwigSpreadsheetBundle\Twig\Node\AlignmentNode;
use K7\TwigSpreadsheetBundle\Wrapper\HeaderFooterWrapper;
use InvalidArgumentException;
use Twig\Node\Node as Twig_Node;

/**
 * Class AlignmentTokenParser.
 */
class AlignmentTokenParser extends BaseTokenParser
{
    /**
     * @var string
     */
    private $alignment;

    /**
     * AlignmentTokenParser constructor.
     *
     * @param array  $attributes optional attributes for the corresponding node
     * @param string $alignment
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $attributes = [], string $alignment = HeaderFooterWrapper::ALIGNMENT_CENTER)
    {
        parent::__construct($attributes);

        $this->alignment = HeaderFooterWrapper::validateAlignment(strtolower($alignment));
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function createNode(array $nodes = [], int $lineNo = 0): Twig_Node
    {
        return new AlignmentNode($nodes, $this->getAttributes(), $lineNo, $this->getTag(), $this->alignment);
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'xls'.$this->alignment;
    }
}
