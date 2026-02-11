<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/billing/Index', [
            'school' => [
                'name' => $school->name,
            ],
        ]);
    }
}
