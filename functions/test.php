<?php
echo "POST recibido:<br>";
var_dump($_POST);
echo "<br><br>Archivo ejecutado correctamente en: " . __DIR__;
file_put_contents(__DIR__ . '/../test_result.txt', 'EJECUTADO: ' . date('Y-m-d H:i:s'));
exit;