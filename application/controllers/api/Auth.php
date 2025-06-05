<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/Api_controller.php';

class Auth extends Api_controller
{

    protected $require_auth = false;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    /**
     * POST /api/auth/login
     * Fazer login
     */
    public function login()
    {
        try {
            $data = $this->get_request_data();

            // Validar campos obrigatórios
            $required_fields = ['email', 'password'];
            $this->validate_required_fields($data, $required_fields);

            // Verificar credenciais
            $user = $this->User_model->verify_credentials($data['email'], $data['password']);

            if (!$user) {
                $this->response([
                    'error' => 'Credenciais inválidas'
                ], 401);
                return;
            }

            // Gerar token JWT
            $token_payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ];

            $token = $this->jwt_lib->generate_token($token_payload);

            // Atualizar último login
            $this->User_model->update_last_login($user['id']);

            $this->response([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 24 * 60 * 60
                ]
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * POST /api/auth/register
     * Registrar novo usuário
     */
    public function register()
    {
        try {
            $data = $this->get_request_data();

            // Validar campos obrigatórios
            $required_fields = ['name', 'email', 'password'];
            $this->validate_required_fields($data, $required_fields);

            // Validar email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->response(['error' => 'Email inválido'], 400);
                return;
            }

            // Verificar se email já existe
            if ($this->User_model->email_exists($data['email'])) {
                $this->response(['error' => 'Email já cadastrado'], 409);
                return;
            }

            // Validar senha
            if (strlen($data['password']) < 6) {
                $this->response(['error' => 'Senha deve ter pelo menos 6 caracteres'], 400);
                return;
            }

            // Criptografar senha
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Criar usuário
            $user_id = $this->User_model->create($data);

            if ($user_id) {
                $user = $this->User_model->get_by_id($user_id);
                unset($user['password']);

                // Gerar token
                $token_payload = [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ];

                $token = $this->jwt_lib->generate_token($token_payload);

                $this->response([
                    'success' => true,
                    'message' => 'Usuário registrado com sucesso',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                        'token_type' => 'Bearer',
                        'expires_in' => 24 * 60 * 60
                    ]
                ], 201);
            } else {
                $this->response(['error' => 'Erro ao registrar usuário'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * POST /api/auth/logout
     * Fazer logout (apenas resposta de sucesso, token expira naturalmente)
     */
    public function logout()
    {
        // Como estamos usando JWT stateless, não precisamos invalidar no servidor
        // O token expira automaticamente

        $this->response([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * GET /api/auth/me
     * Obter dados do usuário logado
     */
    public function me()
    {
        $this->require_auth = true;
        $this->authenticate();

        try {
            $user = $this->User_model->get_by_id($this->current_user['user_id']);

            if (!$user) {
                $this->response(['error' => 'Usuário não encontrado'], 404);
                return;
            }

            unset($user['password']);

            $this->response([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }
}
