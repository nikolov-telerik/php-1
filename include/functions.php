<?php

// Define the path to the database once to be able to change it easy.
define('DB_PATH', './db.txt');

// A global array containing all expense types. These indexes MUST NOT BE CHANGED after the database is already initiated.
$categories = array(
    1 => 'Food',
    2 => 'Rent',
    3 => 'Clothes',
    4 => 'Bills',
    5 => 'Entertainment',
    6 => 'Other'
);

// A global array containing the database. This variable is like 'cache'.
// If the readDb function is called more than once it will read from this variable instead of the file again.
// This saves time (performance-wise).
$db = null;

/**
 * Write a new line to the DB file.
 * This function returns false on failure or positive integer on success.
 *
 * @param $data
 * @param bool $append To append to the file or rewrite the whole file.
 * @return bool|int
 */
function writeDb($data, $append = true) {
    global $db;
    $result = false;

    if ($append) {
        // Initiate the global cache variable if it is not already.
        if ($db === null || !is_array($db)) {
            $db = array();
        }

        // Add the new data to the cache.
        $db[] = $data;

        // `file_put_contents` returns false on failure or the bytes written on success.
        $result = file_put_contents(DB_PATH, implode(';;', $data) . "\n", FILE_APPEND);
    } else {
        // Rewrite the cache variable with the new data.
        $db = $data;

        $newData = '';
        foreach ($data as $index => $row) {
            $newData .= $row['category'] . ';;' . $row['name'] . ';;' . $row['amount'] . ';;' . $row['date'] . ';;' . "\n";
        }

        // `file_put_contents` returns false on failure or the bytes written on success.
        $result = file_put_contents(DB_PATH, $newData);
    }

    return $result;
}

/**
 * Return array of all rows in the DB file ready for use.
 *
 * @return array|bool
 */
function readDb() {
    $parsedData = array();

    if (!file_exists(DB_PATH) || !is_file(DB_PATH) || !is_readable(DB_PATH)) {
        // On missing file return empty array.
        return $parsedData;
    }

    // Read from cache variable if it is already holding the data.
    global $db;
    if ($db !== null && is_array($db)) {
        return $db;
    }

    // `file` also returns false on failure.
    $data = file(DB_PATH);
    if (!$data) {
        // Write the empty array in the cache variable.
        $db = $parsedData;
        return $parsedData;
    }

    // Parse the data now.
    foreach ($data as $row) {
        $tmp = explode(';;', $row);
        $parsedData[] = array(
            'category' => $tmp[0],
            'name' => $tmp[1],
            'amount' => $tmp[2],
            'date' => $tmp[3]
        );
    }

    // Write the data in the cache variable.
    $db = $parsedData;

    return $parsedData;
}

/**
 * Add a new expense.
 *
 * @param string $category
 * @param string $name
 * @param float $amount
 * @param string|null $date
 * @return bool|int
 */
function addExpense($category, $name, $amount, $date = null) {
    global $categories;

    if ($category !== null && !isset($categories[$category])) return false;
    if (strlen($name) < 3) return false;
    $amount = (float) $amount;
    if ($amount <= 0) return false;

    // Format the date as we want to ensure we got the right string for comparison.
    if ($date) $date = formatDate($date);
    if (!$date) {
        // If date does not exist use the current date.
        $date = date("Y-m-d");
    }

    // Crete the expense as an array.
    $expense = array(
        'category' => $category,
        'name' => $name,
        'amount' => $amount,
        'date' => $date
    );

    // Write the new expense to the database file.
    return writeDb($expense);
}

/**
 * Delete an expense.
 *
 * @param int $index
 * @return bool
 */
function deleteExpense($index) {
    // Get the full db first.
    $expenses = readDb();

    if (isset ($expenses[$index])) {
        // Delete the index if it exists in DB.
        unset($expenses[$index]);
    } else {
        // Return false if index is missing.
        return false;
    }

    // Now rewrite the full db. (No FILE_APPEND flag).
    writeDb($expenses, false);
}

/**
 * Update an expense.
 * All parameters can be null (or skipped) if there is no change.
 *
 * @param int $index
 * @param null|string $category
 * @param null|string $name
 * @param null|float $amount
 * @param null|string $date
 * @return bool|int
 */
function updateExpense($index, $category = null, $name = null, $amount = null, $date = null) {
    // Get the full db first.
    $expenses = readDb();

    if (isset ($expenses[$index])) {
        // Change all changeable parameters
        if ($category !== null && $expenses[$index]['category'] != $category) {
            global $categories;

            if (isset($categories[$category])) $expenses[$index]['category'] = $category;
        }
        if ($name !== null && $expenses[$index]['name'] != $name) {
            if (strlen($name) >= 3) $expenses[$index]['name'] = $name;
        }
        if ($amount !== null && $expenses[$index]['amount'] != $amount) {
            $amount = (float) $amount;
            if ($amount > 0) $expenses[$index]['amount'] = $amount;
        }
        if ($date !== null && $expenses[$index]['date'] != $date) {
            // Format the date as we want to ensure we got the right string for comparison.
            $date = formatDate($date);
            if ($date) $expenses[$index]['date'] = $date;
        }
    } else {
        // Return false if index is missing.
        return false;
    }

    // Now rewrite the full db. (No FILE_APPEND flag).
    return writeDb($expenses, false);
}

/**
 * Get a single expense
 *
 * @param $index
 * @return array|null
 */
function getSingleExpense($index) {
    // Get the full db first.
    $expenses = readDb();
    if (isset ($expenses[$index])) {
        return $expenses[$index];
    }

    return null;
}

/**
 * Get all expenses filtered by date or category.
 *
 * @param null|string $category
 * @param null|string $startDate
 * @param null|string $endDate
 * @return array|bool
 */
function getExpenses($category = null, $startDate = null, $endDate = null) {
    global $categories;

    // Get the full db first.
    $expenses = readDb();

    // Validate filters.
    if ($category !== null && !isset($categories[$category])) $category = null;
    if ($startDate !== null) $startDate = formatDate($startDate);
    if ($endDate !== null) $endDate = formatDate($endDate);

    // Foreach all expenses and find the ones to remove by the filter.
    foreach ($expenses as $index => $expense) {
        if (
            ($category !== null && $expense['category'] != $category) ||
            ($startDate !== null && strtotime($expense['date']) < strtotime($startDate)) ||
            ($endDate !== null && strtotime($expense['date']) > strtotime($endDate))) {
            unset ($expenses[$index]);
        }
    }

    return $expenses;
}

/**
 * Format a date to YYYY-MM-DD.
 * Return null if date is invalid.
 *
 * @param string $date
 * @return string|null
 */
function formatDate($date) {
    // `strtotime` will return the seconds from the beginning of time to this date or false on failure.
    $time = strtotime($date);
    if ($time && $time > 100000) {
        // This date is valid. Format it as we need it.
        return date("Y-m-d", $time);
    }

    return null;
}

?>