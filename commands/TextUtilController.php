<?php
namespace app\commands;

use app\core\TextUtil\UsersTextUtil;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class TextUtilController extends Controller{

    /**
     * @var array Возможные разделители для CSV файлов. Массив для преобразования названия разделителя
     *              в символ
     */
    public $allowedSeparators = [
        'comma' => ',',
        'semicolon' => ';'
    ];

    public function actionUsers()
    {
        $arguments = func_get_args();
        if (count($arguments) < 2){
            $this->_printError('A few arguments. Format: text-util/users {separator} ('
                . implode(', ', array_keys($this->allowedSeparators)). ') replaceDates ('
                . implode(', ', $this->getCommands()) . ')');
        }
        $separator = array_shift($arguments);
        $command =  array_shift($arguments);

        if (!isset($this->allowedSeparators[$separator])){
            $this->_printError('Incorrect CSV separator - ' . $separator);
        }

        try {
            $textUtil = new UsersTextUtil(
                Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'csv/people.csv',
                Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR . 'texts');

            $command = 'command' . ucfirst($command);
            echo $textUtil
                ->setCsvSeparator($this->allowedSeparators[$separator])
                ->{$command}($arguments);

        } catch (\Exception $e) {
            $this->_printError($e->getMessage() . '. File: ' . $e->getFile() . ' at line ' . $e->getLine());
        }
    }

    public function getCommands()
    {
        return ['countAverageLineCount', 'replaceDates'];
    }

    private function _printError($message)
    {
        $this->stderr('Error: ', Console::FG_RED);
        $this->stdout($message . "\n");
        Yii::$app->end();
    }
}
