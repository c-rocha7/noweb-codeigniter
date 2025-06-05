<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api_controller extends CI_Controller
{

    protected $allowed_methods = array();
    protected $require_auth = true;
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();

        // Carregar biblioteca JWT
        $this->load->library('JWT_lib');

        // Permitir CORS
        $this->output->set_header('Access-Control-Allow-Origin: *');
        $this->output->set_header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        $this->output->set_header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // Responder OPTIONS
        if ($this->input->method() === 'options') {
            $this->output->set_status_header(200);
            return;
        }

        // Verificar autenticação se necessário
        if ($this->require_auth) {
            $this->authenticate();
        }

        // Verificar método permitido
        if (!empty($this->allowed_methods) && !in_array($this->input->method(), $this->allowed_methods)) {
            $this->response(['error' => 'Método não permitido'], 405);
        }
    }

    /**
     * Verificar autenticação
     */
    protected function authenticate()
    {
        $token = $this->get_token();

        if (!$token) {
            $this->response(['error' => 'Token de acesso requerido'], 401);
            return;
        }

        $payload = $this->jwt_lib->validate_token($token);

        if (!$payload) {
            $this->response(['error' => 'Token inválido ou expirado'], 401);
            return;
        }

        $this->current_user = $payload;
    }

    /**
     * Obter token do header Authorization
     */
    protected function get_token()
    {
        $headers = $this->input->request_headers();

        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
                return $matches[1];
            }
        }

        return false;
    }

    /**
     * Método para enviar resposta JSON
     */
    protected function response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obter dados do corpo da requisição
     */
    protected function get_request_data()
    {
        $input = $this->input->raw_input_stream;
        return json_decode($input, true);
    }

    /**
     * Validar dados obrigatórios
     */
    protected function validate_required_fields($data, $required_fields)
    {
        $missing_fields = array();

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            $this->response([
                'error' => 'Campos obrigatórios não informados',
                'missing_fields' => $missing_fields
            ], 400);
        }
    }
}
