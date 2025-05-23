<?php

namespace K7\TwigSpreadsheetBundle\Wrapper;

use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;
use Twig\Environment;

/**
 * Class HeaderFooterWrapper.
 */
class HeaderFooterWrapper extends BaseWrapper
{
    public const ALIGNMENT_CENTER = 'center';
    public const ALIGNMENT_LEFT = 'left';
    public const ALIGNMENT_RIGHT = 'right';

    public const BASETYPE_FOOTER = 'footer';
    public const BASETYPE_HEADER = 'header';

    public const TYPE_EVEN = 'even';
    public const TYPE_FIRST = 'first';
    public const TYPE_ODD = 'odd';

    protected SheetWrapper $sheetWrapper;
    protected ?HeaderFooter $object;
    protected array $alignmentParameters;

    /**
     * HeaderFooterWrapper constructor.
     *
     * @param array        $context
     * @param Environment  $environment
     * @param SheetWrapper $sheetWrapper
     */
    public function __construct(array $context, Environment $environment, SheetWrapper $sheetWrapper)
    {
        parent::__construct($context, $environment);

        $this->sheetWrapper = $sheetWrapper;
        $this->object = null;
        $this->alignmentParameters = [];
    }

    /**
     * @param string $alignment
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateAlignment(string $alignment): string
    {
        if (!\in_array($alignment, [self::ALIGNMENT_CENTER, self::ALIGNMENT_LEFT, self::ALIGNMENT_RIGHT], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown alignment "%s"', $alignment));
        }

        return $alignment;
    }

    /**
     * @param string $baseType
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateBaseType(string $baseType): string
    {
        if (!\in_array($baseType, [self::BASETYPE_FOOTER, self::BASETYPE_HEADER], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown base type "%s"', $baseType));
        }

        return $baseType;
    }

    /**
     * @param string      $baseType
     * @param string|null $type
     * @param array       $properties
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function start(string $baseType, ?string $type = null, array $properties = []): void
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }

        if ($type !== null) {
            $type = strtolower($type);

            if (!\in_array($type, [self::TYPE_EVEN, self::TYPE_FIRST, self::TYPE_ODD], true)) {
                throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
            }
        }

        $this->object = $this->sheetWrapper->getObject()->getHeaderFooter();
        $this->parameters['baseType'] = self::validateBaseType(strtolower($baseType));
        $this->parameters['type'] = $type;
        $this->parameters['properties'] = $properties;
        $this->parameters['value'] = [self::ALIGNMENT_LEFT => null, self::ALIGNMENT_CENTER => null, self::ALIGNMENT_RIGHT => null]; // will be generated by the alignment tags

        $this->setProperties($properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function end(): void
    {
        if ($this->object === null) {
            throw new \LogicException();
        }

        $value = implode('', $this->parameters['value']);

        switch ($this->parameters['type']) {
            case null:
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setOddHeader($value);
                    $this->object->setEvenHeader($value);
                    $this->object->setFirstHeader($value);
                } else {
                    $this->object->setOddFooter($value);
                    $this->object->setEvenFooter($value);
                    $this->object->setFirstFooter($value);
                }
                break;
            case self::TYPE_EVEN:
                $this->object->setDifferentOddEven(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setEvenHeader($value);
                } else {
                    $this->object->setEvenFooter($value);
                }
                break;
            case self::TYPE_FIRST:
                $this->object->setDifferentFirst(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setFirstHeader($value);
                } else {
                    $this->object->setFirstFooter($value);
                }
                break;
            case self::TYPE_ODD:
                $this->object->setDifferentOddEven(true);
                if ($this->parameters['baseType'] === self::BASETYPE_HEADER) {
                    $this->object->setOddHeader($value);
                } else {
                    $this->object->setOddFooter($value);
                }
                break;
        }

        $this->object = null;
        $this->parameters = [];
    }

    /**
     * @param string $alignment
     * @param array  $properties
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function startAlignment(string $alignment, array $properties = []): void
    {
        if ($this->object === null) {
            throw new \LogicException();
        }

        $alignment = self::validateAlignment(strtolower($alignment));

        $this->alignmentParameters['type'] = $alignment;
        $this->alignmentParameters['properties'] = $properties;

        switch ($alignment) {
            case self::ALIGNMENT_LEFT:
                $this->parameters['value'][self::ALIGNMENT_LEFT] = '&L';
                break;
            case self::ALIGNMENT_CENTER:
                $this->parameters['value'][self::ALIGNMENT_CENTER] = '&C';
                break;
            case self::ALIGNMENT_RIGHT:
                $this->parameters['value'][self::ALIGNMENT_RIGHT] = '&R';
                break;
        }
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function endAlignment($value): void
    {
        if ($this->object === null || !isset($this->alignmentParameters['type'])) {
            throw new \LogicException();
        }

        if (!str_contains($this->parameters['value'][$this->alignmentParameters['type']], '&G')) {
            $this->parameters['value'][$this->alignmentParameters['type']] .= $value;
        }

        $this->alignmentParameters = [];
    }

    /**
     * @return HeaderFooter|null
     */
    public function getObject(): ?HeaderFooter
    {
        return $this->object;
    }

    /**
     * @param HeaderFooter|null $object
     */
    public function setObject(?HeaderFooter $object = null): void
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
    public function setAlignmentParameters(array $alignmentParameters): void
    {
        $this->alignmentParameters = $alignmentParameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureMappings(): array
    {
        return [
            'scaleWithDocument' => function ($value) {
                $this->object->setScaleWithDocument($value);
            },
            'alignWithMargins' => function ($value) {
                $this->object->setAlignWithMargins($value);
            },
        ];
    }
}
