<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;

// Подключаем модули
Loader::includeModule("iblock");

// Соединение с базой данных
$connection = Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

// SQL-запрос к таблице
$sql = "
    SELECT ct.id, ct.name AS custom_name, 
           p.NAME AS product_name, 
           p.IBLOCK_ID, 
           c.NAME AS category_name 
    FROM custom_table ct
    LEFT JOIN b_iblock_element p ON ct.infoblock_element_id = p.ID
    LEFT JOIN b_iblock_element c ON p.IBLOCK_SECTION_ID = c.ID
";
$result = $connection->query($sql);

// Вывод данных
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Custom Name</th><th>Product</th><th>Category</th></tr>";
while ($row = $result->fetch()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['custom_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
    echo "</tr>";
}
echo "</table>";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
