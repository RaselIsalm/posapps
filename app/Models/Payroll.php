<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends BaseModel
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'employee_id',
        'salary_month',
        'salary_year',
        'basic_salary',
        'total_allowance',
        'total_deduction',
        'net_salary',
        'payment_method',
        'transaction_id',
        'payment_date',
        'status',
        'note',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
