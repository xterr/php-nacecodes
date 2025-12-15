<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Command;

use EasyRdf\Graph;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

/**
 * Console command to parse NACE RDF files and generate resource files.
 *
 * Parses the official NACE classification RDF files from Eurostat and generates:
 * - JSON data files for sections, divisions, groups, codes, and MIG groups
 * - YAML translation files for all available languages
 */
class ParseRdfCommand extends Command
{
    private const VERSION = 2;

    public function __construct()
    {
        parent::__construct('nace:parse-rdf');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Parse NACE RDF file and generate resource files')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Path to the NACE RDF file'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output directory for generated files',
                dirname(__DIR__, 2) . '/Resources'
            )
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command parses NACE classification RDF files and generates
JSON data files and YAML translation files.

Example:
  <info>%command.full_name% --file=NACE_Rev.2.rdf</info>
  <info>%command.full_name% -f NACE_Rev.2.rdf -o ./output</info>

The RDF file can be downloaded from Eurostat:
https://ec.europa.eu/eurostat/ramon/nomenclatures/index.cfm?TargetUrl=LST_NOM_DTL&StrNom=NACE_REV2
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getOption('file');
        $outputDir = $input->getOption('output-dir');

        if (!$filename) {
            $io->error('File option is required. Use --file or -f to specify the RDF file.');
            return Command::FAILURE;
        }

        if (!file_exists($filename)) {
            $io->error(sprintf('File not found: %s', $filename));
            return Command::FAILURE;
        }

        $io->title('Parsing NACE RDF File');
        $io->text(sprintf('Input file: <info>%s</info>', $filename));
        $io->text(sprintf('Output directory: <info>%s</info>', $outputDir));

        $doc = new Graph();
        $io->text('Loading RDF file...');
        $doc->parseFile($filename);

        $aSections = [];
        $aDivisions = [];
        $aGroups = [];
        $aClasses = [];
        $aMigs = [];
        $aTranslations = [];

