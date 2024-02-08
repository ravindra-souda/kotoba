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

        return [
            '0_mandatoryLang' => [
                [
                    $mandatoryLang => ['to eat'],
                ],
                0,
            ],
            '0_mandatoryLang_and_secondaryLang' => [
                [
                    $mandatoryLang => ['to eat'],
                    $secondaryLang => ['manger'],
                ],
                0,
            ],
            '0_secondaryLang_and_mandatoryLang' => [
                [
                    $secondaryLang => ['manger'],
                    $mandatoryLang => ['to eat'],
                ],
                0,
            ],
            '0_empty' => ['', 0],
            '0_null' => [null, 0],
            '1_empty_array' => [[''], 1],
            '1_null_array' => [[null], 1],
            '1_missing_mandatoryLang' => [
                [
                    $secondaryLang => ['manger'],
                ],
                1,
            ],
            '2_unknown_lang' => [
                [
                    $secondaryLang => ['manger'],
                    $mandatoryLang => ['to eat'],
                    'dummy' => ['???'],
                ],
                2,
            ],
            '3_mandatoryLang_empty' => [
                [
                    $mandatoryLang => ['       '],
                ],
                3,
            ],
            '3_mandatoryLang_null' => [
                [
                    $mandatoryLang => [null],
                ],
                3,
            ],
            '3_mandatoryLang_not_an_array' => [
                [
                    $mandatoryLang => 'to eat',
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
