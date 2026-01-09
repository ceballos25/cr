<?php
class RifasController {
    const TABLE = 'raffles';

    /**
     * Listar rifas con búsqueda y filtros combinados (Corregido)
     */
    public static function obtenerRifas() {
        $params = [
            'select' => '*',
            'orderBy' => 'id_raffle',
            'orderMode' => 'DESC'
        ];

        // Capturamos los valores de búsqueda y estado
        $search = !empty($_POST['search']) ? trim((string)$_POST['search']) : '';
        $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : '';

        // ESCENARIO 1: Búsqueda de texto Y Filtro de estado
        if ($search !== '' && $status !== '') {
            // Balance 1:1 - title_raffle usa LIKE (%search%) y status_raffle usa = [cite: 1181, 1182]
            $params['linkTo'] = 'title_raffle,status_raffle';
            $params['search'] = "$search,$status";
        } 
        // ESCENARIO 2: Solo búsqueda de texto
        elseif ($search !== '') {
            $params['linkTo'] = 'title_raffle';
            $params['search'] = $search;
        } 
        // ESCENARIO 3: Solo filtro de estado
        elseif ($status !== '') {
            $params['linkTo'] = 'status_raffle';
            $params['equalTo'] = $status; // Filtro exacto [cite: 1028]
        }

        $result = ApiRequest::get(self::TABLE, $params);
        
        if (ApiRequest::isSuccess($result)) {
            $data = $result->results ?? [];
            // Siempre retornamos array para que el JS no falle
            return [
                'success' => true, 
                'data' => is_array($data) ? $data : [$data]
            ];
        }
        
        return ['success' => true, 'data' => []];
    }

    /**
     * Crear Rifa y Generar Boletos (Sin recortes)
     */
    public static function crearRifa($data) {
        set_time_limit(0); 
        
        $datos = [
            'title_raffle'       => trim($data['title_raffle']),
            'description_raffle' => trim($data['description_raffle']),
            'promotions_raffle'  => trim($data['promotions_raffle'] ?? ''),
            'price_raffle'       => $data['price_raffle'],
            'digits_raffle'      => (int)$data['digits_raffle'],
            'date_raffle'        => $data['date_raffle'],
            'status_raffle'      => (int)$data['status_raffle']
        ];
        
        $res = ApiRequest::post(self::TABLE . "?token=no&except=title_raffle", $datos);
        
        if (ApiRequest::isSuccess($res) && isset($res->results->lastId)) {
            $idRifa = $res->results->lastId;
            $cifras = (int)$data['digits_raffle'];
            $totalBoletos = pow(10, $cifras);
            
            for ($i = 0; $i < $totalBoletos; $i++) {
                $numero = str_pad($i, $cifras, "0", STR_PAD_LEFT);
                $ticket = [
                    'number_ticket'    => $numero,
                    'status_ticket'    => 0, 
                    'id_raffle_ticket' => $idRifa
                ];
                ApiRequest::post("tickets?token=no&except=number_ticket", $ticket);
            }
            return ['success' => true, 'message' => "Rifa creada con $totalBoletos boletos."];
        }
        return ['success' => false, 'message' => 'Error al crear la rifa.'];
    }

    public static function actualizarRifa($data) {
        $id = $data['id_raffle'];
        unset($data['action'], $data['id_raffle']);
        $url = self::TABLE . "?id=$id&nameId=id_raffle&token=no&except=title_raffle";
        $res = ApiRequest::put($url, $data);
        return ApiRequest::isSuccess($res) ? ['success' => true, 'message' => 'Rifa actualizada'] : ['success' => false];
    }

    public static function eliminarRifa($data) {
        $url = self::TABLE . "?id=" . $data['id_raffle'] . "&nameId=id_raffle&token=no&except=title_raffle";
        $res = ApiRequest::delete($url);
        return ApiRequest::isSuccess($res) ? ['success' => true, 'message' => 'Rifa eliminada'] : ['success' => false];
    }
}