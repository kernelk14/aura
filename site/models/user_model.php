<?php
/**
 * User Model
 */

class UserModel extends Model {
    public function getUsers() {
        $db = $this->loadDatabase();
        return $db->get('users');
    }
    
    public function getUserById($id) {
        $db = $this->loadDatabase();
        return $db->getWhere('users', ['id' => $id]);
    }
    
    public function createUser($data) {
        $db = $this->loadDatabase();
        return $db->insert('users', $data);
    }
}