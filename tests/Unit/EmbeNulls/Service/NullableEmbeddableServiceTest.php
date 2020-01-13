<?php

declare(strict_types=1);

namespace Unit\EmbeNulls\Service;

use EmbeNulls\Service\NullableEmbeddableService;
use EmbeNulls\Service\YamlParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class NullableEmbeddableServiceTest extends TestCase
{
    private const DEFAULT_KERNEL_PROJECT_DIR = '/kernel-project-dir';
    private const DEFAULT_DOCTRINE_CONFIG_FILE = '%kernel.project_dir%/this/is/a/doctrine-config.yaml';
    const NAMESPACE_OF_PACKAGE_DOG = 'Namespace/Of/Class/Dog';
    const NAMESPACE_OF_PACKAGE_CAT = 'Namespace/Of/Class/Cat';
    const A_DOG_DIR = '%kernel.project_dir%/a-dog-dir';
    const A_CAT_DIR = '%kernel.project_dir%/a-cat-dir';
    const NORMALIZED_DOG_DIR = '/kernel-project-dir/a-dog-dir';
    const NORMALIZED_CAT_DIR = '/kernel-project-dir/a-cat-dir';
    const DOG_FILE_1_PATH = 'a/dog/file/Bulldog.orm.yml';
    const NAMESPACES_OF_CLASS_DOG_1 = 'Namespace/Of/Class/Dog/Bulldog';
    const DOG_FILE_2_PATH = 'a/dog/file/Chihuhua.orm.yaml';
    const NAMESPACES_OF_CLASS_DOG_2 = 'Namespace/Of/Class/Dog/Chihuhua';
    const CAT_FILE_1_PATH = 'a/cat/file/Persian.orm.yml';
    const NAMESPACES_OF_CLASS_CAT_1 = 'Namespace/Of/Class/Cat/Persian';
    const CAT_FILE_2_PATH = 'a/cat/file/Siamese.orm.xml';
    const NAMESPACES_OF_CLASS_CAT_2 = 'Namespace/Of/Class/Cat/Siamese';
    /** @var ObjectProphecy|Finder */
    private $finder;
    /** @var YamlParser|ObjectProphecy */
    private $yamlParser;

    protected function setUp(): void
    {
        $this->finder = $this->prophesize(Finder::class);
        $this->yamlParser = $this->prophesize(YamlParser::class);
    }

    /**
     * @param array $arrayInDoctrineFile
     *
     * @dataProvider getNotValidMappingsArraysData
     */
    public function test_with_a_doctrine_file_that_doesnt_have_valid_mappings_will_not_crash(array $arrayInDoctrineFile)
    {
        $this->yamlParser->parseFile(self::DEFAULT_DOCTRINE_CONFIG_FILE)->willReturn(
            $arrayInDoctrineFile
        )->shouldBeCalledOnce();

        $sut = new NullableEmbeddableService(
            $this->finder->reveal(),
            $this->yamlParser->reveal(),
            self::DEFAULT_DOCTRINE_CONFIG_FILE,
            self::DEFAULT_DOCTRINE_CONFIG_FILE
        );

        $this->assertEmpty($sut->getNullableEmbeddeds(self::NAMESPACE_OF_PACKAGE_DOG));
    }

    public function test_with_a_valid_doctrine_file_is_able_to_create_the_good_config()
    {
        $this->yamlParser->parseFile(self::DEFAULT_DOCTRINE_CONFIG_FILE)->willReturn(
            [
                'doctrine' => [
                    'orm' => [
                        'mappings' => [
                            self::NAMESPACE_OF_PACKAGE_DOG => [
                                'type' => 'yml',
                                'dir'  => self::A_DOG_DIR,
                            ],
                            self::NAMESPACE_OF_PACKAGE_CAT => [
                                'type' => 'yml',
                                'dir'  => self::A_CAT_DIR,
                            ]
                        ]
                    ]
                ]
            ]
        )->shouldBeCalledOnce();

        $this->finder->files()->willReturn($this->finder->reveal());
        $this->finder->in(self::NORMALIZED_DOG_DIR)->willReturn($this->buildDogFiles());
        $this->finder->in(self::NORMALIZED_CAT_DIR)->willReturn($this->buildCatFiles());

        $this->yamlParser->parseFile(self::DOG_FILE_1_PATH)->willReturn(
            [
                self::NAMESPACES_OF_CLASS_DOG_1 => [
                    'type'     => 'entity',
                    'fields'   => [
                        'name' => [
                            'type'     => 'string',
                            'nullable' => true,
                        ]
                    ],
                    'embedded' => [
                        'field1' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => false,
                        ],
                        'field2' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => false,
                            'nullable'     => false,
                        ],
                        'field3' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => false,
                            'nullable'     => true,
                        ],
                    ]
                ]
            ]
        )->shouldBeCalledOnce();

        $this->yamlParser->parseFile(self::DOG_FILE_2_PATH)->willReturn(
            [
                self::NAMESPACES_OF_CLASS_DOG_2 => [
                    'embedded' => [
                        'field1' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => 'hi_',
                            'nullable'     => true
                        ],
                        'field2' => [
                            'class'    => 'a/non/existing/class',
                            'nullable' => true
                        ],
                    ]
                ]
            ]
        )->shouldBeCalledOnce();

        $this->yamlParser->parseFile(self::CAT_FILE_1_PATH)->willReturn(
            [
                self::NAMESPACES_OF_CLASS_CAT_1 => [
                    'not_embedded' => [
                        'field1' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => false,
                            'nullable'     => true
                        ],
                    ]
                ]
            ]
        )->shouldBeCalledOnce();

        $this->yamlParser->parseFile(self::CAT_FILE_1_PATH)->willReturn(
            [
                self::NAMESPACES_OF_CLASS_CAT_1 => [
                    'embedded' => [
                        'field1' => [
                            'class'        => 'a/non/existing/class',
                            'columnPrefix' => false,
                            'nullable'     => false
                        ],
                    ]
                ]
            ]
        )->shouldBeCalledOnce();

        $sut = new NullableEmbeddableService(
            $this->finder->reveal(),
            $this->yamlParser->reveal(),
            self::DEFAULT_DOCTRINE_CONFIG_FILE,
            self::DEFAULT_KERNEL_PROJECT_DIR
        );

        $dog1NullableEmbeddeds = $sut->getNullableEmbeddeds(self::NAMESPACES_OF_CLASS_DOG_1);
        $dog2NullableEmbeddeds = $sut->getNullableEmbeddeds(self::NAMESPACES_OF_CLASS_DOG_2);
        $cat1NullableEmbeddeds = $sut->getNullableEmbeddeds(self::NAMESPACES_OF_CLASS_CAT_1);
        $cat2NullableEmbeddeds = $sut->getNullableEmbeddeds(self::NAMESPACES_OF_CLASS_CAT_2);

        $this->assertCount(1, $dog1NullableEmbeddeds);
        $this->assertContains('field3', $dog1NullableEmbeddeds);
        $this->assertCount(2, $dog2NullableEmbeddeds);
        $this->assertContains('field1', $dog2NullableEmbeddeds);
        $this->assertContains('field2', $dog2NullableEmbeddeds);
        $this->assertEmpty($cat1NullableEmbeddeds);
        $this->assertEmpty($cat2NullableEmbeddeds);
    }

    public function getNotValidMappingsArraysData(): array
    {
        return [
            'test_empty_array'     => [[]],
            'test_no_doctrine'     => [
                [
                    'eloquent' => [
                        'orm' => [
                            'mappings' => [
                                self::NAMESPACE_OF_PACKAGE_DOG => [
                                    'type' => 'yml',
                                    'dir'  => 'a-non-existing-dir',
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'test_no_orm'          => [
                [
                    'doctrine' => [
                        'dbal' => [
                            'types' => [
                                self::NAMESPACE_OF_PACKAGE_DOG => [
                                    'type' => 'yml',
                                    'dir'  => 'a-non-existing-dir',
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'test_no_mappings'     => [
                [
                    'doctrine' => [
                        'orm' => [
                            'auto_mapping' => true,
                        ]
                    ]
                ]
            ],
            'test_empty_mappings'  => [
                [
                    'doctrine' => [
                        'mappings' => null
                    ]
                ]
            ],
            'test_no_mapping_type' => [
                [
                    'doctrine' => [
                        'mappings' => [
                            self::NAMESPACE_OF_PACKAGE_DOG => [
                                'dir' => 'a-non-existing-dir',
                            ]
                        ]
                    ]
                ]
            ],
            'test_no_mapping_dir'  => [
                [
                    'doctrine' => [
                        'mappings' => [
                            self::NAMESPACE_OF_PACKAGE_DOG => [
                                'type' => 'yml',
                            ]
                        ]
                    ]
                ]
            ],
            'test_no_type_yml'     => [
                [
                    'doctrine' => [
                        'mappings' => [
                            self::NAMESPACE_OF_PACKAGE_DOG => [
                                'type' => 'xml',
                                'dir'  => 'a-non-existing-dir',
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }

    private function buildDogFiles(): array
    {
        $file1 = $this->prophesize(SplFileInfo::class);
        $file1->getExtension()->willReturn('yml');
        $file1->getRealPath()->willReturn(self::DOG_FILE_1_PATH);

        $file2 = $this->prophesize(SplFileInfo::class);
        $file2->getExtension()->willReturn('yaml');
        $file2->getRealPath()->willReturn(self::DOG_FILE_2_PATH);

        return [
            $file1->reveal(),
            $file2->reveal()
        ];
    }

    private function buildCatFiles(): array
    {
        $file1 = $this->prophesize(SplFileInfo::class);
        $file1->getExtension()->willReturn('yml');
        $file1->getRealPath()->willReturn(self::CAT_FILE_1_PATH);

        $file2 = $this->prophesize(SplFileInfo::class);
        $file2->getExtension()->willReturn('xml');
        $file2->getRealPath()->willReturn(self::CAT_FILE_2_PATH);

        return [
            $file1->reveal(),
            $file2->reveal()
        ];
    }
}
