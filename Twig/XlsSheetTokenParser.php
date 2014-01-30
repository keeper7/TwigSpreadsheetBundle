<?php

namespace MewesK\PhpExcelTwigExtensionBundle\Twig;

class XlsSheetTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $title = $this->parser->getExpressionParser()->parseExpression();

        $properties = new \Twig_Node_Expression_Array([], $token->getLine());
        if (!$this->parser->getStream()->test(\Twig_Token::BLOCK_END_TYPE)) {
            $properties = $this->parser->getExpressionParser()->parseExpression();
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideXlsSheetEnd'], true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        $this->checkSyntaxErrorsRecursively($body);

        return new XlsSheetNode($title, $properties, $body, $token->getLine(), $this->getTag());
    }

    public function decideXlsSheetEnd(\Twig_Token $token)
    {
        return $token->test('endxlssheet');
    }

    public function getTag()
    {
        return 'xlssheet';
    }

    private function checkSyntaxErrorsRecursively(\Twig_Node $node) {
        foreach ($node->getIterator() as $subNode) {
            if ($subNode instanceof XlsSheetNode) {
                throw new \LogicException(
                    sprintf('Node "%s" is not allowed inside of Node "%s".', get_class($subNode), get_class($node))
                );
            }
            if ($subNode instanceof \Twig_Node && $subNode->count() > 0) {
                $this->checkSyntaxErrorsRecursively($subNode);
            }
        }
    }
}