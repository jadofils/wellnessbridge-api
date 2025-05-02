<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $table = 'children';

    protected $fillable = [
        'name', 'gender', 'dob', 'image', 'address',
        'parentName', 'parentContact'
    ];

    public function birthProperty()
    {
        return $this->hasOne(BirthProperty::class, 'childID');
    }

    public function childHealthRecords()
    {
        return $this->hasMany(ChildHealthRecord::class, 'childID');
    }
}
