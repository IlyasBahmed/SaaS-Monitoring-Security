<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class clients extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'phone',
        'address',
        'status', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Projects::class, 'client_id');
    }
}
