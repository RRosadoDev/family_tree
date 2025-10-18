<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model {
    protected $table = 'people';

    protected $fillable = [
        'name',
        'father_id',
        'is_active'
    ];

    protected $casts = [
        'name' => 'string',
        'is_active' => 'boolean',
    ];

    public function father() {
        return $this->belongsTo(Person::class, 'father_id');
    }

    public function children() {
        return $this->hasMany(Person::class, 'father_id');
    }
}
