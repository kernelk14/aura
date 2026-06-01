<?php

namespace AuraCore;

class Controller extends Base
{
    protected $request;
    protected $response;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    protected function request()
    {
        if (!$this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    protected function response()
    {
        if (!$this->response) {
            $this->response = new Response();
        }
        return $this->response;
    }

    protected function session()
    {
        return new Session();
    }

    protected function validate(array $data, array $rules, array $messages = [])
    {
        $validator = new Validator();
        return $validator->validate($data, $rules, $messages);
    }

    protected function auth($modelClass = null)
    {
        return new Auth($modelClass);
    }

    protected function json($data, $status = 200)
    {
        return Response::jsonResponse($data, $status);
    }

    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }

    protected function withErrors($errors)
    {
        $session = new Session();
        $session->flash('errors', $errors);
        return $this;
    }
}
