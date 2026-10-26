<?php

namespace App\Support;

use App\Exceptions\Domain\ValidationException;

/**
 * Validation for the stage dependency graph ("Stage X setelah Stage Y").
 *
 * Links are [stageId => dependsOnStageId|null]. Both self-references and
 * longer cycles are rejected; DFS is iterative to avoid recursion limits.
 */
class StageDependencies
{
    public static function assertAcyclic(array $links): void
    {
        foreach ($links as $stageId => $dependsOn) {
            if ($dependsOn === null) {
                continue;
            }

            if ((int) $dependsOn === (int) $stageId) {
                throw new ValidationException(
                    'Stage tidak dapat bergantung pada dirinya sendiri.',
                    ['depends_on_project_stage_id' => ["Stage id {$stageId} bergantung pada dirinya sendiri."]]
                );
            }
        }

        $visited = [];
        $inPath = [];

        foreach (array_keys($links) as $start) {
            if (isset($visited[$start])) {
                continue;
            }

            $stack = [[$start, false]];

            while ($stack) {
                [$node, $processed] = array_pop($stack);

                if ($processed) {
                    unset($inPath[$node]);

                    continue;
                }

                if (isset($visited[$node])) {
                    continue;
                }

                $visited[$node] = true;
                $inPath[$node] = true;
                $stack[] = [$node, true];

                $next = $links[$node] ?? null;
                if ($next !== null) {
                    if (isset($inPath[$next])) {
                        throw new ValidationException(
                            'Dependency antar stage membentuk siklus.',
                            ['depends_on_project_stage_id' => ['Dependency antar stage membentuk siklus.']]
                        );
                    }
                    if (! isset($visited[$next])) {
                        $stack[] = [$next, false];
                    }
                }
            }
        }
    }
}
