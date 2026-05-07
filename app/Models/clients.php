<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class clients extends Model
{
    use Notifiable;

    protected $fillable = [
        'user_id',
        'company_name',
        'email',
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
