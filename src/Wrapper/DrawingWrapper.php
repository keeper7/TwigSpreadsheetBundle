<?php

namespace K7\TwigSpreadsheetBundle\Wrapper;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing;
use Twig\Environment;

/**
 * Class DrawingWrapper.
 */
class DrawingWrapper extends BaseWrapper
{
    protected SheetWrapper $sheetWrapper;
    protected HeaderFooterWrapper $headerFooterWrapper;
    protected ?Drawing $object;
    protected array $attributes;

    /**
     * DrawingWrapper constructor.
     *
     * @param array               $context
     * @param Environment         $environment
     * @param SheetWrapper        $sheetWrapper
     * @param HeaderFooterWrapper $headerFooterWrapper
     * @param array               $attributes
     */
    public function __construct(array $context, Environment $environment, SheetWrapper $sheetWrapper, HeaderFooterWrapper $headerFooterWrapper, array $attributes = [])
    {
        parent::__construct($context, $environment);

        $this->sheetWrapper = $sheetWrapper;
        $this->headerFooterWrapper = $headerFooterWrapper;
        $this->object = null;
        $this->attributes = $attributes;
    }

    /**
     * @param string $path
     * @param array  $properties
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws Exception
     */
    public function start(string $path, array $properties = []): void
    {
        if ($this->sheetWrapper->getObject() === null) {
            throw new \LogicException();
        }

        // add to header/footer
        if ($this->headerFooterWrapper->getObject()) {
            $headerFooterParameters = $this->headerFooterWrapper->getParameters();
            $alignment = $this->headerFooterWrapper->getAlignmentParameters()['type'];
            $location = '';

            switch ($alignment) {
                case HeaderFooterWrapper::ALIGNMENT_CENTER:
                    $location .= 'C';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_CENTER] .= '&G';
                    break;
                case HeaderFooterWrapper::ALIGNMENT_LEFT:
                    $location .= 'L';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_LEFT] .= '&G';
                    break;
                case HeaderFooterWrapper::ALIGNMENT_RIGHT:
                    $location .= 'R';
                    $headerFooterParameters['value'][HeaderFooterWrapper::ALIGNMENT_RIGHT] .= '&G';
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown alignment type "%s"', $alignment));
            }

            $location .= $headerFooterParameters['baseType'] === HeaderFooterWrapper::BASETYPE_HEADER ? 'H' : 'F';

            $this->object = new HeaderFooterDrawing();
            $this->object->setPath($path);
            $this->headerFooterWrapper->getObject()->addImage($this->object, $location);
            $this->headerFooterWrapper->setParameters($headerFooterParameters);
        }

        // add to worksheet
        else {
            $this->object = new Drawing();
            $this->object->setWorksheet($this->sheetWrapper->getObject());
            $this->object->setPath($path);
        }

        $this->setProperties($properties);
    }

    public function end(): void
    {
        $this->object = null;
        $this->parameters = [];
    }

    public function getObject(): ?Drawing
    {
        return $this->object;
    }

    /**
     * @param Drawing $object
     */
    public function setObject(Drawing $object): void
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureMappings(): array
    {
        return [
            'coordinates' => function ($value) {
                $this->object->setCoordinates($value);
            },
            'description' => function ($value) {
                $this->object->setDescription($value);
            },
            'height' => function ($value) {
                $this->object->setHeight($value);
            },
            'name' => function ($value) {
                $this->object->setName($value);
            },
            'offsetX' => function ($value) {
                $this->object->setOffsetX($value);
            },
            'offsetY' => function ($value) {
                $this->object->setOffsetY($value);
            },
            'resizeProportional' => function ($value) {
                $this->object->setResizeProportional($value);
            },
            'rotation' => function ($value) {
                $this->object->setRotation($value);
            },
            'shadow' => [
                'alignment' => function ($value) {
                    $this->object->getShadow()->setAlignment($value);
                },
                'alpha' => function ($value) {
                    $this->object->getShadow()->setAlpha($value);
                },
                'blurRadius' => function ($value) {
                    $this->object->getShadow()->setBlurRadius($value);
                },
                'color' => function ($value) {
                    $this->object->getShadow()->getColor()->setRGB($value);
                },
                'direction' => function ($value) {
                    $this->object->getShadow()->setDirection($value);
                },
                'distance' => function ($value) {
                    $this->object->getShadow()->setDistance($value);
                },
                'visible' => function ($value) {
                    $this->object->getShadow()->setVisible($value);
                },
            ],
            'width' => function ($value) {
                $this->object->setWidth($value);
            },
        ];
    }
}
