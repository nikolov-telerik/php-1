<?php

// Require the header on top.
require_once('./include/header.php');

// Validate action
$action = null;
if (isset ($_GET['action'])) {
    $action = $_GET['action'];
    if ($action != 'add' && $action != 'change' && $action != 'delete') {
        $action = null;
    }
}
if (!$action) die("No such action.");

// If there is an index defined, validate it. Exit on error.
$expense = null;
if (isset ($_GET['index'])) {
    $expense = getSingleExpense($_GET['index']);
    $expenseIndex = $_GET['index'];
}
if ($action != 'add' && !$expense) {
    // Redirect to index.php
    header("Location: index.php");
    exit;
}

?>

<?php if ($action == 'add'): // Add expense ?>

        <h1>Add expense</h1>
        <?php
        if (isset ($_GET['save']) && $_GET['save']) {
            // Get all input data or NULL if not set.
            $name = isset($_POST['name']) ? $_POST['name'] : null;
            $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
            $category = isset($_POST['category']) ? $_POST['category'] : null;
            $date = isset($_POST['date']) ? $_POST['date'] : null;

            // Add the expense here.
            $added = addExpense($category, $name, $amount, $date);
            if ($added) {
                echo "<h4>Successfully added.</h4>";
            } else {
                echo "<h4>Error. Invalid data.</h4>";
            }
        }
        ?>

        <form method="POST" action="expense.php?action=add&save=1">
            <input type="text" name="name" placeholder="Name" value="<?php if (isset($_POST['name'])) echo $_POST['name']; ?>"><br>
            <input type="text" name="amount" placeholder="Amount" value="<?php if (isset($_POST['amount'])) echo $_POST['amount']; ?>"><br>
            <select name="category">
                <option>Category</option>
                <?php
                    foreach ($categories as $idx => $category)
                        echo "<option value=\"$idx\"" . (isset ($_POST) && $idx == $_POST['category'] ? ' selected="selected"' : '') . ">$category</option>";
                ?>
            </select><br>
            <input type="text" name="date" placeholder="Date" value="<?php if (isset($_POST['date'])) echo $_POST['date']; ?>"><br><br>

            <button type="submit">Add expense</button>
            <a href="index.php">All expenses</a>
        </form>

<?php elseif ($action == 'change'): // Change expense ?>

        <h1>Edit expense #<?php echo $expenseIndex + 1; ?></h1>
        <?php
        if (isset ($_GET['save']) && $_GET['save']) {
            // Get all input data or NULL if not set.
            $name = isset($_POST['name']) ? $_POST['name'] : null;
            $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
            $category = isset($_POST['category']) ? $_POST['category'] : null;
            $date = isset($_POST['date']) ? $_POST['date'] : null;

            // Update the expense.
            $saved = updateExpense($expenseIndex, $category, $name, $amount, $date);

            // Get the expense again after editing.
            $expense = getSingleExpense($expenseIndex);
            if ($saved) {
                echo "<h4>Successfully saved.</h4>";
            } else {
                echo "<h4>Error. Invalid data.</h4>";
            }
        }
        ?>

        <form method="POST" action="expense.php?action=change&save=1&index=<?php echo $expenseIndex; ?>">
            <input type="text" name="name" placeholder="Name" value="<?php echo $expense['name']; ?>"><br>
            <input type="text" name="amount" placeholder="Amount" value="<?php echo $expense['amount']; ?>"><br>
            <select name="category">
                <?php
                    foreach ($categories as $idx => $category)
                        echo "<option value=\"$idx\"" . ($idx == $expense['category'] ? ' selected="selected"' : '') . ">$category</option>";
                ?>
            </select><br>
            <input type="text" name="date" placeholder="Date" value="<?php echo $expense['date']; ?>"><br><br>

            <button type="submit">Save expense</button>
            <a href="index.php">All expenses</a>
        </form>

<?php elseif ($action == 'delete'): // Delete expense (PHP only)

    // Delete the expense here.
    deleteExpense($expenseIndex);

    // Redirect to the browser referrer (usually index.php).
    header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'));

endif;

// Require the footer in the end.
require_once('./include/footer.php');

?>