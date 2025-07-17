<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Include this to support roles like 'super_manager'
        'status',
        'department_id',
        'manager_id',
        'position',
        'verification_code',
        'verification_code_expires_at',
        'assigned_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: A user belongs to a department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: A user may have a manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relationship: A manager has many employees.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedEmployees()
    {
        return $this->hasMany(User::class, 'assigned_by');
    }

    public function employeeEvaluations()
    {
        return $this->hasMany(EmployeeEvaluation::class, 'employee_id');
    }

    public function standards()
    {
        return $this->hasMany(Standard::class, 'employee_id');
    }
}
