<?php

namespace App\Providers;

use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject $notifications into the header partial on every page for authenticated reporters
        View::composer('spectral.partials.header', function ($view) {
            if (!Auth::check()) {
                $view->with('notifications', collect());
                return;
            }

            $userId = Auth::id();
            $readNotifs = session('read_notifications', []);

            $myIncidents = Incident::with(['investigations.investigator', 'evidence'])
                ->where(function ($query) use ($userId) {
                    $query->where('reported_by', $userId)
                          ->orWhereHas('evidence', function ($q) use ($userId) {
                              $q->where('uploaded_by', $userId);
                          });
                })
                ->orderByDesc('updated_at')
                ->get();

            $notifications = $myIncidents
                ->filter(fn($inc) => $inc->investigations->isNotEmpty())
                ->map(function ($inc) use ($readNotifs) {
                    $latest = $inc->investigations->sortByDesc('investigation_date')->first();
                    return [
                        'incident_code' => $inc->incident_code,
                        'title'         => $inc->title,
                        'status'        => $inc->status,
                        'severity'      => $inc->severity,
                        'investigator'  => $latest?->investigator?->name ?? 'System',
                        'notes'         => $latest?->notes,
                        'updated_at'    => $latest?->investigation_date ?? $inc->updated_at,
                        'incident_id'   => $inc->id,
                        'is_read'       => in_array($inc->id, $readNotifs),
                    ];
                })
                ->values()
                ->take(8);

            $unreadNotifCount = $notifications->where('is_read', false)->count();

            $view->with('notifications', $notifications)->with('unreadNotifCount', $unreadNotifCount);
        });
    }
}
