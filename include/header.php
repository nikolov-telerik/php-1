<!DOCTYPE html>
<html>
    <head>
        <title>Telerik Expenses</title>
        <style type="text/css">
            /* Just simple CSS */

            html, body {
                padding: 0px; margin: 0px;
                font-size: 12px;
                color: #111;
                font-family: Arial;
            }
            body {
                padding: 0px 20px;
            }

            form {
                margin: 10px 0px;
            }
            input {
                width: 150px;
                padding: 2px 5px;
            }
            select {
                width: 164px;
                padding: 2px 5px;
            }
            button {
                padding: 3px 5px;
            }

            a, a:active {
                text-decoration: none;
                color: #111;
                font-weight: bold;
            }
            a:hover {
                text-decoration: underline;
                color: #111;
            }

            table tr td {
                padding: 5px 10px;
                border: 1px solid #ccc;
            }
        </style>
    </head>

    <body>
        <?php
            // Require the functions file in each script.
            require_once('./include/functions.php');
        ?>
