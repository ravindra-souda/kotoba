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
            [
                [
                    $mandatoryLang => ['to eat'],
                ],
                0,
            ],
            [
                [
                    $mandatoryLang => ['to eat'],
                    $secondaryLang => ['manger'],
                ],
                0,
            ],
            [
                [
                    $secondaryLang => ['manger'],
                    $mandatoryLang => ['to eat'],
                ],
                0,
            ],
            ['', 0],
            [null, 0],
            [[''], 1],
            [[null], 1],
            [
                [
                    $secondaryLang => ['manger'],
                ],
                1,
            ],
            [
                [
                    $secondaryLang => ['manger'],
                    $mandatoryLang => ['to eat'],
                    'dummy' => ['???'],
                ],
                2,
            ],
            [
                [
                    $mandatoryLang => ['       '],
                ],
                3,
            ],
            [
                [
                    $mandatoryLang => [null],
                ],
                3,
            ],
            [
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
