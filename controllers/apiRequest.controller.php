<?php

class ApiRequest {

    // Usar constantes definidas en config si están disponibles, sino valores por defecto
    private static $apiBase = '';
    private static $apiKey = '';

    static public function request($url, $method, $fields = []) {
        
        $curl = curl_init();
        
        $base = self::$apiBase ?: (defined('API_BASE') ? API_BASE : 'http://localhost/api.caballosrevelo.test');

        curl_setopt_array($curl, [
            CURLOPT_URL => rtrim($base, '/') . '/' . ltrim($url, '/'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => is_array($fields) ? http_build_query($fields) : $fields,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . (self::$apiKey ?: (defined('API_KEY') ? API_KEY : ''))
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            return (object) [
                'status' => 0,
                'results' => 'Error cURL: ' . $curlError
            ];
        }
        
        $decoded = json_decode($response);
        
        if ($decoded === null) {
            return (object) [
                'status' => $httpCode,
                'results' => $response
            ];
        }
        
        return $decoded;
    }

    static public function get($url, $params = []) {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return self::request($url, 'GET', []);
    }

static public function post($url, $data = []) {
    error_log("POST URL: " . self::$apiBase . $url);
    error_log("POST DATA: " . json_encode($data));
    error_log("POST DATA encoded: " . http_build_query($data));
    
    $result = self::request($url, 'POST', $data);
    
    error_log("POST RESPONSE: " . json_encode($result));
    
    return $result;
}

    static public function put($url, $data = []) {
        // NO agregar datos a URL, solo enviar en body
        return self::request($url, 'PUT', $data);
    }

    static public function delete($url, $data = []) {
        // NO agregar datos a URL
        return self::request($url, 'DELETE', []);
    }

    static public function isSuccess($response) {
        return isset($response->status) && in_array($response->status, [200, 201]);
    }

    static public function getErrorMessage($response) {
        if (isset($response->results)) {
            return $response->results;
        }
        return 'Error desconocido';
    }
}