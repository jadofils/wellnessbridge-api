<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildHealthRecord extends Model
{
    use HasFactory;

    protected $table = 'child_health_records';

    protected $fillable = [
        'childID', 'healthWorkerID', 'checkupDate', 'height', 
        'weight', 'vaccination', 'diagnosis', 'treatment'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class, 'childID');
    }

    public function healthWorker()
    {
        return $this->belongsTo(HealthWorker::class, 'healthWorkerID');
    }
}