        // Parse sections
        $io->section('Parsing sections...');
        foreach ($doc->allResources('http://data.europa.eu/ux2/nace2/sections', 'skos:member') as $resource) {
            $code = $resource->get('skos:notation')->getValue();
            $aSections[$code] = [
                'code' => $code,
                'rawCode' => $code,
                'version' => self::VERSION,
            ];

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() === 'en') {
                    $aSections[$code]['name'] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() !== 'en') {
                    $aTranslations[$altLabel->getLang()][$aSections[$code]['name']] = $altLabel->getValue();
                }
            }
        }
        $io->text(sprintf('Found <info>%d</info> sections', count($aSections)));

        // Parse divisions
        $io->section('Parsing divisions...');
        foreach ($doc->allResources('http://data.europa.eu/ux2/nace2/divisions', 'skos:member') as $resource) {
            $code = $resource->get('skos:notation')->getValue();
            $aDivisions[$code] = [
                'code' => $code,
                'rawCode' => $code,
                'version' => self::VERSION,
                'section' => str_replace('.', '', $resource->get('skos:broader')->get('skos:notation')->getValue()),
            ];

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() === 'en') {
                    $aDivisions[$code]['name'] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() !== 'en') {
                    $aTranslations[$altLabel->getLang()][$aDivisions[$code]['name']] = $altLabel->getValue();
                }
            }
        }
        $io->text(sprintf('Found <info>%d</info> divisions', count($aDivisions)));

        // Parse groups
        $io->section('Parsing groups...');
        foreach ($doc->allResources('http://data.europa.eu/ux2/nace2/groups', 'skos:member') as $resource) {
            $code = $resource->get('skos:notation')->getValue();
            $aGroups[$code] = [
                'code' => str_replace('.', '', $code),
                'rawCode' => $code,
                'version' => self::VERSION,
                'division' => str_replace('.', '', $resource->get('skos:broader')->get('skos:notation')->getValue()),
            ];

            $aGroups[$code]['section'] = $aDivisions[$aGroups[$code]['division']]['section'];

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() === 'en') {
                    $aGroups[$code]['name'] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() !== 'en') {
                    $aTranslations[$altLabel->getLang()][$aGroups[$code]['name']] = $altLabel->getValue();
                }
            }
        }
        $io->text(sprintf('Found <info>%d</info> groups', count($aGroups)));

        // Parse classes
        $io->section('Parsing classes...');
        foreach ($doc->allResources('http://data.europa.eu/ux2/nace2/classes', 'skos:member') as $resource) {
            $code = $resource->get('skos:notation')->getValue();
            $aClasses[$code] = [
                'code' => str_replace('.', '', $code),
                'rawCode' => $code,
                'version' => self::VERSION,
                'group' => str_replace('.', '', $resource->get('skos:broader')->get('skos:notation')->getValue()),
            ];

            $aClasses[$code]['division'] = $aGroups[$resource->get('skos:broader')->get('skos:notation')->getValue()]['division'];
            $aClasses[$code]['section'] = $aDivisions[$aClasses[$code]['division']]['section'];

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() === 'en') {
                    $aClasses[$code]['name'] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:altLabel') as $altLabel) {
                if ($altLabel->getLang() !== 'en') {
                    $aTranslations[$altLabel->getLang()][$aClasses[$code]['name']] = $altLabel->getValue();
                }
            }
        }
        $io->text(sprintf('Found <info>%d</info> classes', count($aClasses)));

        // Parse MIG groups
        $io->section('Parsing MIG groups...');
        foreach ($doc->allResources('http://data.europa.eu/ux2/nace2/MIG', 'skos:member') as $resource) {
            $code = $resource->get('skos:notation')->getValue();
            $aMigs[$code] = [
                'code' => $code,
                'rawCode' => $code,
                'version' => self::VERSION,
                'members' => [],
            ];

            foreach ($resource->all('skos:prefLabel') as $altLabel) {
                if ($altLabel->getLang() === 'en') {
                    $aMigs[$code]['name'] = $altLabel->getValue();
                } else {
                    $aTranslations[$altLabel->getLang()][$aMigs[$code]['name']] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:prefLabel') as $altLabel) {
                if ($altLabel->getLang() !== 'en') {
                    $aTranslations[$altLabel->getLang()][$aMigs[$code]['name']] = $altLabel->getValue();
                }
            }

            foreach ($resource->all('skos:member') as $migMemberResource) {
                $aMigs[$code]['members'][] = $migMemberResource->get('skos:notation')->getValue();
            }
        }
        $io->text(sprintf('Found <info>%d</info> MIG groups', count($aMigs)));

        // Write translation files
        $io->section('Writing translation files...');
        $translationsDir = $outputDir . '/translations';
        if (!is_dir($translationsDir)) {
            mkdir($translationsDir, 0755, true);
        }

        foreach ($aTranslations as $language => $messages) {
            $yaml = Yaml::dump($messages, 2, 2);
            $filePath = sprintf('%s/messages_%s.yaml', $translationsDir, $language);
            file_put_contents($filePath, $yaml);
            $io->text(sprintf('  -> <info>messages_%s.yaml</info> (%d translations)', $language, count($messages)));
        }

        // Write JSON files
        $io->section('Writing JSON files...');

        file_put_contents($outputDir . '/naceSections.json', json_encode(array_values($aSections), JSON_PRETTY_PRINT));
        $io->text(sprintf('  -> <info>naceSections.json</info> (%d entries)', count($aSections)));

        file_put_contents($outputDir . '/naceDivisions.json', json_encode(array_values($aDivisions), JSON_PRETTY_PRINT));
        $io->text(sprintf('  -> <info>naceDivisions.json</info> (%d entries)', count($aDivisions)));

        file_put_contents($outputDir . '/naceGroups.json', json_encode(array_values($aGroups), JSON_PRETTY_PRINT));
        $io->text(sprintf('  -> <info>naceGroups.json</info> (%d entries)', count($aGroups)));

        file_put_contents($outputDir . '/naceCodes.json', json_encode(array_values($aClasses), JSON_PRETTY_PRINT));
        $io->text(sprintf('  -> <info>naceCodes.json</info> (%d entries)', count($aClasses)));

        file_put_contents($outputDir . '/mig.json', json_encode(array_values($aMigs), JSON_PRETTY_PRINT));
        $io->text(sprintf('  -> <info>mig.json</info> (%d entries)', count($aMigs)));

        $io->success('All resource files generated successfully!');

        return Command::SUCCESS;
    }
}
