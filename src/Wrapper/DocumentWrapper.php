<?php

namespace K7\TwigSpreadsheetBundle\Wrapper;

use K7\TwigSpreadsheetBundle\Helper\Filesystem;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use function is_string;
use LogicException;
use PhpOffice\PhpSpreadsheet\Exception;
use \PhpOffice\PhpSpreadsheet\Reader\Exception as Reader_Exception;
use \PhpOffice\PhpSpreadsheet\Writer\Exception as Writer_Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use RuntimeException;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Filesystem\Exception\IOException;
use Twig\Environment as Twig_Environment;
use Twig\Loader\FilesystemLoader as Twig_Loader_Filesystem;

/**
 * Class DocumentWrapper.
 */
class DocumentWrapper extends BaseWrapper
{
    /**
     * @var Spreadsheet|null
     */
    protected $object;
    /**
     * @var array
     */
    protected $attributes;

    /**
     * DocumentWrapper constructor.
     *
     * @param array             $context
     * @param Twig_Environment $environment
     * @param array             $attributes
     */
    public function __construct(array $context, Twig_Environment $environment, array $attributes = [])
    {
        parent::__construct($context, $environment);

        $this->object = null;
        $this->attributes = $attributes;
    }

    /**
     * @param array $properties
     *
     * @throws RuntimeException
     * @throws Reader_Exception
     * @throws Exception
     */
    public function start(array $properties = [])
    {
        // load template
        if (isset($properties['template'])) {
            $templatePath = $this->expandPath($properties['template']);
            $reader = IOFactory::createReaderForFile($templatePath);
            $reader->setIncludeCharts($this->attributes['include_charts'] ?? false);
            $this->object = $reader->load($templatePath);
        }

        // create new
        else {
            $this->object = new Spreadsheet();
            $this->object->removeSheetByIndex(0);
        }

        $this->parameters['properties'] = $properties;

        $this->setProperties($properties);
    }

    /**
     * @throws LogicException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws Writer_Exception
     * @throws IOException
     */
    public function end()
    {
        if ($this->object === null) {
            throw new LogicException();
        }

        $format = null;

        // try document property
        if (isset($this->parameters['format'])) {
            $format = $this->parameters['format'];
        }

        // try Symfony request
        elseif (isset($this->context['app'])) {
            /**
             * @var AppVariable
             */
            $appVariable = $this->context['app'];
            if ($appVariable instanceof AppVariable && $appVariable->getRequest() !== null) {
                $format = $appVariable->getRequest()->getRequestFormat();
            }
        }

        // set default
        if ($format === null || !is_string($format)) {
            $format = 'xlsx';
        } else {
            $format = strtolower($format);
        }

        // set up mPDF
        if ($format === 'pdf') {
            if (!class_exists('\Mpdf\Mpdf')) {
                throw new RuntimeException('Error loading mPDF. Is mPDF correctly installed?');
            }
            IOFactory::registerWriter('Pdf', $this->attributes['pdf_writer']['class']);
        }

        /**
         * @var BaseWriter $writer
         */
        $writer = IOFactory::createWriter($this->object, ucfirst($format));
        $writer->setPreCalculateFormulas($this->attributes['pre_calculate_formulas'] ?? true);

        StringHelper::setDecimalSeparator($this->attributes['string_helper']['decimal_separator'] ?? StringHelper::getDecimalSeparator());
        StringHelper::setThousandsSeparator($this->attributes['string_helper']['thousands_separator'] ??  StringHelper::getThousandsSeparator());

        if ($format === 'pdf' || $format === 'html') {
            $writer->setEmbedImages($this->attributes['embed_images'] ?? false);
        }

        // Set tmp folder for mpdf
        if ($format === 'pdf' && $this->attributes['pdf_writer']['tmp_folder'] !== false) {
            $writer->setTempDir($this->attributes['pdf_writer']['tmp_folder']);
        }

        // set up XML cache
        if ($this->attributes['cache']['xml'] !== false) {
            Filesystem::mkdir($this->attributes['cache']['xml']);
            $writer->setUseDiskCaching(true, $this->attributes['cache']['xml']);
        }

        $writer->setIncludeCharts($this->attributes['include_charts'] ?? false);
        // set special CSV writer attributes
        if ($writer instanceof Csv) {
            /**
             * @var Csv $writer
             */
            $writer->setDelimiter($this->attributes['csv_writer']['delimiter']);
            $writer->setEnclosure($this->attributes['csv_writer']['enclosure']);
            $writer->setExcelCompatibility($this->attributes['csv_writer']['excel_compatibility']);
            $writer->setIncludeSeparatorLine($this->attributes['csv_writer']['include_separator_line']);
            $writer->setLineEnding($this->attributes['csv_writer']['line_ending']);
            $writer->setSheetIndex($this->attributes['csv_writer']['sheet_index']);
            $writer->setUseBOM($this->attributes['csv_writer']['use_bom']);
        }

        $writer->save('php://output');

        $this->object = null;
        $this->parameters = [];
    }

