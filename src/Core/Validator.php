<?php
/**
 * Created by PhpStorm.
 * User: jhouvion
 * Date: 03/01/18
 * Time: 11:28
 */

namespace FrenchFrogs\Core;


use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationRuleParser;

class Validator
{
    /**
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $customAttributes = [];


    /**
     *
     *
     * @param $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @param Rule[]|string|array $rules
     * @param string $index
     * @return $this
     */
    public function setRulesForIndex(string $index, $rules)
    {
        // Explode des rules
        $response = (new ValidationRuleParser([]))
            ->explode([$rules]);

        $this->rules[$index] = array_get($response->rules, 0, []);
        return $this;
    }

    /**
     *
     * @param $index
     * @param $rule
     * @return bool
     */
    public function hasRule($index, $rule)
    {
        return isset(array_get($this->rules, $index, [])[$rule]);
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function addMessage($index, $message)
    {
        $this->messages[$index] = $message;
    }

    /**
     * @param null $index
     * @return bool
     */
    public function errors($index = null)
    {
        if ($this->validator) {
            $errors = $this->validator->errors()->toArray();
            return is_null($index) ? $errors : array_get($errors, $index, []);
        }

        return [];
    }


    /**
     * @param null $index
     * @return bool
     */
    public function fails(...$args)
    {
        $this->make(...$args)->fails();
    }

    /**
     * @param array ...$args
     * @return array
     * @throws \Throwable
     */
    public function valid(...$args)
    {
        return $this->make(...$args)->valid();
    }


    /**
     * @param array ...$args
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function validate(...$args)
    {
        return $this->make(...$args)->validate();
    }

    /**
     *
     *
     * @param null $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Validation\Validator
     * @throws \Throwable
     */
    public function make($data = null, $rules = [], $messages = [], $customAttributes = [])
    {
        // DATA
        // Si vide on prend la requete en cours
        if (is_null($data)) {
            $data = request();
        }

        // formatage en table si c'est une requete
        if ($data instanceof Request) {
            $data = $data->all();
        }

        // On verifie que les data sont bien la
        throw_if(!is_array($data), 'Les données transmises ne sont pas au bon format');


        // RULES
        throw_if(!is_array($rules), 'Les rules transmises ne sont pas au bon format');

        // ON merge les rules si jamais on en a des présetté
        if (!empty($this->rules)) {
            $rules = array_merge($this->rules, $rules);
        }

        // MESSAGES
        throw_if(!is_array($messages), 'Les messages transmises ne sont pas au bon format');

        // ON merge les rules si jamais on en a des présetté
        if (!empty($this->messages)) {
            $messages = array_merge($this->messages, $messages);
        }

        // Creation du validator
        $this->validator = \Validator::make($data, $rules, $messages, $customAttributes);

        return $this->validator;
    }
}