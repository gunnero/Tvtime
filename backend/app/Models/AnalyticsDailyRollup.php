<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyRollup extends Model
{
    protected $fillable = [
        'date',
        'metric_name',
        'dimensions',
        'dimensions_hash',
        'integer_value',
        'decimal_value',
    ];

    public function scopeMetric(Builder $query, string $metricName): Builder
    {
        return $query->where('metric_name', $metricName);
    }

    public function scopeForDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'dimensions' => 'array',
            'integer_value' => 'integer',
            'decimal_value' => 'decimal:2',
        ];
    }
}
