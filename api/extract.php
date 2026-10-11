<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);


// โค้ดส่วนที่เหลือของไฟล์อยู่ต่อจากตรงนี้


require_once __DIR__ . '/lib/DocumentValidator.php';
require_once __DIR__ . '/lib/ResponseFormatter.php';
require_once __DIR__ . '/lib/GeminiClient.php';

// รับเฉพาะ HTTP POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');

    ResponseFormatter::send([
        'success' => false,
        'error' => 'อนุญาตเฉพาะ HTTP POST',
    ], 405);
}

// ตรวจสอบว่ามีไฟล์ส่งมาหรือไม่
if (
    !isset($_FILES['document']) ||
    !is_array($_FILES['document'])
) {
    ResponseFormatter::send([
        'success' => false,
        'error' => 'ไม่พบไฟล์ กรุณาเลือกเอกสารก่อนส่ง',
    ], 400);
}

$file = $_FILES['document'];

// ตรวจสอบไฟล์ก่อนส่งให้ AI
$upload = DocumentValidator::validateUpload($file);

if (!$upload['valid']) {
    ResponseFormatter::send([
        'success' => false,
        'error' => $upload['error'],
    ], 400);
}

// อ่าน API Key จาก Environment Variable
$apiKey = getenv('GEMINI_API_KEY');

if (!is_string($apiKey) || trim($apiKey) === '') {
    ResponseFormatter::send([
        'success' => false,
        'error' => 'ยังไม่ได้ตั้งค่า GEMINI_API_KEY บนเซิร์ฟเวอร์',
    ], 500);
}

// เลือกโมเดล Gemini
$model = getenv('GEMINI_MODEL');

if (!is_string($model) || trim($model) === '') {
    $model = 'gemini-2.5-flash';
}

// อ่านเนื้อหาไฟล์
$binary = file_get_contents($upload['tmp_name']);

if ($binary === false) {
    ResponseFormatter::send([
        'success' => false,
        'error' => 'ไม่สามารถอ่านไฟล์ที่อัปโหลดได้',
    ], 500);
}

try {
    $client = new GeminiClient(
        trim($apiKey),
        trim($model)
    );

    // ส่งเอกสารให้ Gemini วิเคราะห์
    $data = $client->extractInvoice(
        $upload['mime'],
        $binary,
        (string) ($file['name'] ?? 'document')
    );

    // ตรวจสอบความครบถ้วนของข้อมูลที่ AI อ่านได้
    $validation = DocumentValidator::validateInvoiceData($data);

    ResponseFormatter::send([
        'success' => true,
        'data' => $data,
        'validation' => $validation,
    ]);
} catch (Throwable $exception) {
    // เก็บรายละเอียดใน Server Log
    // ไม่ส่งรายละเอียดภายในหรือ API Key กลับไปยังหน้าเว็บ
    error_log(
        '[AI Document Extractor] ' .
        get_class($exception) . ': ' .
        $exception->getMessage()
    );

    ResponseFormatter::send([
        'success' => false,
        'error' => 'วิเคราะห์เอกสารไม่สำเร็จ กรุณาตรวจสอบ API และ Server Log',
    ], 502);
}
