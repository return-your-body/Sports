<?php
include 'db.php'; // 你的資料庫連線
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>助手 - 叫號系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>病人姓名</th>
                <th>治療師</th>
                <th>時段</th>
            </tr>
        </thead>
        <tbody id="calledPatientTable">
            <!-- 這裡會用 AJAX 取得叫號中的病人 -->
        </tbody>
    </table>
</div>

<script>
// 載入叫號中的病人
function loadCalledPatients() {
    fetch("取得正在叫號的病人.php")
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById("calledPatientTable");
            tableBody.innerHTML = "";

            if (data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="3" class="text-center">目前沒有叫號中的病人</td></tr>`;
                return;
            }

            data.forEach(row => {
                let tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${row.name}</td>
                    <td>${row.therapist}</td>
                    <td>${row.shift_time}</td>
                `;
                tableBody.appendChild(tr);
            });
        });
}

// 每 5 秒更新一次
loadCalledPatients();
setInterval(loadCalledPatients, 5000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
