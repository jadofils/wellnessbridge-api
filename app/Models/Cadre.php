<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cadre extends Model
{
    use HasFactory;

    protected $table = 'cadres';
    protected $primaryKey = 'cadID';

    protected $fillable = [
        'name', 'description', 'qualification'
    ];

    public function healthWorkers()
    {
        return $this->hasMany(HealthWorker::class, 'cadID');
    }
}
