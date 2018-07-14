<?php
namespace app\core\TextUtil\Commands;

use app\core\TextUtil\UsersTextUtil;

abstract class CommandAbstract{

    /**
     * @var UsersTextUtil Объект класса UsersTextUtil
     */
    private $_userTextUtil;

    private $_result;

    /**
     * CommandAbstract constructor.
     * @param UsersTextUtil $textUtil
     * @param array $arguments
     */
    public function __construct(UsersTextUtil $textUtil, $arguments = [])
    {
        $this->_userTextUtil = $textUtil;
    }

    /**
     * @return mixed
     */
    abstract public function process();

    /**
     * @return UsersTextUtil
     */
    public function textUtil()
    {
        return $this->_userTextUtil;
    }

    public function setResult($data)
    {
        $this->_result = $data;
        return $this;
    }

    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Список файлов принадлежащих пользователю
     *
     * @param $userId
     * @return array
     */
    public function getUserFiles($userId)
    {
        return glob(rtrim($this->textUtil()->getTextsFolderPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $userId . '-*.txt');
    }
}