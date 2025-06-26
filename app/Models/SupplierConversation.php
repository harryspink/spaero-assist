<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'search_history_id',
        'part_number',
        'supplier_name',
        'supplier_email',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function searchHistory(): BelongsTo
    {
        return $this->belongsTo(SearchHistory::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupplierMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(SupplierMessage::class, 'conversation_id')->latest();
    }

    public function unreadMessages(): HasMany
    {
        return $this->hasMany(SupplierMessage::class, 'conversation_id')
            ->where('direction', 'received')
            ->where('is_read', false);
    }

    public function getUnreadCountAttribute(): int
    {
        return $this->unreadMessages()->count();
    }
}
