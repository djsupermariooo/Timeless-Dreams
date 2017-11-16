<?php

include("../../db.php");

// Check if user is logged in and send them to login page if they are not
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit();
}

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = "";

// Runs when the user presses the submit button
if (isset($_POST['submit'])) {

    for ($i = 0; $i < count($_FILES['file-upload']['name']); $i++) {

        if ($_FILES['file-upload']['error'][$i] > 0) {
            $result = "Error: " . $_FILES['file-upload']['error'][$i];
        } else {

            // Create a random filename so user doesn't overwrite images on upload
            $temp = explode(".", $_FILES['file-upload']['name'][$i]);
            $filename = round(microtime(true)) . '.' . end($temp);

            // Temp file location
            $tmpFilePath = $_FILES['file-upload']['tmp_name'][$i];
            
            //indicate which file to resize (can be any type jpg/png/gif/etc...)
            $file = $tmpFilePath;

            //indicate the path and name for the new resized file
            $resizedFile = $tmpFilePath;

            // Resize image
            smart_resize_image($file, null, 600, 600, false, $resizedFile, false, false, 100);
            
            // Move uploaded image to upload directory
            $moveResult = move_uploaded_file($resizedFile, "../images/uploads/" . $filename);
            if ($moveResult == true) {
                $result = "Upload successful";
            } else {
                $result = "Upload failed";
            }

            // Insert upload info into database
            $category = $_POST['category'];
            $sql = "INSERT INTO photos (category, filename) VALUES ('$category', '$filename')";
            $conn->query($sql);

        }
    }
}

// Read all image info from database
$dbinfo = "SELECT * FROM photos";
$rows = array();
$r = $conn->query($dbinfo);
while ($row = $r->fetch_assoc()) {
    $rows[] = $row;
}

$conn->close();

// Image resizing (courtesy of Nimrod007)
/**
 * easy image resize function
 * @param  $file - file name to resize
 * @param  $string - The image data, as a string
 * @param  $width - new image width
 * @param  $height - new image height
 * @param  $proportional - keep image proportional, default is no
 * @param  $output - name of the new file (include path if needed)
 * @param  $delete_original - if true the original image will be deleted
 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
 * @param  $quality - enter 1-100 (100 is best quality) default is 100
 * @return boolean|resource
 */
  function smart_resize_image($file,
                              $string             = null,
                              $width              = 0, 
                              $height             = 0, 
                              $proportional       = false, 
                              $output             = 'file', 
                              $delete_original    = true, 
                              $use_linux_commands = false,
  							  $quality = 100
  		 ) {
      
    if ( $height <= 0 && $width <= 0 ) return false;
    if ( $file === null && $string === null ) return false;

    # Setting defaults and meta
    $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
    $image                        = '';
    $final_width                  = 0;
    $final_height                 = 0;
    list($width_old, $height_old) = $info;
	$cropHeight = $cropWidth = 0;

    # Calculating proportionality
    if ($proportional) {
      if      ($width  == 0)  $factor = $height/$height_old;
      elseif  ($height == 0)  $factor = $width/$width_old;
      else                    $factor = min( $width / $width_old, $height / $height_old );

      $final_width  = round( $width_old * $factor );
      $final_height = round( $height_old * $factor );
    }
    else {
      $final_width = ( $width <= 0 ) ? $width_old : $width;
      $final_height = ( $height <= 0 ) ? $height_old : $height;
	  $widthX = $width_old / $width;
	  $heightX = $height_old / $height;
	  
	  $x = min($widthX, $heightX);
	  $cropWidth = ($width_old - $width * $x) / 2;
	  $cropHeight = ($height_old - $height * $x) / 2;
    }

    # Loading image to memory according to type
    switch ( $info[2] ) {
      case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
      case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
      case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
      default: return false;
    }
    
    
    # This is the resizing/resampling/transparency-preserving magic
    $image_resized = imagecreatetruecolor( $final_width, $final_height );
    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
      $transparency = imagecolortransparent($image);
      $palletsize = imagecolorstotal($image);

      if ($transparency >= 0 && $transparency < $palletsize) {
        $transparent_color  = imagecolorsforindex($image, $transparency);
        $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($image_resized, 0, 0, $transparency);
        imagecolortransparent($image_resized, $transparency);
      }
      elseif ($info[2] == IMAGETYPE_PNG) {
        imagealphablending($image_resized, false);
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
      }
    }
    imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
	
	
    # Taking care of original, if needed
    if ( $delete_original ) {
      if ( $use_linux_commands ) exec('rm '.$file);
      else @unlink($file);
    }

    # Preparing a method of providing result
    switch ( strtolower($output) ) {
      case 'browser':
        $mime = image_type_to_mime_type($info[2]);
        header("Content-type: $mime");
        $output = NULL;
      break;
      case 'file':
        $output = $file;
      break;
      case 'return':
        return $image_resized;
      break;
      default:
      break;
    }
    
    # Writing image according to type to the output destination and image quality
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
      case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
      case IMAGETYPE_PNG:
        $quality = 9 - (int)((0.9*$quality)/10.0);
        imagepng($image_resized, $output, $quality);
        break;
      default: return false;
    }

    return true;
  }

?>

    <html>

    <head>
        <title>TDP Admin Panel - Upload</title>
        <link rel="stylesheet" type="text/css" href="styles/style.css">
        <link href='http://fonts.googleapis.com/css?family=Dancing+Script:700' rel='stylesheet' type='text/css'>
    </head>

    <body>
        <div id="header">
            <h1>Upload Photos</h1>
        </div>
        <form class="form" action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file-upload[]" multiple>
            <br>
            <br>
            <label for="category">Category: </label>
            <select name="category">
                <option value="maternity">Maternity</option>
                <option value="wedding">Wedding</option>
                <option value="engagement">Engagement</option>
                <option value="child">Child</option>
                <option value="portrait">Portrait</option>
            </select>
            <br>
            <br>
            <input type="submit" value="Upload Images" name="submit">
        </form>
        <div id="result">
            <?php echo $result ?>
        </div>
        <div id="menu">
            <ul>
                <li class="current"><a href="#">All</a></li>
                <li><a href="#">Maternity</a></li>
                <li><a href="#">Wedding</a></li>
                <li><a href="#">Engagement</a></li>
                <li><a href="#">Child</a></li>
                <li><a href="#">Portrait</a></li>
            </ul>
        </div>
        <div id="portfolio">

        </div>
        <script type="application/javascript">
            var photos = JSON.parse('<?php echo json_encode($rows) ?>');
        </script>
        <script type="application/javascript" src="js/app.js"></script>
    </body>

    </html>