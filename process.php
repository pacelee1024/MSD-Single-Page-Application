<?php

require 'vendor/autoload.php';

/* handle the input json format data */
$post_date = file_get_contents("php://input");
$data = json_decode($post_date);

// =============================================
// AWS

use Aws\S3\S3Client;

$client = S3Client::factory(array(
    'version' => '2006-03-01',
    'region' => 'ap-southeast-1',
    'credentials' => [
        'key'    => ' ',
        'secret' => ' ',
    ],
));

//upload function for AWS S3 Bucket
function upload($filename, $filepath) {
    global $client;

    $result = $client->putObject(array(
        'Bucket' => "qrcodegenerator",
        'Key'    => $filename,
        'SourceFile'   => $filepath,
        'ACL'        => 'public-read',
    ));

    return $result['ObjectURL'];
}

// =============================================
// Generate QRCode image & Zip

include "phpqrcode.php";

$zip = new ZipArchive();

$zipfilename = "combined_". time() . ".zip";

$zipfilepath = getcwd() . "/files/" . $zipfilename;

if ($zip->open($zipfilepath, ZipArchive::CREATE)!==TRUE) {
    exit("cannot open <$zipfilepath>\n");
} else {
    // echo "Zip file created at " . $zipfilename . "\n";
}

$generated_files = array();

function generate_qr_code($value) {
    global $zip;
    global $generated_files;

    $path = getcwd() . "/files/" . $value . ".jpg";
    // QRcode::png($value, $path);

    $outerFrame = 4;
    $pixelPerPoint = 5;
    $jpegQuality = 95;

    // generating frame
    $frame = QRcode::text($value);

    $h = count($frame);
    $w = strlen($frame[0]);

    $imgW = $w + 2*$outerFrame;
    $imgH = $h + 2*$outerFrame;

    $base_image = imagecreate($imgW, $imgH);

    $col[0] = imagecolorallocate($base_image,255,255,255); // BG, white
    $col[1] = imagecolorallocate($base_image,0,0,0);     // FG, blue

    imagefill($base_image, 0, 0, $col[0]);

    for($y=0; $y<$h; $y++) {
        for($x=0; $x<$w; $x++) {
            if ($frame[$y][$x] == '1') {
                imagesetpixel($base_image,$x+$outerFrame,$y+$outerFrame,$col[1]);
            }
        }
    }

    // saving to file
    $target_image = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
    imagecopyresized(
        $target_image,
        $base_image,
        0, 0, 0, 0,
        $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH
    );
    imagedestroy($base_image);
    imagejpeg($target_image, $path, $jpegQuality);
    imagedestroy($target_image);

    $zip->addFile($path, $value . ".jpg");
    $server_path = upload($value . ".jpg", "files/" . $value . ".jpg");

    array_push($generated_files, $server_path);
}

//Generate qrcodes for all the inputs
foreach ($data->string as $v) {
    generate_qr_code($v);
}

$zip->close();

$zipfiles3 = upload($zipfilename, $zipfilepath);

// =============================================
// MailGun service for sending email

$http_client = new \Http\Adapter\Guzzle6\Client();
//sandbox test domain can only send to oneself, so here we use a general domain that can send emails to everyone else.
$mailgun = new \Mailgun\Mailgun(' ', $http_client);
$domain = " ";

$title = join(", ", $data->string);
$title = "Your QR Code for " . $title . " are available for download.";

$content = $data->name . ", Please download from " . $zipfiles3 . " <br />Thank you.";
// Now, compose and send the message, with both download link and attachment file in zip format
$mailgun->sendMessage($domain, array('from'       => 'ligaofeng@example.com',
                                     'to'         => $data->email,
                                     'subject'    => $title,
                                     'html'       => $content),
                               array('attachment' => array($zipfilepath)));

header('Content-Type: application/json');

$res = array();
$res["error"] = 0;
//eliminate duplicates input, meaning that multiple same input will only generate one unique qrcode image
$res["images"] = array_unique($generated_files);
$res["zip"] = $zipfiles3;

echo json_encode($res);
