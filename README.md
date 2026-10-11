# AI Document Extractor

A PHP-based document extraction application powered by the Google Gemini API. The application extracts invoice information from uploaded documents, returns structured JSON data, and validates the extracted information.

## Features

- Upload one document at a time
- Support PDF, TXT, PNG, JPG, JPEG, and WEBP
- Extract invoice fields into JSON
- Validate required fields and invoice totals
- Identify missing fields and flag documents for review
- Handle file upload and API errors
- Keep API credentials on the server
- Display extraction results for each processed document
- Allow users to remove a selected file before uploading another

## Tech Stack

- PHP 8.1+
- HTML, CSS, and JavaScript
- Google Gemini API
- PHP cURL
- PHP Fileinfo

## Project Structure

- `index.html` - Web interface
- `assets/css/style.css` - Styles and layout
- `assets/js/app.js` - File selection, upload handling, and API requests
- `api/extract.php` - Document extraction API endpoint
- `api/lib/GeminiClient.php` - Gemini API integration
- `api/lib/DocumentValidator.php` - File and extracted data validation
- `api/lib/ResponseFormatter.php` - JSON response formatting

## Requirements

- PHP 8.1 or later
- PHP cURL, Fileinfo, and mbstring extensions
- A Google Gemini API key
- A modern web browser

## Configuration

Set the `GEMINI_API_KEY` environment variable before starting the PHP server.

Optionally, set `GEMINI_MODEL` to a model supported by your Gemini API account.

Keep API credentials on the server. Never commit API keys, passwords, or real customer documents to a public repository.

## Run Locally

Open a terminal in the project root directory.

Start PHP's built-in development server:

```bash
php -S localhost:8000
```

Open the application in your browser:

[http://localhost:8000](http://localhost:8000)

If you are using XAMPP on Windows, you can also start the PHP development server using the PHP executable included with XAMPP.

## Validation

The application performs preliminary validation of extracted invoice data, including:

- Required invoice fields
- Missing or empty values
- Numeric amounts
- Whether `total_amount` matches the subtotal plus tax

Validation results help identify potentially incomplete or inconsistent information. They do not guarantee that the extracted data is correct. Always compare the results with the original document before using the information.

## Limitations

- Documents are processed one at a time.
- Each document requires a separate Gemini API request.
- Processing time depends on the document size, network connection, API response time, and selected model.
- API usage may incur costs or be subject to rate limits and quotas.
- OCR and extraction quality depend on the model and the quality of the source document.
- The application is a portfolio prototype, not a production document management system.

## Privacy

Use synthetic or non-sensitive documents for testing whenever possible.

Do not upload confidential documents or sensitive customer information to a public demo. Review the applicable data handling and privacy policies before sending documents to an external AI service.

## Future Improvements

- Unit and integration tests
- CSV export
- Extraction confidence scores and a human review workflow
- Automated evaluation against labeled test data
- n8n workflow integration
- Improved error reporting and processing status feedback