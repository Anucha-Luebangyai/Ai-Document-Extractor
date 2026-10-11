<?php

declare(strict_types=1);

final class ResponseFormatter
{
    public static function send(
        array $data,
        int $statusCode = 200
    ): void {
        http_response_code($statusCode);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            http_response_code(500);
            echo '{"success":false,"error":"ไม่สามารถสร้าง JSON ได้"}';
            exit;
        }

        echo $json;
        exit;
    }
}
