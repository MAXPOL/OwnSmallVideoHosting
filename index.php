<?php
function scanDirectory($directory) {
    $mp4Files = [];
    // Open folder
    if ($handle = opendir($directory)) {
        // Read files and folders
        while (false !== ($entry = readdir($handle))) {
            // Let's skip it . and ..
            if ($entry != '.' && $entry != '..') {
                $path = $directory . DIRECTORY_SEPARATOR . $entry;
                // Chekck load files mp4
                if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'mp4') { $mp4Files[] = $path; rsort($mp4Files);}
            }
        }
        closedir($handle);
    }
    return $mp4Files;
}
$mp4Files = scanDirectory('videoStorage');
$videoCounter = 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
  if ($_POST['code'] == '000000000') { //Code load video
echo $_FILES['video']['name'];
   if (strlen($_FILES['video']['name']) > 26) { echo "<script>alert('Name file more 26 symbols')</script>"; }
   else {
   $formatFile = substr($_FILES['video']['name'], -3);
   if ($formatFile !== "avi" || $formatFile !== "mp4") { echo "<script>alert('Not correct file format: mp4 or avi only')</script>"; }
   if ($formatFile == "avi" || $formatFile == "mp4") {
    $uploadDir = '/var/www/html/videoStorage/';
    $originalFile = $uploadDir . $_FILES['video']['name'];
    if (move_uploaded_file($_FILES['video']['tmp_name'], $originalFile)) {
        $counterVideo = exec('cat counter');
        $counterVideo = $counterVideo + 1;
        $outputFile = $uploadDir . $counterVideo . '_'. date('d-m-y') . '_' . pathinfo($originalFile, PATHINFO_FILENAME) . ".mp4";
        $ffmpegCmd = "ffmpeg -i $originalFile -vcodec libx264 -preset veryfast -b:v 500k -maxrate 500k -bufsize 1000k -vf scale=-1:720 -acodec aac -strict -2 $outputFile";
        exec($ffmpegCmd, $output, $returnVar);
        $command = "echo $counterVideo > /var/www/html/counter";
        exec($command);
        if ($returnVar == 0) {
        echo "<script>alert('Video load OK')</script>"; header("Location: https://bppk.info:83/");
        } else { echo "Recoder Error"; }
        // Delete original file
        unlink($originalFile);
    } else { echo "Error load file"; }
  }
}
} else { echo "<script>alert('Load code not correct')</script>"; }
  }

if (isset($_POST['delete']) && $_POST['codeDelete'] == '00000000000') { //Code for delete video
$deleteLink = "/var/www/html/videoStorage/" . $_POST['videoFile'] . ".mp4";
unlink($deleteLink);
header("Location: https://bppk.info:83/");
}
?>

<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous><script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<title>Видеохостинг БППК</title>
<style>
   .table {
    width: 300px;
    margin: auto;
   }
        .table-bordered {
            border-collapse: collapse;
        }
        .table-bordered th,
        .table-bordered td {
            border: 2px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
            height: 100px;
        }
    </style>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copy in clipboard ok: ' + text);
            }, function(err) {
                alert('Copy in clipboard error:  ', err);
            });
        }
    </script>
</head>
<body>
        <center>
    <h1>Videohosting</h1><br>
<button class="btn btn-secondary" onclick="window.location.href='https://bppk.info'">Return</button><br><br>
<?php if ($_SERVER['REMOTE_ADDR'] == "31.132.151.158" || $_SERVER['REMOTE_ADDR'] == "192.168.4.1" ) { ?> // Menu add control panel (delelte and add video) depending ip address access

<details class="btn btn-primary">
  <summary>Администрирование файлов</summary>
<br><br>
   <form action="" method="post" enctype="multipart/form-data">
        <label for="video">Choice video file: </label>
        <input type="file" name="video" id="video" accept="video/mp4, video/x-msvideo" required>
        <label for="code">Enter code load: </label>
        <input type="password" name="code" id="code" required>
        <button type="submit" name="upload">Load</button>
</form>
<hr>
   <form action="" method="post">
        <label for="videoFile">File name: </label>
        <input type="input" name="videoFile" id="videoFile" pattern="[^*]+"  required>
        <label for="codeDelete">Enter code delete: </label>
        <input type="password" name="codeDelete" id="codeDelete" required>
        <button type="submit" name="delete">Delete</button>
</form>
</details>

<?php } ?>
<br><br>
    <?php if (empty($mp4Files)): ?>
        <p>No MP4 files found in the directory.</p>
    <?php else: ?>
        <table class="table table-bordered">
        <?php foreach ($mp4Files as $file):
$link = "<video width=\"320\" height=\"240\"  controls><source src=\"https://bppk.info:83/$file\"></video>"; //Change link for you ip address
$linkEscaped = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
$url = "https://bppk.info:83/$file"; //Change link for you ip address
$urlEscaped = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

?>
            <?php $videoCounter = $videoCounter + 1;
           if ($videoCounter == 2) {
                 echo "<td>";
                 ?>
          <div style="margin-bottom: 10px;">
                <video width="320" height="240" controls>
                    <source src="<?php echo htmlspecialchars($file); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p class="text-nowrap" ><?php echo substr(str_replace("_", " ", htmlspecialchars(basename($file))), 0, -4); ?></p>
<center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $linkEscaped; ?>')">Copy code for site</button>
<br><br>
             <center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $urlEscaped; ?>')">Copy URL</button>
            </div>
                <?php echo "</td>"; } elseif ($videoCounter == 1) {
                echo  "<tr><td>";
                ?>
            <div style="margin-bottom: 20px;">
                <video width="320" height="240" controls>
                    <source src="<?php echo htmlspecialchars($file); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p class="text-nowrap" ><?php echo substr(str_replace("_", " ", htmlspecialchars(basename($file))), 0, -4); ?></p>
<center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $linkEscaped; ?>')">Copy code for site</button>
<br><br>
<center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $urlEscaped; ?>')">Copy URL</button>
            </div>
         <?php echo "</td>";}  elseif ($videoCounter == 3) {
                echo  "<td>";
                ?>
            <div style="margin-bottom: 20px;">
                <video width="320" height="240" controls>
                    <source src="<?php echo htmlspecialchars($file); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <p class="text-nowrap" ><?php echo substr(str_replace("_", " ", htmlspecialchars(basename($file))), 0, -4); ?></p>
<center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $linkEscaped; ?>')">Copy code for site</button>
<br><br>
<center><button class="btn btn-primary" onclick="copyToClipboard('<?php echo $urlEscaped; ?>')">Copy URL</button>
            </div>
                <?php echo "</td></tr>"; $videoCounter = 0;} ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
             
