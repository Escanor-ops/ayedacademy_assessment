<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type','description', 'employee_id','status'];

    /**
     * Get the employee evaluation details associated with the standard.
     */
    public function employeeEvaluationDetails()
    {
        return $this->hasMany(EmployeeEvaluationDetail::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
