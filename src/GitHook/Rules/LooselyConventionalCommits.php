<?php

namespace App\GitHook\Rules;

use CaptainHook\App\Hook\Message\Rule;
use SebastianFeldmann\Git\CommitMessage;

class LooselyConventionalCommits implements Rule
{
    private const TYPES = [
        'build',
        'ci',
        'docs',
        'feat',
        'fix',
        'perf',
        'refactor',
        'test',
    ];

    /**
     * Return a hint how to pass the rule.
     */
    public function getHint(): string
    {
        return 'Commit message has to follow Conventional Commits guidelines: 
            https://www.conventionalcommits.org/en/v1.0.0/';
    }

    /**
     * Follows loosely Conventional Commits guidelines.
     * https://www.conventionalcommits.org/en/v1.0.0/.
     */
    public function pass(CommitMessage $message): bool
    {
        /*
            type(scope): subject
            scope is optional
            subject with first letter not capitalized and no final dot
        */
        $re =
            '/('.
            implode('|', self::TYPES).
            ')(\([a-z]+\))?: [a-z0-9].*[^.]$/m';

        return preg_match($re, $message->getSubject());
    }
}
