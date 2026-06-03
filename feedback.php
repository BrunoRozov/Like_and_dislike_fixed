<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$storageDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$storageFile = $storageDir . DIRECTORY_SEPARATOR . 'feedback.json';

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE); 
    exit;
}


function prepareStorage(string $storageDir, string $storageFile): void
{
    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        jsonResponse(['success' => false, 'message' => 'Andmekausta loomine ebaõnnestus.'], 500);
    }

    if (!file_exists($storageFile)) {
        $initialData = json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if (file_put_contents($storageFile, $initialData, LOCK_EX) === false) {
            jsonResponse(['success' => false, 'message' => 'Andmefaili loomine ebaõnnestus.'], 500);
        }
    }
}

function readFeedback(string $storageFile): array
{
    $json = file_get_contents($storageFile);

    if ($json === false || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveFeedback(string $storageFile, array $entry): void
{
    $handle = fopen($storageFile, 'c+');

    if ($handle === false) {
        jsonResponse(['success' => false, 'message' => 'Andmefaili avamine ebaõnnestus.'], 500);
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        jsonResponse(['success' => false, 'message' => 'Andmefaili lukustamine ebaõnnestus.'], 500);
    }

    rewind($handle);
    $json = stream_get_contents($handle);
    $data = json_decode($json ?: '[]', true);

    if (!is_array($data)) {
        $data = [];
    }

    $entry['id'] = count($data) + 1;
    $data[] = $entry;

    $encodedData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, $encodedData);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

prepareStorage($storageDir, $storageFile);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = readFeedback($storageFile);
    jsonResponse([
        'success' => true,
        'count' => count($data),
        'message' => 'Tagasiside API töötab.'
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Lubatud on ainult GET ja POST päringud.'], 405);
}

$action = $_POST['action'] ?? '';

if ($action !== 'save_feedback') {
    jsonResponse(['success' => false, 'message' => 'Tundmatu tegevus.'], 400);
}

$vote = $_POST['vote'] ?? '';
$feedback = trim((string)($_POST['feedback'] ?? ''));

if (!in_array($vote, ['like', 'dislike'], true)) {
    jsonResponse(['success' => false, 'message' => 'Vigane valik.'], 400);
}

if ($vote === 'dislike' && $feedback === '') {
    jsonResponse(['success' => false, 'message' => 'Tagasiside ei tohi olla tühi.'], 400);
}

if ((function_exists('mb_strlen') ? mb_strlen($feedback) : strlen($feedback)) > 1000) {
    jsonResponse(['success' => false, 'message' => 'Tagasiside on liiga pikk. Maksimum on 1000 märki.'], 400);
}

saveFeedback($storageFile, [
    'time' => date('c'),
    'vote' => $vote,
    'feedback' => $feedback,
]);

jsonResponse(['success' => true, 'message' => 'Tagasiside salvestatud.']);
