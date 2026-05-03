<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'domain',
        'ip_address',
        'stack',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(clients::class, 'client_id');
    }
}
