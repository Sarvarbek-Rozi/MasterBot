<?php


namespace App\Repositories;


use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function createIfNotExists($data) {
        $form = isset($data['message']) ? $data['message'] : $data['callback_query']['message'];
        $user = User::where('chat_id', $form['chat']['id'])->first();

        if(!$user) {
            $user = User::create([
                'chat_id' => $form['chat']['id'],
                'first_name' => $form['chat']['first_name'],
                'last_name' => isset($form['chat']['last_name']) ? $form['chat']['last_name'] : null,
                'language_code' => isset($form['from']['language_code']) ? $form['from']['language_code'] : 'no',
                'username' => isset($form['chat']['username']) ? $form['chat']['username'] : null
            ]);
        } else {
            $user->update([
                'chat_id' => $form['chat']['id'],
                'first_name' => $form['chat']['first_name'],
                'last_name' => isset($form['chat']['last_name']) ? $form['chat']['last_name'] : null,
                'language_code' => isset($form['from']['language_code']) ? $form['from']['language_code'] : 'no',
                'username' => isset($form['chat']['username']) ? $form['chat']['username'] : null
            ]);
        }
        return $user;
    }

    public function setUserStep($user, $step) {
       return $user->update([
            'step' => $step ?? $user->step + 1
        ]);
    }

    public function getUsers() {
        return User::where('username', '!=', config('telegram.admin_username'))->get();
    }

    public function setUserData($user, $message_id) {
        $json = json_decode($user->data, true);

        $json['message_id'] = $message_id;
        $user->data = json_encode($json);
        return $user->save();
    }

}
