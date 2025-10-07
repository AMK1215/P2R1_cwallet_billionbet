<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DeadlockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'process_id',
        'database_name',
        'table_name',
        'lock_type',
        'query_text',
        'deadlock_details',
        'involved_queries',
        'lock_waits',
        'error_code',
        'error_message',
        'severity',
        'user_name',
        'application_name',
        'client_addr',
        'duration_ms',
        'stack_trace',
        'resolved_by',
        'resolved_at',
        'is_resolved',
    ];

    protected $casts = [
        'involved_queries' => 'array',
        'lock_waits' => 'array',
        'stack_trace' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Scope for unresolved deadlocks
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved deadlocks
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope for recent deadlocks (last 24 hours)
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for today's deadlocks
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week's deadlocks
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's deadlocks
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Get deadlock statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'today' => self::today()->count(),
            'this_week' => self::thisWeek()->count(),
            'this_month' => self::thisMonth()->count(),
            'unresolved' => self::unresolved()->count(),
            'resolved' => self::resolved()->count(),
            'avg_duration' => self::whereNotNull('duration_ms')->avg('duration_ms'),
            'max_duration' => self::whereNotNull('duration_ms')->max('duration_ms'),
            'by_table' => self::selectRaw('table_name, COUNT(*) as count')
                ->whereNotNull('table_name')
                ->groupBy('table_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'by_error_code' => self::selectRaw('error_code, COUNT(*) as count')
                ->whereNotNull('error_code')
                ->groupBy('error_code')
                ->orderBy('count', 'desc')
                ->get(),
            'by_hour' => self::selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
        ];
    }

    /**
     * Get deadlock trends (daily for last 30 days)
     */
    public static function getTrends(int $days = 30): array
    {
        return self::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Mark deadlock as resolved
     */
    public function markAsResolved(string $resolvedBy = 'manual'): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_ms) {
            return 'N/A';
        }

        if ($this->duration_ms < 1000) {
            return $this->duration_ms . 'ms';
        }

        return round($this->duration_ms / 1000, 2) . 's';
    }

    /**
     * Get severity badge class
     */
    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->severity) {
            'ERROR' => 'badge-danger',
            'WARNING' => 'badge-warning',
            'INFO' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_resolved ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_resolved ? 'Resolved' : 'Unresolved';
    }
}