    /**
     * @return Spreadsheet|null
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Spreadsheet|null $object
     */
    public function setObject(Spreadsheet $object = null)
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    protected function configureMappings(): array
    {
        return [
            'category' => function ($value) { $this->object->getProperties()->setCategory($value); },
            'company' => function ($value) { $this->object->getProperties()->setCompany($value); },
            'created' => function ($value) { $this->object->getProperties()->setCreated($value); },
            'creator' => function ($value) { $this->object->getProperties()->setCreator($value); },
            'defaultStyle' => function ($value) { $this->object->getDefaultStyle()->applyFromArray($value); },
            'description' => function ($value) { $this->object->getProperties()->setDescription($value); },
            'format' => function ($value) { $this->parameters['format'] = $value; },
            'keywords' => function ($value) { $this->object->getProperties()->setKeywords($value); },
            'lastModifiedBy' => function ($value) { $this->object->getProperties()->setLastModifiedBy($value); },
            'manager' => function ($value) { $this->object->getProperties()->setManager($value); },
            'modified' => function ($value) { $this->object->getProperties()->setModified($value); },
            'security' => [
                'lockRevision' => function ($value) { $this->object->getSecurity()->setLockRevision($value); },
                'lockStructure' => function ($value) { $this->object->getSecurity()->setLockStructure($value); },
                'lockWindows' => function ($value) { $this->object->getSecurity()->setLockWindows($value); },
                'revisionsPassword' => function ($value) { $this->object->getSecurity()->setRevisionsPassword($value); },
                'workbookPassword' => function ($value) { $this->object->getSecurity()->setWorkbookPassword($value); },
            ],
            'subject' => function ($value) { $this->object->getProperties()->setSubject($value); },
            'template' => function ($value) { $this->parameters['template'] = $value; },
            'title' => function ($value) { $this->object->getProperties()->setTitle($value); },
        ];
    }

    /**
     * Resolves paths using Twig namespaces.
     * The path must start with the namespace.
     * Namespaces are case sensitive.
     *
     * @param string $path
     *
     * @return string
     */
    private function expandPath(string $path): string
    {
        $loader = $this->environment->getLoader();

        if ($loader instanceof Twig_Loader_Filesystem && mb_strpos($path, '@') === 0) {
            /*
             * @var Twig_Loader_Filesystem
             */
            foreach ($loader->getNamespaces() as $namespace) {
                if (mb_strpos($path, $namespace) === 1) {
                    foreach ($loader->getPaths($namespace) as $namespacePath) {
                        $expandedPathAttribute = str_replace('@'.$namespace, $namespacePath, $path);
                        if (Filesystem::exists($expandedPathAttribute)) {
                            return $expandedPathAttribute;
                        }
                    }
                }
            }
        }

        return $path;
    }
}
