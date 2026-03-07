<?php

class AdminManager{
    private $storage_directory;

    public function __construct(string $storage_directory = __DIR__."\\tests\\")
    {
        $this->storage_directory = $storage_directory;
        if(!is_dir($storage_directory))
            {
                mkdir($storage_directory,0755, true);
            }
    }

    //сохранить
    public function saveTest(string $fileName, string $category, array $questions):bool{
        $formattedQuestions = [];    
        foreach($questions as $question)
            {
                $formattedQuestions[]=
                [
                    'questions' => htmlspecialchars(is_array($question['question']) ? $question['question'][0] : $question['question']),
                    'options' => array_map('htmlspecialchars', $question['options']),
                    'corrects_indices' => array_map('intval', (array)$question['corrects_indices']),
                    'points' => (int)$question['points'],
                ];
            }
            $data =
            [
                'category' => htmlspecialchars($category),
                'question'=> $formattedQuestions
            ];
        return file_put_contents($this->storage_directory . basename($fileName) . ".json", json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) == false;
    }

    //удалить
    public function deleteTest(string $fileName):bool{
  
        $path = $this->storage_directory . basename($fileName) . '.json'; 
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        echo "Файл не найден по пути: " . $path; 
        return false;
    }

    //вывод списка тестов
    public function listTest(): array {
        return  array_map(fn($f) => basename($f, '.json'), glob($this->storage_directory."*.json"));
    }

    //загрузка для редактирования(рот ебал проблемная хуета)
    public function loadTest(string $fileName): ?array {
    
    $path = rtrim($this->storage_directory, '/') . '/' . basename($fileName) . '.json';
    
    if (!file_exists($path)) {
        echo "пусто";
        return null; 
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    return $data;
    }

}

$admin = new AdminManager();

//СХРОН И УДАЛЕНИЕ
// $questions = [
//     [
//         'question' => ['как какать?'],
//         'options' => ["52", "4", "67","1488"],
//         'corrects_indices' =>[1],
//         'points' => 5
//     ]
//     ,
//     [
//         'question' => ['Что делать, если Коля предлагает соитие?'],
//         'options' => ["Отказаться", "Задуматься", "Сказать Еби меня нежно","Согласиться"],
//         'corrects_indices' =>[1,2],
//         'points' => 10
//     ]
// ];
// $admin->saveTest("sample_test_file", "Залупа простая", $questions);

//$admin->deleteTest('sample_test_file');

//ВЫВЕСТИ СПИСОК
// $tests =  $admin->listTest();
// foreach ($tests as $testName) 
//     {
//         echo $testName. PHP_EOL;
//     }

//Загружаем и редактируем([хуйни рот ебал не работает норм])
$name = "sample_test_file";
$data = $admin->loadTest($name);

// $question = $data["questions"];
// var_dump($question);


if ($data) {
    //$data["questions"][0]["question"] = "Бебра?";
    
    echo "Выбери какой вопрос редактировать!\nСписок вопросов:\n";
    for ($i = 0; $i < count($data["questions"]); $i++) {
        echo   $i.")".$data["questions"][$i]["question"]."\n";
    }
    // $number = fscanf(STDIN, "%d")[0];
    $number_of_question=1;
    echo "\nЧто редактировать хотите?\n1)Сам вопрос\n2)Вариант ответа\n3)Варианты правильных ответов\n4)кол-во очков\n\n";

    $choice = 2 ;

    switch ($choice){
        case 1:
            echo "Пишите: ";
            $data["questions"][$number_of_question]["question"] = "Когда евангелион ?";
            
            echo "ок";
            break;
        case 2:
            echo "Варианты ответа:\n";
            for ($i = 0; $i < count($data["questions"][$number_of_question]["options"]); $i++) {
            echo   $i.")".$data["questions"][$number_of_question]["options"][$i]."\n";
            }   
            $num = 1;
            $data["questions"][$number_of_question]["options"][$num] = "Подставить жопу";

        break;

        case 3: 
             echo "Варианты ответа:\n";

            $data["questions"][$number_of_question]["corrects_indices"];
            
            echo   $i.")".$data["questions"][$number_of_question]["options"][$i]."\n";
              
            $num = 1;
            $data["questions"][$number_of_question]["corrects_indices"][$num] = "витПодстаь жопу";
            echo "";
        break;

        case 4:
            echo "";
        break;
    }


    //$admin->saveTest($name, $data["category"], $data["questions"]);
    // echo "Тест обновлен!";
}
