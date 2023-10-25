<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numbers = explode("\n", $_POST["numbers"]);

    // Validieren und bereinigen der eingegebenen Nummern
    $validNumbers = [];
    foreach ($numbers as $number) {
        $cleanedNumber = trim($number);
        if (preg_match("/^\d{7}$/", $cleanedNumber)) {
            $validNumbers[] = $cleanedNumber;
        }
    }

    if (!empty($validNumbers)) {
        $language = $_POST["language"];
        $languageCode = ($language == "de") ? "_pv_DE" : "_pv_EN";

        if (count($validNumbers) === 1) {
            $number = $validNumbers[0];
            $color = $_POST["color"];
            $colorCode = ($color == "000000") ? "000000" : "d50b1e";
            $filename = $number . $languageCode . ".svg";
            $link = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&qzone=1&format=svg&color=" . $colorCode . "&data=https://qr.einhell.com/" . $number . $languageCode;

            header("Content-Type: image/svg+xml");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            readfile($link);

            exit;
        } else {
            $zipFilename = "QR-Codes" . $languageCode . ".zip";
            $zip = new ZipArchive();
            if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
                foreach ($validNumbers as $number) {
                    $filename = $number . $languageCode . ".svg";
                    $color = $_POST["color"];
                    $colorCode = ($color == "000000") ? "000000" : "d50b1e";
                    $link = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&qzone=1&format=svg&color=" . $colorCode . "&data=https://qr.einhell.com/" . $number . $languageCode;
                    $svgContent = file_get_contents($link);

                    $zip->addFromString($filename, $svgContent);
                }
                $zip->close();

                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename=\"$zipFilename\"");
                readfile($zipFilename);

                // Löschen der temporären ZIP-Datei
                unlink($zipFilename);

                exit;
            } else {
                $error = "Failed to create ZIP file.";
            }
        }
    } else {
        $error = "Invalid input. Please enter valid 7-digit numbers.";
    }
}
?>

<!DOCTYPE html>
<html>
   <head>
      <title>QR</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
         integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <style>
         body {
         height: 100vh;
         display: flex;
         overflow: hidden;
         }
         .color-container {
         display: flex;
         align-items: center;
         }
         .color-container .form-check {
         margin-right: 10px;
         }
      </style>
   </head>
   <body class="text-center">
      <div class="my-auto mx-auto align-items-center">
         <h1>Video-QR <i class="bi bi-qr-code"></i></h1>
         <?php if (isset($error)): ?>
         <p style="color: red;">
            <?php echo $error; ?>
         </p>
         <?php endif; ?>
         <form method="POST" class="card p-4 align-middle"
            style="width: 21rem; margin-left:auto; margin-right:auto; text-align:left">
            <div class="form-group">
               <label for="numbers"><i class="bi bi-list-ol"></i> Enter 7-digit Art.Nos. (one per line):</label>
               <textarea class="form-control" id="numbers" name="numbers" rows="4" required></textarea>
            </div>
            <div class="form-group mt-3">
               <label><i class="bi bi-translate"></i> Select language:</label></br>
               <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="language" id="languageDE" value="de" checked>
                  <label class="form-check-label" for="languageDE">
                  German (DE)
                  </label>
               </div>
               <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="language" id="languageEN" value="en">
                  <label class="form-check-label" for="languageEN">
                  English (EN)
                  </label>
               </div>
            </div>
            <label class="mt-3" for="color"><i class="bi bi-palette"></i> Select color:</label>
            <div class="color-container">
               <div class="form-check">
                  <input class="form-check-input" type="radio" name="color" id="colorBlack" value="000000" checked>
                  <label class="form-check-label" for="colorBlack">
                  Black
                  </label>
               </div>
               <div class="form-check">
                  <input class="form-check-input" type="radio" name="color" id="colorRed" value="d50b1e">
                  <label class="form-check-label" for="colorRed">
                  Red
                  </label>
               </div>
            </div>
            <button class="btn btn-outline-danger mt-4 mb-3" type="submit"><i class="bi bi-download"></i> Generate QR
            Codes</button>
            <li class="list-group-item text-secondary">ⓘ Ein hier generierter QR-Code ist NICHT automatisch im System
               hinterlegt.
            </li>
         </form>
      </div>
   </body>
</html>