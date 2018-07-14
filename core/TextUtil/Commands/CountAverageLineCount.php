<?php
namespace app\core\TextUtil\Commands;

use yii\console\widgets\Table;

/**
 * для каждого пользователя посчитать среднее количество строк в его текстовых файлах и вывести на экран
 * вместе с именем пользователя
 *
 * Class CountAverageLineCount
 * @package app\core\TextUtil\Commands
 */
class CountAverageLineCount extends CommandAbstract{

    public function process()
    {
        $result = [];
        $usersList = $this->textUtil()->getUsersListFromCSV();

        //Обходим всех пользователей
        foreach ($usersList as $userId => $userName){
            $linesCount = 0;
            $userFiles = $this->getUserFiles($userId);
            if (!empty($userFiles)){
                // Обходим все файлы пользователя и считаем строки
                foreach ($userFiles as $file){
                    $handle = fopen($file, "r");
                    while(!feof($handle)){
                        $line = fgets($handle);
                        $linesCount++;
                    }
                    fclose($handle);
                }
            }

            $result[$userId] = count($userFiles) > 0 ? $linesCount/count($userFiles) : 0;
        }

        return $this->tableResult($result);
    }

    /**
     * Отрисовка таблицы результатоы
     *
     * @param $result
     * @return $this
     * @throws \Exception
     */
    private function tableResult($result)
    {
        if (!is_array($result)){
            throw new \Exception('$result must be an array');
        }

        $tableBody = [];
        foreach ($result as $userId => $averageLinesCount){
            $tableBody[] = [$userId, $usersList[$userId], $averageLinesCount];
        }

        return $this->setResult(Table::widget([
            'headers' => ['Id', 'User name', 'Average lines count'],
            'rows' => $tableBody,
        ]));
    }
}