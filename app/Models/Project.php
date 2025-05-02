<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'cadID', 'name', 'description', 'startDate', 'endDate', 'status'
    ];

    public function cadre()
    {
        return $this->belongsTo(Cadre::class, 'cadID');
    }
}
