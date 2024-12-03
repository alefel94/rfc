<?php

$rfc = "";

if ($argc > 1) 
{
    $rfc = $argv[1]; // Primer parámetro pasado al script
    echo "El parámetro recibido es: $rfc\n";
} 
else 
{
    return null;
}



$servername = "localhost"; // Cambia a tu servidor de base de datos
$username = "xx"; // Cambia a tu usuario de base de datos
$password = "yy"; // Cambia a tu contraseña de base de datos
$dbname = "information_schema"; // Base de datos de metadatos


// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los subqueries y contar el total de tablas
$sql = "SELECT COUNT(*) AS total_queries FROM information_schema.columns
        JOIN information_schema.tables ON columns.table_schema = tables.table_schema AND columns.table_name = tables.table_name
        WHERE data_type IN ('char', 'varchar', 'text')
        AND columns.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
        AND tables.table_type = 'BASE TABLE'";

$totalResult = $conn->query($sql);
$totalQueries = ($totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['total_queries'] : 0;

$sql = "SELECT CONCAT('SELECT \"', columns.table_schema, '.', columns.table_name, '.', columns.column_name, '\" AS table_column, \"', columns.table_schema, '\" AS database_name FROM ', columns.table_schema, '.', columns.table_name, ' WHERE ', columns.column_name, ' LIKE \"".$rfc."%\"')
        AS search_query
		
        FROM information_schema.columns
        JOIN information_schema.tables ON columns.table_schema = tables.table_schema AND columns.table_name = tables.table_name
        WHERE data_type IN ('char', 'varchar', 'text')
        AND columns.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
        AND tables.table_type = 'BASE TABLE'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $currentQuery = 0; // Contador de consultas ejecutadas
    echo "Total de consultas a ejecutar: $totalQueries\n\n";

    while ($row = $result->fetch_assoc()) {
        $currentQuery++;
        $subquery = $row['search_query'];
        $subresult = $conn->query($subquery);

        if ($subresult && $subresult->num_rows > 0) {
            echo "Coincidencia encontrada en la consulta ($currentQuery/$totalQueries): " . $subquery . "\n";
            while ($subrow = $subresult->fetch_assoc()) {
                print_r($subrow);
            }
        } else {
           // echo "No se encontró coincidencia en la consulta ($currentQuery/$totalQueries).\n";
        }
    }
} else {
    echo "No se encontraron consultas para ejecutar.";
}

// Cerrar la conexión
$conn->close();

?>

