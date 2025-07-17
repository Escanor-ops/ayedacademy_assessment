<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function managers(): HasMany
    {
        return $this->hasMany(DepartmentManager::class);
    }
    public function currentManager()
{
    return $this->hasOne(User::class)
        ->where('role', 'department_manager')
        ->where('status', 1);
}
public function missions()
{
    return $this->hasMany(MissionRequest::class);
}
public function missionTypes()
{
    return $this->hasMany(MissionType::class);
}
}
