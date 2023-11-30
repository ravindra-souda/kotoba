<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Document\AbstractKotobaDocument as Doc;

trait BuildProviderTrait
{
    /**
     * @param array<string,array<string,array<string,mixed>>> $tests
     *
     * @return array<array<array<string>>>
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
     * @param array<string,array<string,array<string,mixed>>> $tests
     * @param array<string,array<string,mixed>>               $fixtures
     *
     * @return array<array<array<string>>>
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
            $payload = array_merge($fixture, $payload);

            $payload = $this->generateMaxlengthValues($test, $payload);
            $message = $this->generateMessage($message);

            $provider[$key] = [$fixture, $payload, $message];
        }

        return $provider;
    }

    /**
     * @param array<string,mixed> $test
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    private function generateMaxlengthValues(
        array $test,
        array $payload
    ): array {
        if (isset($test['maxlength'])) {
            $maxlength = $test['maxlength'];
            $prop = array_key_first($maxlength);
            $payload[$prop] =
                str_repeat($maxlength[$prop], $test['message']['values'] + 1);
        }

        return $payload;
    }

    /**
     * @param array<string,string>|string $message
     */
    private function generateMessage(array|string $message): string
    {
        if (is_array($message)) {
            ['text' => $text, 'values' => $values] = $message;
            $message = Doc::formatMsg($text, $values);
        }

        return $message;
    }
}
