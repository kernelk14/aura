<?php

namespace AuraCore;

class Auth
{
    protected $session;
    protected $modelClass;
    protected $user = null;

    public function __construct($modelClass = null)
    {
        $this->session = new Session();
        $this->modelClass = $modelClass ?: \App\Models\User::class;
    }

    public function attempt(array $credentials)
    {
        $model = new $this->modelClass;
        $emailField = 'email';
        $passwordField = 'password';

        $user = $model->where($emailField, $credentials[$emailField] ?? '')->first();

        if (!$user) {
            return false;
        }

        if (!password_verify($credentials[$passwordField] ?? '', $user->password)) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function attemptById($id)
    {
        $model = new $this->modelClass;
        $user = $model->find($id);

        if (!$user) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function login($user)
    {
        $this->user = $user;
        $this->session->set('auth_id', $user->getKey());
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('auth_id');
        $this->session->regenerate();
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $id = $this->session->get('auth_id');
        if (!$id) {
            return null;
        }

        $model = new $this->modelClass;
        $this->user = $model->find($id);
        return $this->user;
    }

    public function check()
    {
        return $this->session->has('auth_id');
    }

    public function guest()
    {
        return !$this->check();
    }

    public function id()
    {
        return $this->session->get('auth_id');
    }

    public function setModel($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }
}
