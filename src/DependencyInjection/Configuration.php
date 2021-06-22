<?php

namespace K7\TwigSpreadsheetBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getConfigTreeBuilder()
    {
    	if (version_compare(Kernel::VERSION, '4.3.0', '>=')) {
		    $treeBuilder = new TreeBuilder('k7_twig_spreadsheet');
		    $rootNode = $treeBuilder->getRootNode();
	    } else {
		    $treeBuilder = new TreeBuilder();
		    $rootNode = $treeBuilder->root('k7_twig_spreadsheet');
	    }

        $rootNode
            ->children()
                ->booleanNode('pre_calculate_formulas')
                    ->defaultTrue()
                    ->info('Disabling formula calculations can improve the performance but the resulting documents won\'t immediately show formula results in external programs.')
                ->end()
                ->booleanNode('embed_images')
                    ->defaultFalse()
                    ->info('Embed images into html or xlsx.')
                ->end()
                ->booleanNode('include_charts')
                    ->defaultFalse()
                    ->info('Include charts from template.')
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('bitmap')
                            ->defaultValue('%kernel.cache_dir%/spreadsheet/bitmap')
                            ->cannotBeEmpty()
                            ->info('Using a bitmap cache is necessary, PhpSpreadsheet supports only local files.')
                        ->end()
                        ->scalarNode('xml')
                            ->defaultFalse()
                            ->example('"%kernel.cache_dir%/spreadsheet/xml"')
                            ->info('Using XML caching can improve memory consumption by writing data to disk. Works only for .xlsx and .ods documents.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('string_helper')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('decimal_separator')
                            ->defaultValue('.')
                        ->end()
                        ->scalarNode('thousands_separator')
                            ->defaultValue(',')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('csv_writer')
                    ->addDefaultsIfNotSet()
                    ->info('See PhpOffice\PhpSpreadsheet\Writer\Csv.php for more information.')
                    ->children()
                        ->scalarNode('delimiter')
                            ->defaultValue(',')
                        ->end()
                        ->scalarNode('enclosure')
                            ->defaultValue('"')
                        ->end()
                        ->booleanNode('excel_compatibility')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('include_separator_line')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('line_ending')
                            ->defaultValue(PHP_EOL)
                        ->end()
                        ->integerNode('sheet_index')
                            ->defaultValue(0)
                        ->end()
                        ->booleanNode('use_bom')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pdf_writer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('tmp_folder')
                            ->defaultFalse()
                            ->example('%kernel.project_dir%/var/spreadsheet/pdf"')
                            ->info('Temporary folder for mPDF.')
                        ->end()
                        ->scalarNode('class')
                            ->defaultValue('PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf')
                            ->example('PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf')
                            ->info('Pdf renderer class.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
