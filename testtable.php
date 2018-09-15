<?php
session_start();
require_once(__DIR__ . "/global.php");
HtmlHeader("login.css", "EggsChange - fullfill a wish");

if ($_SESSION['IsLogged'] != "online")
{
    echo "<a>you have to be logged in.</a></br>";
    echo "<form><input type=\"button\" value=\"Login\" onclick=\"window.location.href='login.php'\"/></form>";
    fok();
}

CheckAccountState($_SESSION['Username']);

echo "<h1>Open Wishes</h1>";


function GetTotalPages($wishes_per_page, $cond1)
{
    $SQL_pages_base = "SELECT COUNT(*) AS TotalPages FROM Wishes WHERE ifnull(length(wish_fullfiller), 0) = 0 AND wish_STATE = 0 ";
    
    $db = new PDO(DATABASE_PATH);
    $SQL_get_total_pages = $SQL_pages_base . $cond1;
    $rows = $db->query($SQL_get_total_pages);
    $rows = $rows->fetchAll();
    $db = NULL;
    if ($rows)
    {
        $TotalWishes = $rows[0]['TotalPages'];
        
        //PROBLEM: if the value is not a float but an int the last page is full but it says there is another one
        //HACKY WORK AROUND: 
        $float_pages = $TotalWishes / $wishes_per_page;
        $int_pages = (int)$float_pages;
        if ($float_pages > $int_pages) //only say there is a new page if its more than a even number (1 = 0, 1.1 = 2, 2 = 1, 2.1 = 3)
            return $int_pages;
        else
            return $int_pages - 1;
    }
    return -1;
}


//"SELECT * FROM Wishes WHERE ifnull(length(wish_fullfiller), 0) = 0 ORDER BY wish_reward DESC LIMIT 20"

$SQL_wishlist_query_base = "SELECT * FROM Wishes WHERE ifnull(length(wish_fullfiller), 0) = 0 AND wish_STATE = 0 ";
$SQL_wishlist_query_condition1 = ""; // no default category filter
$SQL_wishlist_query_order_by = "ORDER BY wish_reward DESC ";
$SQL_wishlist_query_range = "LIMIT 10 OFFSET 0 ";

$wishes_per_page = 10;
$wishes_page = 0;
if (!empty($_GET['page']) && is_numeric($_GET['page']))
{
	$wishes_page = $_GET['page'];
	$wishes_page = (int)$wishes_page;
	
	if ($wishes_page < 0)
		$wishes_page = 0;
	
	$wishes_offset = $wishes_page * $wishes_per_page;
	$SQL_wishlist_query_range = "LIMIT $wishes_per_page OFFSET $wishes_offset ";
}

if (!empty($_GET['rank_filter']))
{
	$get_rank_filter = $_GET['rank_filter'];
	if ($get_rank_filter == "points")
	{
        echo "<a>Ranking by wish reward</a></br>";
		$SQL_wishlist_query_order_by = "ORDER BY wish_reward DESC ";
	}
	else if ($get_rank_filter == "time_new")
	{
		echo "<a>Ranking by newest wish</a></br>";
		$SQL_wishlist_query_order_by = "ORDER BY wish_date DESC ";
	}
	else if ($get_rank_filter == "time_old")
	{
		echo "<a>Ranking by oldest wish</a></br>";
		$SQL_wishlist_query_order_by = "ORDER BY wish_date ASC ";
	}
}

if (!empty($_GET['cat_filter']))
{
	$get_cat_filter = $_GET['cat_filter'];
	if ($get_cat_filter == "none")
	{
		//do nothing
	}
	else if ($get_cat_filter == "fun")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Fun' ";
	}
	else if ($get_cat_filter == "friends")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Friends' ";
	}
	else if ($get_cat_filter == "sport")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Sport' ";
	}
	else if ($get_cat_filter == "simple_task")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Simple Task' ";
	}
	else if ($get_cat_filter == "question")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Question' ";
	}
	else if ($get_cat_filter == "school")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'School' ";
	}
	else if ($get_cat_filter == "job")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Job' ";
	}
	else if ($get_cat_filter == "trade")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Trade' ";
	}
	else if ($get_cat_filter == "social_networks")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Social Networks' ";
	}
	else if ($get_cat_filter == "product")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Product' ";
	}
	else if ($get_cat_filter == "music")
	{
		$SQL_wishlist_query_condition1 = "AND wish_category = 'Music' ";
	}
}

$db = new PDO(DATABASE_PATH);
$SQL_wishlist_query = $SQL_wishlist_query_base . $SQL_wishlist_query_condition1 . $SQL_wishlist_query_order_by . $SQL_wishlist_query_range;
//echo "</br>query: " . $SQL_wishlist_query . " </br>";
$rows = $db->query($SQL_wishlist_query);
$rows = $rows->fetchAll();
        
if ($rows)
{
    /*
    echo "rows: " . count($rows) . "</br>";
    echo "total array: ";
    print_r($rows);
    echo "</br></br>";
    
    print_r($rows[0]);
    
    echo "</br></br>";
    
        
    if (empty($rows) || !count($rows))
    {
        echo "<a>currently no wishes available</a></br>";
    }
    */
    $rank = $wishes_page * $wishes_per_page + 1;
	echo "<table id=\"table1\ style=\"text-align:left;\">";
	echo "
	<tr>
		<th>Rank</th>
		<th>Points</th>
		<th>Title</th>
	<tr>
	";
    foreach ($rows as $row)
    {
        if (!empty($row['wish_name']))
        {
            $title = $row['wish_name'];
            $reward = $row['wish_reward'];
            $id = $row['ID'];
            //echo "<li> <a href=\"main_wish.php?id=$id\">$rank. [$reward] $title</a></li>";
			echo "
			<tr>
				<th>$rank.</th>
				<th>[$reward]</th>
				<th><a href=\"main_wish.php?id=$id\">$title</a></th>
			</tr>
			";
            $rank++;
        }
        else
        {
            //echo "<a>an error occured.</a></br>";
            break;
        }
    }
	echo "</table>";
    
    $total_pages = GetTotalPages($wishes_per_page, $SQL_wishlist_query_condition1);
    echo "</br>Page: $wishes_page/$total_pages</br>";
    
    $prev_page = $wishes_page - 1;
    $next_page = $wishes_page;
    if ($wishes_page < $total_pages)
    {
        $next_page = $wishes_page + 1;
        echo "<a href=\"fullfillwish.php?page=$prev_page\">Previous</a><a> . . . </a><a href=\"fullfillwish.php?page=$next_page\">Next</a>";
    }
    else
    {
        echo "<a href=\"fullfillwish.php?page=$prev_page\">Previous</a><a>";
    }
}
else
{
	echo "<a>Currently no wishes found.</a></br>";
}
?>
		<form class="form">
		<input type="button" value="Back" onclick="window.location.href='index.php'" />
		</form>
	</body>
</html>
