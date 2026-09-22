<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class DynamicQrisService
{
    public const STATIC_PAYLOAD = '00020101021126610014COM.GO-JEK.WWW01189360091430922317030210G0922317030303UMI51440014ID.CO.QRIS.WWW0215ID10265401099430303UMI5204504553033605802ID5925F Store, Komputer & Softw6006MALANG61056517462070703A01630452AE';
    public const MERCHANT_NAME = 'Ryu Developer';
    public const NMID = 'ID1026540109943';

    /**
     * Convert static QRIS payload to dynamic QRIS payload with nominal amount.
     */
    public static function makeDynamic(float|int $amount, ?string $basePayload = null): ?string
    {
        $amountInt = (int) round($amount);
        if ($amountInt <= 0) {
            return null;
        }

        $payload = $basePayload ?: self::STATIC_PAYLOAD;

        // Change Point of Initiation Method from 11 (Static) to 12 (Dynamic)
        $payload = preg_replace('/^000201010211/', '000201010212', $payload) ?? str_replace('010211', '010212', $payload);

        // Strip existing CRC (Tag 63 and its 4-digit hex value)
        if (($crcPos = strrpos($payload, '6304')) !== false) {
            $payload = substr($payload, 0, $crcPos);
        }

        // Build Tag 54 for Amount
        $amountStr = (string) $amountInt;
        $tag54Len = str_pad((string) strlen($amountStr), 2, '0', STR_PAD_LEFT);
        $tag54 = '54' . $tag54Len . $amountStr;

        // In standard QRIS, Tag 54 is placed between Tag 53 (5303360) and Tag 58 (5802ID)
        // If an existing Tag 54 is already between 5303360 and 5802ID, replace it
        if (preg_match('/(5303360)(?:54\d{2}\d+)?(5802ID)/', $payload)) {
            $payload = preg_replace('/(5303360)(?:54\d{2}\d+)?(5802ID)/', '${1}' . $tag54 . '${2}', $payload, 1) ?? $payload;
        } else {
            $tag58Pos = strpos($payload, '5802ID');
            if ($tag58Pos !== false) {
                $payload = substr($payload, 0, $tag58Pos) . $tag54 . substr($payload, $tag58Pos);
            } else {
                $payload .= $tag54;
            }
        }

        // Append Tag 63 length indicator
        $payload .= '6304';

        // Calculate CRC16-CCITT and append
        $crc = self::crc16($payload);

        return $payload . $crc;
    }

    /**
     * Render QR code as SVG data URI for crisp, scalable display.
     */
    public static function renderQrSvg(string $payload): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'svgAddXmlHeader' => false,
            'scale' => 5,
        ]);

        return (new QRCode($options))->render($payload);
    }

    /**
     * Render QR code as PNG data URI (base64) for high-resolution file downloads.
     */
    public static function renderQrPng(string $payload, int $scale = 10): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => $scale,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($payload);
    }

    /**
     * Compute CRC-16/CCITT-FALSE (Polynomial 0x1021, Initial 0xFFFF).
     */
    public static function crc16(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);

        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
