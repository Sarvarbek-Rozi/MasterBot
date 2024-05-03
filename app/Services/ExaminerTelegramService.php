<?php


namespace App\Services;


use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class ExaminerTelegramService
{
    protected $telegramUserService;
    protected $regularService;
    public function __construct(TelegramUserService $telegramUserService, RegularService $regularService)
    {
        $this->telegramUserService = $telegramUserService;
        $this->regularService = $regularService;
    }

    public function sendHello($user) {
        $name = $user->first_name != '@' ? $user->first_name : '';

        $text = "👋Salom " . $name .  " <b>! \n \n 🏢\"ABACUS\"</b> o'quv markazi botiga xush kelibsiz!!! \n \n Kerakli bo'limni tanlang👇";

        $keyboard_admin = [
            ['📝 Янги тест жойлаштириш', '📊 Тест натижаларини кўриш'],
            ['👨‍👩‍👧‍👦 Bot foydalanuvchilarini ko\'rish']
        ];
        $keyboard_user = [
            ['📝 Тест топшириш']
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $user->isAdmin() ? $keyboard_admin : $keyboard_user,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->telegramUserService->setUserStep($user, 1);

        Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);

    }

    public function selectCategory($user, $data) {
        if ($this->regularService->selectCategory($data)) {
            $text = "👋Salom " . " <b>! \n \n 🏢\"ABACUS\"</b> o'quv markazi botiga xush kelibsiz!!! \n \n Kerakli bo'limni tanlang👇";

            $keyboard_admin = [
                ['📝 Янги тест жойлаштириш', '📊 Тест натижаларини кўриш'],
                ['👨‍👩‍👧‍👦 Bot foydalanuvchilarini ko\'rish']
            ];
            $keyboard_user = [
                ['📝 Тест топшириш']
            ];
            $reply_markup = Keyboard::make([
                'keyboard' => $user->isAdmin() ? $keyboard_admin : $keyboard_user,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->telegramUserService->setUserStep($user, 1);

            Telegram::sendMessage(['chat_id' =>$user->chat_id, 'parse_mode'=>'html','text' => $text, 'reply_markup' => $reply_markup]);
        }


    }
}
