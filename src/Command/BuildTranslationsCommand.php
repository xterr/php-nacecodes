<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to build translation files.
 *
 * Converts translation files between different formats:
 * - PHP arrays (source of truth for native implementation)
 * - YAML (for Symfony integration)
 * - Laravel format (for Laravel integration)
 */
class BuildTranslationsCommand extends Command
{
    /** @var string */
    private $resourcesDir;

    /** @var string */
    private $domain = 'naceCodes';

    /**
     * @param string|null $resourcesDir
     */
    public function __construct($resourcesDir = null)
    {
        parent::__construct('nace:translations:build');
        $this->resourcesDir = $resourcesDir ?: dirname(__DIR__, 2) . '/Resources/translations';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Build translation files for NACE codes')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'Action to perform: php-from-yaml, yaml-generate, laravel-generate, all'
            )
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_REQUIRED,
                'Process only specific locale (e.g., de, fr, es)'
            )
            ->setHelp(<<<'HELP'
The <info>%command.name%</info> command builds translation files in various formats.

Actions:
  <comment>php-from-yaml</comment>     Convert existing YAML files to PHP arrays (one-time migration)
  <comment>yaml-generate</comment>     Generate YAML files for Symfony from PHP source
  <comment>laravel-generate</comment>  Generate Laravel-format PHP files
  <comment>all</comment>               Generate all formats (YAML + Laravel)

Examples:
  <info>%command.full_name% php-from-yaml</info>              # Migrate all YAML to PHP
  <info>%command.full_name% yaml-generate --locale=de</info>  # Generate German YAML only
  <info>%command.full_name% all</info>                        # Generate all formats
HELP
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $locale = $input->getOption('locale');

        switch ($action) {
            case 'php-from-yaml':
                $io->title('Converting YAML files to PHP arrays');
                $this->yamlToPhp($io, $locale);
                $io->success('Done!');
                break;

            case 'yaml-generate':
                $io->title('Generating YAML files from PHP arrays');
                $this->phpToYaml($io, $locale);
                $io->success('Done!');
                break;

            case 'laravel-generate':
                $io->title('Generating Laravel files from PHP arrays');
                $this->phpToLaravel($io, $locale);
                $io->success('Done!');
                break;

            case 'all':
                $io->title('Generating all translation formats');

                $io->section('YAML');
                $this->phpToYaml($io, $locale);

                $io->section('Laravel');
                $this->phpToLaravel($io, $locale);

                $io->success('All formats generated!');
                break;

            default:
                $io->error(sprintf('Unknown action: %s', $action));
                $io->text('Valid actions: php-from-yaml, yaml-generate, laravel-generate, all');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Converts YAML files to PHP arrays.
     *
     * @param SymfonyStyle $io
     * @param string|null  $locale
     *
     * @return void
     */
    private function yamlToPhp(SymfonyStyle $io, $locale = null)
    {
        $phpDir = $this->resourcesDir . '/php';
        if (!is_dir($phpDir)) {
            mkdir($phpDir, 0755, true);
        }

        $patterns = [
            $this->resourcesDir . '/messages_*.yaml',
            $this->resourcesDir . '/' . $this->domain . '.*.yaml',
        ];

        foreach ($patterns as $pattern) {
            $yamlFiles = glob($pattern);
            if ($yamlFiles === false) {
                $yamlFiles = [];
            }

            foreach ($yamlFiles as $yamlFile) {
                $fileLocale = $this->extractLocaleFromYaml($yamlFile);

                if ($locale !== null && $this->normalizeLocale($fileLocale) !== $this->normalizeLocale($locale)) {
                    continue;
                }

                $io->text(sprintf('Converting: <comment>%s</comment>', basename($yamlFile)));

                $translations = $this->parseYaml($yamlFile);
                $normalizedLocale = $this->normalizeLocale($fileLocale);
                $phpFile = $phpDir . '/' . $this->domain . '.' . $normalizedLocale . '.php';

                $this->writePhpArray($phpFile, $translations, $normalizedLocale);
                $io->text(sprintf('  -> <info>%s</info>', basename($phpFile)));
            }
        }
    }

    /**
     * Generates YAML files from PHP arrays.
     *
     * @param SymfonyStyle $io
     * @param string|null  $locale
     *
     * @return void
     */
    private function phpToYaml(SymfonyStyle $io, $locale = null)
    {
        $yamlDir = $this->resourcesDir . '/yaml';
        if (!is_dir($yamlDir)) {
            mkdir($yamlDir, 0755, true);
        }

        $phpFiles = glob($this->resourcesDir . '/php/' . $this->domain . '.*.php');
        if ($phpFiles === false) {
            $phpFiles = [];
        }

        foreach ($phpFiles as $phpFile) {
            $fileLocale = $this->extractLocaleFromPhp($phpFile);

            if ($locale !== null && $fileLocale !== $locale) {
                continue;
            }

            $io->text(sprintf('Generating: <comment>%s</comment>', basename($phpFile)));

            $translations = require $phpFile;
            $yamlFile = $yamlDir . '/' . $this->domain . '.' . $fileLocale . '.yaml';

            $this->writeYaml($yamlFile, $translations);
            $io->text(sprintf('  -> <info>%s</info>', basename($yamlFile)));
        }
    }

    /**
     * Generates Laravel format files from PHP arrays.
     *
     * @param SymfonyStyle $io
     * @param string|null  $locale
     *
     * @return void
     */
    private function phpToLaravel(SymfonyStyle $io, $locale = null)
    {
        $laravelDir = $this->resourcesDir . '/laravel/vendor/' . $this->getLaravelNamespace();

        $phpFiles = glob($this->resourcesDir . '/php/' . $this->domain . '.*.php');
        if ($phpFiles === false) {
            $phpFiles = [];
        }

        foreach ($phpFiles as $phpFile) {
            $fileLocale = $this->extractLocaleFromPhp($phpFile);

            if ($locale !== null && $fileLocale !== $locale) {
                continue;
            }

            $io->text(sprintf('Generating: <comment>%s</comment>', basename($phpFile)));

            $translations = require $phpFile;
            $localeDir = $laravelDir . '/' . $fileLocale;

            if (!is_dir($localeDir)) {
                mkdir($localeDir, 0755, true);
            }

            $laravelFile = $localeDir . '/' . $this->domain . '.php';
            $this->writeLaravelArray($laravelFile, $translations);
            $io->text(sprintf('  -> <info>%s</info>', basename($laravelFile)));
        }
    }

    /**
     * Parses a YAML file and returns translations array.
     *
     * @param string $file
     *
     * @return array<string, string>
     */
    private function parseYaml($file)
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }

        $lines = explode("\n", $content);
        $translations = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            if (preg_match("/^['\"]?(.+?)['\"]?:\\s*['\"]?(.+?)['\"]?\\s*$/", $line, $matches)) {
                $key = $this->unquoteYaml($matches[1]);
                $value = $this->unquoteYaml($matches[2]);
                $translations[$key] = $value;
            }
        }

        return $translations;
    }

    /**
     * Unquotes a YAML value.
     *
     * @param string $value
     *
     * @return string
     */
    private function unquoteYaml($value)
    {
        $value = trim($value);

        if ((substr($value, 0, 1) === "'" && substr($value, -1) === "'") ||
            (substr($value, 0, 1) === '"' && substr($value, -1) === '"')) {
            $value = substr($value, 1, -1);
        }

        $value = str_replace("''", "'", $value);
        $value = str_replace('\\"', '"', $value);

        return $value;
    }

    /**
     * Writes translations to a YAML file.
     *
     * @param string               $file
     * @param array<string,string> $translations
     *
     * @return void
     */
    private function writeYaml($file, array $translations)
    {
        $content = "# Generated file - do not edit manually\n\n";

        foreach ($translations as $key => $value) {
            $escapedKey = $this->escapeYaml($key);
            $escapedValue = $this->escapeYaml($value);
            $content .= "{$escapedKey}: {$escapedValue}\n";
        }

        file_put_contents($file, $content);
    }

    /**
     * Escapes a value for YAML output.
     *
     * @param string $value
     *
     * @return string
     */
    private function escapeYaml($value)
    {
        if (preg_match('/[:\[\]{}#&*!|>\'"%@`]/', $value) ||
            ctype_digit($value) ||
            in_array(strtolower($value), ['true', 'false', 'null', 'yes', 'no'], true)) {
            return "'" . str_replace("'", "''", $value) . "'";
        }

        return $value;
    }

    /**
     * Writes translations to a PHP array file.
     *
     * @param string               $file
     * @param array<string,string> $translations
     * @param string               $locale
     *
     * @return void
     */
    private function writePhpArray($file, array $translations, $locale)
    {
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * NACE Codes translations - " . strtoupper($locale) . "\n";
        $content .= " *\n";
        $content .= " * @generated Source of truth for translations\n";
        $content .= " */\n\n";
        $content .= "return [\n";

        foreach ($translations as $key => $value) {
            $escapedKey = var_export($key, true);
            $escapedValue = var_export($value, true);
            $content .= "    {$escapedKey} => {$escapedValue},\n";
        }

        $content .= "];\n";

        file_put_contents($file, $content);
    }

    /**
     * Writes translations to a Laravel format PHP file.
     *
     * @param string               $file
     * @param array<string,string> $translations
     *
     * @return void
     */
    private function writeLaravelArray($file, array $translations)
    {
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * Laravel translations\n";
        $content .= " *\n";
        $content .= " * @generated Generated from PHP source\n";
        $content .= " */\n\n";
        $content .= "return [\n";

        foreach ($translations as $key => $value) {
            $escapedKey = var_export($key, true);
            $escapedValue = var_export($value, true);
            $content .= "    {$escapedKey} => {$escapedValue},\n";
        }

        $content .= "];\n";

        file_put_contents($file, $content);
    }

    /**
     * Extracts locale from a YAML filename.
     *
     * @param string $file
     *
     * @return string
     */
    private function extractLocaleFromYaml($file)
    {
        $basename = basename($file);

        if (preg_match('/messages_(.+)\.yaml$/', $basename, $matches)) {
            return $matches[1];
        }

        $pattern = '/' . preg_quote($this->domain, '/') . '\.(.+)\.yaml$/';
        if (preg_match($pattern, $basename, $matches)) {
            return $matches[1];
        }

        return 'en';
    }

    /**
     * Extracts locale from a PHP filename.
     *
     * @param string $file
     *
     * @return string
     */
    private function extractLocaleFromPhp($file)
    {
        $basename = basename($file);
        $pattern = '/' . preg_quote($this->domain, '/') . '\.(.+)\.php$/';

        if (preg_match($pattern, $basename, $matches)) {
            return $matches[1];
        }

        return 'en';
    }

    /**
     * Normalizes a locale code.
     *
     * @param string $locale
     *
     * @return string
     */
    private function normalizeLocale($locale)
    {
        $parts = explode('_', str_replace('-', '_', $locale));
        return strtolower($parts[0]);
    }

    /**
     * Gets the Laravel namespace.
     *
     * @return string
     */
    private function getLaravelNamespace()
    {
        return strtolower($this->domain);
    }
}
