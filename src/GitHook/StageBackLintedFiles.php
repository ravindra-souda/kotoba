<?php

declare(strict_types=1);

namespace App\GitHook;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use CaptainHook\App\Hook\Constrained;
use CaptainHook\App\Hook\Restriction;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Git\Repository;

class StageBackLintedFiles implements Action, Constrained
{
    /**
     * Return the hook restriction information.
     */
    public static function getRestriction(): Restriction
    {
        return Restriction::fromArray([Hooks::PRE_COMMIT]);
    }

    /**
     * Execute the action.
     *
     * @param \CaptainHook\App\Config\Action $action
     *
     * @throws \Exception
     */
    public function execute(
        Config $config,
        IO $io,
        Repository $repository,
        Config\Action $action
    ): void {
        $files = $repository->getIndexOperator()->getStagedFiles();
        if (!$repository->getIndexOperator()->updateIndex($files)) {
            throw new \Exception('Error when staging back linted files');
        }
    }
}
