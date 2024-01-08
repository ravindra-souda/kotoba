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
                true
            ],
            [
                [
                    $mandatoryLang => 'to eat',
                    $secondaryLang => 'manger',
                ], 
                true
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => 'to eat',
                ], 
                true
            ],
            ['', false],
            [null, false],
            [
                [
                    $secondaryLang => 'manger',
                ], 
                false
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => '',
                ], 
                false
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => null,
                ], 
                false
            ],
            [
                [
                    $secondaryLang => 'manger',
                    $mandatoryLang => 'to eat',
                    'dummy' => '???',
                ], 
                false
            ],
        ];
    }

    /**
     * @dataProvider isValidMeaningProvider
     *
     * @param string|array|null $meaning
     * @param bool $expected
     */
    public function testIsValidMeaning(
        string|array|null $meaning,
        bool $expected,
    ): void {
        $mock = $this->getMockForTrait(MeaningTrait::class);
        $this->assertEquals($mock->isValidMeaning($meaning), $expected);
    }
}
