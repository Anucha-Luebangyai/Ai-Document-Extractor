<?php
declare(strict_types=1);

final class DocumentValidator
{
    public const MAX_FILE_SIZE = 10 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'text/plain',
        'image/png',
        'image/jpeg',
        'image/webp',
    ];

    public static function validateUpload(array $file): array
    {
        if (
            !isset(
                $file['error'],
                $file['size'],
                $file['tmp_name']
            ) ||
            !is_string($file['tmp_name']) ||
            !is_numeric($file['size'])
        ) {
            return [
                'valid' => false,
                'error' => 'ข้อมูลไฟล์ไม่ครบถ้วน',
            ];
        }

        $uploadError = (int) $file['error'];

        if ($uploadError !== UPLOAD_ERR_OK) {
            $message = match ($uploadError) {
                UPLOAD_ERR_INI_SIZE,
                UPLOAD_ERR_FORM_SIZE =>
                    'ไฟล์มีขนาดใหญ่เกินกำหนด',

                UPLOAD_ERR_PARTIAL =>
                    'อัปโหลดไฟล์ไม่ครบ',

                UPLOAD_ERR_NO_FILE =>
                    'กรุณาเลือกไฟล์',

                UPLOAD_ERR_NO_TMP_DIR =>
                    'เซิร์ฟเวอร์ไม่มีโฟลเดอร์ชั่วคราว',

                UPLOAD_ERR_CANT_WRITE =>
                    'เซิร์ฟเวอร์ไม่สามารถบันทึกไฟล์ได้',

                default =>
                    'เกิดข้อผิดพลาดระหว่างอัปโหลด',
            };

            return [
                'valid' => false,
                'error' => $message,
            ];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'error' => 'ไฟล์อัปโหลดไม่ถูกต้อง',
            ];
        }

        $size = (int) $file['size'];

        if ($size <= 0) {
            return [
                'valid' => false,
                'error' => 'ไฟล์ว่างเปล่า',
            ];
        }

        if ($size > self::MAX_FILE_SIZE) {
            return [
                'valid' => false,
                'error' => 'ไฟล์ต้องมีขนาดไม่เกิน 10 MB',
            ];
        }

        if (!class_exists('finfo')) {
            return [
                'valid' => false,
                'error' => 'เซิร์ฟเวอร์ไม่มี PHP fileinfo extension',
            ];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (
            !is_string($mime) ||
            !in_array($mime, self::ALLOWED_MIME_TYPES, true)
        ) {
            return [
                'valid' => false,
                'error' => 'ชนิดไฟล์ไม่รองรับหรือไม่ถูกต้อง',
            ];
        }

        return [
            'valid' => true,
            'mime' => $mime,
            'tmp_name' => $file['tmp_name'],
            'size' => $size,
        ];
    }

    public static function validateInvoiceData(array $data): array
    {
        $requiredFields = [
            'invoice_number',
            'invoice_date',
            'vendor',
            'currency',
            'subtotal',
            'tax',
            'total_amount',
        ];

        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (
                !array_key_exists($field, $data) ||
                $data[$field] === null ||
                $data[$field] === ''
            ) {
                $missingFields[] = $field;
            }
        }

        $errors = [];

        foreach (['subtotal', 'tax', 'total_amount'] as $field) {
            $value = $data[$field] ?? null;

            if (
                $value !== null &&
                $value !== '' &&
                !is_numeric($value)
            ) {
                $errors[] = "$field ต้องเป็นตัวเลขหรือ null";
            }
        }

        if (
            isset(
                $data['subtotal'],
                $data['tax'],
                $data['total_amount']
            ) &&
            is_numeric($data['subtotal']) &&
            is_numeric($data['tax']) &&
            is_numeric($data['total_amount'])
        ) {
            $expectedTotal =
                (float) $data['subtotal'] +
                (float) $data['tax'];

            if (
                abs(
                    $expectedTotal -
                    (float) $data['total_amount']
                ) > 0.02
            ) {
                $errors[] = 'ยอดรวมไม่ตรงกับ subtotal + tax';
            }
        }

        $modelMissing = $data['missing_fields'] ?? [];

        if (!is_array($modelMissing)) {
            $modelMissing = [];
        }

        $modelMissing = array_filter(
            $modelMissing,
            'is_string'
        );

        $missingFields = array_values(array_unique(
            array_merge($missingFields, $modelMissing)
        ));

        return [
            'is_valid' =>
                empty($errors) && empty($missingFields),

            'needs_review' =>
                !empty($errors) || !empty($missingFields),

            'errors' => $errors,
            'missing_fields' => $missingFields,
        ];
    }
}
