<?php

declare(strict_types=1);

use App\Document\Trait\MeaningTrait;
use App\Document\Noun;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class MeaningTest extends TestCase
{
    /**
     * @return array<array<<string|bool>>
     */
    public function isValidMeaningProvider(): array
    {
        $mandatoryLang = Noun::getMandatoryLang();
        $secondaryLang = Noun::getAllowedLangs()[1];

        return [
            [
                [
                    $mandatoryLang => 'to eat',
                ], 
                0
            ],
            [
                [
                    $mandatoryLang => 'to eat',
                    $secondaryLang => 'manger',
                ], 
                0
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => 'to eat',
                ], 
                0
            ],
            ['', 0],
            [null, 0],
            [[''], 1],
            [[null], 1],
            [
                [
                    $secondaryLang => 'manger',
                ], 
                1
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => '',
                ], 
                1
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => null,
                ], 
                1
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => 'to eat',
                    'dummy' => '???',
                ], 
                2
            ],
        ];
    }

    /**
     * @dataProvider isValidMeaningProvider
     *
     * @param string|array|null $meaning
     * @param int $expected
     */
    public function testIsValidMeaning(
        string|array|null $meaning,
        int $expected,
    ): void {
        $mock = $this->getMockForTrait(MeaningTrait::class);
        $this->assertEquals($mock->isValidMeaning($meaning), $expected);
    }
}
