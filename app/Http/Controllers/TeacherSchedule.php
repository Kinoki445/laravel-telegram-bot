<?php

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use App\Http\Controllers\TelegramController;


class TeacherSchedule extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage('Напиши преподователя пример: Зятикова ТЮ');
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        $teacher = $bot->message()->text;
        $bot->sendMessage("Обрабатываю");
        $this->end();
        TelegramController::schedule_teacher_action($bot, $teacher);
    }
}
