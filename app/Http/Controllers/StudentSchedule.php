<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use App\Http\Controllers\TelegramController;
use App\Models\User;

class StudentSchedule extends Conversation
{
    public function start(Nutgram $bot)
    {
        $message = $bot->sendMessage('Напиши группу: 3ИС6');
        $user = User::where('id_user', $bot->user()->id)->first();
        $user->SetLastMessageId($message->message_id);
        $bot->message()->delete();
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        $group = $bot->message()->text;
        $bot->message()->delete();
        $this->end();
        TelegramController::schedule_action_2($bot, $group);
    }
}
