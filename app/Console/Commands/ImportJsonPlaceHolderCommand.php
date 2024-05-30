<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use DateTime;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;

class ImportJsonPlaceHolderCommand extends Command
{
    protected $signature = 'import:jsonplaceholder';

    protected $description = 'Get data from jsonplacehlder';

    public function handle()
    {
        $import = new ApiController();
        $response = $import->client->request('GET', '');
        $data = (json_decode($response->getBody()));

        foreach ($data as $key=>$value) {
            $date = implode('-', array_reverse(explode('.', $value)));
        }
        $response = $import->client->request('GET', "$date");
        $group = (json_decode($response->getBody()->getContents(), true));

        $info = $group['data']['groups'][1][14];

        $response = $import->client->request('GET', "$date/group/$info");
        $schedule = (json_decode($response->getBody()->getContents(), true));

        $result = [];
        foreach ($schedule['schedule'] as $item) {
            // Обработка каждого элемента расписания
            if (isset($item['lesson'], $item['name'], $item['teachers'], $item['rooms'])) {
                $lesson = $item['lesson'];
                $name = str_replace("\n", ' ', $item['name']);
                $teachers = implode(', ', $item['teachers']); // Преобразование массива учителей в строку
                $rooms = implode(', ', $item['rooms']); // Преобразование массива комнат в строку

                // Создание строки с информацией
                $scheduleString = "Lesson: $lesson, Name: $name, Teachers: $teachers, Rooms: $rooms";

                // Добавление строки в результат
                $result[] = $scheduleString;
            } else {
                $result[] = "Invalid schedule item structure.";
            }
        }

        // Вывод результата
        dd(implode("\n\n", $result));

    }
}
