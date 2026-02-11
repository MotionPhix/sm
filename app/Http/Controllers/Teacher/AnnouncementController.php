<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('teacher/announcements/Index', [
            'school' => [
                'name' => $school->name,
            ],
        ]);
    }
}
