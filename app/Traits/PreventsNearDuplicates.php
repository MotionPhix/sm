<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

trait PreventsNearDuplicates
{
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
     * Check for exact duplicate and return error message if found.
     * Requires user to bypass with password confirmation.
     */
    protected function checkForDuplicates(string $modelClass, string $name, int $schoolId, string $fieldLabel): ?string
    {
        // Only check for exact duplicates (case-insensitive)
        if ($this->hasExactDuplicate($modelClass, $name, $schoolId)) {
            return "{$fieldLabel} '{$name}' already exists. Enter your password to confirm you want to add it anyway.";
        }

        return null;
    }

    /**
     * Verify password for bypass confirmation.
     */
    protected function verifyBypassPassword(?string $password): bool
    {
        if (!$password) {
            return false;
        }

        return Hash::check($password, auth()->user()->password);
    }
}
