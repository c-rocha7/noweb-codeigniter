<?php

defined('BASEPATH') or exit('No direct script access allowed');

class News_model extends CI_Model
{

    private $table = 'news';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obter todas as notícias com dados do autor
     */
    public function get_all($limit = null, $offset = null, $filters = array())
    {
        $this->db->select('n.*, u.name as author_name, u.email as author_email');
        $this->db->from($this->table . ' n');
        $this->db->join('users u', 'n.author_id = u.id', 'left');

        // Filtros opcionais
        if (!empty($filters['status'])) {
            $this->db->where('n.status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $this->db->where('n.category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('n.title', $filters['search']);
            $this->db->or_like('n.content', $filters['search']);
            $this->db->or_like('n.summary', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('n.created_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obter notícia por ID com dados do autor
     */
    public function get_by_id($id)
    {
        $this->db->select('n.*, u.name as author_name, u.email as author_email');
        $this->db->from($this->table . ' n');
        $this->db->join('users u', 'n.author_id = u.id', 'left');
        $this->db->where('n.id', $id);

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Criar nova notícia
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Atualizar notícia
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Deletar notícia
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Verificar se notícia existe
     */
    public function exists($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Verificar se usuário é autor da notícia
     */
    public function is_author($news_id, $user_id)
    {
        $this->db->where('id', $news_id);
        $this->db->where('author_id', $user_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Contar total de notícias
     */
    public function count_all($filters = array())
    {
        $this->db->from($this->table);

        // Aplicar os mesmos filtros da busca
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $this->db->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('title', $filters['search']);
            $this->db->or_like('content', $filters['search']);
            $this->db->or_like('summary', $filters['search']);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Obter notícias por autor
     */
    public function get_by_author($author_id, $limit = null, $offset = null)
    {
        $this->db->select('n.*, u.name as author_name, u.email as author_email');
        $this->db->from($this->table . ' n');
        $this->db->join('users u', 'n.author_id = u.id', 'left');
        $this->db->where('n.author_id', $author_id);
        $this->db->order_by('n.created_at', 'DESC');

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Atualizar visualizações
     */
    public function increment_views($id)
    {
        $this->db->set('views', 'views + 1', FALSE);
        $this->db->where('id', $id);
        return $this->db->update($this->table);
    }

    /**
     * Obter categorias únicas
     */
    public function get_categories()
    {
        $this->db->select('category');
        $this->db->distinct();
        $this->db->where('category IS NOT NULL');
        $this->db->where('category !=', '');
        $query = $this->db->get($this->table);

        $categories = array();
        foreach ($query->result_array() as $row) {
            $categories[] = $row['category'];
        }

        return $categories;
    }
}
