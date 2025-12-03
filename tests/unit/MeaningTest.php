<?php

declare(strict_types=1);

use App\Document\Noun;
use App\Document\Trait\MeaningTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class MeaningTest extends TestCase
{
    /**
     * @return array<array<mixed,int>>
     */
    public function isValidMeaningProvider(): array
    {
        $mandatoryLang = Noun::getMandatoryLang();
        $secondaryLang = Noun::getAllowedLangs()[1];

        /*
        'meaning' => [
            'en' => [
                ['high', 'tall'],
                ['expensive', 'high-priced']
            ]
            'fr' => [
                ['haut', 'grand'],
                ['onéreux', 'à prix fort']
            ]
        ]
        */

        return [
            '0_mandatoryLang' => [
                [
                    $mandatoryLang => [
                        ['high', 'tall'],
                        ['expensive', 'high-priced'],
                    ],
                ],
                0,
            ],
            '0_mandatoryLang_and_secondaryLang' => [
                [
                    $mandatoryLang => [
                        ['high', 'tall'],
                        ['expensive', 'high-priced'],
                    ],
                    $secondaryLang => [
                        ['haut', 'grand'],
                        ['onéreux', 'à prix fort'],
                    ],
                ],
                0,
            ],
            '0_secondaryLang_and_mandatoryLang' => [
                [
                    $secondaryLang => [
                        ['onéreux', 'à prix fort'],
                    ],
                    $mandatoryLang => [
                        ['expensive', 'high-priced'],
                    ],
                ],
                0,
            ],
            '0_empty' => ['', 0],
            '0_null' => [null, 0],
            '1_empty_array' => [[''], 1],
            '1_null_array' => [[null], 1],
            '1_missing_mandatoryLang' => [
                [
                    $secondaryLang => [
                        ['haut', 'grand'],
                    ],
                ],
                1,
            ],
            '2_unknown_lang' => [
                [
                    $secondaryLang => [
                        ['onéreux', 'à prix fort'],
                    ],
                    $mandatoryLang => [
                        ['expensive', 'high-priced'],
                    ],
                    'dummy' => [
                        ['???'],
                    ],
                ],
                2,
            ],
            '3_mandatoryLang_empty' => [
                [
                    $mandatoryLang => ['       '],
                ],
                3,
            ],
            '3_mandatoryLang_empty_level2' => [
                [
                    $mandatoryLang => [
                        ['   '],
                    ],
                ],
                3,
            ],
            '3_mandatoryLang_null' => [
                [
                    $mandatoryLang => [null],
                ],
                3,
            ],
            '3_mandatoryLang_null_level2' => [
                [
                    $mandatoryLang => [
                        [null],
                    ],
                ],
                3,
            ],
            '3_mandatoryLang_not_an_array' => [
                [
                    $mandatoryLang => 'high',
                ],
                3,
            ],
            '3_mandatoryLang_not_an_array_level2' => [
                [
                    $mandatoryLang => [
                        'high',
                    ],
                ],
                3,
            ],
        ];
    }

    /**
     * @dataProvider isValidMeaningProvider
     *
     * @param null|array<?string>|string $meaning
     */
    public function testIsValidMeaning(
        string|array|null $meaning,
        int $expected,
    ): void {
        /** @var App\Document\Noun $mock */
        $mock = $this->getMockForTrait(MeaningTrait::class);
        $this->assertEquals($mock->isValidMeaning($meaning), $expected);
    }
}
