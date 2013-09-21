<?php

// Require the header on top.
require_once('./include/header.php');

// Declare both filters here.
$category = null;
$startDate = null;
$endDate = null;

// Check if filters are defined by the user. Use isset to prevent notices.
if (isset ($_GET['category'])) $category = $_GET['category'];
if (isset ($_GET['start_date'])) $startDate = $_GET['start_date'];
if (isset ($_GET['end_date'])) $endDate = $_GET['end_date'];

// Get all filtered expenses.
$expenses = getExpenses($category, $startDate, $endDate);

?>

<h1>Expenses</h1>

<a href="expense.php?action=add">Add new expense</a>
<form method="GET" action="">
    <select name="category">
        <option value="">Category</option>
        <?php
        foreach ($categories as $idx => $category)
            echo "<option value=\"$idx\"" . (isset($_GET['category']) && $idx == $_GET['category'] ? ' selected="selected"' : '') . ">$category</option>";
        ?>
    </select>
    <input name="start_date" placeholder="Start date" style="width: 110px;" value="<?php if (isset ($_GET['start_date'])) echo $_GET['start_date']; ?>">
    <input name="end_date" placeholder="End date" style="width: 110px;" value="<?php if (isset ($_GET['end_date'])) echo $_GET['end_date']; ?>">
    <button type="submit">Filter</button>
</form>

<?php if (empty($expenses)): // Show a message if there are no expenses for these filters. ?>

<h3>There are no expenses for these filters.</h3>

<?php else: ?>

<table cellspacing="0" cellpadding="0">
    <?php
        $total = 0;

        // Print expenses table and calculate the total.
        foreach ($expenses as $index => $expense) {
            $total += $expense['amount'];
            echo   "<tr><td>" . $expense['date']
                . "</td><td>" . $expense['name']
                . "</td><td>$" . number_format($expense['amount'], 2)
                . "</td><td>" . $categories[$expense['category']]
                . "</td><td><a href=\"expense.php?action=change&index=$index\">change</a> / <a href=\"expense.php?action=delete&index=$index\">delete</a>"
                . "</td></tr>";
        }
        echo   "<tr><td style='text-align:right;' colspan='2'><b>Total:</b>"
            . "</td><td>$" . number_format($expense['amount'], 2)
            . "</td><td colspan='2'>&nbsp;</td></tr>";
    ?>
</table>

<?php

endif;

// Require the footer in the end.
require_once('./include/footer.php');

?>