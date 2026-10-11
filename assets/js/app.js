
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("#extractForm");
    const fileInput = document.querySelector("#documents");
    const selectButton = document.querySelector("#selectFiles");
    const uploadBox = document.querySelector("#uploadBox");
    const fileList = document.querySelector("#fileList");
    const submitButton = document.querySelector("#submitBtn");
    const results = document.querySelector("#results");

    if (
        !form ||
        !fileInput ||
        !selectButton ||
        !fileList ||
        !submitButton ||
        !results
    ) {
        console.error(
            "ไม่พบองค์ประกอบของระบบอัปโหลด กรุณาตรวจสอบ index.html"
        );
        return;
    }

    let selectedFile = null;
    let isProcessing = false;

    // เลือกไฟล์ได้ครั้งละ 1 ไฟล์
    fileInput.multiple = false;

    // เปิดหน้าต่างเลือกไฟล์
    selectButton.addEventListener("click", () => {
        if (!isProcessing) {
            fileInput.click();
        }
    });

    // เลือกไฟล์ใหม่เพื่อแทนที่ไฟล์เดิม
    fileInput.addEventListener("change", () => {
        const file = fileInput.files?.[0];

        if (!file) return;

        selectedFile = file;

        // ล้าง input แต่เก็บไฟล์ไว้ใน selectedFile
        // เพื่อให้เลือกไฟล์เดิมซ้ำได้
        fileInput.value = "";

        renderFile();
    });

    // แสดงไฟล์ที่เลือก
    function renderFile() {
        fileList.replaceChildren();

        if (!selectedFile) return;

        const row = document.createElement("div");
        row.className = "file-row";

        const name = document.createElement("span");
        name.className = "file-name";
        name.textContent =
            `${selectedFile.name} (${formatSize(selectedFile.size)})`;

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "button remove-file";
        removeButton.textContent = "ลบไฟล์";
        removeButton.disabled = isProcessing;

        removeButton.addEventListener("click", () => {
            if (isProcessing) return;

            selectedFile = null;
            fileInput.value = "";
            renderFile();
        });

        row.append(name, removeButton);
        fileList.appendChild(row);
    }

    function formatSize(bytes) {
        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }

    // รองรับการลากไฟล์มาวาง
    if (uploadBox) {
        uploadBox.addEventListener("dragover", (event) => {
            event.preventDefault();

            if (!isProcessing) {
                uploadBox.classList.add("dragover");
            }
        });

        uploadBox.addEventListener("dragleave", () => {
            uploadBox.classList.remove("dragover");
        });

        uploadBox.addEventListener("drop", (event) => {
            event.preventDefault();
            uploadBox.classList.remove("dragover");

            if (isProcessing) return;

            const file = event.dataTransfer?.files?.[0];

            if (file) {
                selectedFile = file;
                fileInput.value = "";
                renderFile();
            }
        });
    }

    // แสดงผลการวิเคราะห์
    function showResult(fileName, data, success) {
        const emptyState = results.querySelector(".empty-state");

        if (emptyState) {
            results.replaceChildren();
        }

        let resultList = results.querySelector(".result-list");

        if (!resultList) {
            resultList = document.createElement("div");
            resultList.className = "result-list";
            results.appendChild(resultList);
        }

        const card = document.createElement("article");
        card.className = "result-card";

        const header = document.createElement("div");
        header.className = "result-card-header";

        const title = document.createElement("h3");
        title.className = "result-title";
        title.textContent = fileName;

        const status = document.createElement("span");
        status.className = success
            ? "status status-success"
            : "status status-error";
        status.textContent = success
            ? "วิเคราะห์สำเร็จ"
            : "เกิดข้อผิดพลาด";

        header.append(title, status);
        card.appendChild(header);

        if (success) {
            const output = document.createElement("pre");
            output.textContent = JSON.stringify(
                data.data ?? data,
                null,
                2
            );
            card.appendChild(output);
        } else {
            const message = document.createElement("p");
            message.textContent = data;
            card.appendChild(message);
        }

        resultList.prepend(card);
    }

    // ส่งไฟล์ให้ AI วิเคราะห์
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (isProcessing) return;

        if (!selectedFile) {
            alert("กรุณาเลือกไฟล์ก่อนวิเคราะห์");
            return;
        }

        const fileToUpload = selectedFile;

        if (fileToUpload.size > 10 * 1024 * 1024) {
            alert("ไฟล์ต้องมีขนาดไม่เกิน 10 MB");
            return;
        }

        const formData = new FormData();

        // สำคัญ: PHP ใน api/extract.php รับชื่อ document
        formData.append(
            "document",
            fileToUpload,
            fileToUpload.name
        );

        // ยังไม่ล้างไฟล์ตรงนี้ ให้คงไว้ระหว่างประมวลผล
        isProcessing = true;
        submitButton.disabled = true;
        selectButton.disabled = true;
        submitButton.textContent = "กำลังวิเคราะห์...";

        renderFile();

        try {
            const response = await fetch("api/extract.php", {
                method: "POST",
                body: formData
            });

            let result;

            try {
                result = await response.json();
            } catch {
                throw new Error(
                    `เซิร์ฟเวอร์ตอบกลับไม่ใช่ JSON (HTTP ${response.status})`
                );
            }

            if (!response.ok || result.success !== true) {
                throw new Error(
                    result.error ||
                    `วิเคราะห์ไม่สำเร็จ (HTTP ${response.status})`
                );
            }

            showResult(fileToUpload.name, result, true);

        } catch (error) {
            console.error("Document extraction error:", error);

            showResult(
                fileToUpload.name,
                error.message || "ส่งไฟล์ให้ AI ไม่สำเร็จ",
                false
            );

        } finally {
            // ล้างไฟล์หลัง AI ตอบกลับและการประมวลผลสิ้นสุด
            selectedFile = null;
            fileInput.value = "";
            renderFile();

            isProcessing = false;
            submitButton.disabled = false;
            selectButton.disabled = false;
            submitButton.textContent = "วิเคราะห์เอกสารทั้งหมด";
        }
    });
});
