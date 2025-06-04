<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'search_term',
        'results_count',
        'success',
        'search_results',
    ];

    protected $casts = [
        'search_results' => 'array',
        'success' => 'boolean',
    ];

    /**
     * Get the user that owns the search history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the search history.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
