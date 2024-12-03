<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * @phpstan-type AdjectiveType array{
 *     hiragana?: string,
 *     katakana?: string,
 *     kanji?: string,
 *     romaji?: string,
 *     jlpt?: int,
 *     inflections?: array<string,array<string,string>>,
 *     group?: string,
 *     meaning?: array<string,list<string>>,
 * }
 * @phpstan-type AdjectiveExpectedType array{
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     jlpt: int,
 *     inflections: array<string,array<string,string>>,
 *     group: string,
 *     meaning: array<string,list<string>>,
 * }
 * @phpstan-type DeckType array{
 *     title?: string,
 *     description?: string,
 *     type?: string,
 *     color?: string,
 * }
 * @phpstan-type KanaType array{
 *     hiragana?: string,
 *     katakana?: string,
 *     romaji?: string,
 * }
 * @phpstan-type KanaExpectedType array{
 *     hiragana: string,
 *     katakana: string,
 *     romaji: string,
 * }
 * @phpstan-type KanjiType array{
 *     kanji?: string,
 *     meaning?: array<string,list<string>>,
 *     kunyomi?: list<string>,
 *     onyomi?: list<string>,
 * }
 * @phpstan-type NounType array{
 *     hiragana?: string|null,
 *     katakana?: string|null,
 *     kanji?: string,
 *     romaji?: string,
 *     bikago?: string,
 *     jlpt?: int,
 *     meaning?: array<string,list<string>>,
 * }
 * @phpstan-type NounExpectedType array{
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     bikago: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 * }
 * @phpstan-type VerbType array{
 *     hiragana?: string,
 *     katakana?: string,
 *     kanji?: string,
 *     romaji?: string,
 *     group?: string,
 *     jlpt?: int,
 *     meaning?: array<string,list<string>>,
 *     inflections?: array<string,string|array<string,mixed>>,
 * }
 * @phpstan-type VerbExpectedType array{
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     group: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 *     inflections: array<string,string|array<string,mixed>>,
 * }
 */
class Types {}
