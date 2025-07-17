<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionRequest extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'mission_type_id', 'user_id', 'description', 'status','ticket_number','link','deadline'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function missionType()
    {
        return $this->belongsTo(MissionType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(MissionReport::class);
    }

    public function files()
    {
        return $this->hasMany(MissionFile::class, 'mission_id');
    }
}
