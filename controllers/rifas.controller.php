<?php
class RifasController {

    const TABLE = 'raffles';

    public static function obtenerRifas() {
        $params = [
            'select' => 'id_raffle,title_raffle,description_raffle,price_raffle,digits_raffle,date_raffle,status_raffle,promotions_raffle',
            'orderBy' => 'id_raffle',
            'orderMode' => 'DESC'
        ];

        // Búsqueda por título o descripción
        if (!empty($_POST['search'])) {
            $params['linkTo'] = 'title_raffle,description_raffle';
            $params['search'] = trim($_POST['search']);
        }

        // Filtro por estado
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            if (isset($params['linkTo'])) {
                $params['linkTo'] .= ',status_raffle';
                $params['search'] .= ',' . $_POST['status'];
            } else {
                $params['linkTo'] = 'status_raffle';
                $params['equalTo'] = $_POST['status'];
            }
        }

        $result = ApiRequest::get(self::TABLE, $params);

        if (ApiRequest::isSuccess($result)) {
            return [
                'success' => true,
                'results' => $result->results
            ];
        }

        return ['success' => false, 'results' => []];
    }

    public static function crearRifa($data) {
        $datosCrear = [
            'title_raffle'       => trim($data['title_raffle'] ?? ''),
            'description_raffle' => trim($data['description_raffle'] ?? ''),
            'price_raffle'       => (float)($data['price_raffle'] ?? 0),
            'digits_raffle'      => (int)($data['digits_raffle'] ?? 4),
            'date_raffle'        => $data['date_raffle'] ?? '',
            'promotions_raffle'  => trim($data['promotions_raffle'] ?? ''),
            'status_raffle'      => isset($data['status_raffle']) ? (int)$data['status_raffle'] : 1
        ];

        $url = self::TABLE . "?token=no&table=" . self::TABLE . "&suffix=raffle&except=title_raffle";
        $result = ApiRequest::post($url, $datosCrear);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Rifa creada exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }

    public static function actualizarRifa($data) {
        if (empty($data['id_raffle'])) {
            return ['success' => false, 'message' => 'ID de rifa requerido'];
        }

        $datosActualizar = [
            'title_raffle'       => trim($data['title_raffle'] ?? ''),
            'description_raffle' => trim($data['description_raffle'] ?? ''),
            'price_raffle'       => (float)($data['price_raffle'] ?? 0),
            'digits_raffle'      => (int)($data['digits_raffle'] ?? 4),
            'date_raffle'        => $data['date_raffle'] ?? '',
            'promotions_raffle'  => trim($data['promotions_raffle'] ?? ''),
            'status_raffle'      => isset($data['status_raffle']) ? (int)$data['status_raffle'] : 1
        ];

        $url = self::TABLE . "?id=" . $data['id_raffle'] . "&nameId=id_raffle&token=no&except=title_raffle";
        $result = ApiRequest::put($url, $datosActualizar);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Rifa actualizada exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }

    public static function eliminarRifa($data) {
        if (empty($data['id_raffle'])) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $url = self::TABLE . "?id=" . $data['id_raffle'] . "&nameId=id_raffle&token=no&except=title_raffle";
        $result = ApiRequest::delete($url);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Rifa eliminada exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }
}