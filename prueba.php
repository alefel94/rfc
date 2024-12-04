<?php
// Verificar si los datos fueron enviados por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los valores del RFC viejo y nuevo
    $rfcViejo = $_POST['rfcViejo'];
    $rfcNuevo = $_POST['rfcNuevo'];

    // Validar que ambos campos no estén vacíos
    if (empty($rfcViejo) || empty($rfcNuevo)) {
        echo "Por favor, ingrese el RFC viejo y el RFC nuevo.";
        exit();
    }

    echo "Consultas generadas para cambiar RFC de $rfcViejo a $rfcNuevo:\n";

    // Ruta base para las carpetas
    $folderBase = '/home/sigpol/public_html/zapopan/lab/ESTRUCTURA_SELECCION_PERSONAL/';
    $folderOrigen = $folderBase . $rfcViejo;
    $folderDestino = $folderBase . $rfcNuevo;

    // Datos de conexión a la base de datos
    $servername = "sigpol.com";
    $username = "felipe";
    $password = "felipe2024";
    $dbname = "information_schema";

    // Crear la conexión a MySQL
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar si la conexión fue exitosa
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Consulta SQL para obtener las tablas y columnas que contienen el RFC viejo
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
                AND tables.table_type = 'BASE TABLE';";

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Si se encuentran resultados, procesar las consultas generadas
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $selectQuery = $row['select_query'];
            $updateQuery = $row['update_query'];
            $updateQuery2 = $row['update_query2'];
            $primaryKeyColumn = $row['primary_key_column'];

            // Ejecutar la sentencia SELECT para verificar existencia del RFC viejo
            $selectResult = $conn->query($selectQuery);

            // Verificar si hay resultados del SELECT
            if ($selectResult && $selectResult->num_rows > 0) {
                // Extraer las filas del resultado SELECT
                $selectData = $selectResult->fetch_all(MYSQLI_ASSOC);

                // Obtener el valor de la columna clave primaria
                $value_primary = $selectData[0][$primaryKeyColumn];

                // Completar la consulta UPDATE con el valor de la clave primaria
                $updateQuery2 = $updateQuery2 . " " . $value_primary;

                // Mostrar las consultas generadas
                echo "Consulta UPDATE:\n";
                echo $updateQuery . "\n";
                echo $updateQuery2 . "\n";
            }
        }
    } else {
        echo "No se encontraron tablas que contengan el RFC viejo.\n";
    }

    // Cerrar la conexión a la base de datos
    $conn->close();
} else {
    echo "No se enviaron datos por POST.\n";
}
?>
