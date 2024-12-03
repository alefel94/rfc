<?php

// Prefijo antiguo y nuevo
$oldPrefix = 'xxx';
$newPrefix = 'xx1';


// esta es la ruta real donde se guardan los archivos en sigpol
$folderPath = '/home/sigpol/public_html/zapopan/lab/ESTRUCTURA_SELECCION_PERSONAL/xxx'; // Cambia esto a la ruta de tu carpeta



// Verificar si la carpeta existe
if (!is_dir($folderPath)) {
    die("La carpeta especificada no existe.");
}

// Abrir la carpeta
$dir = opendir($folderPath);

if ($dir) {
    // Recorrer los archivos en la carpeta
    while (($file = readdir($dir)) !== false) 
    {
        // Verificar si es un archivo regular y si su nombre comienza con el prefijo antiguo
        if (is_file("$folderPath/$file") && strpos($file, $oldPrefix) === 0) 
        {
            // Generar el nuevo nombre de archivo
            $newFileName = str_replace($oldPrefix, $newPrefix, $file);
            
            // Renombrar el archivo
            if (rename("$folderPath/$file", "$folderPath/$newFileName")) {
                echo "Renombrado: $file a $newFileName\n";
            } else {
                echo "Error al renombrar: $file\n";
            }
        }
    }
    closedir($dir);
} else {
    echo "No se pudo abrir la carpeta.";
}
?>

