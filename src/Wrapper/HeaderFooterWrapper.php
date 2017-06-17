<?php

namespace MewesK\TwigSpreadsheetBundle\Wrapper;

use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;

/**
 * Class HeaderFooterWrapper.
 */
class HeaderFooterWrapper extends BaseWrapper
{
    /**
     * @var SheetWrapper
     */
    protected $sheetWrapper;

    /**
     * @var null|HeaderFooter
     */
    protected $object;
    /**
     * @var array
     */
    protected $alignmentParameters;

    /**
     * HeaderFooterWrapper constructor.
     *
     * @param array             $context
     * @param \Twig_Environment $environment
     * @param SheetWrapper      $sheetWrapper
     */
    public function __construct(array $context, \Twig_Environment $environment, SheetWrapper $sheetWrapper)
    {
        parent::__construct($context, $environment);

        $this->sheetWrapper = $sheetWrapper;

        $this->object = null;
        $this->alignmentParameters = [];
    }

    /**
     * @param string $type
     * @param array  $properties
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function start(string $type, array $properties = [])
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }
        if (in_array(strtolower($type),
                ['header', 'oddheader', 'evenheader', 'firstheader', 'footer', 'oddfooter', 'evenfooter', 'firstfooter'],
                true) === false
        ) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        $this->object = $this->sheetWrapper->getObject()->getHeaderFooter();
        $this->parameters['value'] = ['left' => null, 'center' => null, 'right' => null]; // will be generated by the alignment tags
        $this->parameters['type'] = $type;
        $this->parameters['properties'] = $properties;

        $this->setProperties($properties);
    }

    public function end()
    {
        $value = implode('', $this->parameters['value']);

        switch (strtolower($this->parameters['type'])) {
            case 'header':
                $this->object->setOddHeader($value);
                $this->object->setEvenHeader($value);
                $this->object->setFirstHeader($value);
                break;
            case 'oddheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddHeader($value);
                break;
            case 'evenheader':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenHeader($value);
                break;
            case 'firstheader':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstHeader($value);
                break;
            case 'footer':
                $this->object->setOddFooter($value);
                $this->object->setEvenFooter($value);
                $this->object->setFirstFooter($value);
                break;
            case 'oddfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setOddFooter($value);
                break;
            case 'evenfooter':
                $this->object->setDifferentOddEven(true);
                $this->object->setEvenFooter($value);
                break;
            case 'firstfooter':
                $this->object->setDifferentFirst(true);
                $this->object->setFirstFooter($value);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $this->parameters['type']));
        }

        $this->object = null;
        $this->parameters = [];
    }

    /**
     * @param string $type
     * @param array  $properties
     *
     * @throws \InvalidArgumentException
     */
    public function startAlignment(string $type, array $properties = [])
    {
        $this->alignmentParameters['type'] = $type;
        $this->alignmentParameters['properties'] = $properties;

        switch (strtolower($type)) {
            case 'left':
                $this->parameters['value']['left'] = '&L';
                break;
            case 'center':
                $this->parameters['value']['center'] = '&C';
                break;
            case 'right':
                $this->parameters['value']['right'] = '&R';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $this->alignmentParameters['type']));
        }
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     */
    public function endAlignment($value)
    {
        switch (strtolower($this->alignmentParameters['type'])) {
            case 'left':
                if (strpos($this->parameters['value']['left'], '&G') === false) {
                    $this->parameters['value']['left'] .= $value;
                }
                break;
            case 'center':
                if (strpos($this->parameters['value']['center'], '&G') === false) {
                    $this->parameters['value']['center'] .= $value;
                }
                break;
            case 'right':
                if (strpos($this->parameters['value']['right'], '&G') === false) {
                    $this->parameters['value']['right'] .= $value;
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $this->alignmentParameters['type']));
        }

        $this->alignmentParameters = [];
    }

    /**
     * @return null|HeaderFooter
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param null|HeaderFooter $object
     */
    public function setObject(HeaderFooter $object = null)
    {
        $this->object = $object;
    }

    /**
     * @return array
     */
    public function getAlignmentParameters(): array
    {
        return $this->alignmentParameters;
    }

    /**
     * @param array $alignmentParameters
     */
    public function setAlignmentParameters(array $alignmentParameters)
    {
        $this->alignmentParameters = $alignmentParameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureMappings(): array
    {
        return [
            'scaleWithDocument' => function ($value) { $this->object->setScaleWithDocument($value); },
            'alignWithMargins' => function ($value) { $this->object->setAlignWithMargins($value); },
        ];
    }
}