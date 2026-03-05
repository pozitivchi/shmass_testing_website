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
                    'question' => htmlspecialchars(is_array($question['question']) ? $question['question'][0] : $question['question']),
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

    //загрузка для редактирования
    public function loadTest(string $fileName): ?array {
    $path = rtrim($this->storage_directory, '/') . '/' . basename($fileName) . '.json';
    

    if (!file_exists($path)) {
        echo "пусто";
        return null; 
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    // 3. Если JSON кривой или файл пустой - возвращаем пустой шаблон, чтобы не было Fatal Error
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return ['category' => 'Без названия', 'questions' => []];
    }
    
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

//Загружаем и редактируем
$name = "sample_test_file";
$data = $admin->loadTest($name);

if ($data) {
    $newCategory = 'Высшая математика';
    
    $admin->saveTest($name, $newCategory, $data['questions']);
    echo "Тест обновлен!";
}
