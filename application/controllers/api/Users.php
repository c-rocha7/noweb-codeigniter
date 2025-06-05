<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/Api_controller.php';

class Users extends Api_controller
{
    protected $require_auth = true;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    /**
     * GET /api/users
     * Listar todos os usuários (protegido)
     */
    public function index()
    {
        try {
            $page = $this->input->get('page') ?: 1;
            $per_page = $this->input->get('per_page') ?: 10;
            $offset = ($page - 1) * $per_page;

            $users = $this->User_model->get_all($per_page, $offset);

            // Remover senhas dos resultados
            foreach ($users as &$user) {
                unset($user['password']);
            }

            $total = $this->User_model->count_all();

            $this->response([
                'success' => true,
                'data' => $users,
                'pagination' => [
                    'page' => (int)$page,
                    'per_page' => (int)$per_page,
                    'total' => $total,
                    'total_pages' => ceil($total / $per_page)
                ]
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * GET /api/users/{id}
     * Obter usuário específico
     */
    public function show($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            $user = $this->User_model->get_by_id($id);

            if (!$user) {
                $this->response(['error' => 'Usuário não encontrado'], 404);
                return;
            }

            $this->response([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * POST /api/users
     * Criar novo usuário
     */
    public function create()
    {
        try {
            $data = $this->get_request_data();

            // Validar campos obrigatórios
            $required_fields = ['name', 'email'];
            $this->validate_required_fields($data, $required_fields);

            // Validar email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->response(['error' => 'Email inválido'], 400);
                return;
            }

            // Criar usuário
            $user_id = $this->User_model->create($data);

            if ($user_id) {
                $user = $this->User_model->get_by_id($user_id);
                $this->response([
                    'success' => true,
                    'message' => 'Usuário criado com sucesso',
                    'data' => $user
                ], 201);
            } else {
                $this->response(['error' => 'Erro ao criar usuário'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * PUT /api/users/{id}
     * Atualizar usuário
     */
    public function update($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            if (!$this->User_model->exists($id)) {
                $this->response(['error' => 'Usuário não encontrado'], 404);
                return;
            }

            $data = $this->get_request_data();

            // Validar email se fornecido
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->response(['error' => 'Email inválido'], 400);
                return;
            }

            // Atualizar usuário
            $updated = $this->User_model->update($id, $data);

            if ($updated) {
                $user = $this->User_model->get_by_id($id);
                $this->response([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso',
                    'data' => $user
                ]);
            } else {
                $this->response(['error' => 'Erro ao atualizar usuário'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * DELETE /api/users/{id}
     * Deletar usuário
     */
    public function delete($id)
    {
        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            if (!$this->User_model->exists($id)) {
                $this->response(['error' => 'Usuário não encontrado'], 404);
                return;
            }

            $deleted = $this->User_model->delete($id);

            if ($deleted) {
                $this->response([
                    'success' => true,
                    'message' => 'Usuário deletado com sucesso'
                ]);
            } else {
                $this->response(['error' => 'Erro ao deletar usuário'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }
}
