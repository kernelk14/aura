<?php

use AuraCore\Model;

class UserModel extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
    protected $casts = [
        'id' => 'int',
        'is_active' => 'bool',
    ];
}
