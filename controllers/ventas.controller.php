<?php
class VentasController {
    const TABLE = 'sales';

    public static function obtenerVentas() {
        $params = [
            'rel' => 'customers,raffles',
            'type' => 'customer,raffle', 
            'select' => 'id_sale,total_sale,payment_method_sale,status_sale,date_created_sale,name_customer,lastname_customer,title_raffle',
            'orderBy' => 'id_sale',
            'orderMode' => 'DESC'
        ];
        if (!empty($_POST['search'])) {
            $params['linkTo'] = 'name_customer,lastname_customer,title_raffle';
            $params['search'] = trim($_POST['search']);
        }
        $result = ApiRequest::get(self::TABLE, $params);
        return ApiRequest::isSuccess($result) ? ['success' => true, 'data' => $result->results ?? []] : ['success' => true, 'data' => []];
    }

    public static function obtenerTicketsDisponibles($idRaffle) {
        if (!$idRaffle) return ['success' => false, 'data' => []];
        $params = [
            'linkTo' => 'id_raffle_ticket,status_ticket',
            'equalTo' => $idRaffle . ",0",
            'select' => 'id_ticket,number_ticket',
            'orderBy' => 'number_ticket', 
            'orderMode' => 'ASC'
        ];
        $result = ApiRequest::get('tickets', $params);
        $data = ApiRequest::isSuccess($result) ? ($result->results ?? []) : [];
        return ['success' => true, 'data' => is_array($data) ? $data : [$data]];
    }

    public static function crearVenta($data) {
        $datosVenta = [
            'id_customer_sale' => $data['id_customer'],
            'id_raffle_sale' => $data['id_raffle'],
            'total_sale' => $data['total'],
            'payment_method_sale' => $data['metodo'],
            'status_sale' => 1
        ];
        
        $url = self::TABLE . "?token=no&table=" . self::TABLE . "&suffix=sale";
        $res = ApiRequest::post($url, $datosVenta);

        if (ApiRequest::isSuccess($res)) {
            $idVenta = $res->results->lastId;
            $tickets = explode(',', $data['tickets_ids']);
            
            foreach ($tickets as $idT) {
                $upd = [
                    'status_ticket' => 2, // Vendido
                    'id_customer_ticket' => $data['id_customer'],
                    'id_sale_ticket' => $idVenta
                ];
                ApiRequest::put("tickets?id=$idT&nameId=id_ticket&token=no&table=tickets&suffix=ticket", $upd);
            }
            return ['success' => true, 'message' => 'Venta procesada'];
        }
        return ['success' => false, 'message' => 'Error al registrar'];
    }
}