<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>目前叫號狀態</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table {
            width: 60%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        /* 讓按鈕置中 */
        .center-button {
            text-align: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>


    <h2 style="text-align:center;">目前叫號狀態</h2>

    <table>
        <tr>
            <th>病人姓名</th>
            <th>治療師</th>
            <th>時段</th>
        </tr>
        <tr id="callRow">
            <td id="patient_name">載入中...</td>
            <td id="therapist">載入中...</td>
            <td id="shifttime">載入中...</td>
        </tr>
    </table>

    <!-- 讓返回按鈕置中 -->
    <div class="center-button">
        <a href="h_numberpeople.php">
            <button>返回</button>
        </a>

    </div>


    <script>
        $(document).ready(function () {
            function fetchCallStatus() {
                $.ajax({
                    url: "取得正在叫號的病人.php",
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        if (data.length > 0) {
                            $("#patient_name").text(data[0].patient_name || "無資料");
                            $("#therapist").text(data[0].therapist || "無資料");
                            $("#shifttime").text(data[0].shifttime || "無資料");
                        } else {
                            $("#patient_name").text("目前無叫號中");
                            $("#therapist").text("-");
                            $("#shifttime").text("-");
                        }
                    },
                    error: function () {
                        $("#patient_name").text("載入失敗");
                        $("#therapist").text("-");
                        $("#shifttime").text("-");
                    }
                });
            }

            fetchCallStatus();
            setInterval(fetchCallStatus, 5000); // 每 5 秒更新
        });
    </script>

    </body>

</html>