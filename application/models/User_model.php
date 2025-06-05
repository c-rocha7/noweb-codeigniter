<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{

    private $table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obter todos os usuários
     */
    public function get_all($limit = null, $offset = null)
    {
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    /**
     * Obter usuário por ID
     */
    public function get_by_id($id)
    {
        $query = $this->db->get_where($this->table, array('id' => $id));
        return $query->row_array();
    }

    /**
     * Criar novo usuário
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Atualizar usuário
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Deletar usuário
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Verificar se usuário existe
     */
    public function exists($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Contar total de usuários
     */
    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Buscar usuário por email
     */
    public function get_by_email($email)
    {
        $query = $this->db->get_where($this->table, array('email' => $email));
        return $query->row_array();
    }

    /**
     * Verificar credenciais de login
     */
    public function verify_credentials($email, $password)
    {
        $user = $this->get_by_email($email);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * Atualizar último login
     */
    public function update_last_login($user_id)
    {
        $data = array(
            'last_login' => date('Y-m-d H:i:s')
        );

        $this->db->where('id', $user_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Verificar se email já existe
     */
    public function email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
}
