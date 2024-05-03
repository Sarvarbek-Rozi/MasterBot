<?php


namespace App\Services;


use App\Models\Subject;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class SubjectService
{
    public function create($name) {
        return Subject::create([
            'name' => $name
        ]);
    }
    public function all() {
        return Subject::all();
    }

    public function subjectId($id){
        return Subject::where('id' , $id)->first();
    }

}
