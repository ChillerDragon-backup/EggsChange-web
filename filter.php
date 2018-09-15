<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("left.css", "EggsChange - filter options");

if ($_SESSION['IsLogged'] != "online")
{
    echo "<a>you have to be logged in.</a></br>";
    echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
    fok();
}

CheckAccountState($_SESSION['Username']);

$filter_options = "";
$rank_filter = "";
$cat_filter = "";
$IsFilter = false;
if (!empty($_GET['rank_filter']))
{
    $IsFilter = true;
    $rank_filter = $_GET['rank_filter'];
}
if (!empty($_GET['cat_filter']))
{
    $IsFilter = true;
    $cat_filter = $_GET['cat_filter'];
}

echo "<h1>Wishes filter</h1>";


echo "<form class=\"form\"><input type=\"button\" value=\"Rank by Points\" onclick=\"window.location.href='filter.php?rank_filter=points'\"/></form>";
echo "<form class=\"form\"><input type=\"button\" value=\"Rank by Newest\" onclick=\"window.location.href='filter.php?rank_filter=time_new'\"/></form>";
echo "<form class=\"form\"><input type=\"button\" value=\"Rank by Oldest\" onclick=\"window.location.href='filter.php?rank_filter=time_old'\"/></form>";

if ($IsFilter)
    $filter_options = "?rank_filter=" . $rank_filter . "&cat_filter=" . $cat_filter;
echo "<form class=\"form\"><input type=\"button\" value=\"Done\" onclick=\"window.location.href='fullfillwish.php$filter_options'\"/></form>";
fok();
?>

