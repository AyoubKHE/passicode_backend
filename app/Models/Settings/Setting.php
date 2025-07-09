<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $table = "settings";

    public $timestamps = false;

    protected $fillable = [
        "id",
        "key",
        "value",
        "created_at",
        "updated_at"
    ];
}
