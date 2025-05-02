<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAssignment extends Model
{
    use HasFactory;

    protected $table = 'project_assignments';

    protected $fillable = [
        'hwID', 'prjID', 'assignedDate', 'endDate', 'role'
    ];

    public function healthWorker()
    {
        return $this->belongsTo(HealthWorker::class, 'hwID');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'prjID');
    }
}
