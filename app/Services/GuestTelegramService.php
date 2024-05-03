<?php

namespace App\Services;


use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class GuestTelegramService
{
    protected $telegramUserService;
    protected $ExamsService;
    protected $regularService;
    protected $subjectService;
    protected $testService;
    public function __construct( RegularService $regularService,ExamsService $ExamsService, SubjectService $subjectService, TelegramUserService $telegramUserService, TestService $testService)
    {
        $this->telegramUserService = $telegramUserService;
        $this->ExamsService = $ExamsService;
        $this->regularService = $regularService;
        $this->subjectService = $subjectService;
        $this->testService = $testService;
    }

    public function sendHello($user)
    {
//             Log::info('user='.$user);

        $name = $user->first_name != '@' ? $user->first_name : '';

        $text = "👋Salom " . $name .  " <b>! \n \n 🏢\"ABACUS\"</b> o'quv markazi botiga xush kelibsiz!!! \n ";

        Telegram::sendMessage(['chat_id' => $user->chat_id, 'parse_mode'=>'html','text' => $text]);
        // $this->telegramUserService->setUserStep($user, 1);
        $this->sendHomeMarkup($user);

    }

    public function sendHomeMarkup($user)
    {
        $text ="Kerakli bo'limni tanlang👇";

        $keyboard = [
            ['📝 Test topshirish']
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        $this->telegramUserService->setUserStep($user, 2);

    }

    public function selectCategory($user, $data)
    {
        switch ($data['message']['text']) {
            case "📝 Test topshirish": {
                $this->selectTest($user, $data);
            } break;
        }

    }

    public function selectTest($user, $data) {
        $text = " Testni tanlang \n";

        $testName = $this->testService->all()->pluck('name')->toArray();
        Log::info($testName);
        $keyboard = array_chunk($testName, 3);

        array_push($keyboard, ['🔙 Asosiy menyuga']);
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        $this->telegramUserService->setUserStep($user, 3);

    }

    public function selectSubject($user, $data){
        if ($data['message']['text'] != '🔙 Asosiy menyuga') {
            $testName = $data['message']['text'];
            $this->ExamsService->create($user, $testName);
            $test = $this->testService->findByField('name',$testName);
            $text = " Test ishlash jarayonida sizga omad tilaymiz \n Javoblarni katta harflar bilan kiriting \n";

            foreach (explode(',', $test->file_path) as $file_id) {
                Telegram::sendPhoto(['chat_id' => $user->chat_id, 'photo' => $file_id ]);
            }
            $answers = explode(",",$test->answers);
            $countAnswers = count($answers);
            // Log::info($countAnswers);
            $text .= "Test savollari soni  ". $countAnswers. "  ta \n";
            $text.= "1-savol javobini kiriting";

            $keyboard = [
                ['❌ Bekor qilish']
            ];
            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
            // $this->selectQuestionA($user, $data);
            $this->telegramUserService->setUserStep($user, 4);
        } else {
            $this->sendHomeMarkup($user);

            // $this->telegramUserService->setUserStep($user, 1);
        }
    }

    public function selectQuestionA($user, $data){
        $keyboard = [
            ['❌ Bekor qilish']
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        if ($this->regularService->checkTestAnswers($user, $data)){
            if ($data['message']['text'] != '❌ Bekor qilish') {
                $exam = $this->ExamsService->all($user);
                $len = strlen($exam->answers);
                $len = $len +2;
                $test = $this->testService->findByField('id',$exam->test_id);
                $answers = explode(",",$test->answers);
                $countAnswers = count($answers);
                $countAnswers ++;
                if ($len < $countAnswers ){
                    $answerA = $data['message']['text'];
                    $writeAnswerA = $this->ExamsService->writeAnswer($user, $answerA);
                    $text = $len." - savolni javobini kiriting";
                    $keyboard = [
                        ['❌ Bekor qilish']
                    ];
                    $reply_markup = Keyboard::make([
                        'keyboard' => $keyboard,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
                    $this->telegramUserService->setUserStep($user, 4);
                } else  {
                    $this->telegramUserService->setUserStep($user, 6);
                    $this->answersShow($user, $data);
                }
            } else {
                $this->telegramUserService->setUserStep($user, 1);
                $this->sendHomeMarkup($user);
            }
        } else {
            $this->sendHomeMarkup($user);
        }
    }

    public function selectQuestionB($user, $data){
        if ($data['message']['text'] != '❌ Bekor qilish') {
            $exams = $this->ExamsService->all();
            $len = strlen($exams->answers);
            $len = $len +2;
            $test = $this->testService->testId($exams->test_id);
            // Log::info($test);
            $answers = explode(",",$test->answers);
            $countAnswers = count($answers);
            $countAnswers ++;
            if ($len < $countAnswers ){
                $answerA = $data['message']['text'];
                $writeAnswerA = $this->ExamsService->writeAnswer($answerA);
                $text = $len." - savolni javobini kiriting";
                $keyboard = [
                    ['❌ Bekor qilish']
                ];
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
                $this->telegramUserService->setUserStep($user, 6);
            }  else {
                $this->answersShow($user, $data);
            }
        // } else {
        //     $this->sendHomeMarkup($user);
        }
        else {
            // $this->telegramUserService->setUserStep($user, 4);
            $text = 'Istimos "A" , "B", "C", "D"  harflardan birini jo\'nating!';
            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        }
    }

    public function answersShow($user, $data) {
        if ($data['message']['text'] != '❌ Bekor qilish') {
            $writeAnswerA = $this->ExamsService->writeAnswer($data['message']['text']);
            $text = " Test muvaffaqiyatli tugatildi";
            $resultTest = $this->ExamsService->resultTest();

            if ($data['message']['text'] != '🔙 Asosiy menyuga') {
                $writeAnswerA = $this->ExamsService->writeAnswer($user, $data['message']['text']);
                $text = "<b> Test muvaffaqiyatli tugatildi </b> \n";
                $resultTest = $this->ExamsService->ruseultTest($user);
                $text.= "<b> Sizning natijangiz: </b> ". $resultTest. "\n <b>To`g`ri javoblar ro`yxati </b> \n";
                $resultAnswersAdmin =$this->ExamsService->TestAnswers($user);
                $resultAnswerGuest = $this->ExamsService->all($user)->answers;
                // Log::info($resultAnswerGuest);
                $id = 1;
                $i = 0;
                foreach ($resultAnswersAdmin as $key => $result) {
                    $text .= $id.". Sizning javobingiz ".$resultAnswerGuest[$i]." tog`ri javob  ".$result. "\n";
                    $id++;
                    $i++;
                }

                $keyboard = [
                    ['🔙 Asosiy menyuga']
                ];

                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
            } else{
                $this->sendHomeMarkup($user);
            }

        }
        // }
    }
}
