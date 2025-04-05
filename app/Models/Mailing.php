<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property int $status
 * @property string $total_recipients
 * @property string $sent_count
 * @property string $scheduled_at
 */
class Mailing extends Model
{
    use HasFactory;

    const DAYS_BEFORE_BIRTHDAY = 7;
    const SEND_TIME = '10:30';

    protected $fillable = [
        'id',
        'name',
        'content',
        'status',
        'total_recipients',
        'sent_count',
        'scheduled_at'
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'scheduled_at' => 'datetime'
    ];

    /**
     * @uses self::scopeDateFrom()
     * @uses self::scopeDateTo()
     * @uses self::scopeLastDays()
     * @uses self::scopeLastMonth()
     * @uses self::scopeLastYear()
     */
    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::scope('date_from'),
            AllowedFilter::scope('date_to'),
            AllowedFilter::scope('last_days'),
            AllowedFilter::scope('last_month'),
            AllowedFilter::scope('last_year'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            'name',
            'status',
        ];
    }

    public function scopeDateFrom(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeDateTo(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    public function scopeLastDays(Builder $query, int $days): Builder
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');
        return $query->whereDate('scheduled_at', '>=', $date);
    }

    public function scopeLastMonth(Builder $query): Builder
    {
        $date = Carbon::now()->subMonth()->format('Y-m-d');
        return $query->whereDate('scheduled_at', '>=', $date);
    }

    public function scopeLastYear(Builder $query): Builder
    {
        $date = Carbon::now()->subYear()->format('Y-m-d');
        return $query->whereDate('scheduled_at', '>=', $date);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'mailing_recipients')
            ->withPivot('status', 'sent_at')
            ->withTimestamps();
    }
}
