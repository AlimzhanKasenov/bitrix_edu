<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php
// Логируем загрузку шаблона
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Загрузка шаблона otus.grid ===\n", FILE_APPEND);
?>

<div style="margin: 20px; font-size: 16px; color: green;">
    <?= htmlspecialcharsbx($arResult['TEST'] ?? 'Данных нет'); ?>
</div>

<?php
// Логируем завершение работы шаблона
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/lazyload_log.txt', "=== Завершение шаблона otus.grid ===\n", FILE_APPEND);
?>
