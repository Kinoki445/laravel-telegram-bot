<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use App\Http\Controllers\TelegramController;


class StudentSchedule extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage('Напиши группу: 3ИС6');
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        $group = $bot->message()->text;
        $bot->sendMessage("Обрабатываю");
        $this->end();
        TelegramController::schedule_action_2($bot, $group);
    }
}
