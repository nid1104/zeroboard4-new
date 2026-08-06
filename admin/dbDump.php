<?php
if (!defined('_ZB_PATH')) exit();

function zbDB_getFields($tableName) {
    global $connect;

    $result = $connect->all("show fields from " . $connect->escapeIdentifier($tableName), array(), false);
    $query = "";
    foreach ($result as $data) {
        $field = $connect->escapeIdentifier($data["Field"]);
        $type = " " . $data["Type"];
        if ($data["Null"] == "YES") $null = " null"; else $null = " not null";
        if ($data["Default"] !== null) $default = " default '" . $connect->escape($data["Default"]) . "'"; else $default = "";
        $extra = " " . $data["Extra"];
        if ($data["Key"] == "PRI") $key = " primary key"; else $key = "";
        $query .= "    " . $field . $type . $null . $default . $extra . $key . ",\n";
    }
    return $query;
}

function zbDB_getKeys($tableName) {
    global $connect;

    $result = $connect->all("show keys from " . $connect->escapeIdentifier($tableName));
    $query = "";
    $i = 0;
    $toggle_name = "";
    foreach ($result as $data) {
        if ($data["Key_name"] != "PRIMARY") {
            $key_name = $data["Key_name"];
            $column_name = $data["Column_name"];
            if ($toggle_name != $key_name) {
                if ($toggle_name) $query .= "),\n";
                $query .= "    KEY " . $connect->escapeIdentifier($key_name) . " (" . $connect->escapeIdentifier($column_name);
                $toggle_name = $key_name;
            } else {
                if ($toggle_name) {
                    $query .= "," . $connect->escapeIdentifier($column_name);
                }
            }
        }
    }
    if ($toggle_name && $toggle_name == $key_name) $query .= "),\n";
    return $query;
}

function zbDB_getSchema($tableName) {
    global $connect;

    $fields = zbDB_getFields($tableName);
    $key = zbDB_getKeys($tableName);
    $schema = $fields . "\n" . $key;
    $schema = substr($schema, 0, strlen($schema) - 2);
    $schema = "\nCREATE TABLE " . $connect->escapeIdentifier($tableName) . " ( \n" . $schema . " \n) ENGINE=MyISAM; ";
    echo $schema;
    flush();
}

function zbDB_getDataList($tableName) {
    global $connect;

    $result = $connect->all("show fields from " . $connect->escapeIdentifier($tableName));
    $cols = array();
    $escaped = array();

    foreach ($result as $data) {
        $cols[] = $data["Field"];
        $escaped[] = $connect->escapeIdentifier($data["Field"]);
    }
    $field = implode(",", $escaped);
    $field_array = $cols;
    $field_count = count($field_array);

    $query = "\n";
    $result = $connect->all("select $field from " . $connect->escapeIdentifier($tableName), array(), false);
    foreach ($result as $data) {
        $vals = array();
        for ($i = 0; $i < $field_count; $i++) {
            $vals[] = ($data[$field_array[$i]] === null) ? "NULL" : " '" . $connect->escape($data[$field_array[$i]]) . "'";
        }
        $str = implode(",", $vals);
        echo "INSERT INTO " . $connect->escapeIdentifier($tableName) . " VALUES (" . $str . ");\n";
        flush();
    }
}

function zbDB_down($tableName) {
    echo "\n#\n# '$tableName' structure \n#\n";
    zbDB_getSchema($tableName);
    echo "\n";
    echo "\n#\n# '$tableName' data \n#\n";
    zbDB_getDataList($tableName);
    echo "\n# End of $tableName Dump\n";
    flush();
}

function zbDB_All_down($dbname) {
    global $connect;

    $result = $connect->all("show table status from " . $connect->escapeIdentifier($dbname) . " like 'zetyx%'");
    $i = 0;
    foreach ($result as $dbData) {
        $tableName = $dbData['Name'];
        echo "\n\n";
        zbDB_down($tableName);
    }
}

function zbDB_Header($filename) {
    if (preg_match("/msie/i", Request::header('User-Agent'))) $browser = 1;
    else $browser = 0;

    Response::header("Content-Type", "application/octet-stream");
    if ($browser) {
        Response::header("Content-Disposition", "attachment; filename=\"{$filename}\"");
        Response::header("Expires", "0");
        Response::header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        Response::header('Pragma', 'public');
    } else {
        Response::header("Content-Disposition", "attachment; filename=\"{$filename}\"");
        Response::header("Expires", "0");
        Response::header("Pragma", "public");
    }
}
?>
