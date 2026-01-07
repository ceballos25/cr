<?php
class ClientesController {

    const TABLE = 'customers';

    public static function obtenerClientes() {
        $params = [
            'select' => 'id_customer,name_customer,lastname_customer,phone_customer,email_customer,department_customer,city_customer,status_customer',
            'orderBy' => 'id_customer',
            'orderMode' => 'DESC'
        ];

        // Búsqueda por nombre, apellido, email o teléfono
        if (!empty($_POST['search'])) {
            $search = trim($_POST['search']);
            $params['linkTo'] = 'name_customer,lastname_customer,email_customer,phone_customer';
            $params['search'] = $search;
        }

        // Filtro por estado
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            if (isset($params['linkTo'])) {
                // Si ya hay búsqueda, agregar filtro de estado
                $params['linkTo'] .= ',status_customer';
                $params['search'] .= ',' . $_POST['status'];
            } else {
                // Solo filtro de estado
                $params['linkTo'] = 'status_customer';
                $params['equalTo'] = $_POST['status'];
            }
        }

        $result = ApiRequest::get(self::TABLE, $params);

        if (ApiRequest::isSuccess($result)) {
            return [
                'success' => true,
                'data' => $result->results ?? [],
                'total' => $result->total ?? 0
            ];
        }

        return [
            'success' => false,
            'message' => ApiRequest::getErrorMessage($result)
        ];
    }

    public static function crearCliente($data) {
        // Validaciones
        if (empty($data['name_customer'])) {
            return ['success' => false, 'message' => 'El nombre es obligatorio'];
        }

        if (empty($data['lastname_customer'])) {
            return ['success' => false, 'message' => 'El apellido es obligatorio'];
        }

        $datosCrear = [
            'name_customer' => trim($data['name_customer']),
            'lastname_customer' => trim($data['lastname_customer']),
            'phone_customer' => trim($data['phone_customer'] ?? ''),
            'email_customer' => trim($data['email_customer'] ?? ''),
            'department_customer' => trim($data['department_customer'] ?? ''),
            'city_customer' => trim($data['city_customer'] ?? ''),
            'status_customer' => isset($data['status_customer']) ? (int)$data['status_customer'] : 1
        ];

        $url = self::TABLE . "?token=no&except=name_customer";
        $result = ApiRequest::post($url, $datosCrear);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Cliente creado exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }

    public static function actualizarCliente($data) {
        if (empty($data['id_customer'])) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $datosActualizar = [
            'name_customer' => trim($data['name_customer'] ?? ''),
            'lastname_customer' => trim($data['lastname_customer'] ?? ''),
            'phone_customer' => trim($data['phone_customer'] ?? ''),
            'email_customer' => trim($data['email_customer'] ?? ''),
            'department_customer' => trim($data['department_customer'] ?? ''),
            'city_customer' => trim($data['city_customer'] ?? ''),
            'status_customer' => isset($data['status_customer']) ? (int)$data['status_customer'] : 1
        ];

        $url = self::TABLE . "?id=" . $data['id_customer'] . "&nameId=id_customer&token=no&except=name_customer";
        $result = ApiRequest::put($url, $datosActualizar);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Cliente actualizado exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }

    public static function eliminarCliente($data) {
        if (empty($data['id_customer'])) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $url = self::TABLE . "?id=" . $data['id_customer'] . "&nameId=id_customer&token=no&except=name_customer";
        $result = ApiRequest::delete($url);

        if (ApiRequest::isSuccess($result)) {
            return ['success' => true, 'message' => 'Cliente eliminado exitosamente'];
        }

        return ['success' => false, 'message' => ApiRequest::getErrorMessage($result)];
    }
}