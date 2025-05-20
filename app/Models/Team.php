<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'slide_viewer_url',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'subscription_id',
        'subscription_status',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the owner of the organisation.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users that belong to the organisation.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Determine if the given user belongs to the organisation.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the given user is the owner of the organisation.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Determine if the team has a Stripe customer ID.
     */
    public function hasStripeId(): bool
    {
        return !is_null($this->stripe_id);
    }

    /**
     * Determine if the team has a subscription.
     */
    public function hasSubscription(): bool
    {
        return !is_null($this->subscription_id) && $this->subscription_status !== 'canceled';
    }

    /**
     * Determine if the team is on a trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the team has an active subscription.
     */
    public function subscribed(): bool
    {
        return $this->hasSubscription() && $this->subscription_status === 'active';
    }

    /**
     * Get the subscription status in a human-readable format.
     */
    public function getSubscriptionStatusAttribute($value): string
    {
        if (is_null($value)) {
            return 'None';
        }

        return match ($value) {
            'active' => 'Active',
            'canceled' => 'Canceled',
            'incomplete' => 'Incomplete',
            'incomplete_expired' => 'Expired',
            'past_due' => 'Past Due',
            'trialing' => 'Trial',
            'unpaid' => 'Unpaid',
            default => ucfirst($value),
        };
    }
}
