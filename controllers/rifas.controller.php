<?php
class RifasController {

    const TABLE = 'raffles';

    public static function obtenerRifas() {
        $params = [
            'select' => 'id_raffle,title_raffle,description_raffle,price_raffle,digits_raffle,date_raffle,status_raffle,promotions_raffle',
            'orderBy' => 'id_raffle',
            'orderMode' => 'DESC'
        ];

        $search = !empty($_POST['search']) ? trim($_POST['search']) : '';
        $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : '';

        // LÓGICA SEGÚN PÁGINA 17 DE LA DOCUMENTACIÓN
        if ($search !== '' && $status !== '') {
            // Buscamos el término en title_raffle y aplicamos filtro exacto en status_raffle
            $params['linkTo'] = 'title_raffle,status_raffle';
            $params['search'] = $search . ',' . $status;
        } elseif ($search !== '') {
            // Búsqueda simple por palabra clave
            $params['linkTo'] = 'title_raffle';
            $params['search'] = $search;
        } elseif ($status !== '') {
            // Filtro exacto por estado (Página 11)
            $params['linkTo'] = 'status_raffle';
            $params['equalTo'] = $status;
        }

        $result = ApiRequest::get(self::TABLE, $params);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'data' => $result->results ?? []];
        }

        return ['success' => true, 'data' => []];
    }

    public static function crearRifa($data) {
        if (empty($data['title_raffle']) || empty($data['price_raffle']) || empty($data['digits_raffle']) || empty($data['date_raffle'])) {
            return ['success' => false, 'message' => 'Faltan campos obligatorios'];
        }

        $datosCrear = [
            'title_raffle'       => trim($data['title_raffle']),
            'description_raffle' => trim($data['description_raffle'] ?? ''),
            'price_raffle'       => (float)$data['price_raffle'],
            'digits_raffle'      => (int)$data['digits_raffle'],
            'date_raffle'        => $data['date_raffle'],
            'promotions_raffle'  => trim($data['promotions_raffle'] ?? ''),
            'status_raffle'      => (int)$data['status_raffle']
        ];

        $url = self::TABLE . "?token=no&table=" . self::TABLE . "&suffix=raffle&except=title_raffle";
        $result = ApiRequest::post($url, $datosCrear);

        if (ApiRequest::isSuccess($result)) {
            $idRifaGenerada = $result->results->lastId; 
            $cifras = (int)$data['digits_raffle'];
            $totalTickets = pow(10, $cifras);
            set_time_limit(0); 

            for ($i = 0; $i < $totalTickets; $i++) {
                $numeroFormateado = str_pad($i, $cifras, "0", STR_PAD_LEFT);
                $datosTicket = [
                    "number_ticket"      => $numeroFormateado,
                    "status_ticket"      => 0,
                    "id_raffle_ticket"   => $idRifaGenerada,
                    "date_created_ticket" => date("Y-m-d")
                ];
                $urlTicket = "tickets?token=no&table=tickets&suffix=ticket&except=number_ticket";
                ApiRequest::post($urlTicket, $datosTicket);
            }
            return ['success' => true, 'message' => 'Rifa y ' . $totalTickets . ' números generados'];
        }
        return ['success' => false, 'message' => 'Error API'];
    }

    public static function actualizarRifa($data) {
        if (empty($data['id_raffle'])) return ['success' => false, 'message' => 'ID requerido'];
        $datosActualizar = [
            'title_raffle'       => trim($data['title_raffle']),
            'description_raffle' => trim($data['description_raffle'] ?? ''),
            'price_raffle'       => (float)$data['price_raffle'],
            'digits_raffle'      => (int)$data['digits_raffle'],
            'date_raffle'        => $data['date_raffle'],
            'promotions_raffle'  => trim($data['promotions_raffle'] ?? ''),
            'status_raffle'      => (int)$data['status_raffle']
        ];
        $url = self::TABLE . "?id=" . $data['id_raffle'] . "&nameId=id_raffle&token=no&except=title_raffle";
        $result = ApiRequest::put($url, $datosActualizar);
        return ApiRequest::isSuccess($result) ? ['success' => true, 'message' => 'Actualizado'] : ['success' => false];
    }

    public static function eliminarRifa($data) {
        $url = self::TABLE . "?id=" . $data['id_raffle'] . "&nameId=id_raffle&token=no&except=title_raffle";
        $result = ApiRequest::delete($url);
        return ApiRequest::isSuccess($result) ? ['success' => true, 'message' => 'Eliminado'] : ['success' => false];
    }
}