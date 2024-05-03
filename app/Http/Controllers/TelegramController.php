<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminTelegramService;
use App\Services\GuestTelegramService;
use App\Services\ExaminerTelegramService;
use App\Services\TelegramUserService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Methods\Chat;

class TelegramController extends Controller
{
    protected $ng_domain;
    protected $access_token;
    protected $userService;
    protected $adminService;
    protected $guestService;
    protected $examinerService;
    public function __construct(TelegramUserService $userService, AdminTelegramService $adminService,GuestTelegramService $guestService, ExaminerTelegramService $examinerService)
    {
        $this->ng_domain = config('telegram.ng_domain', null);
        $this->access_token = Telegram::getAccessToken();
        $this->userService = $userService;
        $this->adminService = $adminService;
        $this->guestService = $guestService;
        $this->examinerService = $examinerService;
    }

    protected function setWebhook(Request $request)
    {
        return  $this->sendTelegramData('setwebhook', [
            'query' => [ 'url' => $this->ng_domain . '/' . $this->access_token ]
        ]);
    }
    protected function getWebhook(Request $request)
    {
        return Telegram::getWebhookInfo();
    }

    protected function sendTelegramData($route = '', $params = [], $method = 'POST')
    {
        $client = new Client(['base_uri' => 'https://api.telegram.org/bot' . $this->access_token . '/']);
        $result = $client->request($method, $route, $params);

        return (string) $result->getBody();
    }
    protected function action(Request $request)
    {
        Log::info($request);

//        $file_id = $request['message']['document']['file_id'];
//        $response = $this->sendTelegramData('getFile', [
//            'query' => ['file_id' => $file_id]
//        ]);
//
//        $response = json_decode($response, true);
//        $file_path = $response['result']['file_path'];
//        $file = $this->downloadFileTelegram($file_path);
//
//        Storage::put( 'public/assets/' . $request['message']['document']['file_name'], $file); //return true
//        Telegram::sendDocument(['chat_id' => $request['message']['chat']['id'], 'caption' => 'salom zohid', 'document' => InputFile::create(storage_path('app/public/assets/'). $request['message']['document']['file_name'])]);
//       $response = Telegram::sendMessage(['chat_id' => $request['message']['chat']['id'], 'parse_mode' => 'html', 'text' => "<b>Zohid</b>\n\n\n👇  men sizlarga tushuntiraman" ]);
//       $messageId =  $response->getMessageId();
//        Telegram::editMessageText(['chat_id' => $request['message']['chat']['id'], 'parse_mode' => 'html', 'message_id' => $messageId, 'text' => "Salom Dunyo"]);
//            Log::info($response->getMessageId());
        $user = $this->userService->createIfNotExists($request->all());
        // $form = isset($request['message']) ? $request['message'] : $request['callback_query']['message'];

        // if ($form['text'] === '/start' ) {
        //     $this->userService->setUserStep($user, 0);
        // }

        if($user->isAdmin()) {
            switch ($user->step) {
                case 0: {
                    $this->adminService->sendHello($user);
                } break;
                case 1: {
                    $this->adminService->selectCategory($user, $request);
                } break;
                case 2: {
                    $this->adminService->getSubjectName($user, $request);
                } break;
                case 3: {
                 $this->adminService->selectTestName($user, $request);
                 } break;
                case 4: {
                    $this->adminService->selectSubject($user, $request);
                } break;
                case 5: {
                    $this->adminService->getAnswers($user, $request);
                } break;
                case 6: {
                    $this->adminService->getTestStopDate($user, $request);
                } break;
                case 7: {
                    $this->adminService->getTestFile($user, $request);
                } break;
                case 8: {
                    $this->adminService->selectUserFind($user, $request);
                } break;
                case 9: {
                    $this->adminService->selectfirstNameFilter($user, $request);
                } break;
                case 10: {
                    $this->adminService->selectResultTestName($user, $request);
                } break;
                case 11: {
                    $this->adminService->selectResultUsername($user, $request);
                } break;
            }
       } else {
           switch ($user->step) {
                case 0: {
                    $this->guestService->sendHello($user, $request);
                } break;
                // case 1: {
                //     $this->guestService->sendHomeMarkup($user, $request);
                // } break;
                case 2: {
                    $this->guestService->selectCategory($user, $request);
                } break;
                case 3: {
                   $this->guestService->selectSubject($user, $request);
                } break;
                case 4: {
                    $this->guestService->selectQuestionA($user, $request);
                } break;
                // case 5: {
                //     $this->guestService->selectQuestionB($user, $request);
                // } break;
                case 6: {
                    $this->guestService->answersShow($user, $request);
                } break;
           }
       }

    }
}

