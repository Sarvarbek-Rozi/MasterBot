<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function subject() {
        return $this->belongsTo('App\Models\Subject', 'subject_id');
    }
}
