<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TeacherSchedule;
use App\Http\Controllers\StudentSchedule;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot -> onCommand('start', [TelegramController::class, 'start_action'])->description('The start command!');
$bot -> onCommand('about_me', [TelegramController::class, 'about_action'])->description('The about command!');
$bot -> onCommand('schedule', [TelegramController::class, 'schedule_action'])->description('The schedule command!');
$bot -> onCommand('schedule {data}', [TelegramController::class, 'schedule_action_2'])->description('The schedule command!');
$bot -> onCommand('teacher {teacher}', [TelegramController::class, 'schedule_teacher_action'])->description('The teacher command!');
$bot -> onCommand('setgroup {group}', [TelegramController::class, 'set_group_action'])->description('The SetGroup command!');

$bot->onCallbackQueryData('group {group} {data}', [TelegramController::class,'callback_action_schedule']);
$bot->onCallbackQueryData('teacher {data} {teacher}', [TelegramController::class,'callback_action_teacher']);

// $bot->onCallbackQueryData('other_schedule', [TelegramController::class,'callback_action_schedule_other']);
$bot->onCallbackQueryData('teacher_schedule', TeacherSchedule::class);
$bot->onCallbackQueryData('student_schedule', StudentSchedule::class);
$bot->onCallbackQueryData('schedule', [TelegramController::class,'schedule_action']);
$bot->onCallbackQueryData('menu_schedule', [TelegramController::class,'menu_schedule_action']);



