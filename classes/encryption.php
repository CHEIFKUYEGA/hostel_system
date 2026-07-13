<?php
class Encryption {
    // Ufunguo maalum wa siri (Secret Key) - Tunza kwa siri kubwa sana!
    private static $secret_key = "CBE_BIT_2_SECRET_ENCRYPTION_KEY_2026";
    private static $encrypt_method = "AES-256-CBC";

    // 1. Kazi ya kusimba data (Encrypt) kabla ya kuihifadhi kwenye DB
    public static function encrypt($data) {
        // Kutengeneza Initialization Vector (IV) kwa ajili ya usalama wa algorithm
        $key = hash('sha256', self::$secret_key);
        $iv = substr(hash('sha256', self::$secret_key), 0, 16);
        
        $encrypted = openssl_encrypt($data, self::$encrypt_method, $key, 0, $iv);
        return base64_encode($encrypted);
    }

    // 2. Kazi ya kufungua data (Decrypt) ikitoka kwenye DB ili isomwe na mtumiaji
    public static function decrypt($encrypted_data) {
        $key = hash('sha256', self::$secret_key);
        $iv = substr(hash('sha256', self::$secret_key), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($encrypted_data), self::$encrypt_method, $key, 0, $iv);
        return $decrypted;
    }
}

// === MFANO WA JINSI INAVYOCHAPA KAZI ===
// Ukichukua jina la mwanafunzi:
// $jina_orijinali = "Salumu Juma";
// $jina_salama = Encryption::encrypt($jina_orijinali); 
// Huku ndio linaenda kukaa kwenye DB likiwa kama: "bU15YTRvV0F..." (Mwalimu hawezi kusoma kitu!)
?>