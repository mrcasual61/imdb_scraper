<?php
require_once "simple_html_dom.php";

set_time_limit(60);

//get html content from the site.
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;

// Collect movies into an array.
$movies = array();
$i = 0;
$p = 0;

function get_dom($url, $caller) {

	global $dom;
	$dom = file_get_html($url, false);
	// Based on which function calls this function.
	switch ($caller) {
		case "get_movies":
			get_movies($dom);
			break;
		case "more_info":
			return $dom;
			break;
		default:
			break;
	}

}


function get_movies($dom) {

	global $movies, $i, $p;

	if(!empty($dom)) {
		foreach($dom->find(".lister-item") as $div_class) {
			foreach($div_class->getElementsByTagName('h3') as $div_header) {
				// Link
				$links = $div_header->getElementsByTagName('a');
				foreach($links as $link) {
					$movies[$i]['link'] = $link->getAttribute('href');
				}
				// Title
				foreach($links as $key=>$var) {
					$movies[$i]['title'] = html_entity_decode($var->plaintext);
				}
			}
			// IMDB rating.
			$imdb_rating = $div_class->find(".ratings-imdb-rating", 0);
			$movies[$i]['rating'] = trim($imdb_rating->plaintext);

			//  Director, stars.
			$director = "";
			$stars = "";
			// Get all text for the div.
			$all_text = html_entity_decode($div_class->plaintext);
			// Find starting poit for Directors.
			$director_start = strpos($all_text, "Director") + 10;
			// Find ending point for Stars.
			$stars_end = strpos($all_text, "Votes") - 2;
			//  Find starting point for Stars.
			// Possibly no Stars.  Check first.
			$stars_start = strpos($all_text, "Stars:");
			if ($stars_start === false) {
				// Use $stars_end value for use with Director find.
				$stars_start = $stars_end;
			} else {
				$stars_start = $stars_start + 6;
				$stars = trim(substr($all_text, $stars_start, $stars_end - $stars_start));
			}
			
			// Director
			$director = trim(substr($all_text, $director_start, $stars_start - $director_start - 20));

			$movies[$i]['director'] = $director;
			$movies[$i]['stars'] = $stars;

			// Get image info.
			foreach ($div_class->find(".lister-item-image") as $image_section) {
				// Image src.
				$image_srcs = $image_section->getElementsByTagName('img');
				$image_src = $image_srcs[0]->getAttribute('loadlate');
			}
			$movies[$i]['image_src'] = $image_src;
		
			$i++;
			if ($i == 10) {
				break;
			}
		}

		// Go to next page.
		$next_links = $dom->find(".lister-page-next");
		$next_link = "https://www.imdb.com" . $next_links[0]->getAttribute('href');

		$p++;
		if ($p < 1) {
			get_dom($next_link, "get_movies");
		} else {
			more_info();
		}
	}

}


function more_info() {

	global $dom, $movies;

	// Get info from each movie page.
	$count = count($movies);

	for ($i = 0; $i < $count; $i++) {
		$link = "https://www.imdb.com" . $movies[$i]['link'];
		// Get dom.
		get_dom($link, "more_info");
		// Get storyline.
		$storyline_section = $dom->find("#titleStoryLine", 0);
		$storyline = $storyline_section->getElementsByTagName('p');
		$movies[$i]['storyline'] = trim(html_entity_decode($storyline[0]->plaintext));

		// Get release date.
		$release_date_section = $dom->find("#titleDetails", 0)->plaintext;

		// Find starting poit for Release Date.
		$release_start = strpos($release_date_section, "Release Date:");
		// Find ending point for Release Date.
		$release_end = strpos($release_date_section, "See more", $release_start);

		if ($release_start === false || $release_end === false) {
			$movies[$i]['release_date'] = "";
		} else {
			$release_start = $release_start + 14;
			$release_end = $release_end - 2;
			$release_date = trim(substr($release_date_section, $release_start, $release_end - $release_start));
			$movies[$i]['release_date'] = $release_date;
		}

	}

	db_connect();

}


function db_connect() {

	$servername = "scifi.db.10929588.d55.hostedresource.net";
	$username = "scifi";
	$password = "Aliens@61";
	$db = "scifi";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);
	mysqli_set_charset($conn, 'utf8');

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error . "<br /><br />");
	} else {
		db_insert($conn);
	}

}


function db_insert($conn) {

	global $movies;

	if(is_array($movies)){

	    for ($i = 0; $i < sizeof($movies); $i++){
	        $link = mysqli_real_escape_string($conn, $movies[$i]['link']);
	        $title = mysqli_real_escape_string($conn, $movies[$i]['title']);
	        $rating = mysqli_real_escape_string($conn, $movies[$i]['rating']);
	        $director = mysqli_real_escape_string($conn, $movies[$i]['director']);
	        $stars = mysqli_real_escape_string($conn, $movies[$i]['stars']);
	        $image_src = mysqli_real_escape_string($conn, $movies[$i]['image_src']);
	        $storyline = mysqli_real_escape_string($conn, $movies[$i]['storyline']);
	        $release_date = mysqli_real_escape_string($conn, $movies[$i]['release_date']);

	        $sql .= "INSERT INTO scifi_movies (link, title, rating, director, stars, image_src, storyline, release_date) values ('$link', '$title', '$rating', '$director', '$stars', '$image_src', '$storyline', '$release_date');";
	    }

	    $sql = rtrim($sql, ";");
	    if (!$result = mysqli_multi_query($conn, $sql)) {
		    echo mysqli_error($conn);
		}

	}

	create_csv();

}


function create_csv() {

	global $movies;
	$array = array();

	// Open file.
	$fo = fopen('scifi_movies.csv','w');

	// Loop through array.
	foreach ($movies as $movie) {
		foreach ($movie as $index => $value) {
			$array[$index] = utf8_encode($value);
		}
		fputs($fo, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($fo, $movie);
	}

	fclose($fo);

}


// Get first page dom.
get_dom("https://www.imdb.com/search/title/?title_type=movie&genres=sci-fi&sort=user_rating,desc&explore=title_type,genres", "get_movies");

echo json_encode($movies);

?>