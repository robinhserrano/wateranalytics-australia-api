<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'odoo_user_ids' => 'array',
    ];

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The tags that belong to the contact.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Refresh aggregated/denormalized columns based on relations.
     */
    public function refreshDenormalizedData()
    {
        // Placeholder for future logic (e.g., total sales, last interaction date)
        // For now, this just ensures the method exists for the sync commands.
        return $this;
    }
}
