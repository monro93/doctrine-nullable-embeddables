<?php

declare(strict_types=1);

namespace EmbeNulls\Service;

use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    public function parseFile(string $path)
    {
        return Yaml::parseFile($path);
    }
}
