<?php

namespace app\models\forms;

use app\models\user\User;
use app\services\UserService;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property null|User $user
 */
class LoginForm extends Model implements Form
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;
    private readonly UserService $userService;

    public function __construct(UserService $userService, $config = [])
    {
        $this->userService = $userService;

        parent::__construct($config);
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('app', 'Username'),
            'password' => \Yii::t('app', 'Password'),
            'rememberMe' => \Yii::t('app', 'Remember me'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validatePassword(string $attribute): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Finds user by [[username]].
     */
    public function getUser(): ?User
    {
        if (false === $this->_user) {
            $this->_user = $this->userService->findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return $this->userService->login($this->getUser(), $this->rememberMe);
        }

        return false;
    }

    public function getSafeAttributes(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'rememberMe' => $this->rememberMe,
        ];
    }
}
