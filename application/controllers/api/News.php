<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/Api_controller.php';

class News extends Api_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('News_model');
    }

    /**
     * GET /api/news
     * Listar todas as notícias (público)
     */
    public function index()
    {
        // Rota pública - não requer autenticação
        $this->require_auth = false;

        try {
            $page = $this->input->get('page') ?: 1;
            $per_page = $this->input->get('per_page') ?: 10;
            $offset = ($page - 1) * $per_page;

            // Filtros
            $filters = array();
            if ($this->input->get('status')) {
                $filters['status'] = $this->input->get('status');
            }
            if ($this->input->get('category')) {
                $filters['category'] = $this->input->get('category');
            }
            if ($this->input->get('search')) {
                $filters['search'] = $this->input->get('search');
            }

            // Para visualização pública, mostrar apenas notícias publicadas
            if (!isset($filters['status'])) {
                $filters['status'] = 'published';
            }

            $news = $this->News_model->get_all($per_page, $offset, $filters);
            $total = $this->News_model->count_all($filters);

            $this->response([
                'success' => true,
                'data' => $news,
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
     * GET /api/news/{id}
     * Obter notícia específica (público)
     */
    public function show($id)
    {
        // Rota pública - não requer autenticação
        $this->require_auth = false;

        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            $news = $this->News_model->get_by_id($id);

            if (!$news) {
                $this->response(['error' => 'Notícia não encontrada'], 404);
                return;
            }

            // Para visualização pública, verificar se está publicada
            if ($news['status'] !== 'published') {
                $this->response(['error' => 'Notícia não disponível'], 404);
                return;
            }

            // Incrementar visualizações
            $this->News_model->increment_views($id);
            $news['views'] = $news['views'] + 1;

            $this->response([
                'success' => true,
                'data' => $news
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * POST /api/news
     * Criar nova notícia (protegido)
     */
    public function create()
    {
        // Rota protegida - requer autenticação
        $this->require_auth = true;
        $this->authenticate();

        try {
            $data = $this->get_request_data();

            // Validar campos obrigatórios
            $required_fields = ['title', 'content'];
            $this->validate_required_fields($data, $required_fields);

            // Validar status
            $allowed_status = ['draft', 'published', 'archived'];
            if (isset($data['status']) && !in_array($data['status'], $allowed_status)) {
                $this->response(['error' => 'Status inválido. Use: draft, published ou archived'], 400);
                return;
            }

            // Definir autor como usuário logado
            $data['author_id'] = $this->current_user['user_id'];

            // Definir status padrão
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            // Gerar slug a partir do título se não fornecido
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = $this->generate_slug($data['title']);
            }

            // Definir valores padrão
            if (!isset($data['views'])) {
                $data['views'] = 0;
            }

            // Criar notícia
            $news_id = $this->News_model->create($data);

            if ($news_id) {
                $news = $this->News_model->get_by_id($news_id);
                $this->response([
                    'success' => true,
                    'message' => 'Notícia criada com sucesso',
                    'data' => $news
                ], 201);
            } else {
                $this->response(['error' => 'Erro ao criar notícia'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * PUT /api/news/{id}
     * Atualizar notícia (protegido)
     */
    public function update($id)
    {
        // Rota protegida - requer autenticação
        $this->require_auth = true;
        $this->authenticate();

        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            if (!$this->News_model->exists($id)) {
                $this->response(['error' => 'Notícia não encontrada'], 404);
                return;
            }

            // Verificar se usuário é o autor da notícia
            if (!$this->News_model->is_author($id, $this->current_user['user_id'])) {
                $this->response(['error' => 'Você não tem permissão para editar esta notícia'], 403);
                return;
            }

            $data = $this->get_request_data();

            // Validar status se fornecido
            $allowed_status = ['draft', 'published', 'archived'];
            if (isset($data['status']) && !in_array($data['status'], $allowed_status)) {
                $this->response(['error' => 'Status inválido. Use: draft, published ou archived'], 400);
                return;
            }

            // Atualizar slug se título foi alterado
            if (isset($data['title']) && isset($data['slug']) && empty($data['slug'])) {
                $data['slug'] = $this->generate_slug($data['title']);
            }

            // Não permitir alterar autor
            unset($data['author_id']);

            // Atualizar notícia
            $updated = $this->News_model->update($id, $data);

            if ($updated) {
                $news = $this->News_model->get_by_id($id);
                $this->response([
                    'success' => true,
                    'message' => 'Notícia atualizada com sucesso',
                    'data' => $news
                ]);
            } else {
                $this->response(['error' => 'Erro ao atualizar notícia'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * DELETE /api/news/{id}
     * Deletar notícia (protegido)
     */
    public function delete($id)
    {
        // Rota protegida - requer autenticação
        $this->require_auth = true;
        $this->authenticate();

        try {
            if (!is_numeric($id)) {
                $this->response(['error' => 'ID inválido'], 400);
                return;
            }

            if (!$this->News_model->exists($id)) {
                $this->response(['error' => 'Notícia não encontrada'], 404);
                return;
            }

            // Verificar se usuário é o autor da notícia
            if (!$this->News_model->is_author($id, $this->current_user['user_id'])) {
                $this->response(['error' => 'Você não tem permissão para deletar esta notícia'], 403);
                return;
            }

            $deleted = $this->News_model->delete($id);

            if ($deleted) {
                $this->response([
                    'success' => true,
                    'message' => 'Notícia deletada com sucesso'
                ]);
            } else {
                $this->response(['error' => 'Erro ao deletar notícia'], 500);
            }
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * GET /api/news/my
     * Obter notícias do usuário logado (protegido)
     */
    public function my_news()
    {
        // Rota protegida - requer autenticação
        $this->require_auth = true;
        $this->authenticate();

        try {
            $page = $this->input->get('page') ?: 1;
            $per_page = $this->input->get('per_page') ?: 10;
            $offset = ($page - 1) * $per_page;

            $news = $this->News_model->get_by_author($this->current_user['user_id'], $per_page, $offset);

            $this->response([
                'success' => true,
                'data' => $news,
                'pagination' => [
                    'page' => (int)$page,
                    'per_page' => (int)$per_page
                ]
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * GET /api/news/categories
     * Obter todas as categorias (público)
     */
    public function categories()
    {
        // Rota pública - não requer autenticação
        $this->require_auth = false;

        try {
            $categories = $this->News_model->get_categories();

            $this->response([
                'success' => true,
                'data' => $categories
            ]);
        } catch (Exception $e) {
            $this->response(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Gerar slug a partir do título
     */
    private function generate_slug($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
