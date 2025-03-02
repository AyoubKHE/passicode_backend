<?php

namespace App\Models\Clients;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        "id",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
}
