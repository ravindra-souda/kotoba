<?php

declare(strict_types=1);

namespace App\Tests;

// Refactor Expected and Inside types when PHPStan will support unsealed array shapes
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
 * @phpstan-type AdjectiveInsideDeckType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     jlpt: int,
 *     inflections: array<string,array<string,string>>,
 *     group: string,
 *     meaning: array<string,list<string>>,
 * }
 * @phpstan-type AdjectiveExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     jlpt: int,
 *     inflections: array<string,array<string,string>>,
 *     group: string,
 *     meaning: array<string,list<string>>,
 *     decks: list<DeckExpectedType>,
 * }
 * @phpstan-type DeckType array{
 *     title?: string,
 *     description?: string,
 *     type?: string,
 *     color?: string,
 * }
 * @phpstan-type DeckInsideCardType array{
 *     '@id': string,
 *     '@context'?: string,
 *     title: string,
 *     description: string,
 *     type: string,
 *     color: string,
 * }
 * @phpstan-type DeckExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     title: string,
 *     description: string,
 *     type: string,
 *     color: string,
 *     cards: list<CardInsideDeckType>,
 * }
 * @phpstan-type KanaType array{
 *     hiragana?: string,
 *     katakana?: string,
 *     romaji?: string,
 * }
 * @phpstan-type KanaInsideDeckType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     romaji: string,
 * }
 * @phpstan-type KanaExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     romaji: string,
 *     decks: list<DeckExpectedType>,
 * }
 * @phpstan-type KanjiType array{
 *     kanji?: string,
 *     meaning?: array<string,list<string>>,
 *     kunyomi?: list<string>,
 *     onyomi?: list<string>,
 * }
 * @phpstan-type KanjiInsideDeckType array{
 *     '@id': string,
 *     '@context'?: string,
 *     kanji: string,
 *     meaning: array<string,list<string>>,
 *     kunyomi: list<string>,
 *     onyomi: list<string>,
 * }
 * @phpstan-type KanjiExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     kanji: string,
 *     meaning: array<string,list<string>>,
 *     kunyomi: list<string>,
 *     onyomi: list<string>,
 *     decks: list<DeckExpectedType>,
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
 * @phpstan-type NounInsideDeckType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     bikago: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 * }
 * @phpstan-type NounExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     bikago: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 *     decks: list<DeckExpectedType>,
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
 * @phpstan-type VerbInsideDeckType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     group: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 *     inflections: array<string,string|array<string,mixed>>,
 * }
 * @phpstan-type VerbExpectedType array{
 *     '@id': string,
 *     '@context'?: string,
 *     hiragana: string,
 *     katakana: string,
 *     kanji: string,
 *     romaji: string,
 *     group: string,
 *     jlpt: int,
 *     meaning: array<string,list<string>>,
 *     inflections: array<string,string|array<string,mixed>>,
 *     decks: list<DeckExpectedType>,
 * }
 * @phpstan-type CardExpectedType = AdjectiveExpectedType|KanaExpectedType|KanjiExpectedType|NounExpectedType|VerbExpectedType
 * @phpstan-type CardInsideDeckType = AdjectiveInsideDeckType|KanaInsideDeckType|KanjiInsideDeckType|NounInsideDeckType|VerbInsideDeckType
 */
class Types {}
