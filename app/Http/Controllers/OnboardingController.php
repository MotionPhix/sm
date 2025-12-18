<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function start()
    {
        return Inertia::render('onboarding/Start');
    }
}
