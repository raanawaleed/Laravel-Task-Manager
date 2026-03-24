<?php

namespace App\Http\Controllers;

use App\Facades\ProjectFacade;
use App\Facades\TaskFacade;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $tasks    = TaskFacade::getTasks();
            $projects = ProjectFacade::getAllProjects();

            return view('dashboard', compact('tasks', 'projects'));
        } catch (\Throwable $e) {
            Log::error('DashboardController::index failed', ['error' => $e->getMessage()]);

            return view('dashboard', ['tasks' => collect(), 'projects' => collect()])
                ->withErrors(['error' => 'Unable to load dashboard data.']);
        }
    }
}
