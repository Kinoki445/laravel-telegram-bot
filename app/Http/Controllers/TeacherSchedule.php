<?php

namespace App\Http\Controllers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use App\Http\Controllers\TelegramController;
use App\Models\User;

class TeacherSchedule extends Conversation
{
    public function start(Nutgram $bot)
    {
        $message = $bot->sendMessage('Напиши преподователя пример: Зятикова ТЮ');
        $user = User::where('id_user', $bot->user()->id)->first();
        $user->SetLastMessageId($message->message_id);
        $bot->message()->delete();
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        $teacher = $bot->message()->text;
        $bot->message()->delete();
        $this->end();
        TelegramController::schedule_teacher_action($bot, $teacher);
    }
}
