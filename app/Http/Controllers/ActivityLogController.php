<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogDataTable;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(ActivityLogDataTable $dataTable)
    {
        $users = User::select('id', 'name')->get();
        $modules = Activity::select('log_name')->distinct()->pluck('log_name');

        $subjectTypes = Activity::select('subject_type')->whereNotNull('subject_type')->distinct()->pluck('subject_type');

        return $dataTable->render('backend.activity_log.index', compact('users', 'modules', 'subjectTypes'));
    }

    public function show($id)
    {
        if (request()->ajax()) {
            $activity = Activity::with('causer')->findOrFail($id);

            return view('backend.activity_log.ajax.show', compact('activity'));
        }

        return abort(404);
    }
}
