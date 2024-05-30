<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\Conversation;
use App\Http\Controllers\ApiController;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class TelegramController extends Controller
{
    // Метод для получения расписания
    public function schedule_action(Nutgram $bot) {
        $id= $bot->user()->id;
        $user = User::where('id_user', $id)->first();
        $currentGroup = $user->group;
        if (!(is_null($currentGroup))) {
            $import = new ApiController();
            $response = $import->client->request('GET', '');
            $data = (json_decode($response->getBody()));

            $newArray = [];
            $counter = 0;

            foreach ($data as $value) {
                if ($counter < 5) {
                    $date = implode('-', array_reverse(explode('.', $value)));
                    $newArray[] = $date;
                    $counter++;
                } else {
                    break;
                }
            }

            $response = $import->client->request('GET', "$newArray[0]/group/$currentGroup");
            $schedule = (json_decode($response->getBody()->getContents(), true));

            $result[] = "Расписание на $newArray[0]\nГруппы $currentGroup";
            foreach ($schedule['schedule'] as $item) {
                // Обработка каждого элемента расписания
                if (isset($item['lesson'], $item['name'], $item['teachers'], $item['rooms'])) {
                    $lesson = $item['lesson'];
                    $name = str_replace("\n", ' ', $item['name']);
                    $teachers = implode(', ', $item['teachers']); // Преобразование массива учителей в строку
                    $rooms = implode(', ', $item['rooms']); // Преобразование массива комнат в строку

                    // Создание строки с информацией
                    $scheduleString = "\nУрок: $lesson\nНазвание: $name\nПреподаватель: $teachers - $rooms";

                    // Добавление строки в результат
                    $result[] = $scheduleString;
                } else {
                    $result[] = "Invalid schedule item structure.";
                }
            }

            // Создаем объект разметки клавиатуры
            $keyboard = InlineKeyboardMarkup::make();

            // Добавляем кнопки по одной в каждую строку
            foreach ($newArray as $date) {
                $button = InlineKeyboardButton::make($date, callback_data: "group");
                $keyboard->addRow($button);
            }
            $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "other_schedule"),
                InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
                InlineKeyboardButton::make('Твоя группа', callback_data: "other_schedule"));
            $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

            // Вывод результата
            $text = implode("\n", $result);
            return $bot->sendMessage(
                text: "$text",
                reply_markup: $keyboard
            );
        } else {
            return $bot->sendMessage('Введи команду /setgroup {parameter} чтобы указать свою группу для бота.');
        }
    }

    public function schedule_teacher_action(Nutgram $bot) {
        $id= $bot->user()->id;
        $user = User::where('id_user', $id)->first();
        $currentGroup = $user->group;
        if (!(is_null($currentGroup))) {
            $import = new ApiController();
            $response = $import->client->request('GET', '');
            $data = (json_decode($response->getBody()));

            $newArray = [];
            $counter = 0;

            foreach ($data as $value) {
                if ($counter < 5) {
                    $date = implode('-', array_reverse(explode('.', $value)));
                    $newArray[] = $date;
                    $counter++;
                } else {
                    break;
                }
            }

            $response = $import->client->request('GET', "$newArray[0]/group/$currentGroup");
            $schedule = (json_decode($response->getBody()->getContents(), true));

            $result[] = "Расписание на $newArray[0]\nГруппы $currentGroup";
            foreach ($schedule['schedule'] as $item) {
                // Обработка каждого элемента расписания
                if (isset($item['lesson'], $item['name'], $item['teachers'], $item['rooms'])) {
                    $lesson = $item['lesson'];
                    $name = str_replace("\n", ' ', $item['name']);
                    $teachers = implode(', ', $item['teachers']); // Преобразование массива учителей в строку
                    $rooms = implode(', ', $item['rooms']); // Преобразование массива комнат в строку

                    // Создание строки с информацией
                    $scheduleString = "\nУрок: $lesson\nНазвание: $name\nПреподаватель: $teachers - $rooms";

                    // Добавление строки в результат
                    $result[] = $scheduleString;
                } else {
                    $result[] = "Invalid schedule item structure.";
                }
            }

            // Создаем объект разметки клавиатуры
            $keyboard = InlineKeyboardMarkup::make();

            // Добавляем кнопки по одной в каждую строку
            foreach ($newArray as $date) {
                $button = InlineKeyboardButton::make($date, callback_data: "group");
                $keyboard->addRow($button);
            }
            $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "other_schedule"),
                InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
                InlineKeyboardButton::make('Твоя группа', callback_data: "other_schedule"));
            $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

            // Вывод результата
            $text = implode("\n", $result);
            return $bot->sendMessage(
                text: "$text",
                reply_markup: $keyboard
            );
        } else {
            return $bot->sendMessage('Введи команду /setgroup {parameter} чтобы указать свою группу для бота.');
        }
    }

    // Метод для обработки команды /start
    public function start_action(Nutgram $bot, Request $request)
    {
        $user = User::where('id_user', $bot->user()->id)->first();

        if (is_null($user)) {
            // Создание нового пользователя
            $user = new User();
            $user->id_user = $bot->user()->id;
            $user->username = $bot->user()->username;
            $user->lastname = $bot->user()->first_name;
            $user->save();

            // Логирование начала действия
            Log::channel('telegram')->info('start', ['Зарегистрировался новый пользователь' => ['id'=> $bot->user()->id]]);

            // Отправка сообщения пользователю
            return $bot->sendMessage('Добро пожаловать в бота NTTEK @' . $bot->user()->username);
        } else {
            return $bot->sendMessage('С возвращением в бота NTTEK @' . $bot->user()->username);
        }
    }

    // Метод для обработки команды /about
    public function about_action(Nutgram $bot)
    {
        Log::channel('telegram')->info('about_action');
        $id = $bot->user()->id;
        $username = $bot->user()->username;
        $lastname = $bot->user()->first_name;
        return $bot->sendMessage("Твой UserID = $id \nТвой Username = @$username \nТвоё First_name = $lastname");
    }

    public function set_group_action(Nutgram $bot, $parameter){
        $id = $bot->user()->id;

        // Найти пользователя по ID
        $user = User::where('id_user', $id)->first();

        if ($user) {
            // Обновить группу пользователя
            $user->group = $parameter;
            $user->save();

            // Логирование изменения
            Log::channel("telegram")->info("Пользователь $id поменял свою группу на $parameter");
            $this->schedule_action($bot);
        }
    }

    public function callback_action_schedule(Nutgram $bot){
        return $bot->answerCallbackQuery(
            text: 'You selected A'
        );
    }
}

class TeacherSchedule extends Conversation {

    public function start(Nutgram $bot)
    {
        $bot->sendMessage('Напиши преподователя пример: Зятикова ТЮ');
        $this->next('secondStep');
    }

    public function secondStep(Nutgram $bot)
    {
        $Teacher = $bot->message()->text;
        return TelegramController::schedule_teacher_action($bot, $teacher);
        $this->end();
    }
}
