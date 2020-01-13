<?php

declare(strict_types=1);

namespace EmbeNulls\Service;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

class NullableEmbeddableService
{
    const YML = 'yml';
    const YAML = 'yaml';
    /** @var Finder */
    private $finder;
    /** @var YamlParser */
    private $yamlParser;
    /** @var string */
    private $kernelProjectDir;
    /** @var array */
    private $classNullableEmbeddeds = [];

    public function __construct(Finder $finder, YamlParser $yamlParser, string $doctrineConfigFile, string $kernelProjectDir = '')
    {
        $this->finder = $finder;
        $this->yamlParser = $yamlParser;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->loadNullableEmbeddeds($doctrineConfigFile);
    }

    public function getNullableEmbeddeds(string $class): array
    {
        return $this->classNullableEmbeddeds[$class] ?? [];
    }

    private function loadNullableEmbeddeds(string $doctrineConfigFile): void
    {
        $dirs = $this->getOrmDirectories($doctrineConfigFile);

        $files = $this->getAllYMLFilesInDirs($dirs);

        foreach ($files as $file) {
            $configurationArray = $this->yamlParser->parseFile($file);
            $this->processConfigurationArray($configurationArray);
        }
    }

    private function getOrmDirectories(string $doctrineConfigFile): array
    {
        $config = $this->yamlParser->parseFile($doctrineConfigFile);
        $directories = [];
        if (isset($config['doctrine']) && isset($config['doctrine']['orm']) && isset($config['doctrine']['orm']['mappings'])) {
            $mappings = $config['doctrine']['orm']['mappings'];
            foreach ($mappings as $domain) {
                if (isset($domain['type']) && isset($domain['dir']) && $domain['type'] == self::YML) {
                    $directories[] = $this->normalizePath($domain['dir']);
                }
            }
        }

        return $directories;
    }

    private function getAllYMLFilesInDirs(array $dirs): array
    {
        $files = [];

        foreach ($dirs as $dir) {
            /** @var SplFileInfo $file */
            foreach ($this->finder->files()->in($dir) as $file) {
                if (in_array($file->getExtension(), [self::YML, self::YAML])) {
                    $files[] = $file->getRealPath();
                }
            }
        }

        return $files;
    }

    private function processConfigurationArray(array $configurationArray): void
    {
        foreach ($configurationArray as $class => $config) {
            $nullableEmbedded = [];
            if (isset($config['embedded'])) {
                foreach ($config['embedded'] as $property => $settings) {
                    if (isset($settings['nullable']) && $settings['nullable']) {
                        $nullableEmbedded[] = $property;
                    }
                }
            }
            $this->classNullableEmbeddeds[$class] = $nullableEmbedded;
        }
    }

    private function normalizePath(string $path): string
    {
        return str_replace('%kernel.project_dir%', $this->kernelProjectDir, $path);
    }
}
