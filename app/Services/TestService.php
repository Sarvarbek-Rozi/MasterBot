<?php


namespace App\Services;


use App\Models\Subject;
use App\Models\Test;
use App\Models\TestForm;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TestService
{
    public function create($name) {
        $subject = Subject::where('name', $name)->first();

        TestForm::create(['subject_id' => $subject->id]);
    }
    public function deleteLast() {
        $test_form = TestForm::query()->orderBy('created_at', 'desc')->first();
        if($test_form) {
            $test_form->delete();
        }
    }

    public function update($field, $value) {
        $test_form = TestForm::query()->orderBy('created_at', 'desc')->first();
        if($field != 'date_stop') {
            Log::info($value);
            $test_form->update([$field => $value]);
        }
        if($field == 'date_stop') {
            $test_form->update([$field => Carbon::parse($value)->format('Y-m-d')]);
            $test_form->update(['date_start' => Carbon::now()->format('Y-m-d') ]);
        }
        $test_form->subject;
        Log::info($test_form);
        return $test_form;
    }

    public function storeFile($photo)
    {
        $id = !is_array($photo) ? $photo['file_id'] : last($photo)['file_id'];
        $test_form = TestForm::query()->orderBy('created_at', 'desc')->first();
        if($test_form->file_path) {
            $test_form->update(['file_path' =>  $test_form->file_path . ','.$id ]);
        } else {
            $test_form->update(['file_path' => $id ]);
        }

        $test_form->subject;
        return $test_form;
    }

    protected function downloadFileTelegram($route = '', $params = [], $method = 'GET')
    {
        $client = new Client(['base_uri' => 'https://api.telegram.org/file/bot' . Telegram::getAccessToken() . '/']);
        $result = $client->request($method, $route, $params);

        return (string) $result->getBody();
    }

    protected function sendTelegramData($route = '', $params = [], $method = 'POST')
    {
        $client = new Client(['base_uri' => 'https://api.telegram.org/bot' . Telegram::getAccessToken() . '/']);
        $result = $client->request($method, $route, $params);

        return (string) $result->getBody();
    }

    public function createTrue() {
        $test_form = $this->getLastTestForm();
           $test = Test::create([
            'subject_id' => $test_form->subject_id,
            'name'=> $test_form->name,
            'date_start' => $test_form->date_start,
            'date_stop' => $test_form->date_stop,
            'answers' => $test_form->answers,
            'file_path' => $test_form->file_path,
            ]);
           $test->subject;
           return $test;
    }
    public function getLastTestForm() {
      return TestForm::query()->orderBy('created_at', 'desc')->first();
    }

    public function all() {
        return Test::all();
    }
    public function findByField($filed, $value) {
        return Test::where($filed, $value)->first();
    }

}
