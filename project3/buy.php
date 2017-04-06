<html>
<head><title>Buy Products</title>
<meta charset="utf-8"/>
</head>
<body>
<?php

session_start();
if(isset($_SESSION['basket'])){
}
else{
$_SESSION['basket'] = array();
}
 
error_reporting(E_ALL);
ini_set('display_errors','On');

#function to generate drop down menu
function drop_down_menu($xml){
    
    $drop_down_list = "";
    foreach ($xml->category as $head_category) {
        $drop_down_list = $drop_down_list."<label>Category:".'<select name="category">';
        $drop_down_list = $drop_down_list.'<option value="'.$head_category['id'].'">'.$head_category->name.'</option>'.'<optgroup label="'. $head_category->name.':">';
        foreach($head_category->categories->category as $descendants_category){
                $drop_down_list = $drop_down_list.'<option value="'.$descendants_category['id'].'">'.$descendants_category->name.'</option>';
                $flag = 1;
                foreach($descendants_category->categories as $descendants_sub_category){
                    if ($flag == 1){
                        $drop_down_list = $drop_down_list.'<optgroup label="'.$descendants_category->name.':">';
                        $flag = 0;
                    }
                    foreach($descendants_sub_category->category as $descendants_sub_category_category){
                        $drop_down_list = $drop_down_list.'<option value="'. $descendants_sub_category_category['id'].'">'.$descendants_sub_category_category->name.'</option>';
                    }
                }
                if($flag == 0){
                    $drop_down_list = $drop_down_list.'</optgroup>';
                }
            
        }
        $drop_down_list = $drop_down_list.'</optgroup></select></label>';
    }	
    return $drop_down_list;
}

$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);
$drop_down = drop_down_menu($xml);

#function to display first image
function disp_image($product){
	foreach($product->images->image as $image){
			$img_url = $image->sourceURL;
			return $img_url;
	}
}


function product_row($product){
	$disp_result = '';
	$disp_result = '<tr><td>'.$disp_result.'<a href="buy.php?add='.$product['id'].'">';
	$disp_result = $disp_result.'<img src="'.disp_image($product).'"></a></td><td>'.$product->name.'</td><td>'.$product->minPrice.'</td><td>'.$product->fullDescription.'</td></tr>';
	return $disp_result;
}

#function to display search result
function disp_search_result($result){
	
	$product_listing = '<table border = "1">';
	if($result->categories->category->items){
		foreach($result->categories->category->items as $product_item){
				$a = 0;
				foreach($product_item->product as $product){
					if($a<10){
						$product_listing = $product_listing.product_row($product);
					}
					else{
						$product_listing = $product_listing."</table>";
						return $product_listing;
					}

				}
				$a = $a + 1;
		}
	}

	else{
		return	'No Products match search';
	}	
	
	return $product_listing;
}

#function to get item details to display 
function get_product_details($xmlstr){
	$xml = new SimpleXMLElement($xmlstr);
	
	foreach($xml->categories->category->items->product as $product){
					$details = array((string)$product['id'],(string)disp_image($product), (string)$product->name, (string)$product->minPrice, (string)$product->productOffersURL);
					return $details;
	}
	
}

#function to find products in shopping basket
function get_basket(){
	
	$basket='<table border="1">';
	$basket_items = $_SESSION['basket'];
	for ($i=0; $i<count($basket_items);$i++){
		$basket = $basket.'<tr><td><a href="'.$basket_items[$i][4].'"><img src="'.$basket_items[$i][1].'"></a></td></tr>';
		$basket = $basket.'<td>'.$basket_items[$i][2].'</td><td>$'.$basket_items[$i][3].'</td>';
		$basket = $basket.'<td><a href="buy.php?delete='.$basket_items[$i][0].'">Delete</a></td></tr>';
	}
	$basket = $basket.'</table>';
	return $basket;
}

if (isset($_GET['keyword'])) {
	$category = $_GET['category'];
	$keyword = $_GET['keyword'];
	$response = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId='.$category.'&keyword='.urlencode($keyword).'&numItems=20');
	$result = new SimpleXMLElement($response);
	$search_result = disp_search_result($result);	
}
else if(isset($_GET['add'])){
	$pid = $_GET['add'];
	$basket_items = $_SESSION['basket'];
	$xml = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&productId='.$pid);
	$new_item = get_product_details($xml);
	array_push($basket_items,$new_item);
	if($basket_items != false){
		$_SESSION['basket'] = $basket_items;

	}
	
}

else if(isset($_GET['delete'])){
	$pid = $_GET['delete'];
	$basket_items = $_SESSION['basket'];
	for($i=0;$i<count($basket_items);$i++){
		if((string)$pid == $basket_items[$i][0]){
				unset($basket_items[$i]);
			}
	}
	$_SESSION['basket'] = array_values($basket_items);
	
}
else if(isset($_GET['empty'])){
	$_SESSION['basket'] = array();
}

?>

<p>
Shopping Basket:
<br>
<br>
<?php	echo get_basket();?>
</p>
Total: $<?php 
		$basket_items = $_SESSION['basket'];
		$total = 0;
		for($i=0;$i<count($basket_items);$i++){
			$total = $total + $basket_items[$i][3];
		}
		echo $total;
?>
<br>
<br>
<form action="buy.php" method="GET">
        <input type="hidden" name="empty" value="1"/>
        <input type="submit" value="Empty Basket"/>
</form>
<p>
    <form action="buy.php" method="GET">
        	Find products:<br>
            <?php echo $drop_down; ?>
            <label>Search keywords: <input name="keyword" type="text"></input></label>
            <input type="submit" value="Search"></input>
    </form>
</p>
<?php 
    if (isset($search_result)) {
        echo $search_result;
    }
?>

</body>
</html>
