<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\AbstractKotobaDocument as Doc;
use App\Tests\Types;

/**
 * @phpstan-import-type AdjectiveType from Types
 * @phpstan-import-type DeckType from Types
 *
 * @phpstan-type Message string|array{
 *      text:string,
 *      values:string|int|array<string>
 * }
 * @phpstan-type PostTest array{
 *      payload: AdjectiveType|DeckType,
 *      message: Message,
 *      maxlength?: array<string,string>,
 * }
 * @phpstan-type PutTest array{
 *      fixture: string,
 *      payload?: AdjectiveType|DeckType,
 *      message: Message,
 *      maxlength?: array<string,string>,
 * }
 */
trait BuildProviderTrait
{
    /**
     * @param array<string,PostTest> $tests
     *
     * @return array<string,array<AdjectiveType|DeckType|string>>
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
     * @param array<string,PutTest>                $tests
     * @param array<string,AdjectiveType|DeckType> $fixtures
     *
     * @return array<string,array<AdjectiveType|DeckType|string>>
     */
    final protected function buildPutProvider(
        array $tests,
        array $fixtures
    ): array {
        $provider = [];

        foreach ($tests as $key => $test) {
            ['fixture' => $fixture_key, 'message' => $message] = $test;

            /** @var string $fixture_key */
            $fixture = $fixtures[$fixture_key];

            $payload = $test['payload'] ?? [];

            /** @var AdjectiveType|DeckType $payload */
            $payload = array_merge($fixture, $payload);

            $payload = $this->generateMaxlengthValues($test, $payload);
            $message = $this->generateMessage($message);

            $provider[$key] = [$fixture, $payload, $message];
        }

        return $provider;
    }

    /**
     * @param PostTest|PutTest       $test
     * @param AdjectiveType|DeckType $payload
     *
     * @return AdjectiveType|DeckType
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

            /** @var AdjectiveType|DeckType $payload */
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
