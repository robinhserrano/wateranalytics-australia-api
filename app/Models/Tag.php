<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'odoo_id',
        'name',
        'color',
    ];

    /**
     * The contacts that belong to the tag.
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}
