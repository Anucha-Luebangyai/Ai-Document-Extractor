<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Document Extractor</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <main class="container">
        <header class="page-header">
            <div class="eyebrow">AI POWERED DOCUMENT PROCESSING</div>
            <h1>AI Document Extractor</h1>
            <p>
                อัปโหลดเอกสารใบแจ้งหนี้ แล้วให้ AI ช่วยสกัดข้อมูล
                และตรวจสอบความครบถ้วนของข้อมูล
            </p>
        </header>

        <section class="panel">
            <h2>อัปโหลดเอกสาร</h2>
            <p class="muted">
                รองรับ PDF, TXT, PNG, JPG และ WEBP
            </p>

            <form id="extractForm">
                <div class="upload-box" id="uploadBox">
                    <div class="upload-icon">↑</div>
                    <strong>ลากไฟล์มาวางที่นี่</strong>
                    <span>หรือเลือกไฟล์จากคอมพิวเตอร์</span>

                    <input
                        id="documents"
                        name="documents[]"
                        type="file"
                        accept=".pdf,.txt,.png,.jpg,.jpeg,.webp"
                        multiple
                        hidden
                    >

                    <button
                        type="button"
                        id="selectFiles"
                        class="button button-secondary"
                    >
                        เลือกไฟล์
                    </button>

                    <p class="hint">
                        สูงสุด 1 ไฟล์ต่อครั้ง · ไม่เกิน 10 MB ต่อไฟล์
                    </p>
                </div>

                <div id="fileList" class="file-list" aria-live="polite"></div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="button button-primary"
                >
                    วิเคราะห์เอกสารทั้งหมด
                </button>
            </form>
        </section>

        <section class="results-section">
            <div class="section-heading">
                <h2>ผลการวิเคราะห์</h2>
                <span class="muted">ผลลัพธ์แยกตามไฟล์</span>
            </div>

            <div id="results" aria-live="polite">
                <div class="empty-state">
                    ผลการวิเคราะห์จะแสดงที่นี่
                </div>
            </div>
        </section>
        <section class="notice-panel">
            <div class="notice-title">หมายเหตุการใช้งาน</div>
            <p>
                โปรเจกต์นี้ใช้บริการโฮสต์เว็บไซต์และ AI API ในรูปแบบฟรี
                จึงอาจมีข้อจำกัดด้านประสิทธิภาพ ทำให้การประมวลผลล่าช้า
                หรือเกิดข้อผิดพลาดได้ในบางครั้ง ขึ้นอยู่กับสถานะของเซิร์ฟเวอร์
                โควตาการใช้งาน และความพร้อมให้บริการของ AI API
            </p>
        </section>
        <footer class="page-footer">
            <p>AI Document Extractor · Personal Portfolio Project</p>
            <p>ตรวจสอบข้อมูลจากเอกสารต้นฉบับก่อนนำไปใช้งานจริง</p>
        </footer>
    </main>
</body>
</html>