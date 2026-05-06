<?php
// Подключаем ядро и пролог Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Промо-акция");

// 1. ТОТ ЖЕ САМЫЙ КЛЮЧ, что и в Python-генераторе
$SECRET_KEY = "my_super_secret_key_12345";

// Используем современное D7 API Битрикса для получения параметров из ссылки
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$data = $request->getQuery("data");
$sig = $request->getQuery("sig");

if (!$data || !$sig) {
    // Если параметров нет, выводим стандартную ошибку Битрикса
    ShowError("Неверный формат ссылки. Пожалуйста, отсканируйте QR-код с баннера заново.");
} else {
    // 2. Вычисляем правильную подпись для этих данных на PHP (HMAC-SHA256)
    $expected_sig_raw = hash_hmac('sha256', $data, $SECRET_KEY, true);

    // 3. Форматируем подпись в URL-safe Base64 (как это делал Python)
    // Заменяем + на - и / на _, а также убираем = в конце
    $expected_sig_b64 = rtrim(strtr(base64_encode($expected_sig_raw), '+/', '-_'), '=');

    // 4. Сравниваем подписи (hash_equals защищает от хакерских тайминг-атак)
    if (hash_equals($expected_sig_b64, $sig)) {

        // --- ПОДПИСЬ ВЕРНА! ---
        echo "<div style='padding: 20px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 5px;'>";
        echo "✅ Успех! Ссылка подлинная.<br>";
        echo "Вы пришли с баннера: <b>" . htmlspecialchars($data) . "</b>";
        echo "</div>";

        // ЗДЕСЬ ВАША БИЗНЕС-ЛОГИКА БИТРИКС:
        // Например:
        // - Показать скрытый промокод
        // - Записать переход в Highload-блок для статистики аналитиков
        // - Вывести форму регистрации с уже заполненным скрытым полем UTM-метки

    } else {
        // --- ОШИБКА: ДАННЫЕ ПОДДЕЛАНЫ ---
        // Отдаем статус 403 (Запрещено) для поисковиков и ботов
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->setStatus("403 Forbidden");

        ShowError("❌ Ошибка доступа: недействительная или поддельная ссылка. Данные были изменены.");
    }
}

// Подключаем эпилог Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>