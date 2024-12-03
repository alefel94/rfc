<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfcViejo = $_POST['rfcViejo']; 
    $rfcNuevo = $_POST['rfcNuevo'];

    if (empty($rfcViejo) || empty($rfcNuevo)) {
        echo "<script>alert('Por favor, ingrese el RFC viejo y el RFC nuevo.');</script>";
        echo "<a href='javascript:history.back()'>Volver</a>";
        exit();
    }

    echo "<h2>Consultas generadas para cambiar RFC de $rfcViejo a $rfcNuevo:</h2>";

    $folderBase = '/home/sigpol/public_html/zapopan/lab/ESTRUCTURA_SELECCION_PERSONAL/';
    $folderOrigen = $folderBase . $rfcViejo;
    $folderDestino = $folderBase . $rfcNuevo;

    $servername = "sigpol.com";
    $username = "felipe";
    $password = "felipe2024";
    $dbname = "information_schema";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $sql = "SELECT 
            CONCAT('SELECT * FROM ', columns.table_schema, '.', columns.table_name, 
                ' WHERE ', columns.column_name, ' = \"', '$rfcViejo', '\";') AS select_query,
            CONCAT('UPDATE ', columns.table_schema, '.', columns.table_name, 
                ' SET ', columns.column_name, ' = \"', '$rfcNuevo', 
                '\" WHERE ', columns.column_name, ' = \"', '$rfcViejo', '\";') AS update_query,
            CONCAT('UPDATE ', columns.table_schema, '.', columns.table_name, 
                ' SET ', columns.column_name, ' = \"', '$rfcNuevo', 
                '\" WHERE ', pk.column_name, ' =') AS update_query2,
            pk.column_name as primary_key_column
        FROM 
            information_schema.columns
        JOIN 
            information_schema.tables 
            ON columns.table_schema = tables.table_schema 
            AND columns.table_name = tables.table_name
        JOIN 
            (SELECT 
                kcu.table_schema, 
                kcu.table_name, 
                kcu.column_name
            FROM 
                information_schema.key_column_usage AS kcu
            WHERE 
                kcu.constraint_name = 'PRIMARY' 
                AND kcu.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
            ) AS pk
            ON columns.table_schema = pk.table_schema 
            AND columns.table_name = pk.table_name
        WHERE 
            columns.data_type IN ('char', 'varchar', 'text') 
            AND columns.column_name LIKE '%rfc%'
            AND columns.table_schema NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
            AND tables.table_type = 'BASE TABLE';
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Consulta UPDATE</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $selectQuery = $row['select_query'];
            $updateQuery = $row['update_query'];
            $updateQuery2 = $row['update_query2'];
            $primaryKeyColumn = $row['primary_key_column'];

            // Ejecutar la sentencia SELECT para verificar existencia
            $selectResult = $conn->query($selectQuery);

            // Verificar si hay resultados
            if ($selectResult && $selectResult->num_rows > 0) {
                // Extraer las filas del resultado SELECT
                $selectData = $selectResult->fetch_all(MYSQLI_ASSOC);

                // Mostrar las filas extraídas del SELECT
                $value_primary = $selectData[0][$primaryKeyColumn];

                $updateQuery2 = $updateQuery2 . " " . $value_primary;

                echo "<tr><td><pre>" . $updateQuery . "</pre></td></tr>";
                echo "<tr><td><pre>" . $updateQuery2 . "</pre></td></tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron tablas que contengan el RFC viejo.</p>";
    }

    $conn->close();
    echo "<br><a href='javascript:history.back()'>Volver</a>";
}
?>