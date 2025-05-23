<?php

namespace App\Core;

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        global $config;
        $this->db = Database::getInstance($config['database']);
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id])->fetch();
    }
    
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql)->fetchAll();
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        $this->db->query($sql, $values);
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $set = implode('=?,', $fields) . '=?';
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        $values[] = $id;
        
        return $this->db->query($sql, $values);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function where($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    public function first($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT 1";
        return $this->db->query($sql, $params)->fetch();
    }
    
    public function count($conditions = '1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$conditions}";
        return $this->db->query($sql, $params)->fetch()['count'];
    }
} 