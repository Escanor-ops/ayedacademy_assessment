<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'overall_rating',
        'department_id',
        'assigned_by',
        'status',
        'notices',
        'recommendations'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function employeeEvaluationDetails()
    {
        return $this->hasMany(EmployeeEvaluationDetail::class);
    }
}
