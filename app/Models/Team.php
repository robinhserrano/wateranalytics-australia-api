<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'team_manager_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the team manager.
     */
    public function teamManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_manager_id');
    }

    /**
     * Get all members of this team.
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }
}
