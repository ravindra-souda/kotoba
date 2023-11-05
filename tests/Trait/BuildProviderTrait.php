<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\AbstractKotobaDocument as Doc;
use App\Tests\Types;

/**
 * @phpstan-import-type DeckType from Types
 *
 * @phpstan-type Test array{
 *      payload: DeckType,
 *      message: string|array{text:string,values:string|int|array<string>},
 *      maxlength?: array<string,string>,
 * }
 */
trait BuildProviderTrait
{
    /**
     * @param array<string,Test> $tests
     *
     * @return array<string,array<DeckType|string>>
     */
    final protected function buildPostProvider(array $tests): array
    {
        $provider = [];

        foreach ($tests as $key => $test) {
            ['payload' => $payload, 'message' => $message] = $test;

            $payload = $this->generateMaxlengthValues($test, $payload);
            $message = $this->generateMessage($message);

            $provider[$key] = [$payload, $message];
        }

        return $provider;
    }

    /**
     * @param Test     $test
     * @param DeckType $payload
     *
     * @return DeckType
     */
    private function generateMaxlengthValues(
        array $test,
        array $payload
    ): array {
        if (!isset($test['maxlength']) || !isset($test['message']['values'])) {
            return $payload;
        }
        if (is_numeric($test['message']['values'])) {
            $maxlength = $test['maxlength'];
            $prop = array_key_first($maxlength);

            /** @var DeckType $payload */
            $payload[$prop] =
                str_repeat(
                    $maxlength[$prop],
                    (int) $test['message']['values'] + 1
                );
        }

        return $payload;
    }

    /**
     * @param array<string,array<string>|int|string>|string $message
     */
    private function generateMessage(array|string $message): string
    {
        if (is_array($message)) {
            /** @var string $text */
            ['text' => $text, 'values' => $values] = $message;

            $message = Doc::formatMsg($text, $values);
        }

        return $message;
    }
}
