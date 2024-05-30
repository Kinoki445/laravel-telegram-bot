<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TeacherSchedule;

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
$bot -> onCommand('setgroup {parameter}', [TelegramController::class, 'set_group_action'])->description('The SetGroup command!');


$bot->onCallbackQueryData('group', [TelegramController::class,'callback_action_schedule']);

$bot->onCallbackQueryData('other_schedule', [TelegramController::class,'callback_action_schedule_other']);
$bot->onCallbackQueryData('teacher_schedule', TeacherSchedule::class);
$bot->onCallbackQueryData('schedule', [TelegramController::class,'schedule_action']);
$bot->onCallbackQueryData('menu_schedule', [TelegramController::class,'menu_schedule_action']);



