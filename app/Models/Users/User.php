<?php

namespace App\Models\Users;

use App\Models\Admins\Admin;
use App\Models\Clients\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        "id",
        "first_name",
        "last_name",
        "email",
        "password",
        "role",
        "is_active",
        "email_verification_token",
        "email_verification_token_sent_at",
        "password_reset_token",
        "password_reset_token_sent_at",
        "refresh_token",
        "last_login",
        "created_at",
        "updated_at",
    ];

    protected $hidden = [
        "password",
    ];

    public function admin()
    {
        return $this->hasOne(Admin::class, "user_id", "id");
    }

    public function client()
    {
        return $this->hasOne(Client::class, "user_id", "id");
    }

}
