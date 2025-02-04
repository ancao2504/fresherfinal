<?php

require_once('vendor/autoload.php');

use Endroid\QrCode\QrCode;

class QRUtils {

    /**
     * @data Text to encode
     * @filePath relative path to storage folder (default upload)
     * @fileName name to save (without extension, cause we will using png)
     * @withLogo true to generate QR code with company logo
     * @Author: Phu Vo
     * @Date: 2020.06.27
     */
    static function generateQRCode(String $data, String $filePath = '', String $fileName = '', int $size = null, bool $withLogo = false) {
        global $root_directory;

        // Assign default value
        if (empty($filePath)) $filePath = "upload/";
        if (empty($fileName)) $fileName = "qr_" . rand();
        if (empty($size)) $size = 300;

        // Just in case
        if (substr($filePath, -1) != '/') $filePath .= '/';
        $fullFilePath = $root_directory . $filePath;

        // Check file path and process folder creation
        if (!file_exists($fullFilePath)) {
            mkdir($fullFilePath, '0777', true);
        }

        $qrCode = new QrCode($data);
        $qrCode->setSize($size);
        $qrCode->setEncoding('UTF-8');

        // Process company logo
        if ($withLogo) {
            $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
            $companyLogo = $companyDetails->getLogo();
            $companyLogoPath = $companyLogo->get('imagepath');

            // Process logo size
            $logo = imagecreatefromstring(file_get_contents($companyLogoPath));

            $logoWith = imagesx($logo);
            $logoHeight = imagesy($logo);

            // Some company use png image with transparent background as logo
            // These code below create white background and save to system temp folder
            $newLogo = imagecreatetruecolor($logoWith, $logoHeight);
            $color = imagecolorallocate($newLogo, '255', '255', '255');
            imagefill($newLogo, 0, 0, $color);
            imagecopy($newLogo, $logo, 0, 0, 0, 0, $logoWith, $logoHeight);

            // Get logo binary data
            ob_start();
            imagejpeg($newLogo);
            $binaryLogo = ob_get_contents();
            ob_end_clean();

            // Out put logo binary data to system temp
            $cachePath = tempnam(sys_get_temp_dir(), 'logo');
            $cacheFile = fopen($cachePath, 'w');
            fwrite($cacheFile, $binaryLogo);
            fclose($cacheFile);

            $logoScaledWith = $size / 3;
            $scale = $logoWith / $logoScaledWith;
            $logoScaledHeight = $logoHeight / $scale;

            $qrCode->setLogoPath($cachePath);
            $qrCode->setLogoSize($logoScaledWith, $logoScaledHeight);
        }

        // Save QR code to file
        $qrCode->writeFile($fullFilePath . $fileName . '.png');

        return $filePath . $fileName . '.png';
    }
}