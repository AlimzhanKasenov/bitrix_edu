<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt';
file_put_contents($logFile, "=== [templates.php] Загрузка шаблона ===\n", FILE_APPEND);
file_put_contents($logFile, "[templates.php] arResult: " . print_r($arResult, true) . "\n", FILE_APPEND);

// Тестовый вывод
echo "<p style='color: green;'>Шаблон успешно подключен!</p>";
?>

<div>
    <h2>Список данных из инфоблока</h2>
    <?php if (!empty($arResult['ITEMS'])): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <tr>
                    <td><?= htmlspecialcharsbx($item['ID']) ?></td>
                    <td><?= htmlspecialcharsbx($item['PROPERTY_FIO_VALUE']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Данные отсутствуют.</p>
    <?php endif; ?>
</div>
