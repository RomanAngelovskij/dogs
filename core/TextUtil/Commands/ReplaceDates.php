<?php
namespace app\core\TextUtil\Commands;

use yii\console\widgets\Table;

/**
 * поместить тексты пользователей в папку ./output_texts, заменив в каждом тексте даты в формате dd/mm/yy на даты
 * в формате mm-dd-yyyy. Вывести на экран количество совершенных для каждого пользователя замен вместе с
 * именем пользователя.
 *
 * Class ReplaceDates
 * @package app\core\TextUtil\Commands
 */
class ReplaceDates extends CommandAbstract {


    /**
     * @var array Кол-во замен даты для каждого пользователя
     */
    private $_replacementCount = [];

    public function process()
    {
        $usersList = $this->textUtil()->getUsersListFromCSV();

        //Обходим всех пользователей
        foreach ($usersList as $userId => $userName){
            $this->_replacementCount[$userId] = 0;
            $userFiles = $this->getUserFiles($userId);
            if (!empty($userFiles)){
                // Обходим все файлы пользователя
                foreach ($userFiles as $file){
                    $this->_processDatesInFile($userId, $file);
                }
            }
        }

        $tableBody = [];
        foreach ($this->_replacementCount as $userId => $replacementCount){
            $tableBody[] = [$userId, $usersList[$userId], $replacementCount];
        }

        return $this->setResult(Table::widget([
            'headers' => ['Id', 'User name', 'Replacement count'],
            'rows' => $tableBody,
        ]));
    }

    /**
     * Валидация даты
     *
     * @param $date
     * @param string $format
     * @return bool
     */
    private function _validateDate($date, $format = 'd/m/Y')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Поиск, замена и подсчет кол-ва замен дат
     *
     * @param $userId
     * @param $file
     * @param bool $saveToFile
     * @return bool
     */
    private function _processDatesInFile($userId, $file, $saveToFile = false)
    {
        $text = file_get_contents($file);
        if (!empty($file)){
            //TODO: доделат паттерн
            preg_match_all('|\s+(\d{2}/\d{2}/\d{4})|', $text, $matchDates);
            if (!empty($matchDates)){
                foreach ($matchDates[1] as $matchItem){
                    if ($this->_validateDate($matchItem) === true){
                        $newText = preg_replace(
                                '|' . $matchItem . '|',
                                date('m-d-Y', strtotime($matchItem)),
                                $text
                        );
                        if ($saveToFile === true){
                            file_put_contents($file, $newText);
                        }
                        $this->_replacementCount[$userId]++;
                    }
                }
            }
        }

        return true;
    }

}