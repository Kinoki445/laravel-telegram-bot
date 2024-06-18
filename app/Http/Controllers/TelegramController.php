<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use App\Http\Controllers\ApiController;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use Carbon\Carbon;

class TelegramController extends Controller
{
    // Метод для получения расписания
    public function schedule_action(Nutgram $bot) {
        $id= $bot->user()->id;
        $user = User::where('id_user', $id)->first();
        $currentGroup = $user->group;
        if (!(is_null($currentGroup))) {
            $import = new ApiController();
            $newArray = ApiController::get_date();

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
                $new_date = Carbon::parse($date);
                $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));

                $line = strval($date);
                $button = InlineKeyboardButton::make("$date | $dayOfWeek", callback_data: "group $currentGroup $line");
                $keyboard->addRow($button);
            }
            $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
                InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
                InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
            $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

            // Вывод результата
            $text = implode("\n", $result);
            return $bot->editMessageText(
                text: $text,
                chat_id: $bot->chatId(),
                message_id: $user->GetLastMessageId(),
                reply_markup: $keyboard
            );
        } else {
            $message = $bot->editMessageText(
                text:'Введи команду /setgroup {parameter} чтобы указать свою группу для бота.',
                chat_id: $bot->chatId(),
                message_id: $user->GetLastMessageId()
            );

            $user = User::where('id_user', $bot->user()->id)->first();
            $user->SetLastMessageId($message->message_id);

            return $message;
        }
    }

    public static function schedule_action_2(Nutgram $bot, $parameter) {
        $import = new ApiController();
        $newArray = ApiController::get_date();

        $response = $import->client->request('GET', "$newArray[0]/group/$parameter");
        $schedule = (json_decode($response->getBody()->getContents(), true));

        $result[] = "Расписание на $newArray[0]\nГруппы $parameter";
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
            $new_date = Carbon::parse($date);
            $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));

            $line = strval($date);
            $button = InlineKeyboardButton::make("$date | $dayOfWeek", callback_data: "group $parameter $line");
            $keyboard->addRow($button);
        }
        $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
            InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
            InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
        $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

        // Вывод результата
        $text = implode("\n", $result);
        $user = User::where('id_user', $bot->user()->id)->first();

        return $bot->editMessageText(
            text: $text,
            chat_id: $bot->chatId(),
            message_id: $user->GetLastMessageId(),
            reply_markup: $keyboard
        );
    }

    public function callback_action_teacher(Nutgram $bot, $date_fromcall, $teacher) {
        $import = new ApiController();
        $newArray = ApiController::get_date();

        $response = $import->client->request('GET', "$date_fromcall/teacher/$teacher");
        $schedule = (json_decode($response->getBody()->getContents(), true));

        $result[] = "Расписание на $date_fromcall\n $teacher";
        // Обработка каждого элемента данных
        foreach ($schedule as $key => $item) {
            // Проверка наличия необходимых ключей
            if (isset($item['name'], $item['rooms'])) {
                // Извлечение необходимых значений
                $name = str_replace("\n", ' ', $item['name']);  // Замена символов новой строки на пробел
                $rooms = implode(', ', $item['rooms']);  // Преобразование массива комнат в строку, разделенную запятыми

                // Создание строки с информацией о расписании
                $scheduleString = "\nНазвание: $name\nАудитория: $rooms";

                // Добавление строки в массив результата
                $result[] = $scheduleString;
            } else {
                $result[] = "Неправильная структура элемента расписания.";
            }
        }

        // Создаем объект разметки клавиатуры
        $keyboard = InlineKeyboardMarkup::make();

        // Добавляем кнопки по одной в каждую строку
        foreach ($newArray as $date) {
            $new_date = Carbon::parse($date);
            $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));

            $line = strval($date);
            $button = InlineKeyboardButton::make("$date | $dayOfWeek", callback_data: "teacher $teacher $line");
            $keyboard->addRow($button);
        }
        $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
            InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
            InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
        $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

        // Вывод результата
        $text = implode("\n", $result);
        $user = User::where('id_user', $bot->user()->id)->first();

        return $bot->editMessageText(
            text: $text,
            chat_id: $bot->chatId(),
            message_id: $user->GetLastMessageId(),
            reply_markup: $keyboard
        );
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
            $bot->message()->delete();
            $message = $bot->sendMessage('Добро пожаловать в бота NTTEK @' . $bot->user()->username);
            $user->SetLastMessageId($bot->$message->message_id);
            return $message;
        } else {
            $bot->message()->delete();
            $message = $bot->sendMessage('С возвращением в бота NTTEK @' . $bot->user()->username);
            $user->SetLastMessageId($bot->$message->message_id);
            return $message;
        }
    }

    public function menu_schedule(Nutgram $bot, Request $request)
    {
        $user = User::where('id_user', $bot->user()->id)->first();

        // Создаем объект разметки клавиатуры
        $keyboard = InlineKeyboardMarkup::make();

        $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
        InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
        InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
        $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));
        return $bot->sendMessage(
            text: 'Выбери то что хотел вызвать ' . $bot->user()->username,
            chat_id: $bot->chatId(),
            reply_markup: $keyboard
        );
    }

    public function callback_action_schedule(Nutgram $bot, $group, $parameter) {
        $import = new ApiController();
        $newArray = ApiController::get_date();

        $new_date = Carbon::parse($parameter);
        $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));

        $response = $import->client->request('GET', "$parameter/group/$group");
        $schedule = (json_decode($response->getBody()->getContents(), true));

        $result[] = "Расписание на $dayOfWeek\nГруппы $group";
        // Обработка каждого элемента данных
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
            $line = strval($date);
            $new_date = Carbon::parse($date);
            $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));
            $button = InlineKeyboardButton::make("$date | $dayOfWeek", callback_data: "group $group $line");
            $keyboard->addRow($button);
        }
        $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
            InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
            InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
        $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

        // Вывод результата
        $text = implode("\n", $result);
        $user = User::where('id_user', $bot->user()->id)->first();

        return $bot->editMessageText(
            text: $text,
            chat_id: $bot->chatId(),
            message_id: $user->GetLastMessageId(),
            reply_markup: $keyboard
        );
    }

    public function set_group_action(Nutgram $bot, $parameter){
        $id = $bot->user()->id;

        // Найти пользователя по ID
        $user = User::where('id_user', $id)->first();

        if ($user) {
            // Обновить группу пользователя
            $user->group = $parameter;
            $user->save();

            $bot->message()->delete();

            // Логирование изменения
            Log::channel("telegram")->info("Пользователь $id поменял свою группу на $parameter");
            $this->schedule_action($bot);
        }
    }

    public static function schedule_teacher_action(Nutgram $bot, $teacher)
    {
        $import = new ApiController();
        $newArray = ApiController::get_date();

        $response = $import->client->request('GET', "$newArray[0]/teacher/$teacher");
        $schedule = (json_decode($response->getBody()->getContents(), true));

        $result[] = "Расписание на $newArray[0]\nПреподователя $teacher";
        // Обработка каждого элемента данных
        foreach ($schedule as $key => $item) {
            // Проверка наличия необходимых ключей
            if (isset($item['name'], $item['rooms'])) {
                // Извлечение необходимых значений
                $name = str_replace("\n", ' ', $item['name']);  // Замена символов новой строки на пробел
                $rooms = implode(', ', $item['rooms']);  // Преобразование массива комнат в строку, разделенную запятыми

                // Создание строки с информацией о расписании
                $scheduleString = "\nНазвание: $name\nАудитория: $rooms";

                // Добавление строки в массив результата
                $result[] = $scheduleString;
            } else {
                $result[] = "Неправильная структура элемента расписания.";
            }
        }

        // Создаем объект разметки клавиатуры
        $keyboard = InlineKeyboardMarkup::make();

        // Добавляем кнопки по одной в каждую строку
        foreach ($newArray as $date) {
            $new_date = Carbon::parse($date);
            $dayOfWeek = ucfirst($new_date->locale('ru')->isoFormat('dddd'));

            $line = strval($date);
            $button = InlineKeyboardButton::make("$date | $dayOfWeek", callback_data: "group $teacher $line");
            $keyboard->addRow($button);
        }
        $keyboard->addRow(InlineKeyboardButton::make('Другая группа', callback_data: "student_schedule"),
            InlineKeyboardButton::make('Преподователь', callback_data: "teacher_schedule"),
            InlineKeyboardButton::make('Твоя группа', callback_data: "schedule"));
        $keyboard->addRow(InlineKeyboardButton::make('Меню', callback_data: "menu_schedule"));

        // Вывод результата
        $user = User::where('id_user', $bot->user()->id)->first();
        $text = implode("\n", $result);
        return $bot->editMessageText(
            text: $text,
            chat_id: $bot->chatId(),
            message_id: $user->GetLastMessageId(),
            reply_markup: $keyboard
        );
    }

}

