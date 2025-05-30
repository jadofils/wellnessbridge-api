<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthWorker extends Model
{
    use HasFactory;

    protected $table = 'health_workers';
    protected $primaryKey = 'hwID'; // Use hwID instead of id
    //role as enums
    
    protected $fillable = ['name', 'gender', 'dob', 'role', 'telephone', 'email', 'image', 'address', 'cadID'];

// Set default values for attributes
    protected $attributes = [
        'dob' => null,
        'telephone' => null,
        'image' => null,
        'address' => null,
    ];

    public function cadre()
    {
        return $this->belongsTo(Cadre::class, 'cadID', 'cadID');
        }
}
