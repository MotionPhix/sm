<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $user = $request->user();

        return [
            ...parent::share($request),
            
            'name' => config('app.name'),

            'quote' => ['message' => trim($message), 'author' => trim($author)],

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,

                    'role' => $user?->roleForActiveSchool()?->name,
                    'role_label' => $user?->roleForActiveSchool()?->label,

                    'permissions' => $user
                        ? $user->permissionsForActiveSchool()->pluck('name')
                        : [],

                    'activeSchool' => $user->activeSchool ? [
                        'id' => $user->activeSchool->id,
                        'name' => $user->activeSchool->name,
                    ] : null,

                    'schools' => $user->schools->map(fn ($school) => [
                        'id' => $school->id,
                        'name' => $school->name,
                        'role' => $school->pivot->role_id,
                    ]),
                ] : null,
            ],

            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

        ];
    }
}
