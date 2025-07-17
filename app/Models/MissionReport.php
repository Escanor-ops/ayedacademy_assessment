<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionReport extends Model
{
    use HasFactory;

    protected $fillable = ['mission_request_id', 'user_id', 'content','type'];

    public function missionRequest()
    {
        return $this->belongsTo(MissionRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
