<?php

namespace K7\TwigSpreadsheetBundle\Twig\NodeVisitor;

use K7\TwigSpreadsheetBundle\Twig\Node\BaseNode;
use K7\TwigSpreadsheetBundle\Twig\Node\DocumentNode;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Class SyntaxCheckNodeVisitor.
 */
class SyntaxCheckNodeVisitor implements NodeVisitorInterface
{
    protected array $path = [];

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     */
    public function enterNode(Node $node, Environment $env): Node
    {
        try {
            if ($node instanceof BaseNode) {
                $this->checkAllowedParents($node);
            } else {
                $this->checkAllowedChildren($node);
            }
        } catch (SyntaxError $e) {
            // reset path since throwing an error prevents doLeaveNode to be called
            $this->path = [];
            throw $e;
        }

        $this->path[] = $node !== null ? \get_class($node) : null;

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node, Environment $env): Node
    {
        array_pop($this->path);

        return $node;
    }

    /**
     * @param Node $node
     *
     * @throws SyntaxError
     */
    private function checkAllowedChildren(Node $node): void
    {
        $hasDocumentNode = false;
        $hasTextNode = false;

        /**
         * @var Node $currentNode
         */
        foreach ($node->getIterator() as $currentNode) {
            if ($currentNode instanceof TextNode) {
                if ($hasDocumentNode) {
                    throw new SyntaxError(sprintf('Node "%s" is not allowed after Node "%s".', TextNode::class, DocumentNode::class));
                }
                $hasTextNode = true;
            } elseif ($currentNode instanceof DocumentNode) {
                if ($hasTextNode) {
                    throw new SyntaxError(sprintf('Node "%s" is not allowed before Node "%s".', TextNode::class, DocumentNode::class));
                }
                $hasDocumentNode = true;
            }
        }
    }

    /**
     * @param BaseNode $node
     *
     * @throws SyntaxError
     */
    private function checkAllowedParents(BaseNode $node): void
    {
        $parentName = null;

        // find first parent from this bundle
        foreach (array_reverse($this->path) as $className) {
            if (str_starts_with($className, 'K7\\TwigSpreadsheetBundle\\Twig\\Node\\')) {
                $parentName = $className;
                break;
            }
        }

        // allow no parents (e.g. macros, includes)
        if ($parentName === null) {
            return;
        }

        // check if parent is allowed
        foreach ($node->getAllowedParents() as $className) {
            if ($className === $parentName) {
                return;
            }
        }

        throw new SyntaxError(sprintf('Node "%s" is not allowed inside of Node "%s".', \get_class($node), $parentName));
    }
}
