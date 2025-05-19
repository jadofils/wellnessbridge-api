<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRestriction extends Model
{
    use HasFactory;

    protected $table = 'health_restrictions';
    protected $primaryKey = 'hrID';

    protected $fillable = [
        'recordID', 'description', 'severity'
    ];

    public function childHealthRecord()
    {
        return $this->belongsTo(ChildHealthRecord::class, 'recordID');
    }
}
