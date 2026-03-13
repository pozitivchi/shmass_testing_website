<?php
declare(strict_types=1);

/**
 * Получает список всех JSON-файлов с тестами из указанной директории
 * @param string $dir Путь к директории с тестами
 * @return array Ассоциативный массив [имя_файла => категория]
 */
function getTestsList(string $dir): array
{
    $tests = [];
    
    if (!is_dir($dir)) {
        return $tests;
    }
    
    $files = glob($dir . '/*.json');
    
    foreach ($files as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        if ($data && isset($data['category'])) {
            $tests[$filename] = $data['category'];
        }
    }
    
    return $tests;
}

/**
 * Загружает тест из JSON-файла
 * @param string $dir Путь к директории с тестами
 * @param string $filename Имя файла теста
 * @return array|null Данные теста или null, если файл не существует или некорректен
 */
function loadTest(string $dir, string $filename): ?array
{
    $path = $dir . '/' . $filename;
    
    if (!file_exists($path)) {
        return null;
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    return $data ?: null;
}

/**
 * Обрабатывает результаты теста
 * @param array $testData Данные теста
 * @param array $userAnswers Ответы пользователя
 * @return array Результаты обработки
 */
function processTestResults(array $testData, array $userAnswers): array
{
    $maxScore = 0;
    $totalScore = 0;
    $details = [];
    
    foreach ($testData['questions'] as $idx => $question) {
        $questionMaxScore = $question['score'] ?? 1;
        $maxScore += $questionMaxScore;
        
        // Получаем правильные ответы и сортируем
        $correctIndices = $question['correct_indices'] ?? [];
        sort($correctIndices);
        
        // Получаем ответы пользователя для текущего вопроса
        $userAnswerIndices = $userAnswers[$idx] ?? [];
        // Приводим к массиву целых чисел
        $userAnswerIndices = array_map('intval', (array)$userAnswerIndices);
        sort($userAnswerIndices);
        
        $isCorrect = ($correctIndices === $userAnswerIndices);
        $earnedScore = $isCorrect ? $questionMaxScore : 0;
        $totalScore += $earnedScore;
        
        $details[] = [
            'question_text' => $question['text'],
            'is_correct' => $isCorrect,
            'earned_score' => $earnedScore,
            'max_score' => $questionMaxScore,
            'correct_answers' => $correctIndices,
            'user_answers' => $userAnswerIndices
        ];
    }
    
    return [
        'category' => $testData['category'] ?? 'Без категории',
        'max_score' => $maxScore,
        'total_score' => $totalScore,
        'percentage' => $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0,
        'details' => $details,
        'test_data' => $testData
    ];
}

/**
 * Скачивает результаты в JSON-файл
 * @param array $results Данные результатов
 * @param string $filename Базовое имя файла
 */
function downloadResults(array $results, string $filename): void
{
    // Очищаем буфер вывода
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Формируем имя файла для скачивания
    $downloadFilename = 'res_' . date('Y-m-d_H-i-s') . '_' . $filename;
    
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}