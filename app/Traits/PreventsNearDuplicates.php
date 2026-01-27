<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait PreventsNearDuplicates
{
    /**
     * Find a similar name using Levenshtein distance.
     * Threshold of 2 means up to 2 character differences are considered duplicates.
     */
    protected function findSimilarName(string $modelClass, string $name, int $schoolId): ?object
    {
        $candidates = $modelClass::where('school_id', $schoolId)
            ->pluck('name', 'id')
            ->all();

        foreach ($candidates as $id => $candidateName) {
            $distance = levenshtein(Str::lower($name), Str::lower($candidateName));
            if ($distance <= 2) {
                return (object) ['id' => $id, 'name' => $candidateName];
            }
        }

        return null;
    }

    /**
     * Check for exact duplicate (case-insensitive).
     */
    protected function hasExactDuplicate(string $modelClass, string $name, int $schoolId): bool
    {
        return $modelClass::where('school_id', $schoolId)
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();
    }

    /**
     * Check for similar duplicate and return error message if found.
     */
    protected function checkForDuplicates(string $modelClass, string $name, int $schoolId, string $fieldLabel): ?string
    {
        // Check for exact duplicates first
        if ($this->hasExactDuplicate($modelClass, $name, $schoolId)) {
            return "{$fieldLabel} '{$name}' already exists.";
        }

        // Check for similar names using Levenshtein distance
        $similar = $this->findSimilarName($modelClass, $name, $schoolId);
        if ($similar) {
            return "{$fieldLabel} '{$similar->name}' is very similar to '{$name}'. Please use a different name.";
        }

        return null;
    }
}
