<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Update_news_titles extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    public function index()
    {
        // Verificar se o script já foi executado (opcional - medida de segurança)
        if ($this->input->get('confirm') !== 'yes') {
            echo "<h2>Atenção!</h2>";
            echo "<p>Este script irá alterar TODOS os títulos das notícias para 'noweb'.</p>";
            echo "<p><strong>Esta ação não pode ser desfeita!</strong></p>";
            echo "<p><a href='" . current_url() . "?confirm=yes' style='background: red; color: white; padding: 10px; text-decoration: none;'>CONFIRMAR EXECUÇÃO</a></p>";
            return;
        }

        try {
            // Iniciar transação para garantir consistência
            $this->db->trans_begin();

            // Buscar todas as notícias antes da alteração (para log)
            $original_news = $this->db->select('id, title')->get('news')->result();

            echo "<h2>Iniciando atualização dos títulos...</h2>";
            echo "<p>Total de notícias encontradas: " . count($original_news) . "</p>";

            // Atualizar todos os títulos para "noweb"
            $data = array(
                'title' => 'noweb',
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->db->update('news', $data);
            $affected_rows = $this->db->affected_rows();

            // Verificar se houve erro na transação
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo "<h3 style='color: red;'>ERRO: Falha na atualização!</h3>";
                echo "<p>As alterações foram revertidas.</p>";
            } else {
                $this->db->trans_commit();
                echo "<h3 style='color: green;'>SUCESSO!</h3>";
                echo "<p>Títulos atualizados com sucesso!</p>";
                echo "<p>Total de registros alterados: <strong>" . $affected_rows . "</strong></p>";

                // Mostrar algumas notícias atualizadas como confirmação
                echo "<h4>Primeiras 10 notícias atualizadas:</h4>";
                $updated_news = $this->db->select('id, title, updated_at')
                    ->limit(10)
                    ->get('news')
                    ->result();

                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Título</th><th>Atualizado em</th></tr>";
                foreach ($updated_news as $news) {
                    echo "<tr><td>" . $news->id . "</td><td>" . $news->title . "</td><td>" . $news->updated_at . "</td></tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo "<h3 style='color: red;'>ERRO: " . $e->getMessage() . "</h3>";
            echo "<p>As alterações foram revertidas.</p>";
        }

        echo "<br><p><a href='" . base_url() . "'>Voltar ao início</a></p>";
    }

    // Método alternativo para executar via CLI
    public function cli_update()
    {
        if (!is_cli()) {
            echo "Este método só pode ser executado via linha de comando.\n";
            return;
        }

        echo "Iniciando atualização dos títulos das notícias...\n";

        try {
            $this->db->trans_begin();

            // Contar total de notícias
            $total = $this->db->count_all('news');
            echo "Total de notícias encontradas: {$total}\n";

            if ($total == 0) {
                echo "Nenhuma notícia encontrada na base de dados.\n";
                return;
            }

            // Atualizar títulos
            $data = array(
                'title' => 'noweb',
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->db->update('news', $data);
            $affected_rows = $this->db->affected_rows();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo "ERRO: Falha na atualização. Alterações revertidas.\n";
            } else {
                $this->db->trans_commit();
                echo "SUCESSO: {$affected_rows} títulos atualizados!\n";
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo "ERRO: " . $e->getMessage() . "\n";
        }
    }

    // Método para fazer backup antes da alteração (recomendado)
    public function backup_titles()
    {
        try {
            // Criar tabela de backup se não existir
            $backup_table = "news_titles_backup_" . date('Ymd_His');

            $sql = "CREATE TABLE {$backup_table} AS 
                    SELECT id, title, created_at, updated_at 
                    FROM news";

            $this->db->query($sql);

            $count = $this->db->count_all($backup_table);

            echo "<h3>Backup criado com sucesso!</h3>";
            echo "<p>Tabela: <strong>{$backup_table}</strong></p>";
            echo "<p>Registros salvos: <strong>{$count}</strong></p>";
            echo "<p>Agora você pode executar a atualização com segurança.</p>";
            echo "<p><a href='" . site_url('update_news_titles') . "'>Executar Atualização</a></p>";
        } catch (Exception $e) {
            echo "<h3 style='color: red;'>Erro ao criar backup: " . $e->getMessage() . "</h3>";
        }
    }
}
