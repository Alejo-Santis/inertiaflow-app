<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $logs = Activity::with('causer', 'subject')
            ->when($request->input('log_name'), fn($q, $v) => $q->where('log_name', $v))
            ->when($request->input('event'),    fn($q, $v) => $q->where('event', $v))
            ->when($request->input('search'),   fn($q, $v) => $q->where('description', 'ilike', "%{$v}%"))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn($log) => [
                'id'          => $log->id,
                'log_name'    => $log->log_name,
                'description' => $log->description,
                'event'       => $log->event,
                'subject_type'=> $log->subject_type ? class_basename($log->subject_type) : null,
                'subject_id'  => $log->subject_id,
                'causer_name' => $log->causer?->name ?? 'Sistema',
                'causer_id'   => $log->causer_id,
                'properties'  => $log->properties,
                'created_at'  => $log->created_at->format('d/m/Y H:i:s'),
                'created_human' => $log->created_at->diffForHumans(),
            ]);

        return Inertia::render('AuditLog/Index', [
            'logs'     => $logs,
            'filters'  => $request->only(['log_name', 'event', 'search']),
        ]);
    }
}
