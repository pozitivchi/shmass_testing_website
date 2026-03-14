<?php

class AdminManager{
    private $storage_directory;

    public function __construct(string $storage_directory = __DIR__. "\\..\\data\\")
    {
        $this->storage_directory = $storage_directory;
        if(!is_dir($storage_directory))
            {
                mkdir($storage_directory,0755, true);
            }
    }



    public function saveTest(string $filename, string $category, array $questions): bool {

    $data = [
        'category' => htmlspecialchars($category),
        'questions' => $questions 
    ];

    $jsonContent = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    if ($jsonContent === false) {
        echo "Ошибка кодирования JSON: " . json_last_error_msg();
        return false;
    }


    $path = rtrim($this->storage_directory, '/') . '/' . basename($filename) . '.json';


    $result = file_put_contents($path, $jsonContent);

    if ($result === false) {

        echo "Ошибка: Не удалось записать файл по пути: " . realpath($this->storage_directory) . "/" . basename($filename) . ".json";
        echo "<br>Проверьте права доступа к папке!";
        return false;
    }

    return true;
}


    public function deleteTest(string $fileName):bool{
  
        $path = $this->storage_directory . basename($fileName) . '.json'; 
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return false;
    }


    public function listTests(): array {
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
    
    return $data;
    }

}
