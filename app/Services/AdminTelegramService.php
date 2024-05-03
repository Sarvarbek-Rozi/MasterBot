<?php


namespace App\Services;


use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class AdminTelegramService
{
    protected $telegramUserService;
    protected $regularService;
    protected $subjectService;
    protected $testService;
    public function __construct( RegularService $regularService, SubjectService $subjectService, TelegramUserService $telegramUserService, TestService $testService)
    {
        $this->telegramUserService = $telegramUserService;
        $this->regularService = $regularService;
        $this->subjectService = $subjectService;
        $this->testService = $testService;
    }

    public function sendHello($user)
    {
        $name = $user->first_name != '@' ? $user->first_name : '';

        $text = "👋Salom " . $name .  " <b>! \n \n 🏢\"ABACUS\"</b> o'quv markazi botiga xush kelibsiz!!!";

        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
        $this->sendHomeMarkup($user);

        // hello
    }
    public function sendHomeMarkup($user)
    {
        $text ="Kerakli bo'limni tanlang👇";

        $keyboard = [
            ['📝 Yangi test joylashtirish', "📊 Test natijalarini ko'rish"],
            ["👨‍👩‍👧‍👦 Bot foydalanuvchilarini ko'rish", "📚 Fan qo'shish"]
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegramUserService->setUserStep($user, 1);

        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
    }

    public function selectCategory($user, $data)
    {
        //   Log::info($this->regularService->checkSelectCategory($data, $user));
        if ($this->regularService->checkSelectCategory($data, $user)) {
            switch ($data['message']['text']) {
                case "📚 Fan qo'shish": {
                    $this->selectSubjectAdd($user);
                } break;
                case "📝 Yangi test joylashtirish": {
                    $this->selectTestAdd($user);
                } break;
                case "👨‍👩‍👧‍👦 Bot foydalanuvchilarini ko'rish": {
                    $this->selectUsersShow($user ,$data);
                } break;
                case "📊 Test natijalarini ko'rish": {
                    $this->selectResultTestName($user ,$data);
                } break;
            }

        } else {
            $this->sendHomeMarkup($user);
        }
    }
    public function selectSubjectAdd($user)
    {
        $text = "Fan nomini kiriting:";
        $keyboard = [
            ['🔙 Asosiy menyuga']
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        $this->telegramUserService->setUserStep($user, 2);
    }
    public function getSubjectName($user, $data)
    {
        if ($data['message']['text'] != '🔙 Asosiy menyuga') {

            if($this->regularService->checkSubjectName($data, $user)) {
                $subject = $this->subjectService->create($data['message']['text']);
                if($subject) {
                    $text = " <b>". $subject->name ."</b>  fani muvaffaqiyatli qo'shildi🎉";

                    Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
                    $this->telegramUserService->setUserStep($user, 1);
                    $this->sendHomeMarkup($user);
                }
            } else {
                $this->sendHomeMarkup($user);
            }
        }else {
            $this->sendHomeMarkup($user, $data);
        }
    }
    public function selectTestAdd($user)
    {
        $text = "Fanni tanlang:";
        $subjects = $this->subjectService->all()->pluck('name')->toArray();

        $keyboard = array_chunk($subjects, 3);

        array_push($keyboard, ['🔙 Asosiy menyuga']);

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);

        $this->telegramUserService->setUserStep($user, 3);
    }

    public function selectTestName($user, $data){

        if ($data['message']['text'] != '🔙 Asosiy menyuga') {

            if($this->regularService->checkSelectSubject($data, $user)) {
                    $this->testService->create($data['message']['text']);

                    $text = "Test nomini kiriting:";
                    $keyboard = [['❌ Bekor qilish']];

                        $reply_markup = Keyboard::make([
                            'keyboard' => $keyboard,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup ]);
                        $this->telegramUserService->setUserStep($user, 4);
            }else{
                    $this->selectTestAdd($user);
                }

        } else {
            $this->sendHomeMarkup($user, $data);
        }
    }

    public function selectSubject($user, $data)
    {
        if ($data['message']['text'] != '❌ Bekor qilish') {

            $test_form = $this->testService->update( 'name', $data['message']['text']);

                $text = "Test nomi: <b>". $data['message']['text'] . "</b> \n \n \n Test javoblarini (,) bilan kiriting: \n Masalan:  <b>A,B,C,D ...</b>";
                $keyboard = [['❌ Bekor qilish']];

                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup ]);
                $this->telegramUserService->setUserStep($user, 5);
        } else {
            $this->testService->deleteLast();
            $this->sendHomeMarkup($user);
        }
    }
    public function getAnswers($user, $data)
    {
        if ($data['message']['text'] != '❌ Bekor qilish') {
            if($this->regularService->checkAnswers($data, $user)) {
                $test_form = $this->testService->update( 'answers', $data['message']['text']);
                $answers = explode(',', $data['message']['text']);
                $text = "Fan: <b>". $test_form->subject->name . "</b> \n";
                foreach ($answers as $key => $answer) {
                    $text .= "<b>". ($key + 1) .") " . $answer . "</b>\n";
                }
                $text .= "\n \n Test yakunlanish muddatini (kk.oo.yyyy) formatda kiriting: \n Masalan:  <b>01.01.1991</b>";

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
                $this->telegramUserService->setUserStep($user, 6);
            } else {
                $text = "❌ Noto'g'ri format \n \n Javoblarini (,) bilan kiriting: \n Masalan  <b>A,B,C,D ...</b>";

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);

            }
        } else {
            $this->testService->deleteLast();
            $this->sendHomeMarkup($user);
        }
    }
    public function getTestStopDate($user, $data)
    {
        if ($data['message']['text'] != '❌ Bekor qilish') {
            if($this->regularService->checkDate($data, $user)) {

                $test_form = $this->testService->update( 'date_stop', $data['message']['text']);
                $answers = explode(',', $test_form->answers);
                $text = "Fan: <b>". $test_form->subject->name . "</b> \n";
                foreach ($answers as $key => $answer) {
                    $text .= "<b>". ($key + 1) .") " . $answer . "</b>\n";
                }
                $text .= "Test boshlanish muddati: <b>" . Carbon::parse($test_form->date_start)->format('d.m.Y') . "</b>\n";
                $text .= "Test yakunlanish muddati: <b>" . Carbon::parse($test_form->date_stop)->format('d.m.Y') . "</b> ";
                $text .= "\n \n Test faylini rasm ko'rinishida yuklang:";

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text ]);
                $this->telegramUserService->setUserStep($user, 7);
            } else {
                $text = "❌ Noto'g'ri format \n \n Test yakunlanish muddatini (kk.oo.yyyy) formatda kiriting: \n Masalan:  <b>01.01.1991</b>";

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
            }
        } else {
            $this->testService->deleteLast();
            $this->sendHomeMarkup($user);
        }
    }
    public function getTestFile($user, $data)
    {
        if (isset($data['message']['text']) && $data['message']['text'] == '❌ Bekor qilish') {
            $this->testService->deleteLast();
            $this->sendHomeMarkup($user);
        } else if(isset($data['message']['text']) && $data['message']['text'] == '✅ Saqlash') {
            $test = $this->testService->createTrue();
            $answers = explode(',', $test->answers);
            $text = "🎉 Test muvaffaqiyatli yaratildi \n";
            $text .= "Fan: <b>". $test->subject->name . "</b> \n Javoblar: \n";
            foreach ($answers as $key => $answer) {
                $text .= "<b>". ($key + 1) .") " . $answer . "</b>\n";
            }
            $text .= "Test boshlanish muddati: <b>" . Carbon::parse($test->date_start)->format('d.m.Y') . "</b>\n";
            $text .= "Test yakunlanish muddati: <b>" . Carbon::parse($test->date_stop)->format('d.m.Y') . "</b> ";
            foreach (explode(',', $test->file_path) as $file_id) {
                Telegram::sendPhoto(['chat_id' => $user->chat_id, 'photo' => $file_id ]);
            }

            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
            $this->sendHomeMarkup($user);
        } else {
            if($this->regularService->checkPhoto($data, $user)) {
                $test_form = $this->testService->storeFile($data['message']['photo']);

                $keyboard = [['✅ Saqlash', '❌ Bekor qilish']];

                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                $text = "✅ Rasm muvaffaqiyatli saqlandi! \n \n Yana rasm yuklang yoki <b> Saqlash</b> tugmasini bosing👇";
                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup ]);
            } else {
                $text = "❌ Noto'g'ri format \n \n Test faylini rasm shaklida yuklang!";

                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text]);
            }
        }
    }
    public function selectUsersShow($user, $data)
    {
        // if ($data['message']['text'] != '🔙 Asosiy menyuga') {

            $text = "<b> Foydalanuvchilar:</b> \n";
            $users = $this->telegramUserService->all();
            $id = 1;
            foreach ($users as $struser){
                $username = $struser->username;
                $firstName= $struser->first_name;
                // Log::info('firstName='.$firstName);
                $text.=$id. ". ".$firstName. "  (@". $username. ")". "\n";
                $id+=1;
            };
            $keyboard = [
                ['🔙 Asosiy menyuga'],
                ['📜 Ism bo`yicha qidirish']
            ];

            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);

            // $this->selectUserFind($user, $data);
            $this->telegramUserService->setUserStep($user, 9);

        // } else {
            // $this->sendHomeMarkup($user, $data);
        // }
    }
    public function selectUserFind($user, $data)
    {
        if ($data['message']['text'] != '🔙 Asosiy menyuga') {

            $text = "Ism kiriting";
            if ($data['message']['text'] == "📜 Ism bo`yicha qidirish" ){

                $keyboard = [
                    ['🔙 Asosiy menyuga']
                ];

                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
            $this->telegramUserService->setUserStep($user, 10);

            } else {
                $this->sendHomeMarkup($user);
            }
        } else {
            $this->sendHomeMarkup($user);
        }
    }

    public function selectfirstNameFilter($user, $data){
        if ($data['message']['text'] != '🔙 Asosiy menyuga') {

            $text = "<b>Qidiruv natijalari:</b> \n";

            $query = $this->telegramUserService->query();
            $message = $data['message']['text'];
            if(isset($message) && !empty($message))
            {
                $query = $query->where('first_name', 'like', '%' . $message . '%' )->get();
            }
            $id = 1;
            foreach ($query as $resultName){
                $username = $resultName->username;
                $firstName= $resultName->first_name;
                // Log::info('firstName='.$firstName);
                $text.=$id. ". ".$firstName. "  (@". $username. ")". "\n";
                $id+=1;
            }
            // Log::info("query=".$query);
            $keyboard = [
                ['🔙 Asosiy menyuga']
            ];
            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        }else{

            $this->telegramUserService->setUserStep($user, 1);

            $this->sendHomeMarkup($user. $data);
        }

    }

    public function selectResultTestName($user, $data){
        $text = "Test nomini tanlang: \n";

        $testName = $this->testService->all()->pluck('name')->toArray();
        $keyboard = array_chunk($testName, 3);
        array_push($keyboard, ['🔙 Asosiy menyuga']);

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        $this->telegramUserService->setUserStep($user, 11);
        // $this->selectResultUsername($user, $data);

    }

    public function selectResultUsername($user, $data){
        $text = "Foydalanuvchini tanlang \n";

        $text .= "<b> Foydalanuvchilar:</b> \n";





        $users_text = $this->telegramUserService->getUsers();
        $text .= $users_text;

        $keyboard = [
            ['🔙 Asosiy menyuga']
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
            $response = Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
            $this->telegramUserService->setUserStep($user, 1    );

        // $this->telegramUserService->setUserData($user, $response->getMessageId());


    }
}
