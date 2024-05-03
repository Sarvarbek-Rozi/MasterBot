<?php


namespace App\Services;


use App\Models\Subject;
use App\Models\User;
use App\Models\Exam;
use App\Models\Test;
use App\Models\TestForm;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class ExamsService
{  
    public function create($user, $testName) {
        $test = Test::where('name', $testName)->first();

        Exam::create(['user_id' => $user->id,
                    'test_id' => $test->id,
        ]);
    }
    
    
    public function writeAnswer($user, $answer){

        $test_form = Exam::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        $answers = $test_form->answers ? $test_form->answers : null ;
        $value =$answers;
        $value .= $answer;
        // Log::info($answers);

        $test_form->update(['answers' => $value,]);

    }

    public function all($user) {
        return Exam::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    }

// <<<<<<< HEAD
    public function resultTest() {
        $exam_form = Exam::query()->orderBy('created_at', 'desc')->first();
    }
    public function ruseultTest($user) {
        $exam_form = Exam::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
// >>>>>>> ad46fd2777bc77757fc85202ca0bd4082d434601
        $test_form = Test::where('id', $exam_form->test_id )->first();
        
        $exam_answers = $exam_form->answers;
        // $test_answers = $test_form->answers;
        $test_answers = explode(",",$test_form->answers);
        $countTestAnswers = count($test_answers);
        $resultTest=0;
        for ( $i=0; $i<$countTestAnswers; $i++ ) {
            if ($test_answers[$i] == $exam_answers[$i]) {
               $resultTest++;    
            }
        }

        return $resultTest;
        Log::info("resultTest=".$resultTest);

    }
    
    public function TestAnswers($user){
        $exam_form = Exam::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
        $test_form = Test::where('id', $exam_form->test_id )->first();
        $test_answers = explode(",",$test_form->answers);
        return $test_answers;
    }



}