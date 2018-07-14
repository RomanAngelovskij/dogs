<?php
namespace app\core\TextUtil;

/**
 * Class UsersTextUtil
 * @package core\TextUtil
 */
class UsersTextUtil{
    /**
     * @var string Разделитель для CSV
     */
    protected $_csvSeparator = ';';

    /**
     * @var string Путь к CVS файлу со список юзеров
     */
    protected $_csvFilePath;

    /**
     * @var string Путь к папке с текстами
     */
    protected $_textsFolderPath;

    /**
     * UsersTextUtil constructor.
     * @param string $csvFilePath
     * @param string $textsFolderPath
     * @throws \Exception
     */
    public function __construct($csvFilePath, $textsFolderPath)
    {
        if (!file_exists($csvFilePath) || !is_readable($csvFilePath)){
            throw new \Exception('CSV file "' . $csvFilePath . '" doesn\'t exists');
        }

        if (!file_exists($textsFolderPath) || !is_readable($textsFolderPath)){
            throw new \Exception('Folder "' . $textsFolderPath . '" for text doesn\'t exists');
        }

        $this->_csvFilePath = $csvFilePath;
        $this->_textsFolderPath = $textsFolderPath;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        // Если вызываемый метод содержит вначале слово command, его надо обрабатывать как команду
        if (strpos($name, 'command', 0) === 0){
            $command = preg_replace('|^command|', '', $name);
            $class = 'app\core\TextUtil\Commands\\' . $command;
            //Проверяем существование класса
            if (!class_exists($class)){
                throw new \Exception(lcfirst($command) . ' doesn\'t exist');
            }

            //Проверяем является ли класс командой (наследуюет соответствующий абстрактный класс)
            $reflection = new \ReflectionClass($class);
            $parentClass = $reflection->getParentClass();
            if ($parentClass === false || $parentClass->name != 'app\core\TextUtil\Commands\CommandAbstract'){
                throw new \Exception('Command class must extends CommandAbstract class');
            }

            $commandClass = new $class($this, $arguments);
            if ($commandClass->process()){
                return $commandClass->getResult();
            }

            return false;
        }

        throw new \Exception('Method "' . $name . '" doesn\'t exists');
    }

    /**
     * @return string
     */
    public function getTextsFolderPath()
    {
        return $this->_textsFolderPath;
    }

    /**
     * Установка разделителя для CSV
     *
     * @param $separator
     * @return $this
     * @throws \Exception
     */
    public function setCsvSeparator($separator)
    {
        if (empty($separator)){
            throw new \Exception('Empty CSV separator');
        };

        $this->_csvSeparator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvSeparator()
    {
        return $this->_csvSeparator;
    }

    /**
     * Формирование списка юзеров из CSV
     * @return array
     * @throws \Exception
     */
    public function getUsersListFromCSV()
    {
        $users = [];
        $handle = fopen($this->_csvFilePath, 'r');
        if ($handle !== false){
            while (($row = fgetcsv($handle, 500, $this->_csvSeparator)) !== false) {
                if (isset($row[0]) && isset($row[1])) {
                    $users[$row[0]] = $row[1];
                }
            }
            fclose($handle);
        } else {
            fclose($handle);
            throw new \Exception('Can\'t read CSV file');
        }

        return $users;
    }

}