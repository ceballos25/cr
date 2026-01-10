<?php
class TicketsController {

    const TABLE = 'tickets';

    /**
     * Obtener solo los números ocupados de una rifa
     * Retorna array simple: ["001", "015", "099"]
     */
    public static function obtenerOcupados($idRifa) {
        
        // 1. Buscamos tickets de esa rifa con estado > 0 (Vendidos/Apartados)
        // Asumiendo que 0 es libre, y 1, 2, etc. son ocupados.
        $params = [
            'select'  => 'number_ticket',
            'linkTo'  => 'id_raffle_ticket,status_ticket',
            // Buscamos tickets de la rifa X que NO sean status 0. 
            // Como ApiRequest suele ser limitado, pedimos todos los de la rifa 
            // y filtramos o si tu API soporta filterNot, mejor.
            // Para asegurar compatibilidad con tu framework simple:
            // Traemos todos los ocupados (status_ticket = 1). 
            // Si tienes más estados (2=reservado), ajusta la lógica.
            'equalTo' => "$idRifa,1", 
            'orderBy' => 'id_ticket',
            'orderMode' => 'ASC'
        ];

        // NOTA: Si tu sistema usa status 1 para vendido y 2 para reservado, 
        // y ApiRequest no soporta "IN(1,2)", tendríamos que hacer una query SQL directa 
        // o traer todos los tickets de la rifa y filtrar en PHP.
        // Asumiremos por ahora que status 1 es el principal a bloquear.

        $result = ApiRequest::get(self::TABLE, $params);
        
        $ocupados = [];
        
        if (ApiRequest::isSuccess($result)) {
            $data = $result->results ?? [];
            $lista = is_array($data) ? $data : [$data];

            foreach ($lista as $ticket) {
                // Guardamos solo el número
                $ocupados[] = $ticket->number_ticket;
            }
        }
        
        return $ocupados;
    }
}
?>